<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$backups = $status['backups'] ?? [];
$latest = $status['latest'] ?? null;

$cssPath = FCPATH . 'assets/css/admin-backup-recovery.css';
$cssVersion = is_file($cssPath) ? (string) filemtime($cssPath) : '1';

$formatBytes = static function (?int $bytes): string {
    $value = max(0, (int) $bytes);
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $unit = 0;

    while ($value >= 1024 && $unit < count($units) - 1) {
        $value /= 1024;
        $unit++;
    }

    return number_format($value, $unit === 0 ? 0 : 2)
        . ' ' . $units[$unit];
};

$verificationLabels = [
    'verified' => 'Terverifikasi',
    'not_verified' => 'Belum Diverifikasi',
    'failed_checksum' => 'Checksum Gagal',
    'failed_zip' => 'ZIP Rusak',
    'missing_manifest' => 'Manifest Hilang',
];
?>

<link
    rel="stylesheet"
    href="<?= base_url('assets/css/admin-backup-recovery.css') ?>?v=<?= esc($cssVersion, 'attr') ?>"
>

<div class="backup-recovery-page">

<div class="page-header backup-recovery-header">
    <div>
        <span class="backup-recovery-eyebrow">
            Business Continuity
        </span>

        <h2>Backup & Pemulihan</h2>

        <p>
            Kelola backup database dan upload, periksa integritas
            archive, serta pantau kesiapan disaster recovery.
        </p>
    </div>

    <div class="backup-recovery-actions">
        <?php if (auth_can('system.backups.create')) : ?>
            <form
                action="<?= base_url('/system/backups/create') ?>"
                method="post"
                onsubmit="return confirm(
                    'Buat backup database dan seluruh upload sekarang?'
                )"
            >
                <?= csrf_field() ?>

                <button type="submit" class="btn btn-primary">
                    Buat Backup Sekarang
                </button>
            </form>
        <?php endif; ?>

        <a
            href="<?= base_url('/system/readiness') ?>"
            class="btn btn-secondary"
        >
            Kesiapan Produksi
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

<section class="backup-status-hero <?= !empty($latest)
    ? 'has-backup'
    : 'has-no-backup' ?>">
    <div class="backup-status-main">
        <span>Status Perlindungan Data</span>

        <h3>
            <?= !empty($latest)
                ? (
                    ($latest['verification_status'] ?? '') === 'verified'
                        ? 'Backup terbaru telah terverifikasi'
                        : 'Backup tersedia, tetapi belum terverifikasi'
                )
                : 'Belum ada backup aplikasi' ?>
        </h3>

        <p>
            Archive disimpan di luar document root. Restore hanya
            tersedia melalui CLI server.
        </p>
    </div>

    <div class="backup-latest">
        <span>Backup Terbaru</span>

        <strong>
            <?= !empty($latest['created_at'])
                ? esc(date(
                    'd M Y · H.i',
                    strtotime($latest['created_at'])
                ))
                : '-' ?>
        </strong>

        <small>
            <?= !empty($latest)
                ? esc($formatBytes((int) ($latest['archive_size'] ?? 0)))
                : 'Belum tersedia' ?>
        </small>
    </div>
</section>

<section class="backup-stat-grid">
    <article>
        <span>Total Archive</span>
        <strong><?= (int) ($status['count'] ?? 0) ?></strong>
        <small>
            <?= esc($formatBytes((int) ($status['total_bytes'] ?? 0))) ?>
        </small>
    </article>

    <article>
        <span>Ruang Kosong</span>
        <strong>
            <?= $status['free_bytes'] !== null
                ? esc($formatBytes((int) $status['free_bytes']))
                : '-' ?>
        </strong>
        <small>Storage lokasi backup</small>
    </article>

    <article>
        <span>mysqldump</span>
        <strong>
            <?= !empty($status['native_tools']['mysqldump'])
                ? 'Tersedia'
                : 'Fallback PHP' ?>
        </strong>
        <small>Database backup engine</small>
    </article>

    <article>
        <span>mysql Restore</span>
        <strong>
            <?= !empty($status['native_tools']['mysql'])
                ? 'Siap'
                : 'Belum Siap' ?>
        </strong>
        <small>Wajib untuk pemulihan CLI</small>
    </article>
</section>

<section class="backup-policy-grid">
    <?php foreach ([
        'Latest' => [$status['retention']['keep_latest'], 'selalu dipertahankan'],
        'Daily' => [$status['retention']['daily_days'], 'hari'],
        'Weekly' => [$status['retention']['weekly_weeks'], 'minggu'],
        'Monthly' => [$status['retention']['monthly_months'], 'bulan'],
    ] as $label => $policy) : ?>
        <article>
            <span><?= esc($label) ?></span>
            <strong><?= (int) $policy[0] ?></strong>
            <small><?= esc($policy[1]) ?></small>
        </article>
    <?php endforeach; ?>
</section>

<section class="backup-cli-card">
    <div>
        <span>Restore Safety</span>
        <h3>Pemulihan tidak tersedia dari browser</h3>

        <p>
            Restore database adalah operasi destruktif. Jalankan
            hanya dari terminal server setelah trafik dihentikan.
        </p>
    </div>

    <code>
        php spark backup:restore --file ARCHIVE.zip --yes --maintenance-confirmed YES
    </code>
</section>

<section class="backup-list-card">
    <header>
        <div>
            <span>Backup Inventory</span>
            <h3>Archive dan status integritas</h3>
        </div>

        <?php if (auth_can('system.backups.prune')) : ?>
            <form
                action="<?= base_url('/system/backups/prune') ?>"
                method="post"
                onsubmit="return confirm(
                    'Jalankan retensi dan hapus backup lama?'
                )"
            >
                <?= csrf_field() ?>

                <button type="submit" class="btn btn-secondary">
                    Jalankan Retensi
                </button>
            </form>
        <?php endif; ?>
    </header>

    <?php if ($backups === []) : ?>
        <div class="backup-empty-state">
            <strong>Belum ada backup</strong>
            <p>
                Buat backup pertama, lalu verifikasi integritasnya.
            </p>
        </div>
    <?php else : ?>
        <div class="backup-list">
            <?php foreach ($backups as $backup) : ?>
                <?php
                $verification = (string) (
                    $backup['verification_status'] ?? 'not_verified'
                );
                ?>

                <article>
                    <div class="backup-icon">DB</div>

                    <div class="backup-main">
                        <header>
                            <div>
                                <span><?= esc($backup['tag'] ?? 'backup') ?></span>
                                <h4><?= esc($backup['archive_name'] ?? '-') ?></h4>
                            </div>

                            <span class="backup-verification status-<?= esc(
                                $verification,
                                'attr'
                            ) ?>">
                                <?= esc(
                                    $verificationLabels[$verification]
                                    ?? $verification
                                ) ?>
                            </span>
                        </header>

                        <dl>
                            <div>
                                <dt>Dibuat</dt>
                                <dd>
                                    <?= !empty($backup['created_at'])
                                        ? esc(date(
                                            'd M Y · H.i',
                                            strtotime($backup['created_at'])
                                        ))
                                        : '-' ?>
                                </dd>
                            </div>

                            <div>
                                <dt>Ukuran</dt>
                                <dd>
                                    <?= esc($formatBytes(
                                        (int) ($backup['archive_size'] ?? 0)
                                    )) ?>
                                </dd>
                            </div>

                            <div>
                                <dt>Database</dt>
                                <dd><?= esc($backup['database_method'] ?? '-') ?></dd>
                            </div>

                            <div>
                                <dt>Diverifikasi</dt>
                                <dd>
                                    <?= !empty($backup['verified_at'])
                                        ? esc(date(
                                            'd M Y · H.i',
                                            strtotime($backup['verified_at'])
                                        ))
                                        : 'Belum' ?>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="backup-item-actions">
                        <?php if (auth_can('system.backups.verify')) : ?>
                            <form
                                action="<?= base_url('/system/backups/verify') ?>"
                                method="post"
                            >
                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="archive_name"
                                    value="<?= esc(
                                        $backup['archive_name'],
                                        'attr'
                                    ) ?>"
                                >

                                <button type="submit" class="btn btn-secondary">
                                    Verifikasi
                                </button>
                            </form>
                        <?php endif; ?>

                        <a
                            href="<?= base_url(
                                '/system/backups/manifest/'
                                . rawurlencode($backup['archive_name'])
                            ) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-secondary"
                        >
                            Manifest ↗
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="backup-runbook">
    <header>
        <span>Disaster Recovery</span>
        <h3>Urutan pemulihan yang aman</h3>
    </header>

    <ol>
        <?php foreach ([
            'Hentikan trafik pengguna.',
            'Verifikasi archive yang dipakai.',
            'Buat safety backup kondisi terakhir.',
            'Jalankan restore melalui CLI.',
            'Periksa migration, cache, dan permission.',
            'Smoke test sebelum membuka trafik.',
        ] as $index => $step) : ?>
            <li>
                <b><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></b>
                <span><?= esc($step) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

</div>

<?= $this->endSection() ?>
