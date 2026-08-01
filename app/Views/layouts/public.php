<?php

use App\Libraries\PublicSeoService;

$defaultSeoTitle = site_setting(
    'seo_title',
    'GARDA 01 | Generasi Aktif Randugarut'
);

$defaultSeoDescription = site_setting(
    'seo_description',
    'Website resmi GARDA 01, Generasi Aktif Randugarut.'
);

$pageTitle = !empty($title)
    ? (string) $title
    : (string) $defaultSeoTitle;

$pageDescription = !empty($metaDescription)
    ? (string) $metaDescription
    : (string) $defaultSeoDescription;

$publicLocale = public_locale();
$pageTitle = public_translate_text($pageTitle);
$pageDescription = public_translate_text(
    $pageDescription
);

$pageKeywords = site_setting(
    'seo_keywords',
    'GARDA 01, Karang Taruna, Randugarut, RW 01'
);

$faviconUrl = site_asset_url(
    'site_favicon',
    'assets/img/logo-rw01.png'
);

$organizationName = site_setting(
    'organization_name',
    'GARDA 01'
);

$activePage = $activePage ?? '';

$publicPageClass = preg_replace(
    '/[^a-z0-9-]+/',
    '-',
    strtolower(str_replace('_', '-', (string) $activePage))
);

if (!is_string($publicPageClass) || $publicPageClass === '') {
    $publicPageClass = 'default';
}

$externalPreview = !empty($externalPreview);
$externalPreviewToken =
    $externalPreviewToken ?? null;
$externalPreviewMeta = is_array(
    $externalPreviewMeta ?? null
)
    ? $externalPreviewMeta
    : [];

$navigationPreview =
    function_exists('website_navigation_preview_active')
    && website_navigation_preview_active();

$pageRobots = (
    !empty($cmsPreview)
    || $navigationPreview
    || $externalPreview
)
    ? 'noindex, nofollow, noarchive'
    : ($robots ?? 'index, follow');

$seoService = new PublicSeoService();

$seoMetadata = $seoService->metadata([
    'active_page' => $activePage,
    'activity' => $activity ?? null,
    'program' => $program ?? null,
    'canonical_url' => $canonicalUrl ?? '',
    'image' => $seoImage ?? '',
    'image_alt' => $seoImageAlt ?? '',
]);

$currentUrl = $seoMetadata['canonical'];

$ogImageUrl = $seoMetadata['image'];
$ogImageAlt = $seoMetadata['image_alt'];
$openGraphType = $seoMetadata['open_graph_type'];

$structuredData = $seoService->structuredData([
    'title' => $pageTitle,
    'description' => $pageDescription,
    'active_page' => $activePage,
    'canonical_url' => $currentUrl,
    'image' => $ogImageUrl,
    'page_type' => $seoMetadata[
        'schema_page_type'
    ],
    'activity' => $activity ?? null,
    'program' => $program ?? null,
    'programs' => $programs ?? [],
    'activities' => $activities ?? [],
    'locale' => $publicLocale,
]);

$structuredDataJson = json_encode(
    $structuredData,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
);

$twitterHandle = trim((string) site_setting(
    'seo_twitter_handle',
    ''
));

$googleVerification = trim((string) site_setting(
    'seo_google_verification',
    ''
));

$bingVerification = trim((string) site_setting(
    'seo_bing_verification',
    ''
));

$publicStylesheets = [
    'assets/css/app.css',
    'assets/css/public-footer-refinement.css',
    'assets/css/public-home-impact.css',
    'assets/css/public-cms-preview.css',
    'assets/css/public-external-review.css',
];

$currentPath = parse_url(
    $currentUrl,
    PHP_URL_PATH
);

if (!is_string($currentPath) || $currentPath === '') {
    $currentPath = '/';
}

$alternateIdUrl = public_url(
    $currentPath,
    'id'
);
$alternateEnUrl = public_url(
    $currentPath,
    'en'
);

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

    <meta name="theme-color" content="#04172d">
    <meta name="robots" content="<?= esc($pageRobots, 'attr') ?>">

    <?php if ($externalPreview) : ?>
        <meta name="referrer" content="no-referrer">
    <?php endif; ?>

    <title><?= esc($pageTitle) ?></title>

    <meta
        name="description"
        content="<?= esc($pageDescription, 'attr') ?>"
    >

    <meta
        name="keywords"
        content="<?= esc($pageKeywords, 'attr') ?>"
    >

    <meta
        name="author"
        content="<?= esc($organizationName, 'attr') ?>"
    >

    <link
        rel="canonical"
        href="<?= esc($currentUrl, 'attr') ?>"
    >

    <meta
        property="og:title"
        content="<?= esc($pageTitle, 'attr') ?>"
    >

    <meta
        property="og:description"
        content="<?= esc($pageDescription, 'attr') ?>"
    >

    <meta property="og:type" content="<?= esc($openGraphType, 'attr') ?>">

    <meta
        property="og:url"
        content="<?= esc($currentUrl, 'attr') ?>"
    >

    <meta
        property="og:image"
        content="<?= esc($ogImageUrl, 'attr') ?>"
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

    <meta
        property="og:image:alt"
        content="<?= esc($ogImageAlt, 'attr') ?>"
    >

    <meta name="twitter:card" content="summary_large_image">

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
        content="<?= esc($ogImageUrl, 'attr') ?>"
    >

    <meta
        name="twitter:image:alt"
        content="<?= esc($ogImageAlt, 'attr') ?>"
    >

    <?php if ($twitterHandle !== '') : ?>
        <meta
            name="twitter:site"
            content="<?= esc($twitterHandle, 'attr') ?>"
        >
    <?php endif; ?>

    <?php if ($googleVerification !== '') : ?>
        <meta
            name="google-site-verification"
            content="<?= esc(
                $googleVerification,
                'attr'
            ) ?>"
        >
    <?php endif; ?>

    <?php if ($bingVerification !== '') : ?>
        <meta
            name="msvalidate.01"
            content="<?= esc(
                $bingVerification,
                'attr'
            ) ?>"
        >
    <?php endif; ?>

    <?php if (
        $activePage === 'activity_detail'
        && !empty($activity)
    ) : ?>
        <?php if (!empty(
            $activity['published_at']
            ?? $activity['created_at']
            ?? null
        )) : ?>
            <meta
                property="article:published_time"
                content="<?= esc(
                    date(
                        DATE_ATOM,
                        strtotime(
                            $activity['published_at']
                            ?? $activity['created_at']
                        )
                    ),
                    'attr'
                ) ?>"
            >
        <?php endif; ?>

        <?php if (!empty(
            $activity['updated_at'] ?? null
        )) : ?>
            <meta
                property="article:modified_time"
                content="<?= esc(
                    date(
                        DATE_ATOM,
                        strtotime(
                            $activity['updated_at']
                        )
                    ),
                    'attr'
                ) ?>"
            >
        <?php endif; ?>
    <?php endif; ?>

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
        rel="sitemap"
        type="application/xml"
        href="<?= base_url('sitemap.xml') ?>"
    >

    <?php if ($structuredDataJson !== false) : ?>
        <script type="application/ld+json"><?= $structuredDataJson ?></script>
    <?php endif; ?>

    <link
        rel="icon"
        href="<?= esc($faviconUrl, 'attr') ?>"
    >

    <?= view('partials/public_theme_bootstrap') ?>

    <?php foreach ($publicStylesheets as $stylesheet) : ?>
        <?php
        $stylesheetPath = FCPATH . $stylesheet;

        if (!is_file($stylesheetPath)) {
            continue;
        }

        $stylesheetVersion = (string) filemtime($stylesheetPath);
        ?>
        <link
            rel="stylesheet"
            href="<?= base_url($stylesheet) ?>?v=<?= esc(
                $stylesheetVersion,
                'attr'
            ) ?>"
        >
    <?php endforeach; ?>

    <?= $this->renderSection('head') ?>

    <?php
    $publicPremiumStylesheet =
        'assets/css/public-premium-v2.css';
    $publicPremiumStylesheetPath =
        FCPATH . $publicPremiumStylesheet;
    ?>

    <?php if (is_file($publicPremiumStylesheetPath)) : ?>
        <link
            rel="stylesheet"
            href="<?= base_url($publicPremiumStylesheet) ?>?v=<?= esc(
                (string) filemtime($publicPremiumStylesheetPath),
                'attr'
            ) ?>"
        >
    <?php endif; ?>

    <?php
    $publicExperienceStylesheet =
        'assets/css/public-experience-v3.css';
    $publicExperienceStylesheetPath =
        FCPATH . $publicExperienceStylesheet;
    ?>

    <?php if (is_file($publicExperienceStylesheetPath)) : ?>
        <link
            rel="stylesheet"
            href="<?= base_url($publicExperienceStylesheet) ?>?v=<?= esc(
                (string) filemtime($publicExperienceStylesheetPath),
                'attr'
            ) ?>"
        >
    <?php endif; ?>

    <?php
    $publicPreferencesStylesheet =
        'assets/css/public-preferences.css';
    $publicPreferencesStylesheetPath =
        FCPATH . $publicPreferencesStylesheet;
    ?>

    <?php if (is_file($publicPreferencesStylesheetPath)) : ?>
        <link
            rel="stylesheet"
            href="<?= base_url($publicPreferencesStylesheet) ?>?v=<?= esc(
                (string) filemtime(
                    $publicPreferencesStylesheetPath
                ),
                'attr'
            ) ?>"
        >
    <?php endif; ?>
</head>
<body class="public-body public-experience-v2 public-experience-v3 public-page--<?= esc(
    $publicPageClass,
    'attr'
) ?> <?= !empty($cmsPreview)
    ? 'public-body--cms-preview'
    : '' ?> <?= $navigationPreview
        ? 'public-body--navigation-preview'
        : '' ?> <?= $externalPreview
            ? 'public-body--external-review'
            : '' ?>">
    <a class="public-skip-link" href="#main-content">
        Langsung ke konten utama
    </a>

    <?= view('partials/public_experience', [
        'activePage' => $activePage,
        'pageTitle' => $pageTitle,
    ]) ?>

    <?php if (
        $navigationPreview
        && empty($cmsPreview)
    ) : ?>
        <div class="public-cms-preview-banner">
            <div>
                <strong>Preview Draft Navigasi</strong>

                <span>
                    Susunan menu ini belum tampil untuk
                    pengunjung umum.
                </span>
            </div>

            <a href="<?= base_url(
                '/website/navigation'
            ) ?>">
                Kembali ke Navigation Manager
            </a>
        </div>
    <?php endif; ?>

    <?php if (
        !empty($cmsPreview)
        && !$externalPreview
    ) : ?>
        <div class="public-cms-preview-banner">
            <div>
                <strong>Preview Draft CMS</strong>

                <span>
                    Perubahan ini belum tampil untuk pengunjung umum.
                </span>
            </div>

            <?php if (!empty($cmsPage['page_key'])) : ?>
                <a
                    href="<?= base_url(
                        '/website/pages/edit/'
                        . $cmsPage['page_key']
                    ) ?>"
                >
                    Kembali ke Editor
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($externalPreview) : ?>
        <div class="external-review-public-banner">
            <div>
                <strong>Tautan Review Terbatas</strong>

                <span>
                    Snapshot versi
                    #<?= (int) (
                        $externalPreviewMeta[
                            'version_number'
                        ] ?? 0
                    ) ?>
                    ·
                    <?= esc(
                        $externalPreviewMeta['label']
                        ?? 'Review Eksternal'
                    ) ?>
                    ·
                    Tidak tampil untuk pengunjung umum
                </span>
            </div>

            <a href="#external-review-panel">
                Berikan Tanggapan
            </a>
        </div>
    <?php endif; ?>

    <?= view('partials/public_navbar', [
        'activePage' => $activePage,
    ]) ?>

    <main
        class="public-main"
        id="main-content"
        tabindex="-1"
    >
        <?= $this->renderSection('content') ?>
    </main>

    <?php if (
        $externalPreview
        && is_string($externalPreviewToken)
        && $externalPreviewToken !== ''
    ) : ?>
        <?= view(
            'external_review/feedback_panel',
            [
                'externalPreviewToken' =>
                    $externalPreviewToken,
                'externalPreviewMeta' =>
                    $externalPreviewMeta,
            ]
        ) ?>
    <?php endif; ?>

    <?= view('partials/public_footer') ?>

    <?= $this->renderSection('scripts') ?>

    <?php
    $publicPremiumScript =
        'assets/js/public-premium-v2.js';
    $publicPremiumScriptPath =
        FCPATH . $publicPremiumScript;
    ?>

    <?php if (is_file($publicPremiumScriptPath)) : ?>
        <script
            src="<?= base_url($publicPremiumScript) ?>?v=<?= esc(
                (string) filemtime($publicPremiumScriptPath),
                'attr'
            ) ?>"
        ></script>
    <?php endif; ?>

    <?php
    $publicExperienceScript =
        'assets/js/public-experience-v3.js';
    $publicExperienceScriptPath =
        FCPATH . $publicExperienceScript;
    ?>

    <?php if (is_file($publicExperienceScriptPath)) : ?>
        <script
            src="<?= base_url($publicExperienceScript) ?>?v=<?= esc(
                (string) filemtime($publicExperienceScriptPath),
                'attr'
            ) ?>"
        ></script>
    <?php endif; ?>

    <?php
    $publicPreferencesScript =
        'assets/js/public-preferences.js';
    $publicPreferencesScriptPath =
        FCPATH . $publicPreferencesScript;
    ?>

    <?php if (is_file($publicPreferencesScriptPath)) : ?>
        <script
            src="<?= base_url($publicPreferencesScript) ?>?v=<?= esc(
                (string) filemtime(
                    $publicPreferencesScriptPath
                ),
                'attr'
            ) ?>"
        ></script>
    <?php endif; ?>
</body>
</html>
<?php
$publicDocument = ob_get_clean();
echo public_translate_html($publicDocument);
?>
