<?php

namespace App\Libraries;

use App\Models\SystemHealthIncidentModel;
use App\Models\SystemHealthSnapshotModel;
use Config\SystemMonitoring;
use RuntimeException;
use Throwable;

class SystemMonitoringService
{
    private const MIN_AVAILABILITY_SNAPSHOTS = 2;

    protected SystemMonitoring $config;
    protected SystemHealthSnapshotModel $snapshotModel;
    protected SystemHealthIncidentModel $incidentModel;

    /** @var list<array<string, mixed>> */
    protected array $checks = [];

    public function __construct()
    {
        $this->config = config(SystemMonitoring::class);
        $this->snapshotModel = new SystemHealthSnapshotModel();
        $this->incidentModel = new SystemHealthIncidentModel();
    }

    /** @return array<string, mixed> */
    public function report(): array
    {
        $this->checks = [];

        $this->checkApplicationRuntime();
        $this->checkDatabase();
        $this->checkMigrations();
        $this->checkFilesystem();
        $this->checkDiskSpace();
        $this->checkBackupRecovery();
        $this->checkAuditTrail();
        $this->checkApplicationLogs();
        $this->checkBackupLock();

        $counts = [
            'pass' => 0,
            'warning' => 0,
            'critical' => 0,
        ];

        $points = 0.0;

        foreach ($this->checks as $check) {
            $status = (string) $check['status'];
            $counts[$status]++;

            $points += match ($status) {
                'pass' => 1.0,
                'warning' => 0.55,
                default => 0.0,
            };
        }

        $total = count($this->checks);
        $score = $total > 0
            ? (int) round(($points / $total) * 100)
            : 0;

        $overall = $counts['critical'] > 0
            ? 'critical'
            : ($counts['warning'] > 0 ? 'degraded' : 'healthy');

        $metrics = $this->metricsFromChecks();
        $baseUrl = trim((string) config('App')->baseURL);
        $host = strtolower((string) (parse_url($baseUrl, PHP_URL_HOST) ?? ''));
        $isLocal = ENVIRONMENT !== 'production'
            || $host === ''
            || in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.local');

        return [
            'generated_at' => date(DATE_ATOM),
            'status' => $overall,
            'score' => $score,
            'counts' => $counts,
            'checks_total' => $total,
            'checks' => $this->checks,
            'metrics' => $metrics,
            'environment' => [
                'name' => ENVIRONMENT,
                'base_url' => $baseUrl,
                'is_local' => $isLocal,
                'php_version' => PHP_VERSION,
                'release' => trim((string) env('deployment.release', '')),
                'commit' => substr(
                    trim((string) env('deployment.commit', '')),
                    0,
                    12
                ),
                'hostname' => gethostname() ?: 'unknown',
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function snapshot(string $source = 'scheduler'): array
    {
        if (!$this->monitoringReady()) {
            throw new RuntimeException(
                'Tabel monitoring belum tersedia. Jalankan migration terbaru.'
            );
        }

        $report = $this->report();
        $payload = json_encode(
            $report,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        $snapshotId = $this->snapshotModel->insert([
            'overall_status' => $report['status'],
            'score' => $report['score'],
            'source' => $this->safeKey($source, 30),
            'checks_total' => $report['checks_total'],
            'checks_pass' => $report['counts']['pass'],
            'checks_warning' => $report['counts']['warning'],
            'checks_critical' => $report['counts']['critical'],
            'database_latency_ms' => $report['metrics']['database_latency_ms'],
            'disk_free_bytes' => $report['metrics']['disk_free_bytes'],
            'memory_usage_bytes' => $report['metrics']['memory_usage_bytes'],
            'backup_age_hours' => $report['metrics']['backup_age_hours'],
            'payload_json' => $payload !== false ? $payload : null,
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        if (!$snapshotId) {
            throw new RuntimeException('Snapshot kesehatan gagal disimpan.');
        }

        $this->reconcileIncidents((int) $snapshotId, $report['checks']);

        $report['snapshot_id'] = (int) $snapshotId;

        return $report;
    }

    /** @return array<string, mixed> */
    public function snapshotIfDue(
        string $source = 'dashboard',
        ?int $minimumSeconds = null
    ): array {
        if (!$this->monitoringReady()) {
            return $this->report();
        }

        $minimumSeconds ??= $this->integerEnv(
            'monitoring.snapshotIntervalSeconds',
            $this->config->snapshotIntervalSeconds
        );

        $latest = $this->snapshotModel
            ->orderBy('id', 'DESC')
            ->first();

        if (is_array($latest)) {
            $timestamp = strtotime((string) ($latest['created_at'] ?? ''));

            if (
                $timestamp !== false
                && (time() - $timestamp) < $minimumSeconds
            ) {
                return $this->report();
            }
        }

        return $this->snapshot($source);
    }

    /** @return list<array<string, mixed>> */
    public function recentSnapshots(int $limit = 48): array
    {
        if (!$this->monitoringReady()) {
            return [];
        }

        return $this->snapshotModel
            ->orderBy('id', 'DESC')
            ->findAll(max(1, min(240, $limit)));
    }

    /** @return list<array<string, mixed>> */
    public function recentIncidents(int $limit = 20): array
    {
        if (!$this->monitoringReady()) {
            return [];
        }

        return $this->incidentModel
            ->orderBy(
                "CASE WHEN status = 'open' THEN 0 ELSE 1 END",
                'ASC',
                false
            )
            ->orderBy('last_seen_at', 'DESC')
            ->findAll(max(1, min(100, $limit)));
    }

    /** @return array<string, mixed> */
    public function statistics(): array
    {
        if (!$this->monitoringReady()) {
            return [
                'snapshots_24h' => 0,
                'healthy_24h' => 0,
                'degraded_24h' => 0,
                'critical_24h' => 0,
                'available_24h' => 0,
                'unavailable_24h' => 0,
                'availability_percent' => null,
                'availability_stage' => 'empty',
                'availability_minimum_snapshots' => self::MIN_AVAILABILITY_SNAPSHOTS,
                'open_incidents' => 0,
                'unacknowledged_incidents' => 0,
                'last_snapshot_at' => null,
            ];
        }

        $db = db_connect();
        $since = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $total = $db->table('system_health_snapshots')
            ->where('created_at >=', $since)
            ->countAllResults();

        $healthy = $db->table('system_health_snapshots')
            ->where('created_at >=', $since)
            ->where('overall_status', 'healthy')
            ->countAllResults();

        $degraded = $db->table('system_health_snapshots')
            ->where('created_at >=', $since)
            ->where('overall_status', 'degraded')
            ->countAllResults();

        $critical = $db->table('system_health_snapshots')
            ->where('created_at >=', $since)
            ->where('overall_status', 'critical')
            ->countAllResults();

        // A degraded snapshot means the application is still serving
        // requests, albeit with warnings. Availability therefore measures
        // non-critical observations, while the health score communicates
        // service quality separately.
        $available = $healthy + $degraded;
        $availabilityStage = $total === 0
            ? 'empty'
            : (
                $total < self::MIN_AVAILABILITY_SNAPSHOTS
                    ? 'initial'
                    : 'measured'
            );

        $latest = $this->snapshotModel
            ->orderBy('id', 'DESC')
            ->first();

        $open = $this->incidentModel
            ->where('status', 'open')
            ->countAllResults();

        $unacknowledged = $this->incidentModel
            ->where('status', 'open')
            ->where('acknowledged_at', null)
            ->countAllResults();

        return [
            'snapshots_24h' => $total,
            'healthy_24h' => $healthy,
            'degraded_24h' => $degraded,
            'critical_24h' => $critical,
            'available_24h' => $available,
            'unavailable_24h' => $critical,
            'availability_percent' => $availabilityStage === 'measured'
                ? round(($available / $total) * 100, 2)
                : null,
            'availability_stage' => $availabilityStage,
            'availability_minimum_snapshots' => self::MIN_AVAILABILITY_SNAPSHOTS,
            'open_incidents' => $open,
            'unacknowledged_incidents' => $unacknowledged,
            'last_snapshot_at' => $latest['created_at'] ?? null,
        ];
    }

    public function acknowledgeIncident(int $incidentId, int $userId): bool
    {
        if (!$this->monitoringReady()) {
            return false;
        }

        $incident = $this->incidentModel->find($incidentId);

        if (!is_array($incident) || ($incident['status'] ?? '') !== 'open') {
            return false;
        }

        return $this->incidentModel->update($incidentId, [
            'acknowledged_at' => date('Y-m-d H:i:s'),
            'acknowledged_by' => $userId > 0 ? $userId : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return array{deleted_snapshots:int,deleted_incidents:int} */
    public function prune(?int $days = null): array
    {
        if (!$this->monitoringReady()) {
            return [
                'deleted_snapshots' => 0,
                'deleted_incidents' => 0,
            ];
        }

        $days ??= $this->integerEnv(
            'monitoring.snapshotRetentionDays',
            $this->config->snapshotRetentionDays
        );

        $days = max(7, min(365, $days));
        $snapshotCutoff = date(
            'Y-m-d H:i:s',
            strtotime('-' . $days . ' days')
        );
        $incidentCutoff = date(
            'Y-m-d H:i:s',
            strtotime('-' . max(30, $days * 2) . ' days')
        );

        $db = db_connect();

        $deletedSnapshots = $db->table('system_health_snapshots')
            ->where('created_at <', $snapshotCutoff)
            ->countAllResults();

        $deletedIncidents = $db->table('system_health_incidents')
            ->where('status', 'resolved')
            ->where('resolved_at <', $incidentCutoff)
            ->countAllResults();

        if ($deletedSnapshots > 0) {
            $db->table('system_health_snapshots')
                ->where('created_at <', $snapshotCutoff)
                ->delete();
        }

        if ($deletedIncidents > 0) {
            $db->table('system_health_incidents')
                ->where('status', 'resolved')
                ->where('resolved_at <', $incidentCutoff)
                ->delete();
        }

        return [
            'deleted_snapshots' => $deletedSnapshots,
            'deleted_incidents' => $deletedIncidents,
        ];
    }

    public function monitoringReady(): bool
    {
        try {
            $db = db_connect();

            return $db->tableExists('system_health_snapshots')
                && $db->tableExists('system_health_incidents');
        } catch (Throwable $exception) {
            return false;
        }
    }

    protected function checkApplicationRuntime(): void
    {
        $this->addCheck(
            'application.runtime',
            'Aplikasi PHP',
            'pass',
            'Proses aplikasi aktif dan dapat menjalankan pemeriksaan.',
            'Tidak ada tindakan.',
            false,
            [
                'php_version' => PHP_VERSION,
                'memory_usage_bytes' => memory_get_usage(true),
                'memory_peak_bytes' => memory_get_peak_usage(true),
            ]
        );
    }

    protected function checkDatabase(): void
    {
        try {
            $started = microtime(true);
            $db = db_connect();
            $db->initialize();
            $db->query('SELECT 1');
            $latency = round((microtime(true) - $started) * 1000, 2);

            $warning = $this->integerEnv(
                'monitoring.databaseWarningLatencyMs',
                $this->config->databaseWarningLatencyMs
            );
            $critical = $this->integerEnv(
                'monitoring.databaseCriticalLatencyMs',
                $this->config->databaseCriticalLatencyMs
            );

            $status = $latency >= $critical
                ? 'critical'
                : ($latency >= $warning ? 'warning' : 'pass');

            $this->addCheck(
                'database.connection',
                'Koneksi Database',
                $status,
                'Query database selesai dalam ' . number_format($latency, 2) . ' ms.',
                $status === 'pass'
                    ? 'Tidak ada tindakan.'
                    : 'Periksa beban MySQL, koneksi, indeks, dan sumber daya server.',
                true,
                ['latency_ms' => $latency]
            );
        } catch (Throwable $exception) {
            $this->addCheck(
                'database.connection',
                'Koneksi Database',
                'critical',
                'Database tidak dapat diakses.',
                'Periksa MySQL, kredensial, jaringan, dan kapasitas server.',
                true
            );
        }
    }

    protected function checkMigrations(): void
    {
        try {
            $db = db_connect();
            $table = (string) (config('Migrations')->table ?? 'migrations');

            if (!$db->tableExists($table)) {
                $this->addCheck(
                    'database.migrations',
                    'Status Migration',
                    'critical',
                    'Tabel migration tidak ditemukan.',
                    'Jalankan php spark migrate.',
                    true
                );
                return;
            }

            $fileVersions = [];
            $files = glob(APPPATH . 'Database/Migrations/*.php');

            if (is_array($files)) {
                foreach ($files as $file) {
                    if (preg_match(
                        '/^(\d{4}-\d{2}-\d{2}-\d{6})_/',
                        basename($file),
                        $matches
                    ) === 1) {
                        $fileVersions[] = $matches[1];
                    }
                }
            }

            $rows = $db->table($table)
                ->select('version')
                ->get()
                ->getResultArray();

            $migrated = array_map(
                static fn (array $row): string =>
                    (string) ($row['version'] ?? ''),
                $rows
            );

            $pending = array_values(array_diff(
                array_unique($fileVersions),
                array_unique($migrated)
            ));

            $this->addCheck(
                'database.migrations',
                'Status Migration',
                $pending === [] ? 'pass' : 'critical',
                $pending === []
                    ? 'Tidak ada migration tertunda.'
                    : count($pending) . ' migration belum dijalankan.',
                $pending === []
                    ? 'Tidak ada tindakan.'
                    : 'Jalankan php spark migrate sebelum membuka trafik.',
                true,
                ['pending_count' => count($pending)]
            );
        } catch (Throwable $exception) {
            $this->addCheck(
                'database.migrations',
                'Status Migration',
                'warning',
                'Status migration belum dapat diperiksa.',
                'Periksa koneksi database dan konfigurasi migration.',
                true
            );
        }
    }

    protected function checkFilesystem(): void
    {
        $failed = [];

        foreach ($this->config->writableDirectories as $directory) {
            if (!is_dir($directory) || !is_writable($directory)) {
                $failed[] = basename(rtrim($directory, DIRECTORY_SEPARATOR));
            }
        }

        $this->addCheck(
            'filesystem.writable',
            'Folder Operasional',
            $failed === [] ? 'pass' : 'critical',
            $failed === []
                ? 'Seluruh folder operasional tersedia dan writable.'
                : 'Folder bermasalah: ' . implode(', ', array_unique($failed)) . '.',
            $failed === []
                ? 'Tidak ada tindakan.'
                : 'Perbaiki folder dan permission 755/775 sesuai user web server.',
            true,
            ['failed_count' => count($failed)]
        );
    }

    protected function checkDiskSpace(): void
    {
        $free = @disk_free_space(WRITEPATH);

        if ($free === false) {
            $this->addCheck(
                'filesystem.disk',
                'Ruang Penyimpanan',
                'warning',
                'Ruang disk tidak dapat dihitung.',
                'Periksa permission dan monitoring storage server.',
                true
            );
            return;
        }

        $warning = $this->integerEnv(
            'monitoring.diskWarningBytes',
            $this->config->diskWarningBytes
        );
        $critical = $this->integerEnv(
            'monitoring.diskCriticalBytes',
            $this->config->diskCriticalBytes
        );

        $status = $free <= $critical
            ? 'critical'
            : ($free <= $warning ? 'warning' : 'pass');

        $this->addCheck(
            'filesystem.disk',
            'Ruang Penyimpanan',
            $status,
            'Ruang kosong: ' . $this->formatBytes((int) $free) . '.',
            $status === 'pass'
                ? 'Tidak ada tindakan.'
                : 'Bersihkan log, cache, backup lama, atau tambah kapasitas.',
            true,
            ['free_bytes' => (int) $free]
        );
    }

    protected function checkBackupRecovery(): void
    {
        try {
            $service = new BackupRecoveryService();
            $status = $service->status();
            $latest = $status['latest'] ?? null;

            if (!is_array($latest)) {
                $this->addCheck(
                    'backup.latest',
                    'Backup Terbaru',
                    ENVIRONMENT === 'production' ? 'critical' : 'warning',
                    'Belum ada backup aplikasi.',
                    'Jalankan backup:create, verifikasi, dan aktifkan jadwal harian.',
                    true
                );
                return;
            }

            $age = $service->latestBackupAgeHours();
            $warning = $this->integerEnv(
                'monitoring.backupWarningAgeHours',
                $this->config->backupWarningAgeHours
            );
            $critical = $this->integerEnv(
                'monitoring.backupCriticalAgeHours',
                $this->config->backupCriticalAgeHours
            );

            $verified = ($latest['verification_status'] ?? '') === 'verified';
            $ageStatus = $age !== null && $age <= $warning
                ? 'pass'
                : ($age !== null && $age <= $critical ? 'warning' : 'critical');

            $statusValue = !$verified
                ? (ENVIRONMENT === 'production' ? 'critical' : 'warning')
                : $ageStatus;

            $message = 'Usia backup: '
                . ($age !== null ? $age . ' jam' : 'tidak diketahui')
                . '; integritas: '
                . ($verified ? 'terverifikasi' : 'belum terverifikasi')
                . '.';

            $this->addCheck(
                'backup.latest',
                'Backup Terbaru',
                $statusValue,
                $message,
                $statusValue === 'pass'
                    ? 'Pertahankan jadwal dan salinan off-site.'
                    : 'Buat serta verifikasi backup baru, lalu periksa scheduler.',
                true,
                [
                    'age_hours' => $age,
                    'verified' => $verified,
                    'archive_size' => (int) ($latest['archive_size'] ?? 0),
                ]
            );
        } catch (Throwable $exception) {
            $this->addCheck(
                'backup.latest',
                'Backup Terbaru',
                ENVIRONMENT === 'production' ? 'critical' : 'warning',
                'Layanan backup belum dapat diperiksa.',
                'Periksa extension ZIP, konfigurasi backup, dan permission.',
                true
            );
        }
    }

    protected function checkAuditTrail(): void
    {
        try {
            $db = db_connect();

            if (!$db->tableExists('cms_audit_logs')) {
                $this->addCheck(
                    'audit.cms',
                    'Audit Aktivitas CMS',
                    'warning',
                    'Tabel audit CMS tidak tersedia.',
                    'Jalankan migration Fase 3D.',
                    true
                );
                return;
            }

            $since = date(
                'Y-m-d H:i:s',
                strtotime('-' . $this->config->recentSecurityWindowHours . ' hours')
            );

            $securityEvents = $db->table('cms_audit_logs')
                ->where('created_at >=', $since)
                ->where('severity', 'security')
                ->countAllResults();

            $this->addCheck(
                'audit.cms',
                'Audit Aktivitas CMS',
                $securityEvents > 20 ? 'warning' : 'pass',
                $securityEvents . ' event keamanan tercatat dalam '
                    . $this->config->recentSecurityWindowHours . ' jam.',
                $securityEvents > 20
                    ? 'Tinjau Audit CMS dan pola akses yang ditolak.'
                    : 'Tidak ada tindakan.',
                true,
                ['security_events' => $securityEvents]
            );
        } catch (Throwable $exception) {
            $this->addCheck(
                'audit.cms',
                'Audit Aktivitas CMS',
                'warning',
                'Audit CMS belum dapat diperiksa.',
                'Periksa database dan model audit.',
                true
            );
        }
    }

    protected function checkApplicationLogs(): void
    {
        $summary = $this->scanRecentLogs();
        $critical = (int) $summary['critical'];
        $errors = (int) $summary['error'];
        $warnings = (int) $summary['warning'];

        // Log entries are retrospective evidence, not proof that the current
        // request path is unavailable. Keep them visible as an incident and
        // warning without making /health/ready return 503 indefinitely after
        // the underlying problem has already recovered.
        $status = $critical > 0 || $errors > 0 || $warnings > 20
            ? 'warning'
            : 'pass';

        $this->addCheck(
            'logs.application',
            'Log Aplikasi Terbaru',
            $status,
            'CRITICAL: ' . $critical
                . '; ERROR: ' . $errors
                . '; WARNING: ' . $warnings
                . '.',
            $status === 'pass'
                ? 'Tidak ada tindakan.'
                : 'Tinjau log dua hari terakhir dan selesaikan akar masalah; status kesiapan ditentukan oleh kondisi komponen saat ini.',
            true,
            $summary
        );
    }

    protected function checkBackupLock(): void
    {
        $lock = WRITEPATH . 'backup-operation.lock';

        if (!is_file($lock)) {
            $this->addCheck(
                'backup.lock',
                'Lock Operasi Backup',
                'pass',
                'Tidak ada operasi backup yang tertahan.',
                'Tidak ada tindakan.',
                true
            );
            return;
        }

        $ageMinutes = (int) floor(
            (time() - (filemtime($lock) ?: time())) / 60
        );
        $stale = $ageMinutes >= $this->config->staleBackupLockMinutes;

        $this->addCheck(
            'backup.lock',
            'Lock Operasi Backup',
            $stale ? 'warning' : 'pass',
            $stale
                ? 'Lock backup telah aktif selama ' . $ageMinutes . ' menit.'
                : 'Operasi backup sedang berjalan.',
            $stale
                ? 'Pastikan tidak ada proses aktif sebelum membersihkan lock secara manual.'
                : 'Tunggu operasi selesai.',
            true,
            ['age_minutes' => $ageMinutes]
        );
    }

    /** @return array<string, int|float|null> */
    protected function metricsFromChecks(): array
    {
        $metrics = [
            'database_latency_ms' => null,
            'disk_free_bytes' => null,
            'memory_usage_bytes' => memory_get_usage(true),
            'memory_peak_bytes' => memory_get_peak_usage(true),
            'backup_age_hours' => null,
            'log_critical_count' => 0,
            'log_error_count' => 0,
            'security_events_24h' => 0,
        ];

        foreach ($this->checks as $check) {
            $metadata = is_array($check['metadata'] ?? null)
                ? $check['metadata']
                : [];

            if ($check['component'] === 'database.connection') {
                $metrics['database_latency_ms'] = $metadata['latency_ms'] ?? null;
            } elseif ($check['component'] === 'filesystem.disk') {
                $metrics['disk_free_bytes'] = $metadata['free_bytes'] ?? null;
            } elseif ($check['component'] === 'backup.latest') {
                $metrics['backup_age_hours'] = $metadata['age_hours'] ?? null;
            } elseif ($check['component'] === 'logs.application') {
                $metrics['log_critical_count'] = (int) ($metadata['critical'] ?? 0);
                $metrics['log_error_count'] = (int) ($metadata['error'] ?? 0);
            } elseif ($check['component'] === 'audit.cms') {
                $metrics['security_events_24h'] = (int) (
                    $metadata['security_events'] ?? 0
                );
            }
        }

        return $metrics;
    }

    /** @return array{critical:int,error:int,warning:int,files_scanned:int,bytes_scanned:int} */
    protected function scanRecentLogs(): array
    {
        $result = [
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'files_scanned' => 0,
            'bytes_scanned' => 0,
        ];

        $files = glob(WRITEPATH . 'logs/log-*.log');

        if (!is_array($files) || $files === []) {
            return $result;
        }

        usort(
            $files,
            static fn (string $left, string $right): int =>
                (filemtime($right) ?: 0) <=> (filemtime($left) ?: 0)
        );

        $remaining = $this->integerEnv(
            'monitoring.logScanMaximumBytes',
            $this->config->logScanMaximumBytes
        );

        foreach ($files as $file) {
            if ($remaining <= 0 || (filemtime($file) ?: 0) < strtotime('-2 days')) {
                break;
            }

            $size = filesize($file) ?: 0;
            $readBytes = min($remaining, $size);
            $handle = fopen($file, 'rb');

            if (!is_resource($handle)) {
                continue;
            }

            if ($size > $readBytes) {
                fseek($handle, -$readBytes, SEEK_END);
            }

            $content = stream_get_contents($handle);
            fclose($handle);

            if (!is_string($content)) {
                continue;
            }

            $result['critical'] += preg_match_all(
                '/\bCRITICAL\b/i',
                $content
            );
            $result['error'] += preg_match_all(
                '/\bERROR\b/i',
                $content
            );
            $result['warning'] += preg_match_all(
                '/\bWARNING\b/i',
                $content
            );
            $result['files_scanned']++;
            $result['bytes_scanned'] += strlen($content);
            $remaining -= strlen($content);
        }

        return $result;
    }

    /** @param list<array<string, mixed>> $checks */
    protected function reconcileIncidents(int $snapshotId, array $checks): void
    {
        if (!$this->monitoringReady()) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($checks as $check) {
            if (empty($check['incident'])) {
                continue;
            }

            $component = (string) $check['component'];
            $open = $this->incidentModel
                ->where('component', $component)
                ->where('status', 'open')
                ->first();

            if (in_array($check['status'], ['warning', 'critical'], true)) {
                $metadata = json_encode(
                    $check['metadata'] ?? [],
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );

                if (is_array($open)) {
                    $this->incidentModel->update((int) $open['id'], [
                        'severity' => $check['status'],
                        'title' => $check['title'],
                        'message' => $check['message'],
                        'occurrence_count' => (int) ($open['occurrence_count'] ?? 0) + 1,
                        'last_seen_at' => $now,
                        'last_snapshot_id' => $snapshotId,
                        'metadata_json' => $metadata !== false ? $metadata : null,
                        'updated_at' => $now,
                    ]);
                } else {
                    $this->incidentModel->insert([
                        'component' => $component,
                        'status' => 'open',
                        'severity' => $check['status'],
                        'title' => $check['title'],
                        'message' => $check['message'],
                        'occurrence_count' => 1,
                        'first_seen_at' => $now,
                        'last_seen_at' => $now,
                        'last_snapshot_id' => $snapshotId,
                        'metadata_json' => $metadata !== false ? $metadata : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                continue;
            }

            if (is_array($open)) {
                $this->incidentModel->update((int) $open['id'], [
                    'status' => 'resolved',
                    'resolved_at' => $now,
                    'last_seen_at' => $now,
                    'last_snapshot_id' => $snapshotId,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /** @param array<string, mixed> $metadata */
    protected function addCheck(
        string $component,
        string $title,
        string $status,
        string $message,
        string $recommendation,
        bool $incident,
        array $metadata = []
    ): void {
        if (!in_array($status, ['pass', 'warning', 'critical'], true)) {
            throw new RuntimeException('Status monitoring tidak valid.');
        }

        $this->checks[] = [
            'component' => $component,
            'title' => $title,
            'status' => $status,
            'message' => $message,
            'recommendation' => $recommendation,
            'incident' => $incident,
            'metadata' => $metadata,
        ];
    }

    protected function safeKey(string $value, int $maximum): string
    {
        $clean = strtolower(trim(
            preg_replace('/[^a-zA-Z0-9_.-]+/', '-', $value) ?? ''
        ));

        return substr($clean !== '' ? $clean : 'system', 0, $maximum);
    }

    protected function integerEnv(string $key, int $default): int
    {
        return max(0, (int) env($key, $default));
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = max(0, $bytes);
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        return number_format($value, $unit === 0 ? 0 : 2)
            . ' ' . $units[$unit];
    }
}
