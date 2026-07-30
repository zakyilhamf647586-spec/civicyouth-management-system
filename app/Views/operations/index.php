<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$counts = $report['counts'];
$metrics = $report['metrics'];
$status = $report['status'];
$trend = array_reverse($snapshots);

$statusLabels = [
    'healthy' => 'Sehat',
    'degraded' => 'Perlu Perhatian',
    'critical' => 'Kritis',
];

$checkLabels = [
    'pass' => 'Normal',
    'warning' => 'Peringatan',
    'critical' => 'Kritis',
];

$formatBytes = static function ($bytes): string {
    if ($bytes === null) {
        return '-';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = max(0, (float) $bytes);
    $unit = 0;

    while ($value >= 1024 && $unit < count($units) - 1) {
        $value /= 1024;
        $unit++;
    }

    return number_format($value, $unit === 0 ? 0 : 2)
        . ' ' . $units[$unit];
};

$cssPath = FCPATH . 'assets/css/admin-operations-dashboard.css';
$cssVersion = is_file($cssPath) ? (string) filemtime($cssPath) : '1';
?>

<link
    rel="stylesheet"
    href="<?= base_url('assets/css/admin-operations-dashboard.css') ?>?v=<?= esc($cssVersion, 'attr') ?>"
>

<div class="operations-page">

<div class="page-header operations-header">
    <div>
        <span class="operations-eyebrow">Live Operations</span>
        <h2>Operational Dashboard</h2>
        <p>
            Satu pusat untuk kesehatan aplikasi, database, storage,
            backup, audit keamanan, log, insiden, dan riwayat snapshot.
        </p>
    </div>

    <div class="operations-actions">
        <?php if (auth_can('system.operations.manage')) : ?>
            <form action="<?= base_url('/system/operations/snapshot') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">
                    Ambil Snapshot
                </button>
            </form>
        <?php endif; ?>

        <a
            href="<?= base_url('/system/operations/export') ?>"
            class="btn btn-secondary"
        >
            Ekspor JSON
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<section class="operations-hero status-<?= esc($status, 'attr') ?>">
    <div class="operations-score">
        <span>Health Score</span>
        <strong><?= (int) $report['score'] ?></strong>
        <small>/ 100</small>
    </div>

    <div class="operations-status-copy">
        <span>Status Saat Ini</span>
        <h3><?= esc($statusLabels[$status] ?? $status) ?></h3>
        <p>
            <?php if ($status === 'critical') : ?>
                Satu atau lebih komponen kritis memerlukan penanganan
                sebelum sistem dianggap aman untuk operasi penuh.
            <?php elseif ($status === 'degraded') : ?>
                Sistem masih dapat digunakan, tetapi warning perlu
                ditinjau agar tidak berkembang menjadi gangguan.
            <?php else : ?>
                Seluruh komponen operasional utama berada dalam kondisi
                normal pada pemeriksaan terakhir.
            <?php endif; ?>
        </p>
    </div>

    <div class="operations-time">
        <span>Pemeriksaan Terakhir</span>
        <strong>
            <?= esc(date(
                'd M Y · H.i.s',
                strtotime($report['generated_at'])
            )) ?>
        </strong>
        <small>
            <?= esc($report['environment']['release'] ?: 'Release lokal') ?>
            · PHP <?= esc($report['environment']['php_version']) ?>
        </small>
    </div>
</section>

<section class="operations-stat-grid">
    <article class="is-pass">
        <span>Komponen Normal</span>
        <strong><?= (int) $counts['pass'] ?></strong>
        <small>dari <?= (int) $report['checks_total'] ?> pemeriksaan</small>
    </article>

    <article class="is-warning">
        <span>Peringatan</span>
        <strong><?= (int) $counts['warning'] ?></strong>
        <small>perlu ditinjau</small>
    </article>

    <article class="is-critical">
        <span>Kritis</span>
        <strong><?= (int) $counts['critical'] ?></strong>
        <small>memerlukan tindakan</small>
    </article>

    <article>
        <span>Availability 24 Jam</span>
        <strong>
            <?= $statistics['availability_percent'] !== null
                ? esc(number_format(
                    (float) $statistics['availability_percent'],
                    2
                )) . '%'
                : '-' ?>
        </strong>
        <small>
            <?= (int) $statistics['snapshots_24h'] ?> snapshot
        </small>
    </article>
</section>

<section class="operations-metric-grid">
    <article>
        <span>Database Latency</span>
        <strong>
            <?= $metrics['database_latency_ms'] !== null
                ? esc(number_format(
                    (float) $metrics['database_latency_ms'],
                    2
                )) . ' ms'
                : '-' ?>
        </strong>
    </article>

    <article>
        <span>Disk Free</span>
        <strong><?= esc($formatBytes($metrics['disk_free_bytes'])) ?></strong>
    </article>

    <article>
        <span>Memory Aktif</span>
        <strong><?= esc($formatBytes($metrics['memory_usage_bytes'])) ?></strong>
    </article>

    <article>
        <span>Usia Backup</span>
        <strong>
            <?= $metrics['backup_age_hours'] !== null
                ? (int) $metrics['backup_age_hours'] . ' jam'
                : '-' ?>
        </strong>
    </article>

    <article>
        <span>Log Critical/Error</span>
        <strong>
            <?= (int) $metrics['log_critical_count'] ?> /
            <?= (int) $metrics['log_error_count'] ?>
        </strong>
    </article>

    <article>
        <span>Security Events</span>
        <strong><?= (int) $metrics['security_events_24h'] ?></strong>
    </article>
</section>

<section class="operations-link-grid">
    <a href="<?= base_url('/system/readiness') ?>">
        <span>Production Readiness</span>
        <strong>Pra-deploy & konfigurasi</strong>
        <small>Buka pemeriksaan →</small>
    </a>

    <a href="<?= base_url('/system/backups') ?>">
        <span>Backup & Recovery</span>
        <strong>Archive dan verifikasi</strong>
        <small>Buka perlindungan data →</small>
    </a>

    <a href="<?= base_url('/website/audit') ?>">
        <span>Audit Aktivitas CMS</span>
        <strong>Jejak perubahan & keamanan</strong>
        <small>Buka audit →</small>
    </a>

    <a href="<?= base_url('/health/ready') ?>" target="_blank" rel="noopener noreferrer">
        <span>Readiness Endpoint</span>
        <strong>/health/ready</strong>
        <small>Lihat respons minimal ↗</small>
    </a>
</section>

<section class="operations-card operations-checks">
    <header>
        <div>
            <span>Current Components</span>
            <h3>Kondisi setiap komponen</h3>
        </div>
        <small><?= count($report['checks']) ?> komponen</small>
    </header>

    <div class="operations-check-list">
        <?php foreach ($report['checks'] as $check) : ?>
            <article class="status-<?= esc($check['status'], 'attr') ?>">
                <div class="operations-check-icon">
                    <?= match ($check['status']) {
                        'pass' => '✓',
                        'warning' => '!',
                        default => '×',
                    } ?>
                </div>

                <div>
                    <div class="operations-check-heading">
                        <h4><?= esc($check['title']) ?></h4>
                        <span>
                            <?= esc($checkLabels[$check['status']] ?? $check['status']) ?>
                        </span>
                    </div>
                    <p><?= esc($check['message']) ?></p>
                    <small><?= esc($check['recommendation']) ?></small>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="operations-card operations-trend">
    <header>
        <div>
            <span>Health Timeline</span>
            <h3>Riwayat snapshot terbaru</h3>
        </div>
        <small>maksimal 48 titik</small>
    </header>

    <?php if ($trend === []) : ?>
        <div class="operations-empty">
            Belum ada snapshot tersimpan. Jalankan migration lalu ambil
            snapshot pertama.
        </div>
    <?php else : ?>
        <div class="operations-trend-bars" aria-label="Riwayat health score">
            <?php foreach ($trend as $snapshot) : ?>
                <div
                    class="trend-bar status-<?= esc(
                        $snapshot['overall_status'],
                        'attr'
                    ) ?>"
                    title="<?= esc(
                        date('d M H.i', strtotime($snapshot['created_at']))
                        . ' · '
                        . $snapshot['score']
                        . '/100 · '
                        . $snapshot['overall_status'],
                        'attr'
                    ) ?>"
                >
                    <span style="height: <?= max(
                        5,
                        min(100, (int) $snapshot['score'])
                    ) ?>%"></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="operations-trend-legend">
            <span><i class="is-healthy"></i> Sehat</span>
            <span><i class="is-degraded"></i> Degraded</span>
            <span><i class="is-critical"></i> Kritis</span>
        </div>
    <?php endif; ?>
</section>

<section class="operations-card operations-incidents">
    <header>
        <div>
            <span>Incident Center</span>
            <h3>Insiden aktif dan terselesaikan</h3>
        </div>
        <small>
            <?= (int) $statistics['open_incidents'] ?> aktif
        </small>
    </header>

    <?php if ($incidents === []) : ?>
        <div class="operations-empty">Belum ada insiden monitoring.</div>
    <?php else : ?>
        <div class="operations-incident-list">
            <?php foreach ($incidents as $incident) : ?>
                <article class="status-<?= esc($incident['status'], 'attr') ?> severity-<?= esc(
                    $incident['severity'],
                    'attr'
                ) ?>">
                    <div class="incident-copy">
                        <div>
                            <span>
                                <?= esc(strtoupper($incident['severity'])) ?>
                                · <?= esc($incident['component']) ?>
                            </span>
                            <h4><?= esc($incident['title']) ?></h4>
                        </div>
                        <p><?= esc($incident['message'] ?? '-') ?></p>
                        <small>
                            Pertama: <?= esc(date(
                                'd M Y H.i',
                                strtotime($incident['first_seen_at'])
                            )) ?>
                            · Terakhir: <?= esc(date(
                                'd M Y H.i',
                                strtotime($incident['last_seen_at'])
                            )) ?>
                            · <?= (int) $incident['occurrence_count'] ?> kali
                        </small>
                    </div>

                    <div class="incident-status">
                        <strong>
                            <?= ($incident['status'] ?? '') === 'open'
                                ? 'Aktif'
                                : 'Selesai' ?>
                        </strong>

                        <?php if (
                            ($incident['status'] ?? '') === 'open'
                            && empty($incident['acknowledged_at'])
                            && auth_can('system.operations.manage')
                        ) : ?>
                            <form
                                action="<?= base_url(
                                    '/system/operations/incidents/'
                                    . (int) $incident['id']
                                    . '/acknowledge'
                                ) ?>"
                                method="post"
                            >
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-secondary">
                                    Tandai Diketahui
                                </button>
                            </form>
                        <?php elseif (!empty($incident['acknowledged_at'])) : ?>
                            <small>Sudah diketahui operator</small>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="operations-card operations-security">
    <header>
        <div>
            <span>Security & Warning Feed</span>
            <h3>Event audit yang perlu perhatian</h3>
        </div>
        <a href="<?= base_url('/website/audit') ?>">Lihat semua →</a>
    </header>

    <?php if ($securityEvents === []) : ?>
        <div class="operations-empty">Tidak ada event terbaru.</div>
    <?php else : ?>
        <div class="operations-security-list">
            <?php foreach ($securityEvents as $event) : ?>
                <article>
                    <span><?= esc(strtoupper($event['severity'])) ?></span>
                    <div>
                        <strong><?= esc($event['summary']) ?></strong>
                        <small>
                            <?= esc($event['event_type']) ?>
                            · <?= esc($event['actor_name'] ?: 'Sistem') ?>
                            · <?= esc(date(
                                'd M Y H.i',
                                strtotime($event['created_at'])
                            )) ?>
                        </small>
                    </div>
                    <a href="<?= base_url('/website/audit/' . (int) $event['id']) ?>">
                        Detail
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="operations-scheduler-card">
    <div>
        <span>Automated Monitoring</span>
        <h3>Snapshot terjadwal setiap lima menit</h3>
        <p>
            Scheduler menyimpan riwayat, membuka insiden baru, dan
            menutup insiden secara otomatis ketika komponen pulih.
        </p>
    </div>
    <code>php spark system:health:snapshot</code>
</section>

</div>

<?= $this->endSection() ?>
