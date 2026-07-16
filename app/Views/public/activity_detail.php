<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<?php
$statusLabel = match ($activity['status'] ?? '') {
    'planned'   => 'Direncanakan',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
    default     => 'Kegiatan',
};

$statusClass = match ($activity['status'] ?? '') {
    'planned'   => 'activity-status-planned',
    'completed' => 'activity-status-completed',
    'cancelled' => 'activity-status-cancelled',
    default     => '',
};

$documentationFile = $activity['documentation_file'] ?? '';

$documentationPath = FCPATH
    . 'uploads/activities/'
    . $documentationFile;

$hasDocumentationImage =
    $documentationFile !== ''
    && is_file($documentationPath);
?>

<section class="activity-detail-hero">

    <div class="activity-detail-hero-copy">

        <a
            href="<?= base_url('/kegiatan') ?>"
            class="activity-detail-back"
        >
            ← Kembali ke Kegiatan
        </a>

        <?php if (
            !empty($activity['program_name'])
            && !empty($activity['program_slug'])
        ) : ?>
            <a
                href="<?= base_url(
                    '/program/' . $activity['program_slug']
                ) ?>"
                class="activity-detail-program"
            >
                <?= esc($activity['program_name']) ?>

                <?php if (!empty($activity['program_label'])) : ?>
                    <span>
                        · <?= esc($activity['program_label']) ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php else : ?>
            <span class="activity-detail-program muted">
                Dokumentasi Organisasi
            </span>
        <?php endif; ?>

        <h1><?= esc($activity['title']) ?></h1>

        <?php if (!empty($activity['description'])) : ?>
            <p class="activity-detail-lead">
                <?= esc($activity['description']) ?>
            </p>
        <?php endif; ?>

        <div class="activity-detail-meta">

            <div>
                <span>Tanggal</span>

                <strong>
                    <?= !empty($activity['activity_date'])
                        ? date(
                            'd M Y',
                            strtotime($activity['activity_date'])
                        )
                        : '-' ?>
                </strong>
            </div>

            <div>
                <span>Lokasi</span>

                <strong>
                    <?= esc($activity['location'] ?? '-') ?>
                </strong>
            </div>

            <div>
                <span>Status</span>

                <strong class="<?= esc($statusClass) ?>">
                    <?= esc($statusLabel) ?>
                </strong>
            </div>

        </div>

    </div>

    <aside class="activity-detail-program-card">

        <img
            src="<?= base_url('assets/img/logo-rw01.png') ?>"
            alt="Logo GARDA 01"
        >

        <span>Dokumentasi Resmi</span>

        <strong>GARDA 01</strong>

        <p>
            <?= esc(
                $activity['program_tagline']
                ?? 'Guyub • Bergerak • Berdampak'
            ) ?>
        </p>

    </aside>

</section>

<section class="activity-detail-main">

    <article class="activity-detail-documentation">

        <?php if ($hasDocumentationImage) : ?>
            <img
                src="<?= base_url(
                    'uploads/activities/' . $documentationFile
                ) ?>"
                alt="<?= esc($activity['title']) ?>"
                class="activity-detail-main-image"
            >
        <?php else : ?>
            <div class="activity-detail-image-placeholder">
                <img
                    src="<?= base_url('assets/img/logo-rw01.png') ?>"
                    alt=""
                >

                <strong>GARDA 01</strong>

                <span>Dokumentasi Kegiatan</span>
            </div>
        <?php endif; ?>

        <div class="activity-detail-caption">
            <span>Dokumentasi Utama</span>

            <p>
                <?= esc($activity['title']) ?>
                · <?= esc($activity['location'] ?? 'Randugarut RW 01') ?>
            </p>
        </div>

    </article>

    <aside class="activity-detail-summary">

        <span class="public-kicker">
            Ringkasan Kegiatan
        </span>

        <h2>Informasi pelaksanaan</h2>

        <dl>
            <div>
                <dt>Nama Kegiatan</dt>
                <dd><?= esc($activity['title']) ?></dd>
            </div>

            <div>
                <dt>Pilar Program</dt>

                <dd>
                    <?= esc(
                        $activity['program_name']
                        ?? 'Belum dikategorikan'
                    ) ?>
                </dd>
            </div>

            <div>
                <dt>Tanggal</dt>

                <dd>
                    <?= !empty($activity['activity_date'])
                        ? date(
                            'd M Y',
                            strtotime($activity['activity_date'])
                        )
                        : '-' ?>
                </dd>
            </div>

            <div>
                <dt>Lokasi</dt>
                <dd><?= esc($activity['location'] ?? '-') ?></dd>
            </div>

            <div>
                <dt>Status</dt>
                <dd><?= esc($statusLabel) ?></dd>
            </div>
        </dl>

        <?php if (!empty($activity['documentation_link'])) : ?>
            <a
                href="<?= esc($activity['documentation_link']) ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-primary activity-documentation-button"
            >
                Buka Dokumentasi Lengkap
            </a>
        <?php endif; ?>

    </aside>

</section>

<?= view(
    'public/partials/activity_gallery',
    [
        'galleryImages' => $galleryImages ?? [],
        'activity'      => $activity,
    ]
) ?>

<?php if (!empty($activity['description'])) : ?>
    <section class="activity-editorial-section">

        <div class="activity-editorial-heading">
            <span class="public-kicker">
                Tentang Kegiatan
            </span>

            <h2>Cerita dan pelaksanaan kegiatan</h2>
        </div>

        <div class="activity-editorial-content">
            <?= nl2br(esc($activity['description'])) ?>
        </div>

    </section>
<?php endif; ?>

<?php if (!empty($activity['result'])) : ?>
    <section class="activity-impact-section">

        <div class="activity-impact-number">
            <span>Dampak</span>
            <strong>01</strong>
        </div>

        <div class="activity-impact-copy">
            <span class="public-kicker">
                Hasil Kegiatan
            </span>

            <h2>Dampak dan capaian</h2>

            <p>
                <?= nl2br(esc($activity['result'])) ?>
            </p>
        </div>

    </section>
<?php endif; ?>

<?php if (!empty($relatedActivities)) : ?>
    <section class="activity-related-section">

        <div class="public-section-header">

            <span class="public-kicker">
                Dokumentasi Lainnya
            </span>

            <h2>Kegiatan terkait</h2>

            <p>
                Kegiatan lain yang berada dalam ruang gerak
                GARDA 01.
            </p>

        </div>

        <div class="activity-related-grid">

            <?php foreach ($relatedActivities as $related) : ?>
                <?php
                $relatedFile =
                    $related['documentation_file'] ?? '';

                $relatedPath = FCPATH
                    . 'uploads/activities/'
                    . $relatedFile;

                $hasRelatedImage =
                    $relatedFile !== ''
                    && is_file($relatedPath);
                ?>

                <article class="activity-related-card">

                    <a
                        href="<?= base_url(
                            '/kegiatan/' . $related['id']
                        ) ?>"
                        class="activity-related-media"
                    >
                        <?php if ($hasRelatedImage) : ?>
                            <img
                                src="<?= base_url(
                                    'uploads/activities/'
                                    . $relatedFile
                                ) ?>"
                                alt="<?= esc($related['title']) ?>"
                                loading="lazy"
                            >
                        <?php else : ?>
                            <div>
                                <span>GARDA 01</span>
                            </div>
                        <?php endif; ?>
                    </a>

                    <div class="activity-related-content">

                        <?php if (
                            !empty($related['program_name'])
                        ) : ?>
                            <span class="public-activity-program">
                                <?= esc($related['program_name']) ?>
                            </span>
                        <?php endif; ?>

                        <h3>
                            <a
                                href="<?= base_url(
                                    '/kegiatan/' . $related['id']
                                ) ?>"
                            >
                                <?= esc($related['title']) ?>
                            </a>
                        </h3>

                        <p>
                            <?= !empty($related['activity_date'])
                                ? date(
                                    'd M Y',
                                    strtotime(
                                        $related['activity_date']
                                    )
                                )
                                : '-' ?>

                            · <?= esc(
                                $related['location']
                                ?? 'Randugarut RW 01'
                            ) ?>
                        </p>

                    </div>

                </article>
            <?php endforeach; ?>

        </div>

    </section>
<?php endif; ?>

<section class="public-page-cta">

    <div>
        <span class="public-kicker">
            Guyub • Bergerak • Berdampak
        </span>

        <h2>Lihat gerakan GARDA 01 lainnya</h2>

        <p>
            Jelajahi dokumentasi kegiatan dan tujuh pilar program
            Karang Taruna RW 01 Randugarut.
        </p>
    </div>

    <div class="activity-detail-cta-actions">
        <a
            href="<?= base_url('/kegiatan') ?>"
            class="btn btn-primary"
        >
            Semua Kegiatan
        </a>

        <a
            href="<?= base_url('/program') ?>"
            class="btn btn-secondary"
        >
            Lihat Program
        </a>
    </div>

</section>

<?= $this->endSection() ?>