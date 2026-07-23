<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<?php
$formatPublicDate = static function (?string $date): string {
    if (empty($date)) {
        return '-';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return '-';
    }

    $months = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    return date('d', $timestamp)
        . ' '
        . $months[(int) date('n', $timestamp)]
        . ' '
        . date('Y', $timestamp);
};

$activityHasImage = static function (?array $activity): bool {
    if (
        empty($activity)
        || empty($activity['documentation_file'])
    ) {
        return false;
    }

    return is_file(
        FCPATH
        . 'uploads/activities/'
        . $activity['documentation_file']
    );
};

$programShortName = static function (?string $name): string {
    if (empty($name)) {
        return 'GARDA 01';
    }

    return trim(
        str_replace('GARDA 01 ', '', $name)
    );
};

$programClass = static function (?string $slug): string {
    $slug = strtolower((string) $slug);

    return preg_replace(
        '/[^a-z0-9\-]/',
        '',
        $slug
    ) ?: 'general';
};

$featuredHasImage = $activityHasImage(
    $featuredActivity ?? null
);

$impactHasImage = $activityHasImage(
    $impactActivity ?? null
);

$cmsPage = $cmsPage ?? null;

$visiblePrograms = array_slice(
    $programs ?? [],
    0,
    public_cms_int(
        $cmsPage,
        'programs',
        'item_limit',
        8,
        1,
        12
    )
);

$visibleLatestActivities = array_slice(
    $latestActivities ?? [],
    0,
    public_cms_int(
        $cmsPage,
        'latest',
        'item_limit',
        3,
        1,
        6
    )
);
?>

<div class="garda-home">

    <!-- HERO -->
    <section class="garda-home-hero">

        <div class="garda-home-hero-copy">

            <span class="garda-home-eyebrow">
                <?= esc(public_cms_value(
                    $cmsPage,
                    'hero',
                    'eyebrow',
                    'Karang Taruna RW 01 • Kelurahan Randugarut'
                )) ?>
            </span>

            <h1>
                <?= esc(public_cms_value(
                    $cmsPage,
                    'hero',
                    'title',
                    'GARDA 01'
                )) ?>
            </h1>

            <h2>
                <?= esc(public_cms_value(
                    $cmsPage,
                    'hero',
                    'subtitle',
                    'Generasi Aktif Randugarut'
                )) ?>
            </h2>

            <p class="garda-home-manifesto">
                <?= nl2br(esc(public_cms_value(
                    $cmsPage,
                    'hero',
                    'manifesto',
                    "Guyub dalam kebersamaan.\nBergerak melalui karya.\nBerdampak bagi lingkungan."
                ))) ?>
            </p>

            <p class="garda-home-introduction">
                <?= esc(public_cms_value(
                    $cmsPage,
                    'hero',
                    'introduction',
                    'Ruang tumbuh dan kolaborasi pemuda RW 01 dalam kegiatan sosial, lingkungan, olahraga, kreativitas, pendidikan, usaha, dan pemberdayaan masyarakat.'
                )) ?>
            </p>

            <div class="garda-home-hero-actions">
                <a
                    href="<?= esc(public_cms_url(
                        $cmsPage,
                        'hero',
                        'primary_url',
                        '/kegiatan'
                    ), 'attr') ?>"
                    class="btn btn-primary"
                >
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'hero',
                        'primary_label',
                        'Lihat Gerak Kami'
                    )) ?>
                </a>

                <a
                    href="<?= esc(public_cms_url(
                        $cmsPage,
                        'hero',
                        'secondary_url',
                        '/profil'
                    ), 'attr') ?>"
                    class="btn garda-home-outline-button"
                >
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'hero',
                        'secondary_label',
                        'Kenali GARDA 01'
                    )) ?>
                </a>
            </div>

            <div
                class="garda-home-hero-watermark"
                aria-hidden="true"
            >
                <?= esc(public_cms_value(
                    $cmsPage,
                    'hero',
                    'watermark',
                    'G01'
                )) ?>
            </div>

        </div>

        <aside class="garda-home-featured">

            <?php if (!empty($featuredActivity)) : ?>

                <a
                    href="<?= base_url(
                        '/kegiatan/' . $featuredActivity['id']
                    ) ?>"
                    class="garda-home-featured-media"
                >

                    <?php if ($featuredHasImage) : ?>
                        <img
                            src="<?= base_url(
                                'uploads/activities/'
                                . $featuredActivity['documentation_file']
                            ) ?>"
                            alt="<?= esc(
                                $featuredActivity['title']
                            ) ?>"
                        >
                    <?php else : ?>
                        <div
                            class="garda-home-activity-fallback
                            program-<?= esc(
                                $programClass(
                                    $featuredActivity['program_slug']
                                    ?? null
                                )
                            ) ?>"
                        >
                            <span>
                                <?= esc(
                                    $featuredActivity['program_name']
                                    ?? 'GARDA 01'
                                ) ?>
                            </span>

                            <strong>Gerak Pemuda</strong>

                            <small>
                                Dokumentasi kegiatan GARDA 01
                            </small>
                        </div>
                    <?php endif; ?>

                    <span class="garda-home-featured-label">
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'hero',
                            'featured_label',
                            'Gerak Terbaru'
                        )) ?>
                    </span>

                </a>

                <div class="garda-home-featured-content">

                    <span class="garda-home-program-label">
                        <?= esc(
                            $featuredActivity['program_name']
                            ?? 'GARDA 01'
                        ) ?>
                    </span>

                    <h3>
                        <a
                            href="<?= base_url(
                                '/kegiatan/'
                                . $featuredActivity['id']
                            ) ?>"
                        >
                            <?= esc(
                                $featuredActivity['title']
                            ) ?>
                        </a>
                    </h3>

                    <p>
                        <?= esc(
                            $formatPublicDate(
                                $featuredActivity['activity_date']
                                ?? null
                            )
                        ) ?>

                        <span>•</span>

                        <?= esc(
                            $featuredActivity['location']
                            ?? 'Randugarut RW 01'
                        ) ?>
                    </p>

                    <a
                        href="<?= base_url(
                            '/kegiatan/'
                            . $featuredActivity['id']
                        ) ?>"
                        class="garda-home-text-link"
                    >
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'hero',
                            'featured_link_label',
                            'Lihat dokumentasi'
                        )) ?>
                        <span aria-hidden="true">→</span>
                    </a>

                </div>

            <?php else : ?>

                <div class="garda-home-featured-empty">

                    <img
                        src="<?= esc(
                            site_asset_url(
                                'site_logo',
                                'assets/img/logo-rw01.png'
                            ),
                            'attr'
                        ) ?>"
                        alt=""
                    >

                    <span>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'hero',
                            'featured_label',
                            'Gerak Terbaru'
                        )) ?>
                    </span>

                    <strong>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'hero',
                            'featured_empty_title',
                            'GARDA 01'
                        )) ?>
                    </strong>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'hero',
                            'featured_empty_body',
                            'Dokumentasi kegiatan akan segera ditampilkan.'
                        )) ?>
                    </p>

                </div>

            <?php endif; ?>

        </aside>

    </section>

    <!-- STATISTIK DAMPAK -->
    <?php if (public_cms_section_enabled(
        $cmsPage,
        'statistics',
        true
    )) : ?>
        <section class="garda-home-statistics">

            <article>
                <strong><?= esc($connectedRt ?? 0) ?></strong>
                <span>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'statistics',
                        'rt_label',
                        'RT Terhubung'
                    )) ?>
                </span>
                <small>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'statistics',
                        'rt_description',
                        'Wilayah pemuda yang terdata dan terlibat.'
                    )) ?>
                </small>
            </article>

            <article>
                <strong>
                    <?= esc($activeOfficials ?? 0) ?><?= esc(
                        public_cms_value(
                            $cmsPage,
                            'statistics',
                            'officials_suffix',
                            '+'
                        )
                    ) ?>
                </strong>
                <span>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'statistics',
                        'officials_label',
                        'Pengurus Aktif'
                    )) ?>
                </span>
                <small>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'statistics',
                        'officials_description',
                        'Penggerak organisasi dan program GARDA 01.'
                    )) ?>
                </small>
            </article>

            <article>
                <strong><?= esc($completedActivities ?? 0) ?></strong>
                <span>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'statistics',
                        'activities_label',
                        'Kegiatan Terlaksana'
                    )) ?>
                </span>
                <small>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'statistics',
                        'activities_description',
                        'Gerakan yang telah selesai dan terdokumentasi.'
                    )) ?>
                </small>
            </article>

            <article>
                <strong><?= esc($programCount ?? 0) ?></strong>
                <span>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'statistics',
                        'programs_label',
                        'Pilar Gerakan'
                    )) ?>
                </span>
                <small>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'statistics',
                        'programs_description',
                        'Ruang kontribusi pemuda dalam berbagai bidang.'
                    )) ?>
                </small>
            </article>

        </section>
    <?php endif; ?>

    <!-- NILAI UTAMA -->
    <?php if (public_cms_section_enabled(
        $cmsPage,
        'about',
        true
    )) : ?>
        <section class="garda-home-section garda-home-values">

            <div class="garda-home-section-heading">
                <span class="public-kicker">
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'about',
                        'kicker',
                        'Semangat GARDA 01'
                    )) ?>
                </span>

                <h2>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'about',
                        'title',
                        'Bertumbuh melalui kebersamaan dan tindakan nyata'
                    )) ?>
                </h2>

                <p>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'about',
                        'body',
                        'GARDA 01 hadir bukan sekadar sebagai struktur organisasi, tetapi sebagai ruang bagi pemuda untuk saling menguatkan, menciptakan karya, dan memberi manfaat bagi lingkungan.'
                    )) ?>
                </p>
            </div>

            <div class="garda-home-value-grid">

                <article>
                    <span class="garda-home-value-number">01</span>

                    <h3>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'about',
                            'value_one_title',
                            'Guyub'
                        )) ?>
                    </h3>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'about',
                            'value_one_body',
                            'Menyatukan pemuda, warga, dan berbagai unsur masyarakat melalui kebersamaan yang sehat, terbuka, dan saling menguatkan.'
                        )) ?>
                    </p>
                </article>

                <article>
                    <span class="garda-home-value-number">02</span>

                    <h3>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'about',
                            'value_two_title',
                            'Bergerak'
                        )) ?>
                    </h3>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'about',
                            'value_two_body',
                            'Mengubah gagasan menjadi kegiatan nyata melalui kolaborasi, tanggung jawab, dan keberanian mengambil peran.'
                        )) ?>
                    </p>
                </article>

                <article>
                    <span class="garda-home-value-number">03</span>

                    <h3>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'about',
                            'value_three_title',
                            'Berdampak'
                        )) ?>
                    </h3>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'about',
                            'value_three_body',
                            'Menghadirkan program yang relevan, terdokumentasi, dan memberikan manfaat yang dapat dirasakan lingkungan.'
                        )) ?>
                    </p>
                </article>

            </div>

        </section>
    <?php endif; ?>

    <!-- PILAR PROGRAM -->
    <?php if (public_cms_section_enabled(
        $cmsPage,
        'programs',
        true
    )) : ?>
        <section class="garda-home-section garda-home-programs">

            <div class="garda-home-section-heading-row">

                <div class="garda-home-section-heading">
                    <span class="public-kicker">
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'programs',
                            'kicker',
                            'Pilar Gerakan'
                        )) ?>
                    </span>

                    <h2>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'programs',
                            'title',
                            'Dari kepedulian menjadi aksi'
                        )) ?>
                    </h2>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'programs',
                            'body',
                            'Setiap pilar GARDA 01 menjadi ruang bagi pemuda untuk berkontribusi sesuai minat, kemampuan, dan kebutuhan lingkungan.'
                        )) ?>
                    </p>
                </div>

                <a
                    href="<?= esc(public_cms_url(
                        $cmsPage,
                        'programs',
                        'section_link_url',
                        '/program'
                    ), 'attr') ?>"
                    class="garda-home-section-link"
                >
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'programs',
                        'section_link_label',
                        'Seluruh Program'
                    )) ?>
                    <span aria-hidden="true">→</span>
                </a>

            </div>

            <?php if (!empty($visiblePrograms)) : ?>

                <div class="garda-home-program-grid">

                    <?php foreach ($visiblePrograms as $program) : ?>

                        <article class="garda-home-program-card">

                            <span class="garda-home-program-number">
                                <?= esc(
                                    $program['number']
                                    ?? str_pad(
                                        (string) (
                                            $program['display_order']
                                            ?? 0
                                        ),
                                        2,
                                        '0',
                                        STR_PAD_LEFT
                                    )
                                ) ?>
                            </span>

                            <span class="garda-home-program-category">
                                <?= esc(
                                    $program['label']
                                    ?? 'Pilar GARDA 01'
                                ) ?>
                            </span>

                            <h3><?= esc($program['name']) ?></h3>

                            <p>
                                <?= esc(
                                    mb_strimwidth(
                                        (string) (
                                            $program['short_description']
                                            ?? ''
                                        ),
                                        0,
                                        145,
                                        '…'
                                    )
                                ) ?>
                            </p>

                            <a
                                href="<?= base_url(
                                    '/program/' . $program['slug']
                                ) ?>"
                            >
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'programs',
                                    'card_link_label',
                                    'Pelajari program'
                                )) ?>
                                <span aria-hidden="true">→</span>
                            </a>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else : ?>

                <div class="public-empty">
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'programs',
                        'empty_message',
                        'Program GARDA 01 belum tersedia.'
                    )) ?>
                </div>

            <?php endif; ?>

        </section>
    <?php endif; ?>

    <!-- CERITA DAMPAK -->
    <?php if (
        public_cms_section_enabled(
            $cmsPage,
            'impact',
            true
        )
        && !empty($impactActivity)
    ) : ?>
        <?php
        $impactProgramName = $impactActivity['program_name']
            ?? 'GARDA 01';

        $impactProgramLabel = $impactActivity['program_label']
            ?? 'Gerakan Pemuda';

        $impactSummary = trim(
            (string) ($impactActivity['result'] ?? '')
        );

        if (mb_strlen($impactSummary) < 60) {
            $impactSummary = trim(
                (string) ($impactActivity['description'] ?? '')
            );
        }

        if (mb_strlen($impactSummary) < 60) {
            $impactSummary = public_cms_value(
                $cmsPage,
                'impact',
                'fallback_summary',
                'Gerakan bersama pemuda dan warga untuk menghadirkan manfaat nyata, memperkuat kepedulian, serta menjaga kebersamaan di lingkungan RW 01 Randugarut.'
            );
        }
        ?>

        <section class="g01-impact-story">
            <div class="g01-impact-story__media">
                <?php if ($impactHasImage) : ?>
                    <img
                        src="<?= base_url(
                            'uploads/activities/'
                            . $impactActivity['documentation_file']
                        ) ?>"
                        alt="<?= esc($impactActivity['title']) ?>"
                        loading="lazy"
                    >
                <?php else : ?>
                    <div class="g01-impact-story__fallback">
                        <span><?= esc($impactProgramName) ?></span>
                        <strong>
                            <?= esc(public_cms_value(
                                $cmsPage,
                                'impact',
                                'media_title',
                                'Cerita Dampak'
                            )) ?>
                        </strong>
                        <small>
                            <?= esc(public_cms_value(
                                $cmsPage,
                                'impact',
                                'media_subtitle',
                                'Guyub • Bergerak • Berdampak'
                            )) ?>
                        </small>
                    </div>
                <?php endif; ?>

                <div class="g01-impact-story__media-label">
                    <span><?= esc($impactProgramName) ?></span>
                    <small><?= esc($impactProgramLabel) ?></small>
                </div>
            </div>

            <div class="g01-impact-story__content">
                <span class="g01-impact-story__eyebrow">
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'impact',
                        'eyebrow',
                        'Cerita Dampak'
                    )) ?>
                </span>

                <span class="g01-impact-story__program">
                    <?= esc($impactProgramName) ?>
                </span>

                <h2><?= esc($impactActivity['title']) ?></h2>

                <p class="g01-impact-story__summary">
                    <?= esc(
                        mb_strimwidth(
                            $impactSummary,
                            0,
                            360,
                            '…'
                        )
                    ) ?>
                </p>

                <div class="g01-impact-story__meta">
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="3"></rect>
                            <path d="M7 3v4M17 3v4M3 10h18"></path>
                        </svg>

                        <?= esc(
                            $formatPublicDate(
                                $impactActivity['activity_date']
                                ?? null
                            )
                        ) ?>
                    </span>

                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                            <circle cx="12" cy="10" r="2.5"></circle>
                        </svg>

                        <?= esc(
                            $impactActivity['location']
                            ?? 'Randugarut RW 01'
                        ) ?>
                    </span>
                </div>

                <a
                    href="<?= base_url(
                        '/kegiatan/' . $impactActivity['id']
                    ) ?>"
                    class="g01-impact-story__button"
                >
                    <span>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'impact',
                            'button_label',
                            'Baca Cerita Kegiatan'
                        )) ?>
                    </span>
                    <span aria-hidden="true">→</span>
                </a>

                <div
                    class="g01-impact-story__watermark"
                    aria-hidden="true"
                >
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'impact',
                        'watermark',
                        '01'
                    )) ?>
                </div>
            </div>
        </section>

    <?php endif; ?>

    <!-- KEGIATAN TERBARU -->
    <?php if (public_cms_section_enabled(
        $cmsPage,
        'latest',
        true
    )) : ?>
        <section class="garda-home-section garda-home-latest">

            <div class="garda-home-section-heading-row">

                <div class="garda-home-section-heading">
                    <span class="public-kicker">
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'latest',
                            'kicker',
                            'Jejak Gerak GARDA 01'
                        )) ?>
                    </span>

                    <h2>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'latest',
                            'title',
                            'Kegiatan terbaru'
                        )) ?>
                    </h2>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'latest',
                            'body',
                            'Dokumentasi kegiatan, kolaborasi, dan kontribusi pemuda GARDA 01 bagi lingkungan.'
                        )) ?>
                    </p>
                </div>

                <a
                    href="<?= esc(public_cms_url(
                        $cmsPage,
                        'latest',
                        'section_link_url',
                        '/kegiatan'
                    ), 'attr') ?>"
                    class="garda-home-section-link"
                >
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'latest',
                        'section_link_label',
                        'Seluruh Kegiatan'
                    )) ?>
                    <span aria-hidden="true">→</span>
                </a>

            </div>

            <?php if (!empty($visibleLatestActivities)) : ?>

                <div class="garda-home-activity-grid">

                    <?php foreach (
                        $visibleLatestActivities as $activity
                    ) : ?>
                        <?php
                        $hasImage = $activityHasImage($activity);

                        $shortProgram = $programShortName(
                            $activity['program_name'] ?? null
                        );
                        ?>

                        <article class="garda-home-activity-card">

                            <a
                                href="<?= base_url(
                                    '/kegiatan/' . $activity['id']
                                ) ?>"
                                class="garda-home-activity-media"
                            >

                                <?php if ($hasImage) : ?>
                                    <img
                                        src="<?= base_url(
                                            'uploads/activities/'
                                            . $activity[
                                                'documentation_file'
                                            ]
                                        ) ?>"
                                        alt="<?= esc(
                                            $activity['title']
                                        ) ?>"
                                        loading="lazy"
                                    >
                                <?php else : ?>
                                    <div
                                        class="garda-home-activity-fallback
                                        program-<?= esc(
                                            $programClass(
                                                $activity['program_slug']
                                                ?? null
                                            )
                                        ) ?>"
                                    >
                                        <span>
                                            GARDA 01
                                            <?= esc($shortProgram) ?>
                                        </span>

                                        <strong>
                                            Dokumentasi Kegiatan
                                        </strong>

                                        <small>
                                            Guyub • Bergerak • Berdampak
                                        </small>
                                    </div>
                                <?php endif; ?>

                            </a>

                            <div class="garda-home-activity-content">

                                <span class="garda-home-program-label">
                                    <?= esc(
                                        $activity['program_name']
                                        ?? 'GARDA 01'
                                    ) ?>
                                </span>

                                <span class="garda-home-activity-date">
                                    <?= esc(
                                        $formatPublicDate(
                                            $activity['activity_date']
                                            ?? null
                                        )
                                    ) ?>
                                </span>

                                <h3>
                                    <a
                                        href="<?= base_url(
                                            '/kegiatan/'
                                            . $activity['id']
                                        ) ?>"
                                    >
                                        <?= esc($activity['title']) ?>
                                    </a>
                                </h3>

                                <p>
                                    <?= esc(
                                        $activity['location']
                                        ?? 'Randugarut RW 01'
                                    ) ?>
                                </p>

                                <a
                                    href="<?= base_url(
                                        '/kegiatan/' . $activity['id']
                                    ) ?>"
                                    class="garda-home-text-link"
                                >
                                    <?= esc(public_cms_value(
                                        $cmsPage,
                                        'latest',
                                        'card_link_label',
                                        'Lihat Dokumentasi'
                                    )) ?>
                                    <span aria-hidden="true">→</span>
                                </a>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else : ?>

                <div class="public-empty">
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'latest',
                        'empty_message',
                        'Dokumentasi kegiatan terbaru belum tersedia.'
                    )) ?>
                </div>

            <?php endif; ?>

        </section>
    <?php endif; ?>

    <!-- AJAKAN KOLABORASI -->
    <?php if (public_cms_section_enabled(
        $cmsPage,
        'collaboration',
        true
    )) : ?>
        <section class="garda-home-collaboration">

            <div class="garda-home-collaboration-copy">

                <span class="public-kicker">
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'collaboration',
                        'kicker',
                        'Bergerak Bersama'
                    )) ?>
                </span>

                <h2>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'collaboration',
                        'title',
                        'Mari hadirkan lebih banyak dampak untuk lingkungan'
                    )) ?>
                </h2>

                <p>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'collaboration',
                        'body',
                        'GARDA 01 terbuka untuk berkolaborasi dengan warga, komunitas, UMKM, lembaga pendidikan, pemerintah, serta mitra sosial dalam kegiatan yang bermanfaat bagi masyarakat.'
                    )) ?>
                </p>

                <div class="garda-home-collaboration-actions">

                    <a
                        href="<?= esc(public_cms_url(
                            $cmsPage,
                            'collaboration',
                            'primary_url',
                            '/kontak'
                        ), 'attr') ?>"
                        class="btn btn-primary"
                    >
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'collaboration',
                            'primary_label',
                            'Hubungi GARDA 01'
                        )) ?>
                    </a>

                    <a
                        href="<?= esc(public_cms_url(
                            $cmsPage,
                            'collaboration',
                            'secondary_url',
                            '/program'
                        ), 'attr') ?>"
                        class="btn garda-home-outline-button"
                    >
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'collaboration',
                            'secondary_label',
                            'Lihat Program'
                        )) ?>
                    </a>

                </div>

            </div>

            <aside class="garda-home-collaboration-list">

                <span>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'collaboration',
                        'list_label',
                        'Terbuka untuk kolaborasi:'
                    )) ?>
                </span>

                <ul>
                    <?php foreach (public_cms_lines(
                        $cmsPage,
                        'collaboration',
                        'collaboration_items',
                        "Sosial dan kemanusiaan
Lingkungan dan kebersihan
Olahraga dan kepemudaan
Pendidikan dan keterampilan
Usaha produktif pemuda
Media dan kreativitas"
                    ) as $item) : ?>
                        <li><?= esc($item) ?></li>
                    <?php endforeach; ?>
                </ul>

            </aside>

            <div
                class="garda-home-collaboration-watermark"
                aria-hidden="true"
            >
                <?= esc(public_cms_value(
                    $cmsPage,
                    'collaboration',
                    'watermark',
                    'GARDA 01'
                )) ?>
            </div>

        </section>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>