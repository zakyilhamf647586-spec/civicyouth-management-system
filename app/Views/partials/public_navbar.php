<?php
$activePage = $activePage ?? '';
?>

<header class="public-navbar">
    <a href="<?= base_url('/') ?>" class="public-brand">
        <img
            src="<?= base_url('assets/img/logo-rw01.png') ?>"
            alt="Logo Karang Taruna RW 01"
        >

        <div>
            <strong>Karang Taruna RW 01</strong>
            <span>Kelurahan Randugarut</span>
        </div>
    </a>

    <nav class="public-nav-links" aria-label="Navigasi publik">
        <a
            href="<?= base_url('/') ?>"
            class="<?= $activePage === 'home' ? 'active' : '' ?>"
            <?= $activePage === 'home' ? 'aria-current="page"' : '' ?>
        >
            Beranda
        </a>

        <a href="<?= base_url('/#profil') ?>">
            Profil
        </a>

        <a href="<?= base_url('/#program') ?>">
            Program
        </a>

        <a
            href="<?= base_url('/pengurus') ?>"
            class="<?= $activePage === 'officials' ? 'active' : '' ?>"
            <?= $activePage === 'officials' ? 'aria-current="page"' : '' ?>
        >
            Pengurus
        </a>

        <a
            href="<?= base_url('/kegiatan') ?>"
            class="<?= in_array($activePage, ['activities', 'activity_detail'], true) ? 'active' : '' ?>"
            <?= in_array($activePage, ['activities', 'activity_detail'], true) ? 'aria-current="page"' : '' ?>
        >
            Kegiatan
        </a>

        <a href="<?= base_url('/login') ?>" class="public-login-btn">
            Masuk Sistem
        </a>
    </nav>
</header>