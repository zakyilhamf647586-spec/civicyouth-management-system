(function () {
    'use strict';

    const body = document.body;
    const main = document.getElementById('main-content');

    if (
        !body
        || !main
        || !body.classList.contains('public-experience-v3')
    ) {
        return;
    }

    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const finePointer = window.matchMedia(
        '(pointer: fine)'
    ).matches;

    const previewMode = body.classList.contains(
        'public-body--cms-preview'
    ) || body.classList.contains(
        'public-body--navigation-preview'
    ) || body.classList.contains(
        'public-body--external-review'
    );

    const arrival = document.getElementById('g01Arrival');
    const arrivalLabel = document.getElementById('g01ArrivalLabel');
    const readingProgress = document.getElementById('g01ReadingProgress');
    const chapterRail = document.getElementById('g01ChapterRail');
    const chapterToggle = document.getElementById('g01ChapterToggle');
    const chapterLinks = document.getElementById('g01ChapterLinks');
    const chapterNumber = document.getElementById('g01ChapterNumber');
    const chapterLabel = document.getElementById('g01ChapterLabel');
    const chapterCount = document.getElementById('g01ChapterCount');
    const deck = document.getElementById('g01Deck');
    const deckTrigger = document.getElementById('g01DeckTrigger');
    const navbar = document.getElementById('publicNavbar');
    const pointer = document.getElementById('g01Pointer');

    if (previewMode && arrival) {
        arrival.classList.add('is-bypassed');
    }

    try {
        const source = window.sessionStorage.getItem(
            'g01-arrival-source'
        );
        const storedLabel = window.sessionStorage.getItem(
            'g01-arrival-label'
        );

        if (
            source === 'introducing'
            && storedLabel
            && arrivalLabel
        ) {
            arrivalLabel.textContent = storedLabel;
        }

        window.sessionStorage.removeItem('g01-arrival-source');
        window.sessionStorage.removeItem('g01-arrival-label');
    } catch (storageError) {
        // Arrival copy falls back to the server-rendered page label.
    }

    const normalizeText = function (value, maximum) {
        const text = String(value || '')
            .replace(/\s+/g, ' ')
            .trim();

        if (text.length <= maximum) {
            return text;
        }

        return text.slice(0, maximum - 1).trim() + '…';
    };

    const closeChapterRail = function () {
        if (!chapterRail || !chapterToggle) {
            return;
        }

        chapterRail.classList.remove('is-open');
        chapterToggle.setAttribute('aria-expanded', 'false');
    };

    const sectionCandidates = Array.from(
        main.querySelectorAll('section')
    );

    const chapters = sectionCandidates
        .map(function (section) {
            return {
                section: section,
                heading: section.querySelector('h1, h2'),
            };
        })
        .filter(function (candidate) {
            return candidate.heading
                && normalizeText(
                    candidate.heading.textContent,
                    90
                ).length > 1;
        })
        .filter(function (candidate, index, all) {
            return all.findIndex(function (item) {
                return item.section === candidate.section;
            }) === index;
        })
        .slice(0, 9);

    chapters.forEach(function (chapter, index) {
        const sectionIndex = String(index + 1).padStart(2, '0');
        const title = normalizeText(
            chapter.heading.textContent,
            52
        );

        if (!chapter.section.id) {
            chapter.section.id = 'g01-bab-' + sectionIndex;
        }

        chapter.section.setAttribute('data-g01-chapter', sectionIndex);
        chapter.title = title;
        chapter.index = sectionIndex;

        if (!chapterLinks) {
            return;
        }

        const link = document.createElement('a');
        link.href = '#' + chapter.section.id;
        link.dataset.index = sectionIndex;
        link.textContent = title;

        link.addEventListener('click', function () {
            closeChapterRail();
        });

        chapterLinks.appendChild(link);
        chapter.link = link;
    });

    if (chapterCount) {
        chapterCount.textContent = String(
            chapters.length
        ).padStart(2, '0') + ' bab';
    }

    if (chapterRail && chapters.length < 2) {
        chapterRail.hidden = true;
    }

    if (chapterRail && chapterToggle) {
        chapterToggle.addEventListener('click', function () {
            const isOpen = chapterRail.classList.toggle('is-open');

            chapterToggle.setAttribute(
                'aria-expanded',
                String(isOpen)
            );
        });

        document.addEventListener('click', function (event) {
            if (!chapterRail.contains(event.target)) {
                closeChapterRail();
            }
        });
    }

    let currentChapter = -1;
    let lastScrollY = window.scrollY;
    let scrollFrame = 0;

    const updateScrollExperience = function () {
        window.cancelAnimationFrame(scrollFrame);

        scrollFrame = window.requestAnimationFrame(function () {
            const scrollY = window.scrollY;
            const scrollable = Math.max(
                document.documentElement.scrollHeight
                - window.innerHeight,
                1
            );

            const percentage = Math.min(
                100,
                Math.max(0, (scrollY / scrollable) * 100)
            );

            document.documentElement.style.setProperty(
                '--g01-v3-progress',
                percentage.toFixed(2) + '%'
            );

            if (readingProgress) {
                readingProgress.setAttribute(
                    'aria-valuenow',
                    String(Math.round(percentage))
                );
            }

            let nextChapter = 0;

            chapters.forEach(function (chapter, index) {
                if (
                    chapter.section
                        .getBoundingClientRect()
                        .top
                    <= window.innerHeight * 0.44
                ) {
                    nextChapter = index;
                }
            });

            if (
                nextChapter !== currentChapter
                && chapters[nextChapter]
            ) {
                currentChapter = nextChapter;

                if (chapterNumber) {
                    chapterNumber.textContent =
                        chapters[nextChapter].index;
                }

                if (chapterLabel) {
                    chapterLabel.textContent =
                        chapters[nextChapter].title;
                }

                chapters.forEach(function (chapter, index) {
                    if (chapter.link) {
                        chapter.link.classList.toggle(
                            'is-active',
                            index === nextChapter
                        );
                    }
                });
            }

            if (
                navbar
                && !body.classList.contains(
                    'public-navigation-open'
                )
                && !body.classList.contains(
                    'public-deck-open'
                )
            ) {
                const movingDown =
                    scrollY > lastScrollY + 2;

                navbar.classList.toggle(
                    'g01-navbar-hidden',
                    movingDown && scrollY > 260
                );
            }

            lastScrollY = scrollY;
        });
    };

    updateScrollExperience();

    window.addEventListener(
        'scroll',
        updateScrollExperience,
        { passive: true }
    );

    window.addEventListener(
        'resize',
        updateScrollExperience,
        { passive: true }
    );

    let lastFocusedElement = null;

    const deckFocusable = function () {
        if (!deck) {
            return [];
        }

        return Array.from(deck.querySelectorAll(
            'a[href], button:not([disabled]), '
            + '[tabindex]:not([tabindex="-1"])'
        )).filter(function (element) {
            return element.offsetParent !== null;
        });
    };

    const openDeck = function () {
        if (!deck || !deckTrigger) {
            return;
        }

        lastFocusedElement = document.activeElement;
        deck.classList.add('is-open');
        deck.setAttribute('aria-hidden', 'false');
        deckTrigger.setAttribute('aria-expanded', 'true');
        body.classList.add('public-deck-open');

        if (navbar) {
            navbar.classList.remove('g01-navbar-hidden');
        }

        window.setTimeout(function () {
            const current = deck.querySelector(
                '.g01-deck__link.is-current'
            );
            const focusable = deckFocusable();

            (current || focusable[0] || deck).focus();
        }, reduceMotion ? 0 : 220);
    };

    const closeDeck = function () {
        if (!deck || !deckTrigger) {
            return;
        }

        deck.classList.remove('is-open');
        deck.setAttribute('aria-hidden', 'true');
        deckTrigger.setAttribute('aria-expanded', 'false');
        body.classList.remove('public-deck-open');

        if (
            lastFocusedElement
            && typeof lastFocusedElement.focus === 'function'
        ) {
            lastFocusedElement.focus();
        }
    };

    if (deckTrigger) {
        deckTrigger.addEventListener('click', openDeck);
    }

    document.querySelectorAll('[data-g01-deck-close]')
        .forEach(function (button) {
            button.addEventListener('click', closeDeck);
        });

    document.addEventListener('keydown', function (event) {
        const target = event.target;
        const isTyping = target instanceof HTMLElement
            && target.matches(
                'input, textarea, select, [contenteditable="true"]'
            );

        if (
            event.key.toLowerCase() === 'g'
            && !isTyping
            && !event.ctrlKey
            && !event.metaKey
            && !event.altKey
        ) {
            event.preventDefault();

            if (deck && deck.classList.contains('is-open')) {
                closeDeck();
            } else {
                openDeck();
            }
        }

        if (event.key === 'Escape') {
            closeDeck();
            closeChapterRail();
        }

        if (
            event.key === 'Tab'
            && deck
            && deck.classList.contains('is-open')
        ) {
            const focusable = deckFocusable();

            if (!focusable.length) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (
                event.shiftKey
                && document.activeElement === first
            ) {
                event.preventDefault();
                last.focus();
            } else if (
                !event.shiftKey
                && document.activeElement === last
            ) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    if (finePointer && !reduceMotion && pointer) {
        let pointerFrame = 0;
        let pointerX = -80;
        let pointerY = -80;

        document.addEventListener(
            'pointermove',
            function (event) {
                pointerX = event.clientX;
                pointerY = event.clientY;

                window.cancelAnimationFrame(pointerFrame);

                pointerFrame = window.requestAnimationFrame(
                    function () {
                        pointer.style.transform =
                            'translate3d('
                            + (pointerX - pointer.offsetWidth / 2)
                            + 'px,'
                            + (pointerY - pointer.offsetHeight / 2)
                            + 'px,0)';

                        pointer.classList.add('is-visible');
                    }
                );
            },
            { passive: true }
        );

        document.addEventListener('pointerleave', function () {
            pointer.classList.remove('is-visible');
        });

        document.querySelectorAll(
            'a, button, .program-pillar-card, '
            + '.public-activity-card, .garda-home-program-card'
        ).forEach(function (element) {
            element.addEventListener('pointerenter', function () {
                pointer.classList.add('is-interactive');
            });

            element.addEventListener('pointerleave', function () {
                pointer.classList.remove('is-interactive');
            });
        });

        document.querySelectorAll(
            '.garda-home-hero-copy, '
            + '.public-editorial-hero-copy, '
            + '.program-detail-copy, '
            + '.activity-detail-hero-copy, '
            + '.officials-hero-copy, '
            + '.contact-public-hero-copy'
        ).forEach(function (surface) {
            surface.addEventListener(
                'pointermove',
                function (event) {
                    const bounds = surface.getBoundingClientRect();

                    surface.style.setProperty(
                        '--g01-spotlight-x',
                        (event.clientX - bounds.left) + 'px'
                    );

                    surface.style.setProperty(
                        '--g01-spotlight-y',
                        (event.clientY - bounds.top) + 'px'
                    );
                },
                { passive: true }
            );
        });
    }

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a');

        if (
            !link
            || previewMode
            || reduceMotion
            || event.defaultPrevented
            || event.button !== 0
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
            || link.target === '_blank'
            || link.hasAttribute('download')
            || link.classList.contains('g01-no-transition')
        ) {
            return;
        }

        let targetUrl;

        try {
            targetUrl = new URL(link.href, window.location.href);
        } catch (urlError) {
            return;
        }

        if (
            !/^https?:$/.test(targetUrl.protocol)
            || targetUrl.origin !== window.location.origin
            || (
                targetUrl.pathname === window.location.pathname
                && targetUrl.search === window.location.search
                && targetUrl.hash
            )
        ) {
            return;
        }

        if (!arrival) {
            return;
        }

        event.preventDefault();
        closeDeck();

        if (arrivalLabel) {
            arrivalLabel.textContent = normalizeText(
                link.textContent,
                30
            ) || arrival.dataset.pageLabel || 'GARDA 01';
        }

        arrival.classList.add('is-covering');

        window.setTimeout(function () {
            window.location.assign(targetUrl.href);
        }, 620);
    });

    window.addEventListener('pageshow', function () {
        if (!arrival || previewMode) {
            return;
        }

        arrival.classList.remove('is-covering');
    });
}());
