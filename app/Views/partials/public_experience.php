<?php

$activePage = $activePage ?? '';
$pageTitle = trim((string) ($pageTitle ?? 'GARDA 01'));

$organizationName = site_setting(
    'organization_name',
    'GARDA 01'
);

$organizationFullName = site_setting(
    'organization_full_name',
    'Generasi Aktif Randugarut'
);

$organizationTagline = site_setting(
    'organization_tagline',
    'Guyub • Bergerak • Berdampak'
);

$experiencePages = [
    'home' => ['01', 'Beranda', 'Mulai dari sini'],
    'profile' => ['02', 'Tentang', 'Kenali siapa kami'],
    'programs' => ['03', 'Program', 'Tujuh ruang kontribusi'],
    'program_detail' => ['03', 'Program', 'Mendalami satu pilar'],
    'activities' => ['04', 'Kegiatan', 'Jejak gerak nyata'],
    'activity_detail' => ['04', 'Cerita Gerak', 'Satu aksi, satu cerita'],
    'officials' => ['05', 'Pengurus', 'Manusia di balik gerakan'],
    'contact' => ['06', 'Kontak', 'Mari bergerak bersama'],
];

$experiencePage = $experiencePages[$activePage]
    ?? ['00', 'GARDA 01', 'Guyub, bergerak, berdampak'];

$experiencePage[1] = public_translate_text(
    $experiencePage[1]
);
$experiencePage[2] = public_translate_text(
    $experiencePage[2]
);

$navigationItems = website_navigation_items('header');
?>

<div
    class="g01-arrival"
    id="g01Arrival"
    data-page-label="<?= esc($experiencePage[1], 'attr') ?>"
    data-chapter-unit="<?= esc(
        public_locale() === 'en'
            ? 'chapters'
            : 'bab',
        'attr'
    ) ?>"
    aria-hidden="true"
>
    <div class="g01-arrival__grid" aria-hidden="true"></div>

    <div class="g01-arrival__content">
        <div class="g01-arrival__meta">
            <span>G/01 · PUBLIC EXPEDITION</span>
            <span>CH / <?= esc($experiencePage[0]) ?></span>
        </div>

        <strong id="g01ArrivalLabel">
            <?= esc($experiencePage[1]) ?>
        </strong>

        <div class="g01-arrival__rule">
            <span></span>
        </div>

        <small><?= esc($experiencePage[2]) ?></small>
    </div>
</div>

<div
    class="g01-reading-progress"
    id="g01ReadingProgress"
    role="progressbar"
    aria-label="Progres membaca halaman"
    aria-valuemin="0"
    aria-valuemax="100"
    aria-valuenow="0"
>
    <span></span>
</div>

<p class="g01-coordinate" aria-hidden="true">
    <span>RANDUGARUT / RW 01</span>
    <strong><?= esc($experiencePage[0]) ?></strong>
</p>

<aside
    class="g01-chapter-rail"
    id="g01ChapterRail"
    aria-label="Navigator bab halaman"
>
    <button
        type="button"
        class="g01-chapter-rail__toggle"
        id="g01ChapterToggle"
        aria-label="Buka navigator bab halaman"
        aria-controls="g01ChapterPanel"
        aria-expanded="false"
    >
        <span class="g01-chapter-rail__number" id="g01ChapterNumber">
            01
        </span>

        <span class="g01-chapter-rail__label" id="g01ChapterLabel">
            Pembuka
        </span>

        <span class="g01-chapter-rail__icon" aria-hidden="true">
            +
        </span>
    </button>

    <div class="g01-chapter-rail__panel" id="g01ChapterPanel">
        <p>
            <span>Di halaman ini</span>
            <small id="g01ChapterCount">
                00 <?= public_locale() === 'en'
                    ? 'chapters'
                    : 'bab' ?>
            </small>
        </p>

        <nav
            class="g01-chapter-rail__links"
            id="g01ChapterLinks"
            aria-label="Daftar bab halaman"
        ></nav>
    </div>
</aside>

<button
    type="button"
    class="g01-deck-trigger"
    id="g01DeckTrigger"
    aria-controls="g01Deck"
    aria-expanded="false"
    data-g01-magnetic
>
    <span>G</span>

    <span>
        <strong>Jelajah</strong>
        <small>Tekan G</small>
    </span>
</button>

<div
    class="g01-deck"
    id="g01Deck"
    aria-hidden="true"
>
    <button
        type="button"
        class="g01-deck__backdrop"
        aria-label="Tutup navigator"
        data-g01-deck-close
    ></button>

    <section
        class="g01-deck__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="g01DeckTitle"
    >
        <header class="g01-deck__header">
            <div>
                <span>G/01 · FIELD DIRECTORY</span>
                <strong><?= esc($organizationName) ?></strong>
            </div>

            <button
                type="button"
                class="g01-deck__close"
                aria-label="Tutup navigator"
                data-g01-deck-close
                data-g01-magnetic
            >
                <span></span>
                <span></span>
            </button>
        </header>

        <div class="g01-deck__intro">
            <p>Jangan hanya melihat.</p>

            <h2 id="g01DeckTitle">
                Pilih arah,
                <em>temukan gerak.</em>
            </h2>
        </div>

        <nav class="g01-deck__navigation" aria-label="Direktori situs">
            <?php foreach ($navigationItems as $index => $item) : ?>
                <?php
                $isCurrent =
                    website_navigation_item_active(
                        $item,
                        $activePage
                    );

                $targetBlank =
                    ($item['target'] ?? 'self')
                    === 'blank';

                $itemUrl = website_navigation_url(
                    (string) $item['url']
                );
                ?>

                <a
                    href="<?= esc($itemUrl, 'attr') ?>"
                    class="g01-deck__link <?= $isCurrent
                        ? 'is-current'
                        : '' ?>"
                    <?= $isCurrent
                        ? 'aria-current="page"'
                        : '' ?>
                    <?= $targetBlank
                        ? 'target="_blank" rel="noopener noreferrer"'
                        : '' ?>
                >
                    <small>
                        <?= str_pad(
                            (string) ($index + 1),
                            2,
                            '0',
                            STR_PAD_LEFT
                        ) ?>
                    </small>

                    <strong><?= esc($item['label']) ?></strong>

                    <span aria-hidden="true">↗</span>
                </a>
            <?php endforeach; ?>
        </nav>

        <footer class="g01-deck__footer">
            <a href="<?= public_url('/') ?>">
                <span aria-hidden="true">←</span>
                Kembali ke Introducing
            </a>

            <p>
                <?= esc($organizationFullName) ?>
                <span>·</span>
                <?= esc($organizationTagline) ?>
            </p>
        </footer>
    </section>
</div>

<div class="g01-pointer" id="g01Pointer" aria-hidden="true">
    <span></span>
</div>
