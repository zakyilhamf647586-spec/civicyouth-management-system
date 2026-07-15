<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <title>
        <?= esc($title ?? 'GARDA 01 | Karang Taruna RW 01 Randugarut') ?>
    </title>

    <meta
        name="description"
        content="<?= esc(
            $metaDescription
            ?? 'Website resmi GARDA 01 — Generasi Aktif Randugarut, Karang Taruna RW 01 Kelurahan Randugarut.'
        ) ?>"
    >

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        rel="icon"
        type="image/png"
        href="<?= base_url('assets/img/logo-rw01.png') ?>"
    >

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/app.css') ?>"
    >
</head>

<body>

<div class="public-site">

    <?= view('partials/public_navbar', [
        'activePage' => $activePage ?? '',
    ]) ?>

    <main class="public-main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <?= view('partials/public_footer') ?>

</div>

<?= $this->renderSection('scripts') ?>

</body>
</html>