<?php

$currentPath = trim(service('uri')->getPath(), '/');
$currentSegment = explode('/', $currentPath)[0] ?? 'dashboard';

$isActive = static function (array $prefixes) use ($currentPath): string {
    foreach ($prefixes as $prefix) {
        if (
            $currentPath === $prefix
            || str_starts_with($currentPath, $prefix . '/')
        ) {
            return 'active';
        }
    }

    return '';
};

$pageContexts = [
    'dashboard' => [
        'section' => 'Dashboard',
        'label'   => 'Ringkasan Organisasi',
    ],

    'members' => [
        'section' => 'Organisasi',
        'label'   => 'Data Anggota',
    ],

    'structures' => [
        'section' => 'Organisasi',
        'label'   => 'Struktur Pengurus',
    ],

    'meetings' => [
        'section' => 'Organisasi',
        'label'   => 'Rapat dan Notulen',
    ],

    'attendances' => [
        'section' => 'Organisasi',
        'label'   => 'Absensi',
    ],

    'activities' => [
        'section' => 'Organisasi',
        'label'   => 'Kegiatan',
    ],

    'cash' => [
        'section' => 'Keuangan',
        'label'   => 'Kas Organisasi',
    ],

    'programs' => [
        'section' => 'Website Publik',
        'label'   => 'Program GARDA 01',
    ],

    'content-studio' => [
        'section' => 'Media dan Publikasi',
        'label'   => 'AI Content Studio',
    ],

    'messages' => [
        'section' => 'Website Publik',
        'label'   => 'Pesan Masuk',
    ],

    'reports' => [
        'section' => 'Laporan',
        'label'   => 'Laporan Organisasi',
    ],

    'settings' => [
        'section' => 'Pengaturan',
        'label'   => 'Pengaturan Website',
    ],
];

$pageContext = $pageContexts[$currentSegment] ?? [
    'section' => 'GARDA 01 Portal',
    'label'   => $title ?? 'Portal Manajemen',
];

$pageTitle = $title ?? $pageContext['label'];

$portalOrganizationName = site_setting(
    'organization_name',
    'GARDA 01'
);

$portalLogoUrl = site_asset_url(
    'site_logo',
    'assets/img/logo-rw01.png'
);

$portalFaviconUrl = site_asset_url(
    'site_favicon',
    'assets/img/logo-rw01.png'
);

$portalStylesheetPath = FCPATH . 'assets/css/app.css';
$portalStylesheetVersion = is_file($portalStylesheetPath)
    ? (string) filemtime($portalStylesheetPath)
    : '1';

$userName =
    session()->get('name')
    ?? session()->get('full_name')
    ?? session()->get('user_name')
    ?? session()->get('email')
    ?? 'Pengurus GARDA 01';

$userRole =
    session()->get('role_name')
    ?? session()->get('role')
    ?? 'Administrator';

$nameParts = preg_split(
    '/\s+/',
    trim((string) $userName)
);

$userInitials = '';

foreach (array_slice($nameParts ?: [], 0, 2) as $part) {
    if ($part !== '') {
        $userInitials .= mb_strtoupper(
            mb_substr($part, 0, 1)
        );
    }
}

if ($userInitials === '') {
    $userInitials = 'G01';
}

$dayNames = [
    1 => 'Senin',
    2 => 'Selasa',
    3 => 'Rabu',
    4 => 'Kamis',
    5 => 'Jumat',
    6 => 'Sabtu',
    7 => 'Minggu',
];

$monthNames = [
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
];

$todayLabel =
    $dayNames[(int) date('N')]
    . ', '
    . date('d')
    . ' '
    . $monthNames[(int) date('n')]
    . ' '
    . date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="#04172d"
    >

    <title>
        <?= esc($pageTitle) ?> — <?= esc($portalOrganizationName) ?> Portal
    </title>

    <link
        rel="icon"
        href="<?= esc($portalFaviconUrl, 'attr') ?>"
    >

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/app.css') ?>?v=<?= esc($portalStylesheetVersion, 'attr') ?>"
    >
</head>

<body class="garda-admin-body">

<!-- SVG icon collection -->
<svg
    xmlns="http://www.w3.org/2000/svg"
    style="display:none"
    aria-hidden="true"
>
    <symbol id="icon-dashboard" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7" rx="2"></rect>
        <rect x="14" y="3" width="7" height="7" rx="2"></rect>
        <rect x="3" y="14" width="7" height="7" rx="2"></rect>
        <rect x="14" y="14" width="7" height="7" rx="2"></rect>
    </symbol>

    <symbol id="icon-members" viewBox="0 0 24 24">
        <circle cx="9" cy="8" r="4"></circle>
        <path d="M3 21v-2a6 6 0 0 1 12 0v2"></path>
        <circle cx="18" cy="9" r="3"></circle>
        <path d="M17 15a5 5 0 0 1 4 5v1"></path>
    </symbol>

    <symbol id="icon-structure" viewBox="0 0 24 24">
        <rect x="8" y="3" width="8" height="5" rx="1.5"></rect>
        <rect x="2" y="16" width="7" height="5" rx="1.5"></rect>
        <rect x="15" y="16" width="7" height="5" rx="1.5"></rect>
        <path d="M12 8v4M5.5 16v-4h13v4"></path>
    </symbol>

    <symbol id="icon-meeting" viewBox="0 0 24 24">
        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
        <path d="M7 3v4M17 3v4M3 10h18"></path>
        <path d="M8 14h3M8 17h7"></path>
    </symbol>

    <symbol id="icon-attendance" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="9"></circle>
        <path d="m8 12 3 3 5-6"></path>
    </symbol>

    <symbol id="icon-activity" viewBox="0 0 24 24">
        <path d="M5 21V4"></path>
        <path d="M5 5h11l-2 4 2 4H5"></path>
    </symbol>

    <symbol id="icon-cash" viewBox="0 0 24 24">
        <rect x="3" y="6" width="18" height="14" rx="3"></rect>
        <path d="M16 10h5v6h-5a3 3 0 0 1 0-6Z"></path>
        <circle cx="16" cy="13" r="1"></circle>
        <path d="M5 6V4h12"></path>
    </symbol>

    <symbol id="icon-program" viewBox="0 0 24 24">
        <path d="M12 3 3 8l9 5 9-5-9-5Z"></path>
        <path d="m3 12 9 5 9-5"></path>
        <path d="m3 16 9 5 9-5"></path>
    </symbol>

    <symbol id="icon-ai" viewBox="0 0 24 24">
        <path d="m12 3 1.2 4.2L17 9l-3.8 1.8L12 15l-1.2-4.2L7 9l3.8-1.8L12 3Z"></path>
        <path d="m19 14 .7 2.3L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14Z"></path>
        <path d="m5 13 .7 2.3L8 16l-2.3.7L5 19l-.7-2.3L2 16l2.3-.7L5 13Z"></path>
    </symbol>

    <symbol id="icon-message" viewBox="0 0 24 24">
        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
        <path d="m4 7 8 6 8-6"></path>
    </symbol>

    <symbol id="icon-report" viewBox="0 0 24 24">
        <path d="M5 3h10l4 4v14H5V3Z"></path>
        <path d="M15 3v5h5"></path>
        <path d="M8 13h8M8 17h8M8 9h3"></path>
    </symbol>

    <symbol id="icon-globe" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="9"></circle>
        <path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"></path>
    </symbol>

    <symbol id="icon-menu" viewBox="0 0 24 24">
        <path d="M4 7h16M4 12h16M4 17h16"></path>
    </symbol>

    <symbol id="icon-bell" viewBox="0 0 24 24">
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
        <path d="M10 21h4"></path>
    </symbol>

    <symbol id="icon-user" viewBox="0 0 24 24">
        <circle cx="12" cy="8" r="4"></circle>
        <path d="M4 21a8 8 0 0 1 16 0"></path>
    </symbol>

    <symbol id="icon-logout" viewBox="0 0 24 24">
        <path d="M10 5H5v14h5"></path>
        <path d="m14 8 4 4-4 4M18 12H9"></path>
    </symbol>

    <symbol id="icon-collapse" viewBox="0 0 24 24">
        <path d="m15 6-6 6 6 6"></path>
    </symbol>

    <symbol id="icon-chevron" viewBox="0 0 24 24">
        <path d="m9 6 6 6-6 6"></path>
    </symbol>

    <symbol id="icon-settings" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="3"></circle>

        <path
            d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3A1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"
        ></path>
    </symbol>
</svg>

<div class="garda-admin-shell">

    <div
        class="garda-admin-overlay"
        id="adminSidebarOverlay"
    ></div>

    <aside
        class="garda-admin-sidebar"
        id="adminSidebar"
        aria-label="Navigasi GARDA 01 Portal"
    >
        <div class="garda-admin-brand">

            <a
                href="<?= base_url('/dashboard') ?>"
                class="garda-admin-brand-link"
            >
                <img
                    src="<?= esc($portalLogoUrl, 'attr') ?>"
                    alt="Logo <?= esc($portalOrganizationName) ?>"
                >

                <div class="garda-admin-brand-copy">
                    <strong><?= esc($portalOrganizationName) ?></strong>
                    <span>Portal Manajemen</span>
                </div>
            </a>

            <button
                type="button"
                class="garda-admin-collapse-button"
                id="adminSidebarCollapse"
                aria-label="Perkecil sidebar"
                title="Perkecil sidebar"
            >
                <svg aria-hidden="true">
                    <use href="#icon-collapse"></use>
                </svg>
            </button>

        </div>

        <div class="garda-admin-organization-label">
            <span>Karang Taruna RW 01</span>
            <small>Kelurahan Randugarut</small>
        </div>

        <nav class="garda-admin-navigation">

            <a
                href="<?= base_url('/dashboard') ?>"
                class="garda-admin-nav-link <?= $isActive(['dashboard']) ?>"
                title="Dashboard"
            >
                <svg aria-hidden="true">
                    <use href="#icon-dashboard"></use>
                </svg>

                <span>Dashboard</span>
            </a>

            <div class="garda-admin-nav-section">
                <div class="garda-admin-nav-heading">
                    Organisasi
                </div>

                <a
                    href="<?= base_url('/members') ?>"
                    class="garda-admin-nav-link <?= $isActive(['members']) ?>"
                    title="Data Anggota"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-members"></use>
                    </svg>

                    <span>Data Anggota</span>
                </a>

                <a
                    href="<?= base_url('/structures') ?>"
                    class="garda-admin-nav-link <?= $isActive(['structures']) ?>"
                    title="Struktur Pengurus"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-structure"></use>
                    </svg>

                    <span>Struktur Pengurus</span>
                </a>

                <a
                    href="<?= base_url('/meetings') ?>"
                    class="garda-admin-nav-link <?= $isActive(['meetings']) ?>"
                    title="Rapat"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-meeting"></use>
                    </svg>

                    <span>Rapat</span>
                </a>

                <a
                    href="<?= base_url('/attendances') ?>"
                    class="garda-admin-nav-link <?= $isActive(['attendances']) ?>"
                    title="Absensi"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-attendance"></use>
                    </svg>

                    <span>Absensi</span>
                </a>

                <a
                    href="<?= base_url('/activities') ?>"
                    class="garda-admin-nav-link <?= $isActive(['activities']) ?>"
                    title="Kegiatan"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-activity"></use>
                    </svg>

                    <span>Kegiatan</span>
                </a>
            </div>

            <div class="garda-admin-nav-section">
                <div class="garda-admin-nav-heading">
                    Keuangan
                </div>

                <a
                    href="<?= base_url('/cash') ?>"
                    class="garda-admin-nav-link <?= $isActive(['cash']) ?>"
                    title="Kas Organisasi"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-cash"></use>
                    </svg>

                    <span>Kas Organisasi</span>
                </a>
            </div>

            <div class="garda-admin-nav-section">
                <div class="garda-admin-nav-heading">
                    Website & Publikasi
                </div>

                <a
                    href="<?= base_url('/programs') ?>"
                    class="garda-admin-nav-link <?= $isActive(['programs']) ?>"
                    title="Program GARDA 01"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-program"></use>
                    </svg>

                    <span>Program GARDA 01</span>
                </a>

                <a
                    href="<?= base_url('/settings/website') ?>"
                    class="garda-admin-nav-link
                    <?= $isActive(['settings']) ?>"
                    title="Pengaturan Website"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-settings"></use>
                    </svg>

                    <span>Pengaturan Website</span>
                </a>

                <a
                    href="<?= base_url('/content-studio') ?>"
                    class="garda-admin-nav-link <?= $isActive(['content-studio']) ?>"
                    title="AI Content Studio"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-ai"></use>
                    </svg>

                    <span>AI Content Studio</span>
                </a>

                <a
                    href="<?= base_url('/messages') ?>"
                    class="garda-admin-nav-link <?= $isActive(['messages']) ?>"
                    title="Pesan Masuk"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-message"></use>
                    </svg>

                    <span>Pesan Masuk</span>
                </a>
            </div>

            <div class="garda-admin-nav-section">
                <div class="garda-admin-nav-heading">
                    Laporan
                </div>

                <a
                    href="<?= base_url('/reports') ?>"
                    class="garda-admin-nav-link <?= $isActive(['reports']) ?>"
                    title="Laporan Organisasi"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-report"></use>
                    </svg>

                    <span>Laporan Organisasi</span>
                </a>
            </div>

        </nav>

        <div class="garda-admin-sidebar-footer">

            <div class="garda-admin-sidebar-user">
                <div class="garda-admin-user-avatar">
                    <?= esc($userInitials) ?>
                </div>

                <div class="garda-admin-sidebar-user-copy">
                    <strong><?= esc($userName) ?></strong>
                    <span><?= esc($userRole) ?></span>
                </div>
            </div>

            <div class="garda-admin-sidebar-actions">

                <a
                    href="<?= base_url('/') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="Lihat website publik"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-globe"></use>
                    </svg>

                    <span>Lihat Website</span>
                </a>

                <a
                    href="<?= base_url('/logout') ?>"
                    class="garda-admin-logout-link"
                    title="Keluar"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-logout"></use>
                    </svg>

                    <span>Keluar</span>
                </a>

            </div>

        </div>

    </aside>

    <div class="garda-admin-workspace">

        <header class="garda-admin-topbar">

            <div class="garda-admin-topbar-left">

                <button
                    type="button"
                    class="garda-admin-mobile-menu"
                    id="adminMobileMenu"
                    aria-label="Buka menu"
                    aria-expanded="false"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-menu"></use>
                    </svg>
                </button>

                <div class="garda-admin-page-context">
                    <span>
                        <?= esc($pageContext['section']) ?>
                        <b>/</b>
                        <?= esc($pageContext['label']) ?>
                    </span>

                    <strong><?= esc($pageTitle) ?></strong>
                </div>

            </div>

            <div class="garda-admin-topbar-actions">

                <div class="garda-admin-date">
                    <?= esc($todayLabel) ?>
                </div>

                <a
                    href="<?= base_url('/messages') ?>"
                    class="garda-admin-icon-button"
                    title="Pesan dan notifikasi"
                    aria-label="Pesan dan notifikasi"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-bell"></use>
                    </svg>
                </a>

                <a
                    href="<?= base_url('/') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="garda-admin-public-button"
                >
                    <svg aria-hidden="true">
                        <use href="#icon-globe"></use>
                    </svg>

                    <span>Lihat Website</span>
                </a>

                <div
                    class="garda-admin-profile"
                    id="adminProfile"
                >
                    <button
                        type="button"
                        class="garda-admin-profile-button"
                        id="adminProfileButton"
                        aria-expanded="false"
                        aria-controls="adminProfileMenu"
                    >
                        <span class="garda-admin-profile-avatar">
                            <?= esc($userInitials) ?>
                        </span>

                        <span class="garda-admin-profile-copy">
                            <strong><?= esc($userName) ?></strong>
                            <small><?= esc($userRole) ?></small>
                        </span>

                        <svg aria-hidden="true">
                            <use href="#icon-chevron"></use>
                        </svg>
                    </button>

                    <div
                        class="garda-admin-profile-menu"
                        id="adminProfileMenu"
                    >
                        <div class="garda-admin-profile-summary">
                            <span class="garda-admin-profile-avatar large">
                                <?= esc($userInitials) ?>
                            </span>

                            <div>
                                <strong><?= esc($userName) ?></strong>
                                <span><?= esc($userRole) ?></span>
                            </div>
                        </div>

                        <a href="<?= base_url('/') ?>">
                            <svg aria-hidden="true">
                                <use href="#icon-globe"></use>
                            </svg>

                            Website Publik
                        </a>

                        <a
                            href="<?= base_url('/logout') ?>"
                            class="danger"
                        >
                            <svg aria-hidden="true">
                                <use href="#icon-logout"></use>
                            </svg>

                            Keluar dari Portal
                        </a>
                    </div>
                </div>

            </div>

        </header>

        <main class="garda-admin-content">
            <div class="garda-admin-content-inner">
                <?= $this->renderSection('content') ?>
            </div>
        </main>

    </div>

</div>

<script
    src="<?= base_url('assets/js/admin-portal.js') ?>"
></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>