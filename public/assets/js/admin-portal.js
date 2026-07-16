(function () {
    'use strict';

    const body = document.body;

    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminSidebarOverlay');

    const mobileMenuButton = document.getElementById(
        'adminMobileMenu'
    );

    const collapseButton = document.getElementById(
        'adminSidebarCollapse'
    );

    const profile = document.getElementById('adminProfile');

    const profileButton = document.getElementById(
        'adminProfileButton'
    );

    const profileMenu = document.getElementById(
        'adminProfileMenu'
    );

    const desktopBreakpoint = 1080;

    function openMobileSidebar() {
        body.classList.add('garda-admin-sidebar-open');

        if (mobileMenuButton) {
            mobileMenuButton.setAttribute(
                'aria-expanded',
                'true'
            );
        }
    }

    function closeMobileSidebar() {
        body.classList.remove('garda-admin-sidebar-open');

        if (mobileMenuButton) {
            mobileMenuButton.setAttribute(
                'aria-expanded',
                'false'
            );
        }
    }

    function closeProfileMenu() {
        if (!profile || !profileButton) {
            return;
        }

        profile.classList.remove('open');

        profileButton.setAttribute(
            'aria-expanded',
            'false'
        );
    }

    if (
        window.innerWidth >= desktopBreakpoint
        && localStorage.getItem(
            'gardaAdminSidebarCollapsed'
        ) === 'true'
    ) {
        body.classList.add(
            'garda-admin-sidebar-collapsed'
        );
    }

    mobileMenuButton?.addEventListener(
        'click',
        function () {
            if (
                body.classList.contains(
                    'garda-admin-sidebar-open'
                )
            ) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        }
    );

    overlay?.addEventListener(
        'click',
        closeMobileSidebar
    );

    collapseButton?.addEventListener(
        'click',
        function () {
            body.classList.toggle(
                'garda-admin-sidebar-collapsed'
            );

            localStorage.setItem(
                'gardaAdminSidebarCollapsed',
                String(
                    body.classList.contains(
                        'garda-admin-sidebar-collapsed'
                    )
                )
            );
        }
    );

    profileButton?.addEventListener(
        'click',
        function (event) {
            event.stopPropagation();

            const isOpen = profile.classList.toggle(
                'open'
            );

            profileButton.setAttribute(
                'aria-expanded',
                String(isOpen)
            );
        }
    );

    profileMenu?.addEventListener(
        'click',
        function (event) {
            event.stopPropagation();
        }
    );

    document.addEventListener(
        'click',
        function () {
            closeProfileMenu();
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeMobileSidebar();
                closeProfileMenu();
            }
        }
    );

    sidebar
        ?.querySelectorAll('a')
        .forEach(function (link) {
            link.addEventListener(
                'click',
                function () {
                    if (
                        window.innerWidth
                        < desktopBreakpoint
                    ) {
                        closeMobileSidebar();
                    }
                }
            );
        });

    window.addEventListener(
        'resize',
        function () {
            if (
                window.innerWidth
                >= desktopBreakpoint
            ) {
                closeMobileSidebar();
            } else {
                body.classList.remove(
                    'garda-admin-sidebar-collapsed'
                );
            }
        }
    );
})();