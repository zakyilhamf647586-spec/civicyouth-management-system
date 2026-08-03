<?php

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

$logoUrl = site_asset_url(
    'site_logo',
    'assets/img/logo-rw01.png'
);

$faviconUrl = site_asset_url(
    'site_favicon',
    'assets/img/logo-rw01.png'
);

$pageTitle = $title
    ?? 'GARDA 01 — Introducing | Generasi Aktif Randugarut';

$pageDescription = $metaDescription
    ?? 'Gerbang digital GARDA 01, Generasi Aktif Randugarut.';

$publicLocale = public_locale();
$pageTitle = public_translate_text(
    (string) $pageTitle
);
$pageDescription = public_translate_text(
    (string) $pageDescription
);
$canonicalUrl = public_url('/');
$alternateIdUrl = public_url('/', 'id');
$alternateEnUrl = public_url('/', 'en');
$stylesheet = 'assets/css/public-introducing.css';
$script = 'assets/js/public-introducing.js';
$preferencesStylesheet =
    'assets/css/public-preferences.css';
$preferencesScript =
    'assets/js/public-preferences.js';

$stylesheetVersion = is_file(FCPATH . $stylesheet)
    ? (string) filemtime(FCPATH . $stylesheet)
    : '1';

$scriptVersion = is_file(FCPATH . $script)
    ? (string) filemtime(FCPATH . $script)
    : '1';

$destinations = [
    [
        'name' => public_t(
            'navigation.home',
            'Beranda'
        ),
        'url' => public_url('/home'),
        'index' => '01',
    ],
    [
        'name' => public_t(
            'navigation.profile',
            'Tentang'
        ),
        'url' => public_url('/profil'),
        'index' => '02',
    ],
    [
        'name' => public_t(
            'navigation.programs',
            'Program'
        ),
        'url' => public_url('/program'),
        'index' => '03',
    ],
    [
        'name' => public_t(
            'navigation.activities',
            'Kegiatan'
        ),
        'url' => public_url('/kegiatan'),
        'index' => '04',
    ],
    [
        'name' => public_t(
            'navigation.officials',
            'Pengurus'
        ),
        'url' => public_url('/pengurus'),
        'index' => '05',
    ],
    [
        'name' => public_t(
            'navigation.contact',
            'Kontak'
        ),
        'url' => public_url('/kontak'),
        'index' => '06',
    ],
];

$pillars = [
    ['name' => $publicLocale === 'en' ? 'Care' : 'Peduli', 'code' => '01', 'x' => '50%', 'y' => '4%'],
    ['name' => $publicLocale === 'en' ? 'Green' : 'Hijau', 'code' => '02', 'x' => '78%', 'y' => '17%'],
    ['name' => 'Sport', 'code' => '03', 'x' => '92%', 'y' => '50%'],
    ['name' => $publicLocale === 'en' ? 'Creative' : 'Kreatif', 'code' => '04', 'x' => '76%', 'y' => '82%'],
    ['name' => 'Enterprise', 'code' => '05', 'x' => '49%', 'y' => '94%'],
    ['name' => $publicLocale === 'en' ? 'Learning' : 'Belajar', 'code' => '06', 'x' => '12%', 'y' => '70%'],
    ['name' => $publicLocale === 'en' ? 'Values' : 'Berkah', 'code' => '07', 'x' => '7%', 'y' => '28%'],
];

$structuredData = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $pageTitle,
    'description' => $pageDescription,
    'url' => $canonicalUrl,
    'inLanguage' => $publicLocale === 'en'
        ? 'en-US'
        : 'id-ID',
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $organizationName,
        'url' => $canonicalUrl,
    ],
], JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT);

ob_start();
?>
<!DOCTYPE html>
<html lang="<?= esc($publicLocale, 'attr') ?>">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title><?= esc($pageTitle) ?></title>

    <meta
        name="description"
        content="<?= esc($pageDescription, 'attr') ?>"
    >

    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#031321">

    <link
        rel="canonical"
        href="<?= esc($canonicalUrl, 'attr') ?>"
    >

    <link
        rel="alternate"
        hreflang="id-ID"
        href="<?= esc($alternateIdUrl, 'attr') ?>"
    >

    <link
        rel="alternate"
        hreflang="en"
        href="<?= esc($alternateEnUrl, 'attr') ?>"
    >

    <link
        rel="alternate"
        hreflang="x-default"
        href="<?= esc($alternateIdUrl, 'attr') ?>"
    >

    <link
        rel="icon"
        href="<?= esc($faviconUrl, 'attr') ?>"
    >

    <meta property="og:type" content="website">
    <meta
        property="og:title"
        content="<?= esc($pageTitle, 'attr') ?>"
    >
    <meta
        property="og:description"
        content="<?= esc($pageDescription, 'attr') ?>"
    >
    <meta
        property="og:url"
        content="<?= esc($canonicalUrl, 'attr') ?>"
    >
    <meta
        property="og:image"
        content="<?= esc($logoUrl, 'attr') ?>"
    >
    <meta
        property="og:site_name"
        content="<?= esc($organizationName, 'attr') ?>"
    >
    <meta
        property="og:locale"
        content="<?= $publicLocale === 'en'
            ? 'en_US'
            : 'id_ID' ?>"
    >

    <meta
        property="og:locale:alternate"
        content="<?= $publicLocale === 'en'
            ? 'id_ID'
            : 'en_US' ?>"
    >

    <meta name="twitter:card" content="summary">
    <meta
        name="twitter:title"
        content="<?= esc($pageTitle, 'attr') ?>"
    >
    <meta
        name="twitter:description"
        content="<?= esc($pageDescription, 'attr') ?>"
    >
    <meta
        name="twitter:image"
        content="<?= esc($logoUrl, 'attr') ?>"
    >

    <?php if ($structuredData !== false) : ?>
        <script type="application/ld+json"><?= $structuredData ?></script>
    <?php endif; ?>

    <?= view('partials/public_theme_bootstrap') ?>

    <link
        rel="stylesheet"
        href="<?= base_url($stylesheet) ?>?v=<?= esc(
            $stylesheetVersion,
            'attr'
        ) ?>"
    >

    <?php if (is_file(FCPATH . $preferencesStylesheet)) : ?>
        <link
            rel="stylesheet"
            href="<?= base_url($preferencesStylesheet) ?>?v=<?= esc(
                (string) filemtime(
                    FCPATH . $preferencesStylesheet
                ),
                'attr'
            ) ?>"
        >
    <?php endif; ?>

    <script
        src="<?= base_url($script) ?>?v=<?= esc(
            $scriptVersion,
            'attr'
        ) ?>"
    ></script>
</head>
<body class="g01-intro-body">
    <?= view('partials/public_preferences', [
        'preferenceContext' => 'introducing',
    ]) ?>

    <main class="g01-intro" id="g01-intro-main">
        <div class="g01-intro-noise" aria-hidden="true"></div>
        <div class="g01-intro-pointer" aria-hidden="true"></div>

        <aside
            class="g01-intro-progress"
            aria-label="Progres perjalanan"
        >
            <span id="g01IntroChapter">01</span>

            <span class="g01-intro-progress-track" aria-hidden="true">
                <span id="g01IntroProgressFill"></span>
            </span>

            <span>05</span>
        </aside>

        <a
            class="g01-intro-skip"
            id="g01IntroSkip"
            href="#gateway"
        >
            Lewati intro
            <span aria-hidden="true">↘</span>
        </a>

        <section
            class="g01-intro-chapter g01-intro-hero"
            id="start"
            data-intro-chapter
        >
            <div class="g01-intro-sparks" aria-hidden="true">
                <?php for ($spark = 0; $spark < 18; $spark++) : ?>
                    <span
                        style="
                            --spark-x: <?= (($spark * 63 + 7) % 96) ?>%;
                            --spark-y: <?= (($spark * 41 + 11) % 91) ?>%;
                            --spark-size: <?= 1 + ($spark % 3) ?>px;
                            --spark-opacity: <?= 0.2 + (($spark % 5) * 0.12) ?>;
                            --spark-duration: <?= 3 + (($spark % 5) * 0.7) ?>s;
                        "
                    ></span>
                <?php endfor; ?>
            </div>

            <div class="g01-intro-storm" aria-hidden="true">
                <svg
                    class="g01-intro-lightning-field"
                    viewBox="0 0 1440 900"
                    preserveAspectRatio="none"
                    focusable="false"
                >
                    <path
                        class="g01-intro-lightning g01-intro-lightning--left"
                        pathLength="1"
                        d="M 72 -20 L 188 112 L 145 182 L 288 284 L 232 362 L 342 438"
                    ></path>
                    <path
                        class="g01-intro-lightning g01-intro-lightning--left-branch"
                        pathLength="1"
                        d="M 188 112 L 286 128 L 334 186"
                    ></path>
                    <path
                        class="g01-intro-lightning g01-intro-lightning--core"
                        pathLength="1"
                        d="M 824 32 L 770 122 L 812 184 L 746 270 L 778 326"
                    ></path>
                    <path
                        class="g01-intro-lightning g01-intro-lightning--right"
                        pathLength="1"
                        d="M 1370 -18 L 1248 106 L 1301 182 L 1166 278 L 1214 358 L 1112 429"
                    ></path>
                    <path
                        class="g01-intro-lightning g01-intro-lightning--right-branch"
                        pathLength="1"
                        d="M 1248 106 L 1152 126 L 1094 192"
                    ></path>
                </svg>

                <span class="g01-intro-shockwave g01-intro-shockwave--one"></span>
                <span class="g01-intro-shockwave g01-intro-shockwave--two"></span>
            </div>

            <div
                class="g01-intro-orbit g01-intro-orbit--one"
                aria-hidden="true"
            ></div>

            <div
                class="g01-intro-orbit g01-intro-orbit--two"
                aria-hidden="true"
            ></div>

            <div
                class="g01-intro-crosshair"
                aria-hidden="true"
            ></div>

            <div class="g01-intro-scene-meta g01-intro-scene-meta--left">
                <span>06°59&apos; S</span>
                <span>110°20&apos; E</span>
            </div>

            <div class="g01-intro-scene-meta g01-intro-scene-meta--right">
                <span>RANDUGARUT</span>
                <span>RW 01 / 2026</span>
            </div>

            <div class="g01-intro-hero-copy" data-intro-reveal>
                <p class="g01-intro-eyebrow">
                    Sebuah gerbang digital dari sudut kecil Randugarut
                </p>

                <h1 class="g01-intro-title" aria-label="GARDA 01">
                    <span
                        class="g01-intro-title-garda"
                        data-signal="GARDA"
                    >GARDA</span>
                    <span
                        class="g01-intro-title-zero"
                        data-signal="0"
                    >0</span>
                    <span
                        class="g01-intro-title-one"
                        data-signal="1"
                    >1</span>
                </h1>

                <p class="g01-intro-opening">
                    Bukan sekadar situs organisasi.
                    <br>
                    Ini adalah jejak dari mereka yang memilih untuk bergerak.
                </p>

                <div class="g01-intro-signal" aria-hidden="true">
                    <span>SIGNAL</span>
                    <i><b></b></i>
                    <strong>ACTIVE / 01</strong>
                </div>
            </div>

            <a
                class="g01-intro-scroll"
                href="#manifesto"
            >
                <span class="g01-intro-scroll-wheel" aria-hidden="true">
                    <span></span>
                </span>

                <span>
                    Gulir perlahan
                    <small>perjalanan dimulai di bawah</small>
                </span>
            </a>

            <p class="g01-intro-edition">INTRODUCING / 001</p>
        </section>

        <section
            class="g01-intro-chapter g01-intro-manifesto"
            id="manifesto"
            data-intro-chapter
        >
            <div class="g01-intro-manifesto-index" aria-hidden="true">
                01
            </div>

            <div class="g01-intro-manifesto-heading" data-intro-reveal>
                <p class="g01-intro-eyebrow">
                    Kami mulai dengan satu keyakinan
                </p>

                <h2>
                    KAMI
                    <span>BUKAN</span>
                    PENONTON.
                </h2>
            </div>

            <div
                class="g01-intro-manifesto-statement"
                data-intro-reveal
            >
                <span
                    class="g01-intro-statement-rule"
                    aria-hidden="true"
                ></span>

                <p>
                    Di lingkungan kecil, dampak tidak harus menunggu sesuatu
                    yang besar. Ia dimulai dari menyapa, membantu,
                    berkarya—lalu mengajak yang lain ikut bergerak.
                </p>

                <small>
                    <?= esc($organizationName) ?>
                    /
                    <?= esc(strtoupper($organizationFullName)) ?>
                </small>
            </div>

            <div class="g01-intro-kinetic" aria-hidden="true">
                <div>
                    <?php for ($repeat = 0; $repeat < 2; $repeat++) : ?>
                        <span>GUYUB</span>
                        <i>✦</i>
                        <span>BERGERAK</span>
                        <i>✦</i>
                        <span>BERDAMPAK</span>
                        <i>✦</i>
                    <?php endfor; ?>
                </div>
            </div>
        </section>

        <section
            class="g01-intro-chapter g01-intro-origin"
            id="origin"
            data-intro-chapter
        >
            <div class="g01-intro-compass" aria-hidden="true">
                <span class="g01-intro-compass-ring g01-intro-ring-a"></span>
                <span class="g01-intro-compass-ring g01-intro-ring-b"></span>
                <span class="g01-intro-axis g01-intro-axis-x"></span>
                <span class="g01-intro-axis g01-intro-axis-y"></span>
                <span class="g01-intro-compass-core">01</span>
            </div>

            <div class="g01-intro-origin-copy" data-intro-reveal>
                <p class="g01-intro-eyebrow">Titik nol kami</p>

                <h2>
                    DARI
                    <br>
                    <em>RW 01,</em>
                    <br>
                    KAMI MULAI.
                </h2>

                <p>
                    Kami tidak datang dengan janji untuk mengubah dunia dalam
                    semalam. Kami datang dengan kesediaan untuk hadir—dan
                    mengubah apa yang paling dekat terlebih dahulu.
                </p>
            </div>

            <div class="g01-intro-coordinates" data-intro-reveal>
                <div>
                    <strong>04</strong>
                    <span>RT TERHUBUNG</span>
                </div>

                <div>
                    <strong>07</strong>
                    <span>RUANG KONTRIBUSI</span>
                </div>

                <div>
                    <strong>01</strong>
                    <span>GERAKAN BERSAMA</span>
                </div>
            </div>
        </section>

        <section
            class="g01-intro-chapter g01-intro-universe"
            id="universe"
            data-intro-chapter
        >
            <div class="g01-intro-universe-copy" data-intro-reveal>
                <p class="g01-intro-eyebrow">Ekosistem gerakan</p>

                <h2>
                    TUJUH RUANG.
                    <br>
                    <span>SATU DENYUT.</span>
                </h2>

                <p>
                    Setiap orang punya cara berbeda untuk berdampak.
                    Temukan ruangmu, bawa energimu, dan biarkan semuanya
                    saling terhubung.
                </p>
            </div>

            <div class="g01-intro-system" data-intro-reveal>
                <div class="g01-intro-system-ring g01-intro-system-ring--one"></div>
                <div class="g01-intro-system-ring g01-intro-system-ring--two"></div>
                <div class="g01-intro-system-ring g01-intro-system-ring--three"></div>

                <div class="g01-intro-system-core">
                    <small>GARDA</small>
                    <strong>01</strong>
                    <span>THE CORE</span>
                </div>

                <?php foreach ($pillars as $pillar) : ?>
                    <div
                        class="g01-intro-pillar"
                        style="
                            --node-x: <?= esc($pillar['x'], 'attr') ?>;
                            --node-y: <?= esc($pillar['y'], 'attr') ?>;
                        "
                    >
                        <small><?= esc($pillar['code']) ?></small>
                        <strong><?= esc($pillar['name']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section
            class="g01-intro-chapter g01-intro-gateway"
            id="gateway"
            data-intro-chapter
        >
            <div class="g01-intro-gateway-glow" aria-hidden="true"></div>

            <div class="g01-intro-gateway-heading" data-intro-reveal>
                <p class="g01-intro-eyebrow">
                    Intro selesai. Cerita sebenarnya baru dimulai.
                </p>

                <h2>
                    PILIH
                    <br>
                    <span>ARAHMU.</span>
                </h2>
            </div>

            <div class="g01-intro-portal-shell" data-intro-reveal>
                <div class="g01-intro-portal-core" aria-hidden="true">
                    <div class="g01-intro-portal-mark">
                        <img
                            src="<?= esc($logoUrl, 'attr') ?>"
                            alt=""
                        >
                        <small><?= esc(strtoupper($organizationFullName)) ?></small>
                        <strong>01</strong>
                        <span>RANDUGARUT</span>
                    </div>
                </div>

                <nav
                    class="g01-intro-destinations"
                    aria-label="Navigasi situs utama"
                >
                    <?php foreach ($destinations as $index => $item) : ?>
                        <button
                            type="button"
                            class="<?= $index === 0 ? 'is-active' : '' ?>"
                            data-intro-destination
                            data-name="<?= esc($item['name'], 'attr') ?>"
                            data-url="<?= esc($item['url'], 'attr') ?>"
                        >
                            <span><?= esc($item['index']) ?></span>
                            <strong><?= esc($item['name']) ?></strong>
                            <i aria-hidden="true">↗</i>
                        </button>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="g01-intro-enter-row" data-intro-reveal>
                <p>
                    Tujuan terpilih
                    <span id="g01IntroDestinationName">Beranda</span>
                </p>

                <a
                    id="g01IntroEnter"
                    href="<?= public_url('/home') ?>"
                    data-enter-prefix="<?= esc(
                        $publicLocale === 'en'
                            ? 'Enter '
                            : 'Masuk ke ',
                        'attr'
                    ) ?>"
                >
                    <span id="g01IntroEnterLabel">Masuk ke Beranda</span>
                    <i aria-hidden="true">→</i>
                </a>
            </div>

            <footer class="g01-intro-closing">
                <div class="g01-intro-closing-brand">
                    <span>
                        <img
                            src="<?= esc($logoUrl, 'attr') ?>"
                            alt=""
                        >
                    </span>

                    <p>
                        <strong><?= esc($organizationName) ?></strong>
                        <small><?= esc($organizationFullName) ?></small>
                    </p>
                </div>

                <p><?= esc(strtoupper($organizationTagline)) ?></p>
                <p>RW 01 RANDUGARUT / SEMARANG</p>
            </footer>
        </section>

        <div
            class="g01-intro-transition"
            id="g01IntroTransition"
            aria-hidden="true"
        >
            <p>PORTAL / DESTINATION LOCKED</p>
            <strong id="g01IntroTransitionName">BERANDA</strong>
            <span id="g01IntroTransitionPath">/home</span>
            <small>Memasuki ruang GARDA 01...</small>
        </div>
    </main>

    <?php if (is_file(FCPATH . $preferencesScript)) : ?>
        <script
            src="<?= base_url($preferencesScript) ?>?v=<?= esc(
                (string) filemtime(
                    FCPATH . $preferencesScript
                ),
                'attr'
            ) ?>"
        ></script>
    <?php endif; ?>
</body>
</html>
<?php
$introducingDocument = ob_get_clean();
echo public_translate_html(
    $introducingDocument
);
?>
