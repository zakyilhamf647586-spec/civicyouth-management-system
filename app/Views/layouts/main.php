<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'CivicYouth') ?> - CivicYouth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="navbar">
    <h1>CivicYouth</h1>

    <div class="navbar-menu">
        <a href="<?= base_url('/dashboard') ?>">Dashboard</a>
        <a href="<?= base_url('/members') ?>">Anggota</a>
        <a href="<?= base_url('/structures') ?>">Struktur</a>
        <a href="<?= base_url('/meetings') ?>">Rapat</a>
        <a href="<?= base_url('/attendances') ?>">Absensi</a>
        <a href="<?= base_url('/cash') ?>">Kas</a>
        <a href="<?= base_url('/activities') ?>">Kegiatan</a>
        <a href="<?= base_url('/reports') ?>">Laporan</a>
        <a href="<?= base_url('/logout') ?>">Logout</a>
    </div>
</div>

<div class="container">
    <?= $this->renderSection('content') ?>
</div>

</body>
</html>