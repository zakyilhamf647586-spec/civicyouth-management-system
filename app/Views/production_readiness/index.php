<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$summary = $report['summary'];
$environment = $report['environment'];
$readiness = $summary['readiness'];
$deploymentContext = $report['deployment_context'] ?? [];
$deploymentStreams = $deploymentContext['streams'] ?? [
    'now' => ['items' => [], 'blocking' => 0, 'warnings' => 0],
    'hosting' => ['items' => [], 'blocking' => 0, 'warnings' => 0],
    'final' => ['items' => [], 'blocking' => 0, 'warnings' => 0],
];
$isLocalEnvironment = !empty($deploymentContext['is_local']);
$currentStatus = (string) ($deploymentContext['current_status'] ?? 'in_progress');

$streamByCheckId = [];
foreach ($deploymentStreams as $streamKey => $stream) {
    foreach (($stream['items'] ?? []) as $streamCheck) {
        $streamByCheckId[(string) ($streamCheck['id'] ?? '')] = $streamKey;
    }
}

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

$currentStatusLabels = [
    'on_track' => 'Pengembangan Lokal Berada di Jalur Aman',
    'in_progress' => 'Pengembangan Lokal Masih Berproses',
    'needs_attention' => 'Ada Prioritas Proyek yang Perlu Ditangani',
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

    <?php if (auth_can_any([
        'system.readiness.export',
        'website.audit.view',
    ])) : ?>
        <div class="production-readiness-actions">
            <?php if (auth_can('system.readiness.export')) : ?>
                <a
                    href="<?= base_url('/system/readiness/export') ?>"
                    class="btn btn-secondary"
                >
                    Ekspor JSON
                </a>
            <?php endif; ?>

            <?php if (auth_can('website.audit.view')) : ?>
                <a
                    href="<?= base_url('/website/audit') ?>"
                    class="btn btn-secondary"
                >
                    Buka Audit CMS
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<section class="readiness-hero status-<?= esc($readiness, 'attr') ?> <?= $isLocalEnvironment
    ? 'is-local-context phase-' . esc($currentStatus, 'attr')
    : '' ?>">
    <div class="readiness-score">
        <span>Readiness Score</span>
        <strong><?= (int) $summary['score'] ?></strong>
        <small>/ 100</small>
    </div>

    <div class="readiness-hero-copy">
        <span><?= $isLocalEnvironment ? 'Tahap Saat Ini' : 'Status Release' ?></span>
        <h3>
            <?= esc(
                $isLocalEnvironment
                    ? ($currentStatusLabels[$currentStatus] ?? $currentStatus)
                    : ($readinessLabels[$readiness] ?? $readiness)
            ) ?>
        </h3>

        <p>
            <?php if ($isLocalEnvironment) : ?>
                Nilai produksi tetap dihitung secara ketat, tetapi kegagalan
                HTTPS, domain publik, dan mode production memang diharapkan
                selama website masih berjalan di localhost.
            <?php elseif ($readiness === 'blocked') : ?>
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
        <span><?= $isLocalEnvironment ? 'Prioritas Proyek' : 'Blocking Issues' ?></span>
        <strong>
            <?= $isLocalEnvironment
                ? count($deploymentStreams['now']['items'] ?? [])
                : (int) $summary['blocking'] ?>
        </strong>
        <small>
            <?php if ($isLocalEnvironment) : ?>
                <?= (int) ($deploymentStreams['now']['blocking'] ?? 0) ?> blocker,
                <?= (int) ($deploymentStreams['now']['warnings'] ?? 0) ?> warning
                yang bisa ditangani sekarang
            <?php else : ?>
                dari <?= (int) $summary['automated'] ?>
                pemeriksaan otomatis
            <?php endif; ?>
        </small>
    </div>
</section>

<?php if ($isLocalEnvironment) : ?>
    <section class="readiness-local-note" aria-label="Konteks pengembangan lokal">
        <span class="readiness-local-note-icon" aria-hidden="true">L</span>
        <div>
            <strong>Status produksi merah bukan berarti website rusak.</strong>
            <p>
                Sistem sedang menilai localhost dengan standar server publik.
                Selesaikan kolom “Kerjakan sekarang”; kolom hosting baru
                ditindaklanjuti setelah domain dan server tersedia.
            </p>
        </div>
        <span class="readiness-local-note-badge">LOCAL DEVELOPMENT</span>
    </section>
<?php endif; ?>

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

<section class="readiness-roadmap" aria-labelledby="readiness-roadmap-title">
    <header>
        <span>Jalur Menuju Publik</span>
        <h3 id="readiness-roadmap-title">Empat checkpoint, tanpa perlu terburu-buru</h3>
        <p>
            <?php if ($isLocalEnvironment) : ?>
                Fokus tetap pada mutu proyek sekarang. Infrastruktur production
                dikerjakan ketika keputusan domain dan hosting sudah dibuat.
            <?php else : ?>
                Infrastruktur production telah terdeteksi. Tuntaskan konfigurasi
                server dan validasi terakhir sebelum trafik dibuka penuh.
            <?php endif; ?>
        </p>
    </header>

    <ol>
        <?php foreach ([
            [
                'state' => $isLocalEnvironment ? 'active' : 'complete',
                'title' => 'Kualitas proyek lokal',
                'copy' => 'Konten, CMS, akses, responsif, data, dan pengujian.',
                'meta' => count($deploymentStreams['now']['items'] ?? [])
                    . ' item aktif',
            ],
            [
                'state' => $isLocalEnvironment ? 'waiting' : 'complete',
                'title' => 'Domain & hosting',
                'copy' => 'Pilih domain, paket hosting, panel, PHP, dan database.',
                'meta' => $isLocalEnvironment ? 'Belum perlu dibeli' : 'Tersedia',
            ],
            [
                'state' => $isLocalEnvironment
                    ? 'waiting'
                    : (($deploymentStreams['hosting']['items'] ?? []) === []
                        ? 'complete'
                        : 'active'),
                'title' => 'Konfigurasi server',
                'copy' => '.env production, HTTPS, cookie, permission, dan backup.',
                'meta' => count($deploymentStreams['hosting']['items'] ?? [])
                    . ' pemeriksaan',
            ],
            [
                'state' => $isLocalEnvironment ? 'waiting' : 'active',
                'title' => 'Validasi & rilis',
                'copy' => 'Migration, restore test, smoke test, SEO, dan pembukaan trafik.',
                'meta' => count($deploymentStreams['final']['items'] ?? [])
                    . ' validasi',
            ],
        ] as $index => $checkpoint) : ?>
            <li class="is-<?= esc($checkpoint['state'], 'attr') ?>">
                <b><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></b>
                <div>
                    <strong><?= esc($checkpoint['title']) ?></strong>
                    <p><?= esc($checkpoint['copy']) ?></p>
                    <small><?= esc($checkpoint['meta']) ?></small>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

<section class="readiness-action-center" aria-labelledby="readiness-action-title">
    <header>
        <div>
            <span>Action Center</span>
            <h3 id="readiness-action-title">Prioritas dipisahkan menurut waktunya</h3>
        </div>
        <small>Detail teknis lengkap tetap tersedia di bawah</small>
    </header>

    <div class="readiness-workstream-grid">
        <?php foreach ([
            'now' => [
                'label' => 'Kerjakan sekarang',
                'description' => 'Perbaikan proyek yang bisa diselesaikan di localhost.',
            ],
            'hosting' => [
                'label' => 'Saat hosting tersedia',
                'description' => 'Konfigurasi yang membutuhkan domain atau server asli.',
            ],
            'final' => [
                'label' => 'Validasi sebelum publik',
                'description' => 'Pemeriksaan manusia setelah konfigurasi production selesai.',
            ],
        ] as $streamKey => $streamMeta) : ?>
            <?php $stream = $deploymentStreams[$streamKey] ?? ['items' => []]; ?>
            <article class="workstream-<?= esc($streamKey, 'attr') ?>">
                <div class="workstream-heading">
                    <span><?= esc($streamMeta['label']) ?></span>
                    <strong><?= count($stream['items'] ?? []) ?></strong>
                </div>
                <p><?= esc($streamMeta['description']) ?></p>

                <?php if (($stream['items'] ?? []) === []) : ?>
                    <div class="workstream-empty">Tidak ada pekerjaan tertunda.</div>
                <?php else : ?>
                    <ul>
                        <?php foreach (array_slice($stream['items'], 0, 5) as $item) : ?>
                            <?php $itemId = (string) ($item['id'] ?? ''); ?>
                            <li>
                                <span class="status-<?= esc($item['status'], 'attr') ?>">
                                    <?= esc($statusLabels[$item['status']] ?? $item['status']) ?>
                                </span>
                                <div>
                                    <strong><?= esc($item['title']) ?></strong>
                                    <small><?= esc($item['recommendation']) ?></small>

                                    <?php if (
                                        $itemId === 'database.demo_admin'
                                        && auth_can('users.view')
                                    ) : ?>
                                        <a
                                            href="<?= base_url('/users') ?>"
                                            class="workstream-action-link"
                                        >
                                            Buka transisi Admin →
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (count($stream['items']) > 5) : ?>
                        <small class="workstream-more">
                            +<?= count($stream['items']) - 5 ?> item lain pada detail teknis
                        </small>
                    <?php endif; ?>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($isLocalEnvironment) : ?>
    <section class="readiness-command-card is-local-maintenance">
        <div>
            <span>Local Maintenance</span>
            <h3>Rawat proyek tanpa membuat backup berlebihan</h3>
            <p>
                Perintah ini memakai backup yang masih segar, membuat yang baru
                setelah 24 jam, memverifikasi archive, menjalankan retensi,
                lalu menyimpan snapshot kesehatan.
            </p>
        </div>

        <code>php spark system:maintenance --max-age-hours 24</code>
    </section>
<?php endif; ?>

<section class="readiness-command-card">
    <div>
        <span>CLI Preflight</span>
        <h3>Jalankan sebelum membuat package</h3>
        <p>
            <?= $isLocalEnvironment
                ? 'Mode strict memang akan gagal di localhost. Gunakan setelah konfigurasi server production selesai.'
                : 'Mode strict harus lulus tanpa blocker atau warning sebelum package release digunakan.' ?>
        </p>
    </div>

    <code>php spark production:check --strict</code>
</section>

<div class="readiness-technical-heading">
    <span>Detail Teknis</span>
    <h3>Seluruh hasil pemeriksaan</h3>
    <p>
        Bagian ini mempertahankan hasil mentah agar operator dapat menelusuri
        setiap konfigurasi tanpa kehilangan konteks.
    </p>
</div>

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
                        <?php $checkStream = $streamByCheckId[(string) $check['id']] ?? 'now'; ?>
                        <span class="check-blocking <?= $isLocalEnvironment
                            && $checkStream === 'hosting'
                                ? 'is-deferred'
                                : '' ?>">
                            <?= $isLocalEnvironment && $checkStream === 'hosting'
                                ? 'Saat Hosting'
                                : 'Blocking' ?>
                        </span>
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
