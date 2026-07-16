<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<section class="public-page-hero">
    <span class="public-kicker">
        Dokumentasi Organisasi
    </span>

    <h1>
        Kegiatan GARDA 01
    </h1>

    <p>
        Kumpulan program, agenda, dan dokumentasi aktivitas
        Karang Taruna RW 01 Kelurahan Randugarut.
    </p>
</section>

<section class="public-content-section">

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert-error">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="activity-public-filter">
        <a
            href="<?= base_url('/kegiatan') ?>"
            class="<?= empty($selectedProgram) ? 'active' : '' ?>"
        >
            Semua
        </a>

        <?php foreach (($programs ?? []) as $program) : ?>
            <?php
            $programName = str_replace(
                'GARDA 01 ',
                '',
                $program['name']
            );
            ?>

            <a
                href="<?= base_url('/kegiatan')
                    . '?program='
                    . urlencode($program['slug']) ?>"
                class="<?= ($selectedProgram ?? '') === $program['slug']
                    ? 'active'
                    : '' ?>"
            >
                <?= esc($programName) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($activities)) : ?>

        <div class="public-activity-grid">

            <?php foreach ($activities as $activity) : ?>
                <?php
                $documentationFile =
                    $activity['documentation_file'] ?? '';

                $documentationPath = FCPATH
                    . 'uploads/activities/'
                    . $documentationFile;

                $hasDocumentationImage =
                    $documentationFile !== ''
                    && is_file($documentationPath);

                $activityDescription = trim(
                    (string) (
                        $activity['description']
                        ?? ''
                    )
                );

                if (mb_strlen($activityDescription) > 115) {
                    $activityDescription =
                        mb_substr(
                            $activityDescription,
                            0,
                            112
                        )
                        . '...';
                }

                $statusLabel = match (
                    $activity['status'] ?? ''
                ) {
                    'planned'   => 'Direncanakan',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                    default     => 'Kegiatan',
                };
                ?>

                <article class="public-activity-card">

                    <?php if ($hasDocumentationImage) : ?>
                        <a
                            href="<?= base_url(
                                '/kegiatan/' . $activity['id']
                            ) ?>"
                            class="public-activity-image-link"
                        >
                            <img
                                src="<?= base_url(
                                    'uploads/activities/'
                                    . $documentationFile
                                ) ?>"
                                alt="<?= esc($activity['title']) ?>"
                                loading="lazy"
                            >
                        </a>
                    <?php else : ?>
                        <a
                            href="<?= base_url(
                                '/kegiatan/' . $activity['id']
                            ) ?>"
                            class="public-activity-placeholder"
                        >
                            <span>GARDA 01</span>
                            <small>Dokumentasi Kegiatan</small>
                        </a>
                    <?php endif; ?>

                    <div class="public-activity-card-content">

                        <?php if (
                            !empty($activity['program_name'])
                            && !empty($activity['program_slug'])
                        ) : ?>
                            <a
                                href="<?= base_url(
                                    '/program/'
                                    . $activity['program_slug']
                                ) ?>"
                                class="public-activity-program"
                            >
                                <?= esc($activity['program_name']) ?>
                            </a>
                        <?php else : ?>
                            <span
                                class="public-activity-program muted"
                            >
                                Dokumentasi Organisasi
                            </span>
                        <?php endif; ?>

                        <div class="public-activity-meta">
                            <span>
                                <?= !empty($activity['activity_date'])
                                    ? date(
                                        'd M Y',
                                        strtotime(
                                            $activity['activity_date']
                                        )
                                    )
                                    : '-' ?>
                            </span>

                            <span class="public-activity-status">
                                <?= esc($statusLabel) ?>
                            </span>
                        </div>

                        <h3>
                            <a
                                href="<?= base_url(
                                    '/kegiatan/' . $activity['id']
                                ) ?>"
                            >
                                <?= esc($activity['title']) ?>
                            </a>
                        </h3>

                        <p class="public-activity-location">
                            <?= esc(
                                $activity['location']
                                ?? 'Randugarut RW 01'
                            ) ?>
                        </p>

                        <?php if ($activityDescription !== '') : ?>
                            <p class="public-activity-excerpt">
                                <?= esc($activityDescription) ?>
                            </p>
                        <?php endif; ?>

                        <a
                            href="<?= base_url(
                                '/kegiatan/' . $activity['id']
                            ) ?>"
                            class="public-read-more"
                        >
                            Lihat Detail
                            <span aria-hidden="true">→</span>
                        </a>

                    </div>
                </article>

            <?php endforeach; ?>

        </div>

        <?php if (isset($pager)) : ?>
            <div class="pagination-wrapper public-pagination">
                <?= $pager->links(
                    'public_activities',
                    'default_full'
                ) ?>
            </div>
        <?php endif; ?>

    <?php else : ?>

        <div class="public-empty">
            <strong>
                Belum ada kegiatan pada kategori ini.
            </strong>

            <p>
                Pilih kategori program lain atau tampilkan seluruh
                dokumentasi kegiatan GARDA 01.
            </p>

            <a
                href="<?= base_url('/kegiatan') ?>"
                class="btn btn-primary"
            >
                Lihat Semua Kegiatan
            </a>
        </div>

    <?php endif; ?>

</section>

<section class="public-page-cta">
    <div>
        <span class="public-kicker">
            Pilar Gerakan
        </span>

        <h2>
            Kenali program GARDA 01
        </h2>

        <p>
            Setiap kegiatan menjadi bagian dari pilar sosial,
            lingkungan, olahraga, kreativitas, usaha, pendidikan,
            atau keagamaan.
        </p>
    </div>

    <a
        href="<?= base_url('/program') ?>"
        class="btn btn-primary"
    >
        Jelajahi Program
    </a>
</section>

<?= $this->endSection() ?>