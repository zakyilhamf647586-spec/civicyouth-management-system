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

$navigationItems = website_navigation_items(
    'header'
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
            href="<?= public_url('/') ?>"
            class="public-brand"
            aria-label="<?= esc(public_t(
                'accessibility.return_to_intro',
                'Kembali ke Introducing {organization}',
                ['organization' => $organizationName]
            ), 'attr') ?>"
        >
            <img
                src="<?= esc($logoUrl, 'attr') ?>"
                alt="Logo <?= esc($organizationName) ?>"
                class="public-brand-mark"
                decoding="async"
            >

            <span class="public-brand-copy">
                <strong><?= esc($organizationName) ?></strong>
                <span><?= esc($organizationFullName) ?></span>
            </span>
        </a>

        <?= view('partials/public_preferences', [
            'preferenceContext' => 'navbar',
        ]) ?>

        <button
            type="button"
            class="public-mobile-toggle"
            id="publicMobileToggle"
            aria-label="<?= esc(public_t(
                'accessibility.navigation_open',
                'Buka menu navigasi'
            ), 'attr') ?>"
            aria-controls="publicNavLinks"
            aria-expanded="false"
            data-label-open="<?= esc(public_t(
                'accessibility.navigation_open',
                'Buka menu navigasi'
            ), 'attr') ?>"
            data-label-close="<?= esc(public_t(
                'accessibility.navigation_close',
                'Tutup menu navigasi'
            ), 'attr') ?>"
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
            <?php foreach (
                $navigationItems as $item
            ) : ?>
                <?php
                $isPortal =
                    ($item['style'] ?? 'default')
                    === 'portal';

                $isCurrent =
                    website_navigation_item_active(
                        $item,
                        $activePage
                    );

                $targetBlank =
                    ($item['target'] ?? 'self')
                    === 'blank';

                $itemUrl = website_navigation_url(
                    (string) $item['url'],
                    !$isPortal
                );
                ?>

                <a
                    href="<?= esc($itemUrl, 'attr') ?>"
                    class="<?= $isPortal
                        ? 'public-portal-button'
                        : ($isCurrent ? 'active' : '') ?>"
                    <?= $isCurrent
                        ? 'aria-current="page"'
                        : '' ?>
                    <?= $targetBlank
                        ? 'target="_blank" rel="noopener noreferrer"'
                        : '' ?>
                >
                    <?php if ($isPortal) : ?>
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
                    <?php endif; ?>

                    <span><?= esc($item['label']) ?></span>
                </a>
            <?php endforeach; ?>

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
        toggle.setAttribute(
            'aria-label',
            toggle.dataset.labelOpen || 'Buka menu navigasi'
        );
        document.body.classList.remove('public-navigation-open');
    };

    toggle.addEventListener('click', function () {
        const isOpen = navigation.classList.toggle('active');

        toggle.classList.toggle('active', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute(
            'aria-label',
            isOpen
                ? (toggle.dataset.labelClose || 'Tutup menu navigasi')
                : (toggle.dataset.labelOpen || 'Buka menu navigasi')
        );
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

    document.addEventListener('pointerdown', function (event) {
        const navbar = toggle.closest('.public-navbar');

        if (
            navigation.classList.contains('active')
            && navbar
            && !navbar.contains(event.target)
        ) {
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
