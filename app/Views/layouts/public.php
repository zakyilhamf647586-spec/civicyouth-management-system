<?php

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

$ogImageUrl = site_asset_url(
    'seo_og_image',
    'assets/img/logo-rw01.png'
);

$organizationName = site_setting(
    'organization_name',
    'GARDA 01'
);

$activePage = $activePage ?? '';
$currentUrl = current_url();
$pageRobots = $robots ?? 'index, follow';

$publicStylesheets = [
    'assets/css/app.css',
    'assets/css/public-footer-refinement.css',
    'assets/css/public-home-impact.css',
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

    <meta property="og:type" content="website">

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
<body class="public-body">
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
