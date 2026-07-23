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

$navigationPreview =
    function_exists('website_navigation_preview_active')
    && website_navigation_preview_active();

$pageRobots = (
    !empty($cmsPreview)
    || $navigationPreview
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
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta name="theme-color" content="#04172d">
    <meta name="robots" content="<?= esc($pageRobots, 'attr') ?>">

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

    <meta property="og:locale" content="id_ID">

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
        href="<?= esc($currentUrl, 'attr') ?>"
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
</head>
<body class="public-body <?= !empty($cmsPreview)
    ? 'public-body--cms-preview'
    : '' ?> <?= $navigationPreview
        ? 'public-body--navigation-preview'
        : '' ?>">
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

    <?php if (!empty($cmsPreview)) : ?>
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

    <?= view('partials/public_navbar', [
        'activePage' => $activePage,
    ]) ?>

    <main class="public-main">
        <?= $this->renderSection('content') ?>
    </main>

    <?= view('partials/public_footer') ?>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
