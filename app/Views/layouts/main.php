<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Karang Taruna RW 01') ?> - Karang Taruna RW 01</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="navbar">
    <div class="brand-area">
        <img src="<?= base_url('assets/img/logo-rw01.png') ?>" alt="Logo Karang Taruna RW 01" class="brand-logo">

        <div class="brand-text">
            <h1>Karang Taruna RW 01</h1>
            <span>Sistem Manajemen Organisasi Pemuda</span>
        </div>
    </div>

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