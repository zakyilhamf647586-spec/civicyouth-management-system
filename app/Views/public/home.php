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
?>

<div class="garda-home">

    <!-- HERO -->
    <section class="garda-home-hero">

        <div class="garda-home-hero-copy">

            <span class="garda-home-eyebrow">
                Karang Taruna RW 01 • Kelurahan Randugarut
            </span>

            <h1>GARDA 01</h1>

            <h2>Generasi Aktif Randugarut</h2>

            <p class="garda-home-manifesto">
                Guyub dalam kebersamaan.<br>
                Bergerak melalui karya.<br>
                Berdampak bagi lingkungan.
            </p>

            <p class="garda-home-introduction">
                Ruang tumbuh dan kolaborasi pemuda RW 01 dalam
                kegiatan sosial, lingkungan, olahraga, kreativitas,
                pendidikan, usaha, dan pemberdayaan masyarakat.
            </p>

            <div class="garda-home-hero-actions">
                <a
                    href="<?= base_url('/kegiatan') ?>"
                    class="btn btn-primary"
                >
                    Lihat Gerak Kami
                </a>

                <a
                    href="<?= base_url('/profil') ?>"
                    class="btn garda-home-outline-button"
                >
                    Kenali GARDA 01
                </a>
            </div>

            <div
                class="garda-home-hero-watermark"
                aria-hidden="true"
            >
                G01
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
                        Gerak Terbaru
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
                        Lihat dokumentasi
                        <span aria-hidden="true">→</span>
                    </a>

                </div>

            <?php else : ?>

                <div class="garda-home-featured-empty">

                    <img
                        src="<?= base_url(
                            'assets/img/logo-rw01.png'
                        ) ?>"
                        alt=""
                    >

                    <span>Gerak Terbaru</span>

                    <strong>GARDA 01</strong>

                    <p>
                        Dokumentasi kegiatan akan segera ditampilkan.
                    </p>

                </div>

            <?php endif; ?>

        </aside>

    </section>

    <!-- STATISTIK DAMPAK -->
    <section class="garda-home-statistics">

        <article>
            <strong><?= esc($connectedRt ?? 0) ?></strong>
            <span>RT Terhubung</span>
            <small>
                Wilayah pemuda yang terdata dan terlibat.
            </small>
        </article>

        <article>
            <strong><?= esc($activeOfficials ?? 0) ?>+</strong>
            <span>Pengurus Aktif</span>
            <small>
                Penggerak organisasi dan program GARDA 01.
            </small>
        </article>

        <article>
            <strong><?= esc($completedActivities ?? 0) ?></strong>
            <span>Kegiatan Terlaksana</span>
            <small>
                Gerakan yang telah selesai dan terdokumentasi.
            </small>
        </article>

        <article>
            <strong><?= esc($programCount ?? 0) ?></strong>
            <span>Pilar Gerakan</span>
            <small>
                Ruang kontribusi pemuda dalam berbagai bidang.
            </small>
        </article>

    </section>

    <!-- NILAI UTAMA -->
    <section class="garda-home-section garda-home-values">

        <div class="garda-home-section-heading">
            <span class="public-kicker">
                Semangat GARDA 01
            </span>

            <h2>
                Bertumbuh melalui kebersamaan dan tindakan nyata
            </h2>

            <p>
                GARDA 01 hadir bukan sekadar sebagai struktur
                organisasi, tetapi sebagai ruang bagi pemuda untuk
                saling menguatkan, menciptakan karya, dan memberi
                manfaat bagi lingkungan.
            </p>
        </div>

        <div class="garda-home-value-grid">

            <article>
                <span class="garda-home-value-number">
                    01
                </span>

                <h3>Guyub</h3>

                <p>
                    Menyatukan pemuda, warga, dan berbagai unsur
                    masyarakat melalui kebersamaan yang sehat,
                    terbuka, dan saling menguatkan.
                </p>
            </article>

            <article>
                <span class="garda-home-value-number">
                    02
                </span>

                <h3>Bergerak</h3>

                <p>
                    Mengubah gagasan menjadi kegiatan nyata melalui
                    kolaborasi, tanggung jawab, dan keberanian
                    mengambil peran.
                </p>
            </article>

            <article>
                <span class="garda-home-value-number">
                    03
                </span>

                <h3>Berdampak</h3>

                <p>
                    Menghadirkan program yang relevan,
                    terdokumentasi, dan memberikan manfaat yang
                    dapat dirasakan lingkungan.
                </p>
            </article>

        </div>

    </section>

    <!-- PILAR PROGRAM -->
    <section class="garda-home-section garda-home-programs">

        <div class="garda-home-section-heading-row">

            <div class="garda-home-section-heading">
                <span class="public-kicker">
                    Pilar Gerakan
                </span>

                <h2>Dari kepedulian menjadi aksi</h2>

                <p>
                    Setiap pilar GARDA 01 menjadi ruang bagi pemuda
                    untuk berkontribusi sesuai minat, kemampuan,
                    dan kebutuhan lingkungan.
                </p>
            </div>

            <a
                href="<?= base_url('/program') ?>"
                class="garda-home-section-link"
            >
                Seluruh Program
                <span aria-hidden="true">→</span>
            </a>

        </div>

        <?php if (!empty($programs)) : ?>

            <div class="garda-home-program-grid">

                <?php foreach ($programs as $program) : ?>

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

                        <h3>
                            <?= esc($program['name']) ?>
                        </h3>

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
                            Pelajari program
                            <span aria-hidden="true">→</span>
                        </a>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else : ?>

            <div class="public-empty">
                Program GARDA 01 belum tersedia.
            </div>

        <?php endif; ?>

    </section>

    <!-- CERITA DAMPAK -->
    <?php if (!empty($impactActivity)) : ?>

        <section class="garda-home-impact">

            <div class="garda-home-impact-media">

                <?php if ($impactHasImage) : ?>
                    <img
                        src="<?= base_url(
                            'uploads/activities/'
                            . $impactActivity['documentation_file']
                        ) ?>"
                        alt="<?= esc(
                            $impactActivity['title']
                        ) ?>"
                        loading="lazy"
                    >
                <?php else : ?>
                    <div
                        class="garda-home-activity-fallback
                        program-<?= esc(
                            $programClass(
                                $impactActivity['program_slug']
                                ?? null
                            )
                        ) ?>"
                    >
                        <span>
                            <?= esc(
                                $impactActivity['program_name']
                                ?? 'GARDA 01'
                            ) ?>
                        </span>

                        <strong>Cerita Dampak</strong>

                        <small>
                            Guyub • Bergerak • Berdampak
                        </small>
                    </div>
                <?php endif; ?>

            </div>

            <div class="garda-home-impact-content">

                <span class="public-kicker">
                    Cerita Dampak
                </span>

                <?php if (
                    !empty($impactActivity['program_name'])
                ) : ?>
                    <span class="garda-home-impact-program">
                        <?= esc(
                            $impactActivity['program_name']
                        ) ?>
                    </span>
                <?php endif; ?>

                <h2>
                    <?= esc($impactActivity['title']) ?>
                </h2>

                <p>
                    <?= esc(
                        mb_strimwidth(
                            (string) (
                                $impactActivity['result']
                                ?: $impactActivity['description']
                                ?: 'Kegiatan pemuda GARDA 01 sebagai bentuk kontribusi nyata bagi lingkungan RW 01 Randugarut.'
                            ),
                            0,
                            310,
                            '…'
                        )
                    ) ?>
                </p>

                <div class="garda-home-impact-meta">
                    <span>
                        <?= esc(
                            $formatPublicDate(
                                $impactActivity['activity_date']
                                ?? null
                            )
                        ) ?>
                    </span>

                    <span>
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
                    class="btn btn-primary"
                >
                    Baca Cerita Kegiatan
                </a>

            </div>

        </section>

    <?php endif; ?>

    <!-- KEGIATAN TERBARU -->
    <section class="garda-home-section garda-home-latest">

        <div class="garda-home-section-heading-row">

            <div class="garda-home-section-heading">
                <span class="public-kicker">
                    Jejak Gerak GARDA 01
                </span>

                <h2>Kegiatan terbaru</h2>

                <p>
                    Dokumentasi kegiatan, kolaborasi, dan kontribusi
                    pemuda GARDA 01 bagi lingkungan.
                </p>
            </div>

            <a
                href="<?= base_url('/kegiatan') ?>"
                class="garda-home-section-link"
            >
                Seluruh Kegiatan
                <span aria-hidden="true">→</span>
            </a>

        </div>

        <?php if (!empty($latestActivities)) : ?>

            <div class="garda-home-activity-grid">

                <?php foreach ($latestActivities as $activity) : ?>
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
                                    '/kegiatan/'
                                    . $activity['id']
                                ) ?>"
                                class="garda-home-text-link"
                            >
                                Lihat Dokumentasi
                                <span aria-hidden="true">→</span>
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else : ?>

            <div class="public-empty">
                Dokumentasi kegiatan terbaru belum tersedia.
            </div>

        <?php endif; ?>

    </section>

    <!-- AJAKAN KOLABORASI -->
    <section class="garda-home-collaboration">

        <div class="garda-home-collaboration-copy">

            <span class="public-kicker">
                Bergerak Bersama
            </span>

            <h2>
                Mari hadirkan lebih banyak dampak untuk lingkungan
            </h2>

            <p>
                GARDA 01 terbuka untuk berkolaborasi dengan warga,
                komunitas, UMKM, lembaga pendidikan, pemerintah,
                serta mitra sosial dalam kegiatan yang bermanfaat
                bagi masyarakat.
            </p>

            <div class="garda-home-collaboration-actions">

                <a
                    href="<?= base_url('/profil') ?>"
                    class="btn btn-primary"
                >
                    Kenali GARDA 01
                </a>

                <a
                    href="<?= base_url('/program') ?>"
                    class="btn garda-home-outline-button"
                >
                    Lihat Program
                </a>

            </div>

        </div>

        <aside class="garda-home-collaboration-list">

            <span>Terbuka untuk kolaborasi:</span>

            <ul>
                <li>Sosial dan kemanusiaan</li>
                <li>Lingkungan dan kebersihan</li>
                <li>Olahraga dan kepemudaan</li>
                <li>Pendidikan dan keterampilan</li>
                <li>Usaha produktif pemuda</li>
                <li>Media dan kreativitas</li>
            </ul>

        </aside>

        <div
            class="garda-home-collaboration-watermark"
            aria-hidden="true"
        >
            GARDA 01
        </div>

    </section>

</div>

<?= $this->endSection() ?>