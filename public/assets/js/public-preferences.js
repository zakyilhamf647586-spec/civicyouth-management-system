(function () {
    'use strict';

    const storageKey = 'g01_theme';
    const root = document.documentElement;
    const colorMedia = window.matchMedia
        ? window.matchMedia('(prefers-color-scheme: dark)')
        : null;

    const validThemes = new Set([
        'auto',
        'ivory',
        'midnight',
    ]);

    const readPreference = function () {
        try {
            const stored = window.localStorage.getItem(
                storageKey
            );

            return validThemes.has(stored)
                ? stored
                : 'auto';
        } catch (error) {
            return 'auto';
        }
    };

    const resolveTheme = function (preference) {
        if (preference !== 'auto') {
            return preference;
        }

        return colorMedia && colorMedia.matches
            ? 'midnight'
            : 'ivory';
    };

    const syncThemeColor = function (resolved) {
        const meta = document.querySelector(
            'meta[name="theme-color"]'
        );

        if (!meta) {
            return;
        }

        if (document.body.classList.contains(
            'g01-intro-body'
        )) {
            meta.setAttribute('content', '#031321');
            return;
        }

        meta.setAttribute(
            'content',
            resolved === 'midnight'
                ? '#031321'
                : '#f7f3e9'
        );
    };

    const updateControls = function (preference) {
        document
            .querySelectorAll('[data-g01-theme-choice]')
            .forEach(function (button) {
                const active =
                    button.dataset.g01ThemeChoice
                    === preference;

                button.classList.toggle(
                    'is-active',
                    active
                );
                button.setAttribute(
                    'aria-pressed',
                    String(active)
                );
            });

        document
            .querySelectorAll('[data-g01-theme-summary]')
            .forEach(function (summary) {
                summary.textContent =
                    preference.toUpperCase();
            });
    };

    const applyTheme = function (
        preference,
        persist
    ) {
        const safePreference = validThemes.has(preference)
            ? preference
            : 'auto';
        const resolved = resolveTheme(safePreference);

        root.dataset.g01ThemePreference =
            safePreference;
        root.dataset.g01Theme = resolved;
        root.style.colorScheme =
            resolved === 'midnight'
                ? 'dark'
                : 'light';

        syncThemeColor(resolved);
        updateControls(safePreference);

        if (!persist) {
            return;
        }

        try {
            window.localStorage.setItem(
                storageKey,
                safePreference
            );
        } catch (error) {
            // The theme remains active for this page.
        }
    };

    const closeMenu = function (menu, restoreFocus) {
        const trigger = menu.querySelector(
            '[data-g01-preferences-trigger]'
        );
        const panel = menu.querySelector(
            '[data-g01-preferences-panel]'
        );

        if (!trigger || !panel) {
            return;
        }

        trigger.setAttribute('aria-expanded', 'false');
        panel.hidden = true;
        menu.classList.remove('is-open');

        if (restoreFocus) {
            trigger.focus();
        }
    };

    const openMenu = function (menu) {
        document
            .querySelectorAll('[data-g01-preferences]')
            .forEach(function (candidate) {
                if (candidate !== menu) {
                    closeMenu(candidate, false);
                }
            });

        const trigger = menu.querySelector(
            '[data-g01-preferences-trigger]'
        );
        const panel = menu.querySelector(
            '[data-g01-preferences-panel]'
        );

        if (!trigger || !panel) {
            return;
        }

        trigger.setAttribute('aria-expanded', 'true');
        panel.hidden = false;
        menu.classList.add('is-open');

        window.requestAnimationFrame(function () {
            const firstChoice = panel.querySelector(
                'a, button'
            );

            if (firstChoice) {
                firstChoice.focus({
                    preventScroll: true,
                });
            }
        });
    };

    document
        .querySelectorAll('[data-g01-preferences]')
        .forEach(function (menu) {
            const trigger = menu.querySelector(
                '[data-g01-preferences-trigger]'
            );

            if (!trigger) {
                return;
            }

            trigger.addEventListener('click', function () {
                const open =
                    trigger.getAttribute('aria-expanded')
                    === 'true';

                if (open) {
                    closeMenu(menu, false);
                } else {
                    openMenu(menu);
                }
            });

            menu
                .querySelectorAll(
                    '[data-g01-preferences-close]'
                )
                .forEach(function (button) {
                    button.addEventListener(
                        'click',
                        function () {
                            closeMenu(menu, true);
                        }
                    );
                });
        });

    document
        .querySelectorAll('[data-g01-theme-choice]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                applyTheme(
                    button.dataset.g01ThemeChoice,
                    true
                );
            });
        });

    document
        .querySelectorAll('[data-g01-locale-choice]')
        .forEach(function (link) {
            link.addEventListener('click', function () {
                const locale =
                    link.dataset.g01LocaleChoice;

                if (locale !== 'id' && locale !== 'en') {
                    return;
                }

                try {
                    window.localStorage.setItem(
                        'g01_locale',
                        locale
                    );
                } catch (error) {
                    // The destination URL still changes language.
                }

                document.cookie = 'g01_locale='
                    + locale
                    + '; Max-Age=31536000; Path=/; SameSite=Lax'
                    + (
                        window.location.protocol === 'https:'
                            ? '; Secure'
                            : ''
                    );
            });
        });

    const validationCopy = document.documentElement.lang
        .toLowerCase()
        .startsWith('en')
        ? {
            required: 'Please complete this field.',
            email: 'Please enter a valid email address.',
            short: function (length) {
                return 'Please use at least '
                    + length
                    + ' characters.';
            },
            long: function (length) {
                return 'Please use no more than '
                    + length
                    + ' characters.';
            },
            pattern: 'Please use the requested format.',
            invalid: 'Please check this field.',
        }
        : {
            required: 'Mohon isi bidang ini.',
            email: 'Mohon masukkan alamat email yang valid.',
            short: function (length) {
                return 'Gunakan minimal '
                    + length
                    + ' karakter.';
            },
            long: function (length) {
                return 'Gunakan maksimal '
                    + length
                    + ' karakter.';
            },
            pattern: 'Mohon gunakan format yang diminta.',
            invalid: 'Mohon periksa kembali bidang ini.',
        };

    const formControls =
        'input:not([type="hidden"]), select, textarea';

    const clearValidationMessage = function (field) {
        if (
            field
            && typeof field.setCustomValidity === 'function'
        ) {
            field.setCustomValidity('');
        }
    };

    document.addEventListener('invalid', function (event) {
        const field = event.target;

        if (!field || !field.matches(formControls)) {
            return;
        }

        clearValidationMessage(field);

        const validity = field.validity;
        let message = validationCopy.invalid;

        if (validity.valueMissing) {
            message = validationCopy.required;
        } else if (
            validity.typeMismatch
            && field.type === 'email'
        ) {
            message = validationCopy.email;
        } else if (validity.tooShort) {
            message = validationCopy.short(
                field.minLength
            );
        } else if (validity.tooLong) {
            message = validationCopy.long(
                field.maxLength
            );
        } else if (validity.patternMismatch) {
            message = validationCopy.pattern;
        }

        field.setCustomValidity(message);
    }, true);

    ['input', 'change'].forEach(function (eventName) {
        document.addEventListener(
            eventName,
            function (event) {
                const field = event.target;

                if (
                    field
                    && field.matches(formControls)
                ) {
                    clearValidationMessage(field);
                }
            }
        );
    });

    document.addEventListener('pointerdown', function (event) {
        document
            .querySelectorAll(
                '[data-g01-preferences].is-open'
            )
            .forEach(function (menu) {
                if (!menu.contains(event.target)) {
                    closeMenu(menu, false);
                }
            });
    });

    document.addEventListener('focusin', function (event) {
        document
            .querySelectorAll(
                '[data-g01-preferences].is-open'
            )
            .forEach(function (menu) {
                if (!menu.contains(event.target)) {
                    closeMenu(menu, false);
                }
            });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        document
            .querySelectorAll(
                '[data-g01-preferences].is-open'
            )
            .forEach(function (menu) {
                closeMenu(menu, true);
            });
    });

    if (colorMedia) {
        colorMedia.addEventListener('change', function () {
            const preference = readPreference();

            if (preference === 'auto') {
                applyTheme(preference, false);
            }
        });
    }

    applyTheme(readPreference(), false);
})();
