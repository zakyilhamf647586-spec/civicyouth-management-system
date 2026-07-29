(function () {
    'use strict';

    document.documentElement.classList.add(
        'g01-intro-js'
    );

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const root = document.documentElement;
            const reduceMotion = window.matchMedia(
                '(prefers-reduced-motion: reduce)'
            ).matches;

            const hero = document.querySelector(
                '.g01-intro-hero'
            );

            const chapterLabel = document.getElementById(
                'g01IntroChapter'
            );

            const progressFill = document.getElementById(
                'g01IntroProgressFill'
            );

            const skipLink = document.getElementById(
                'g01IntroSkip'
            );

            const chapters = Array.from(
                document.querySelectorAll(
                    '[data-intro-chapter]'
                )
            );

            let scrollFrame = 0;

            const updateScrollState = function () {
                window.cancelAnimationFrame(scrollFrame);

                scrollFrame = window.requestAnimationFrame(
                    function () {
                        const available =
                            document.documentElement
                                .scrollHeight
                            - window.innerHeight;

                        const progress = available > 0
                            ? Math.min(
                                window.scrollY / available,
                                1
                            )
                            : 0;

                        root.style.setProperty(
                            '--g01-intro-progress-value',
                            String(progress)
                        );

                        let activeChapter = 1;
                        const target =
                            window.innerHeight * 0.52;

                        chapters.forEach(
                            function (chapter, index) {
                                if (
                                    chapter
                                        .getBoundingClientRect()
                                        .top
                                    <= target
                                ) {
                                    activeChapter =
                                        index + 1;
                                }
                            }
                        );

                        if (chapterLabel) {
                            chapterLabel.textContent =
                                String(activeChapter)
                                    .padStart(2, '0');
                        }

                        if (progressFill) {
                            progressFill.setAttribute(
                                'data-progress',
                                String(progress)
                            );
                        }

                        if (skipLink) {
                            skipLink.classList.toggle(
                                'is-hidden',
                                activeChapter
                                    === chapters.length
                            );
                        }
                    }
                );
            };

            window.addEventListener(
                'scroll',
                updateScrollState,
                { passive: true }
            );

            updateScrollState();

            let heroIsVisible = true;

            if (!reduceMotion) {
                window.addEventListener(
                    'pointermove',
                    function (event) {
                        root.style.setProperty(
                            '--g01-intro-pointer-x',
                            event.clientX + 'px'
                        );

                        root.style.setProperty(
                            '--g01-intro-pointer-y',
                            event.clientY + 'px'
                        );

                        if (!hero || !heroIsVisible) {
                            return;
                        }

                        const heroBounds =
                            hero.getBoundingClientRect();

                        if (
                            heroBounds.width <= 0
                            || heroBounds.height <= 0
                        ) {
                            return;
                        }

                        const heroX = (
                            (
                                event.clientX
                                - heroBounds.left
                            )
                            / heroBounds.width
                            - 0.5
                        ) * 48;

                        const heroY = (
                            (
                                event.clientY
                                - heroBounds.top
                            )
                            / heroBounds.height
                            - 0.5
                        ) * 36;

                        root.style.setProperty(
                            '--g01-intro-hero-x',
                            String(heroX)
                        );

                        root.style.setProperty(
                            '--g01-intro-hero-y',
                            String(heroY)
                        );
                    },
                    { passive: true }
                );
            }

            if (hero && reduceMotion) {
                hero.classList.add(
                    'is-awake'
                );
            } else if (hero) {
                window.requestAnimationFrame(
                    function () {
                        window.requestAnimationFrame(
                            function () {
                                hero.classList.add(
                                    'is-awake'
                                );
                            }
                        );
                    }
                );
            }

            if (hero && !reduceMotion) {
                let thunderTimer = 0;
                let thunderResetTimer = 0;
                let lastManualThunder = 0;

                const triggerThunder = function () {
                    window.clearTimeout(
                        thunderResetTimer
                    );

                    hero.classList.remove(
                        'is-thunder'
                    );

                    // Restart the short impact sequence,
                    // including consecutive interactions.
                    void hero.offsetWidth;

                    hero.classList.add(
                        'is-thunder'
                    );

                    thunderResetTimer =
                        window.setTimeout(
                            function () {
                                hero.classList.remove(
                                    'is-thunder'
                                );
                            },
                            1320
                        );
                };

                const scheduleThunder = function () {
                    window.clearTimeout(thunderTimer);

                    const nextDelay =
                        5400
                        + Math.round(
                            Math.random() * 4200
                        );

                    thunderTimer = window.setTimeout(
                        function () {
                            if (
                                heroIsVisible
                                && document.visibilityState
                                    === 'visible'
                            ) {
                                triggerThunder();
                            }

                            scheduleThunder();
                        },
                        nextDelay
                    );
                };

                if ('IntersectionObserver' in window) {
                    const heroObserver =
                        new IntersectionObserver(
                            function (entries) {
                                entries.forEach(
                                    function (entry) {
                                        heroIsVisible =
                                            entry.isIntersecting;
                                    }
                                );
                            },
                            {
                                threshold: 0.08,
                            }
                        );

                    heroObserver.observe(hero);
                }

                hero.addEventListener(
                    'pointerdown',
                    function (event) {
                        if (
                            event.target.closest(
                                'a, button'
                            )
                        ) {
                            return;
                        }

                        const now = Date.now();

                        if (
                            now - lastManualThunder
                            < 1800
                        ) {
                            return;
                        }

                        lastManualThunder = now;
                        triggerThunder();
                    }
                );

                window.setTimeout(
                    triggerThunder,
                    780
                );

                scheduleThunder();

                window.addEventListener(
                    'pagehide',
                    function () {
                        window.clearTimeout(
                            thunderTimer
                        );

                        window.clearTimeout(
                            thunderResetTimer
                        );
                    },
                    { once: true }
                );
            }

            const revealElements = Array.from(
                document.querySelectorAll(
                    '[data-intro-reveal]'
                )
            );

            if (
                !reduceMotion
                && 'IntersectionObserver' in window
            ) {
                const observer = new IntersectionObserver(
                    function (entries) {
                        entries.forEach(function (entry) {
                            if (!entry.isIntersecting) {
                                return;
                            }

                            entry.target.classList.add(
                                'is-visible'
                            );

                            observer.unobserve(
                                entry.target
                            );
                        });
                    },
                    {
                        threshold: 0.12,
                        rootMargin:
                            '0px 0px -5% 0px',
                    }
                );

                revealElements.forEach(
                    function (element) {
                        observer.observe(element);
                    }
                );
            } else {
                revealElements.forEach(
                    function (element) {
                        element.classList.add(
                            'is-visible'
                        );
                    }
                );
            }

            const destinationButtons = Array.from(
                document.querySelectorAll(
                    '[data-intro-destination]'
                )
            );

            const destinationName =
                document.getElementById(
                    'g01IntroDestinationName'
                );

            const enterLink = document.getElementById(
                'g01IntroEnter'
            );

            const enterLabel = document.getElementById(
                'g01IntroEnterLabel'
            );

            const transition = document.getElementById(
                'g01IntroTransition'
            );

            const transitionName =
                document.getElementById(
                    'g01IntroTransitionName'
                );

            const transitionPath =
                document.getElementById(
                    'g01IntroTransitionPath'
                );

            const selectDestination = function (button) {
                const name =
                    button.getAttribute('data-name')
                    || 'Beranda';

                const url =
                    button.getAttribute('data-url')
                    || '/home';

                destinationButtons.forEach(
                    function (candidate) {
                        const isSelected =
                            candidate === button;

                        candidate.classList.toggle(
                            'is-active',
                            isSelected
                        );

                        candidate.setAttribute(
                            'aria-pressed',
                            String(isSelected)
                        );
                    }
                );

                if (destinationName) {
                    destinationName.textContent = name;
                }

                if (enterLabel) {
                    enterLabel.textContent =
                        'Masuk ke ' + name;
                }

                if (enterLink) {
                    enterLink.href = url;
                    enterLink.setAttribute(
                        'data-name',
                        name
                    );
                }
            };

            destinationButtons.forEach(
                function (button) {
                    button.addEventListener(
                        'click',
                        function () {
                            selectDestination(button);
                        }
                    );
                }
            );

            if (destinationButtons.length > 0) {
                selectDestination(
                    destinationButtons[0]
                );
            }

            if (enterLink && transition) {
                enterLink.addEventListener(
                    'click',
                    function (event) {
                        if (
                            reduceMotion
                            || event.metaKey
                            || event.ctrlKey
                            || event.shiftKey
                            || event.altKey
                        ) {
                            return;
                        }

                        event.preventDefault();

                        const targetUrl = enterLink.href;
                        const name =
                            enterLink.getAttribute(
                                'data-name'
                            )
                            || 'Beranda';

                        let path = targetUrl;

                        try {
                            path = new URL(
                                targetUrl,
                                window.location.href
                            ).pathname;
                        } catch (error) {
                            path = targetUrl;
                        }

                        if (transitionName) {
                            transitionName.textContent =
                                name.toUpperCase();
                        }

                        if (transitionPath) {
                            transitionPath.textContent =
                                path;
                        }

                        try {
                            window.sessionStorage.setItem(
                                'g01-arrival-label',
                                name
                            );

                            window.sessionStorage.setItem(
                                'g01-arrival-source',
                                'introducing'
                            );
                        } catch (storageError) {
                            // Continue without transition hand-off data.
                        }

                        transition.setAttribute(
                            'aria-hidden',
                            'false'
                        );

                        transition.classList.add(
                            'is-active'
                        );

                        window.setTimeout(
                            function () {
                                window.location.assign(
                                    targetUrl
                                );
                            },
                            850
                        );
                    }
                );
            }

            window.addEventListener(
                'pageshow',
                function () {
                    if (!transition) {
                        return;
                    }

                    transition.classList.remove(
                        'is-active'
                    );

                    transition.setAttribute(
                        'aria-hidden',
                        'true'
                    );
                }
            );
        }
    );
}());
