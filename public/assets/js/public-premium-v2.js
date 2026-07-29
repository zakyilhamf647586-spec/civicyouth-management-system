(function () {
    'use strict';

    const body = document.body;

    if (!body || !body.classList.contains('public-experience-v2')) {
        return;
    }

    const navbar = document.getElementById('publicNavbar');
    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const updateNavbarState = function () {
        if (!navbar) {
            return;
        }

        navbar.classList.toggle(
            'public-navbar--scrolled',
            window.scrollY > 24
        );
    };

    updateNavbarState();
    window.addEventListener('scroll', updateNavbarState, {
        passive: true,
    });

    const revealSelectors = [
        '.garda-home-hero > *',
        '.garda-home-statistics article',
        '.garda-home-section-heading',
        '.garda-home-value-grid article',
        '.garda-home-program-card',
        '.g01-impact-story > *',
        '.garda-home-activity-card',
        '.garda-home-collaboration > *',
        '.public-editorial-hero > *',
        '.public-content-section > *',
        '.public-section-header',
        '.program-pillar-card',
        '.public-activity-card',
        '.program-detail-hero > *',
        '.activity-detail-hero > *',
        '.activity-detail-main > *',
        '.activity-editorial-section > *',
        '.activity-impact-section > *',
        '.activity-related-card',
        '.officials-hero > *',
        '.officials-structure-section > *',
        '.officials-profile-section > *',
        '.contact-public-hero > *',
        '.contact-channel-section > *',
        '.contact-public-wrapper > *',
        '.public-page-cta > *',
    ];

    const revealElements = Array.from(
        document.querySelectorAll(revealSelectors.join(','))
    );

    revealElements.forEach(function (element, index) {
        element.setAttribute('data-public-reveal', '');
        element.style.setProperty(
            '--public-reveal-delay',
            String((index % 4) * 55) + 'ms'
        );
    });

    if (!reduceMotion && 'IntersectionObserver' in window) {
        body.classList.add('public-motion-ready');

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -6% 0px',
        });

        revealElements.forEach(function (element) {
            observer.observe(element);
        });
    } else {
        revealElements.forEach(function (element) {
            element.classList.add('is-visible');
        });
    }

    const backToTop = document.createElement('button');
    backToTop.type = 'button';
    backToTop.className = 'public-back-to-top';
    backToTop.setAttribute('aria-label', 'Kembali ke bagian atas halaman');
    backToTop.innerHTML = [
        '<svg viewBox="0 0 24 24" aria-hidden="true">',
        '<path d="m6 14 6-6 6 6"></path>',
        '</svg>',
    ].join('');

    document.body.appendChild(backToTop);

    const updateBackToTop = function () {
        backToTop.classList.toggle(
            'is-visible',
            window.scrollY > 680
        );
    };

    updateBackToTop();
    window.addEventListener('scroll', updateBackToTop, {
        passive: true,
    });

    backToTop.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: reduceMotion ? 'auto' : 'smooth',
        });
    });
}());
