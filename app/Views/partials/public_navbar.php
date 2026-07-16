<?php

$activePage = $activePage ?? '';

$organizationName = site_setting(
    'organization_name',
    'GARDA 01'
);

$organizationFullName = site_setting(
    'organization_full_name',
    'Generasi Aktif Randugarut'
);

$organizationLegalName = site_setting(
    'organization_legal_name',
    'Karang Taruna RW 01 Kelurahan Randugarut'
);

$organizationTagline = site_setting(
    'organization_tagline',
    'Guyub • Bergerak • Berdampak'
);

$logoUrl = site_asset_url(
    'site_logo',
    'assets/img/logo-rw01.png'
);

$isNavActive = static function (
    array $pages
) use ($activePage): string {
    return in_array($activePage, $pages, true)
        ? 'active'
        : '';
};
?>

<header class="public-navbar" id="publicNavbar">
    <div class="public-navbar-inner">

        <a
            href="<?= base_url('/') ?>"
            class="public-brand"
            aria-label="<?= esc($organizationName) ?> — Beranda"
        >
            <img
                src="<?= esc($logoUrl, 'attr') ?>"
                alt="Logo <?= esc($organizationName) ?>"
                class="public-brand-mark"
            >

            <span class="public-brand-copy">
                <strong><?= esc($organizationName) ?></strong>
                <span><?= esc($organizationFullName) ?></span>
            </span>
        </a>

        <button
            type="button"
            class="public-mobile-toggle"
            id="publicMobileToggle"
            aria-label="Buka menu navigasi"
            aria-controls="publicNavLinks"
            aria-expanded="false"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav
            class="public-nav-links"
            id="publicNavLinks"
            aria-label="Navigasi utama"
        >
            <a
                href="<?= base_url('/') ?>"
                class="<?= $isNavActive(['home']) ?>"
                <?= $activePage === 'home'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Beranda
            </a>

            <a
                href="<?= base_url('/profil') ?>"
                class="<?= $isNavActive(['profile']) ?>"
                <?= $activePage === 'profile'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Tentang
            </a>

            <a
                href="<?= base_url('/program') ?>"
                class="<?= $isNavActive([
                    'programs',
                    'program_detail',
                ]) ?>"
                <?= in_array(
                    $activePage,
                    ['programs', 'program_detail'],
                    true
                )
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Program
            </a>

            <a
                href="<?= base_url('/kegiatan') ?>"
                class="<?= $isNavActive([
                    'activities',
                    'activity_detail',
                ]) ?>"
                <?= in_array(
                    $activePage,
                    ['activities', 'activity_detail'],
                    true
                )
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Kegiatan
            </a>

            <a
                href="<?= base_url('/pengurus') ?>"
                class="<?= $isNavActive(['officials']) ?>"
                <?= $activePage === 'officials'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Pengurus
            </a>

            <a
                href="<?= base_url('/kontak') ?>"
                class="<?= $isNavActive(['contact']) ?>"
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
                    class="public-portal-icon public-portal-button-icon"
                >
                    <path
                        d="M7 10V8a5 5 0 0 1 10 0v2"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    ></path>

                    <rect
                        x="5"
                        y="10"
                        width="14"
                        height="10"
                        rx="2.5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    ></rect>

                    <circle
                        cx="12"
                        cy="15"
                        r="1.2"
                        fill="currentColor"
                    ></circle>
                </svg>

                <span>Portal Pengurus</span>
            </a>

            <div class="public-nav-mobile-identity">
                <strong><?= esc($organizationLegalName) ?></strong>
                <span><?= esc($organizationTagline) ?></span>
            </div>
        </nav>

    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('publicMobileToggle');
    const navigation = document.getElementById('publicNavLinks');

    if (!toggle || !navigation) {
        return;
    }

    const closeNavigation = function () {
        navigation.classList.remove('active');
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('public-navigation-open');
    };

    toggle.addEventListener('click', function () {
        const isOpen = navigation.classList.toggle('active');

        toggle.classList.toggle('active', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
        document.body.classList.toggle(
            'public-navigation-open',
            isOpen
        );
    });

    navigation.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeNavigation);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeNavigation();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 920) {
            closeNavigation();
        }
    });
});
</script>
