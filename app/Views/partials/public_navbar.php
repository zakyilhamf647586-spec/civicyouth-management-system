<?php

$activePage = $activePage ?? '';

$isActivityPage = in_array(
    $activePage,
    ['activities', 'activity_detail'],
    true
);

$isProgramPage = in_array(
    $activePage,
    ['programs', 'program_detail'],
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
                href="<?= base_url('/profil') ?>"
                class="<?= $activePage === 'profile' ? 'active' : '' ?>"
                <?= $activePage === 'profile'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Tentang
            </a>

            <a
                href="<?= base_url('/program') ?>"
                class="<?= $isProgramPage ? 'active' : '' ?>"
                <?= $isProgramPage
                    ? 'aria-current="page"'
                    : '' ?>
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
                href="<?= base_url('/kontak') ?>"
                class="<?= $activePage === 'contact'
                    ? 'active'
                    : '' ?>"
                <?= $activePage === 'contact'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Kontak
            </a>

            <a
                href="<?= base_url('/login') ?>"
                class="public-portal-button"
            >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    class="public-portal-button-icon"
                >
                    <path
                        d="M7 10V8a5 5 0 0 1 10 0v2"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />

                    <rect
                        x="5"
                        y="10"
                        width="14"
                        height="10"
                        rx="2.5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />

                    <circle
                        cx="12"
                        cy="15"
                        r="1.2"
                        fill="currentColor"
                    />
                </svg>

                <span>Portal Pengurus</span>
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