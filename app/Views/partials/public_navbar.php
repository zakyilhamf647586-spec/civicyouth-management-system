<?php

$activePage = $activePage ?? '';

$isActivityPage = in_array(
    $activePage,
    ['activities', 'activity_detail'],
    true
);
?>

<header class="public-navbar">
    <div class="public-navbar-inner">

        <a href="<?= base_url('/') ?>" class="public-brand">
            <img
                src="<?= base_url('assets/img/logo-rw01.png') ?>"
                alt="Logo GARDA 01"
                class="public-brand-mark"
            >

            <div class="public-brand-copy">
                <strong>GARDA 01</strong>
                <span>Karang Taruna RW 01 Randugarut</span>
            </div>
        </a>

        <button
            type="button"
            class="public-mobile-toggle"
            id="publicMobileToggle"
            aria-label="Buka menu navigasi"
            aria-expanded="false"
            aria-controls="publicNavigation"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav
            class="public-nav-links"
            id="publicNavigation"
            aria-label="Navigasi publik"
        >
            <a
                href="<?= base_url('/') ?>"
                class="<?= $activePage === 'home' ? 'active' : '' ?>"
                <?= $activePage === 'home'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Beranda
            </a>

            <a
                href="<?= base_url('/#profil') ?>"
                class="<?= $activePage === 'profile' ? 'active' : '' ?>"
            >
                Tentang
            </a>

            <a
                href="<?= base_url('/#program') ?>"
                class="<?= $activePage === 'programs' ? 'active' : '' ?>"
            >
                Program
            </a>

            <a
                href="<?= base_url('/kegiatan') ?>"
                class="<?= $isActivityPage ? 'active' : '' ?>"
                <?= $isActivityPage
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Kegiatan
            </a>

            <a
                href="<?= base_url('/pengurus') ?>"
                class="<?= $activePage === 'officials' ? 'active' : '' ?>"
                <?= $activePage === 'officials'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Pengurus
            </a>

            <a
                href="<?= base_url('/login') ?>"
                class="public-portal-button"
            >
                <span class="public-portal-icon" aria-hidden="true">⌾</span>
                Portal Pengurus
            </a>
        </nav>

    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('publicMobileToggle');
    const navigation = document.getElementById('publicNavigation');

    if (!toggle || !navigation) {
        return;
    }

    toggle.addEventListener('click', function () {
        const isOpen = navigation.classList.toggle('active');

        toggle.classList.toggle('active', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    navigation.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            navigation.classList.remove('active');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            navigation.classList.remove('active');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
});
</script>