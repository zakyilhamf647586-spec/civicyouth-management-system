<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$summary = $report['summary'];
$environment = $report['environment'];
$readiness = $summary['readiness'];

$statusLabels = [
    'pass' => 'Lulus',
    'warning' => 'Peringatan',
    'fail' => 'Gagal',
    'manual' => 'Manual',
];

$readinessLabels = [
    'ready' => 'Siap Diproduksi',
    'ready_with_warnings' => 'Siap dengan Peringatan',
    'blocked' => 'Belum Siap Diproduksi',
];

$cssPath = FCPATH . 'assets/css/admin-production-readiness.css';
$cssVersion = is_file($cssPath) ? (string) filemtime($cssPath) : '1';
?>

<link
    rel="stylesheet"
    href="<?= base_url('assets/css/admin-production-readiness.css') ?>?v=<?= esc($cssVersion, 'attr') ?>"
>

<div class="production-readiness-page">

<div class="page-header production-readiness-header">
    <div>
        <span class="production-readiness-eyebrow">
            Production Operations
        </span>

        <h2>Kesiapan Produksi</h2>

        <p>
            Pemeriksaan pra-deploy untuk runtime, environment,
            keamanan, database, filesystem, dan kelengkapan release.
        </p>
    </div>

    <div class="production-readiness-actions">
        <a
            href="<?= base_url('/system/readiness/export') ?>"
            class="btn btn-secondary"
        >
            Ekspor JSON
        </a>

        <a
            href="<?= base_url('/website/audit') ?>"
            class="btn btn-secondary"
        >
            Buka Audit CMS
        </a>
    </div>
</div>

<section class="readiness-hero status-<?= esc($readiness, 'attr') ?>">
    <div class="readiness-score">
        <span>Readiness Score</span>
        <strong><?= (int) $summary['score'] ?></strong>
        <small>/ 100</small>
    </div>

    <div class="readiness-hero-copy">
        <span>Status Release</span>
        <h3><?= esc($readinessLabels[$readiness] ?? $readiness) ?></h3>

        <p>
            <?php if ($readiness === 'blocked') : ?>
                Terdapat blocker kritis yang harus diselesaikan
                sebelum domain production dibuka.
            <?php elseif ($readiness === 'ready_with_warnings') : ?>
                Tidak ada blocker otomatis, tetapi warning dan
                validasi manual tetap harus diperiksa.
            <?php else : ?>
                Pemeriksaan otomatis lulus. Lanjutkan backup,
                deployment, dan smoke test.
            <?php endif; ?>
        </p>
    </div>

    <div class="readiness-blocker">
        <span>Blocking Issues</span>
        <strong><?= (int) $summary['blocking'] ?></strong>
        <small>
            dari <?= (int) $summary['automated'] ?>
            pemeriksaan otomatis
        </small>
    </div>
</section>

<section class="readiness-stat-grid">
    <article class="is-pass">
        <span>Lulus</span>
        <strong><?= (int) $summary['counts']['pass'] ?></strong>
    </article>

    <article class="is-warning">
        <span>Peringatan</span>
        <strong><?= (int) $summary['counts']['warning'] ?></strong>
    </article>

    <article class="is-fail">
        <span>Gagal</span>
        <strong><?= (int) $summary['counts']['fail'] ?></strong>
    </article>

    <article class="is-manual">
        <span>Validasi Manual</span>
        <strong><?= (int) $summary['counts']['manual'] ?></strong>
    </article>
</section>

<section class="readiness-environment">
    <?php foreach ([
        'Environment' => $environment['name'],
        'Base URL' => $environment['base_url'],
        'PHP' => $environment['php_version'],
        'Release' => $environment['release'] ?: 'Belum dicatat',
        'Commit' => $environment['commit'] ?: 'Belum dicatat',
    ] as $label => $value) : ?>
        <article>
            <span><?= esc($label) ?></span>
            <strong><?= esc($value) ?></strong>
        </article>
    <?php endforeach; ?>
</section>

<section class="readiness-command-card">
    <div>
        <span>CLI Preflight</span>
        <h3>Jalankan sebelum membuat package</h3>
        <p>
            Mode strict gagal apabila masih ada blocker atau warning.
        </p>
    </div>

    <code>php spark production:check --strict</code>
</section>

<?php foreach ($report['categories'] as $category => $checks) : ?>
    <?php if ($checks === []) : ?>
        <?php continue; ?>
    <?php endif; ?>

    <section class="readiness-category">
        <header>
            <div>
                <span>
                    <?= esc($categoryLabels[$category] ?? $category) ?>
                </span>

                <h3>
                    <?= $category === 'manual'
                        ? 'Checklist yang harus dikonfirmasi manusia'
                        : 'Pemeriksaan ' . esc($categoryLabels[$category] ?? $category) ?>
                </h3>
            </div>

            <small><?= count($checks) ?> item</small>
        </header>

        <div class="readiness-check-list">
            <?php foreach ($checks as $check) : ?>
                <article class="check-status-<?= esc($check['status'], 'attr') ?>">
                    <div class="check-indicator">
                        <?= match ($check['status']) {
                            'pass' => '✓',
                            'warning' => '!',
                            'fail' => '×',
                            default => '○',
                        } ?>
                    </div>

                    <div class="check-copy">
                        <div class="check-title-line">
                            <h4><?= esc($check['title']) ?></h4>
                            <span>
                                <?= esc($statusLabels[$check['status']] ?? $check['status']) ?>
                            </span>
                        </div>

                        <p><?= esc($check['message']) ?></p>
                        <small><?= esc($check['recommendation']) ?></small>
                    </div>

                    <?php if (!empty($check['blocking'])) : ?>
                        <span class="check-blocking">Blocking</span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<section class="readiness-deployment-order">
    <header>
        <span>Urutan Aman Deployment</span>
        <h3>Jangan melewati checkpoint utama</h3>
    </header>

    <ol>
        <?php foreach ([
            'Backup database dan source production lama.',
            'Jalankan production:check dan buat package release.',
            'Upload ke folder release baru, bukan menimpa source aktif secara acak.',
            'Atur .env production, permission, dan document root.',
            'Jalankan migration, cache clear, lalu smoke test.',
            'Buka akses pengguna setelah hasil uji dinyatakan aman.',
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
