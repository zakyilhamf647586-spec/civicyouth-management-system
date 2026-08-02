<?php

namespace App\Commands;

use App\Libraries\BackupRecoveryService;
use App\Libraries\CmsAuditService;
use App\Libraries\SystemMonitoringService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SystemMaintenance extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'system:maintenance';
    protected $description =
        'Backup seperlunya, verifikasi, retensi, dan snapshot kesehatan.';
    protected $usage =
        'system:maintenance [--max-age-hours 24] [--force-backup]';

    protected $options = [
        '--max-age-hours' =>
            'Buat backup baru jika usia backup terakhir mencapai batas ini.',
        '--force-backup' => 'Selalu buat backup baru.',
    ];

    public function run(array $params)
    {
        $maximumAge = $this->maximumAgeHours();

        if ($maximumAge === null) {
            CLI::error('--max-age-hours harus berupa bilangan bulat 1-720.');
            return EXIT_ERROR;
        }

        $backupService = new BackupRecoveryService();
        $forceBackup = CLI::getOption('force-backup') !== null;

        try {
            $backups = $backupService->listBackups();
            $latest = $backups[0] ?? null;
            $latestAge = $backupService->latestBackupAgeHours();
            $createBackup = $forceBackup
                || !is_array($latest)
                || $latestAge === null
                || $latestAge >= $maximumAge;

            if ($createBackup) {
                $latest = $backupService->create([
                    'tag' => 'scheduled-maintenance',
                ]);
                $latestAge = 0;
            }

            $archiveName = trim((string) (
                is_array($latest)
                    ? ($latest['archive_name'] ?? '')
                    : ''
            ));

            if ($archiveName === '') {
                throw new \RuntimeException(
                    'Archive terbaru tidak dapat ditentukan.'
                );
            }

            $backupService->verify($archiveName);

            $retention = $backupService->prune(false);
            $health = (new SystemMonitoringService())->snapshot(
                'scheduled-maintenance'
            );

            (new CmsAuditService())->record([
                'module' => 'system',
                'event_type' => 'system.maintenance_completed',
                'severity' => $health['status'] === 'critical'
                    ? 'warning'
                    : 'info',
                'subject_type' => 'maintenance',
                'subject_key' => $archiveName,
                'subject_label' => 'Scheduled Maintenance',
                'summary' => 'Maintenance terjadwal selesai.',
                'metadata' => [
                    'archive' => $archiveName,
                    'backup_created' => $createBackup,
                    'backup_verified_now' => true,
                    'backup_age_hours' => $latestAge,
                    'retention_deleted' => count(
                        $retention['deleted'] ?? []
                    ),
                    'health_status' => $health['status'] ?? null,
                    'health_score' => $health['score'] ?? null,
                ],
                'actor_type' => 'system',
                'actor_name' => 'Maintenance Scheduler',
            ]);

            CLI::write('Maintenance GARDA 01 selesai.', 'green');
            CLI::write(
                'Backup : ' . ($createBackup
                    ? 'baru dibuat'
                    : 'backup terbaru masih segar')
            );
            CLI::write('Archive: ' . $archiveName);
            CLI::write('Verifikasi: dijalankan dan lulus');
            CLI::write(
                'Retensi: ' . count($retention['deleted'] ?? [])
                . ' archive lama dibersihkan'
            );
            CLI::write(
                'Kesehatan: ' . strtoupper(
                    (string) ($health['status'] ?? 'unknown')
                ) . ' - ' . (int) ($health['score'] ?? 0) . '/100'
            );

            return ($health['status'] ?? '') === 'critical'
                ? EXIT_ERROR
                : EXIT_SUCCESS;
        } catch (\Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }

    private function maximumAgeHours(): ?int
    {
        $raw = CLI::getOption('max-age-hours');
        $value = $raw === null || $raw === false || $raw === ''
            ? 24
            : filter_var($raw, FILTER_VALIDATE_INT);

        if ($value === false || $value < 1 || $value > 720) {
            return null;
        }

        return (int) $value;
    }
}
