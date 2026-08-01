<?php

$preferenceContext = preg_replace(
    '/[^a-z0-9-]+/',
    '-',
    strtolower((string) (
        $preferenceContext ?? 'public'
    ))
);

if (
    !is_string($preferenceContext)
    || $preferenceContext === ''
) {
    $preferenceContext = 'public';
}

$preferenceId = 'g01Preferences-'
    . $preferenceContext;
$preferencePanelId = $preferenceId . '-panel';
$currentLocale = public_locale();
?>

<div
    class="g01-preferences g01-preferences--<?= esc(
        $preferenceContext,
        'attr'
    ) ?>"
    data-g01-preferences
>
    <button
        type="button"
        class="g01-preferences__trigger"
        aria-label="<?= esc(
            public_t(
                'preferences.open',
                'Buka pilihan bahasa dan tema'
            ),
            'attr'
        ) ?>"
        aria-controls="<?= esc(
            $preferencePanelId,
            'attr'
        ) ?>"
        aria-expanded="false"
        data-g01-preferences-trigger
    >
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M3 12h18"></path>
            <path d="M12 3c2.4 2.5 3.7 5.5 3.7 9S14.4 18.5 12 21"></path>
            <path d="M12 3C9.6 5.5 8.3 8.5 8.3 12s1.3 6.5 3.7 9"></path>
        </svg>

        <span data-g01-preferences-summary>
            <?= strtoupper(esc($currentLocale)) ?>
            <i aria-hidden="true">·</i>
            <b data-g01-theme-summary>AUTO</b>
        </span>
    </button>

    <section
        class="g01-preferences__panel"
        id="<?= esc($preferencePanelId, 'attr') ?>"
        aria-label="<?= esc(
            public_t(
                'preferences.label',
                'Bahasa dan tema'
            ),
            'attr'
        ) ?>"
        data-g01-preferences-panel
        hidden
    >
        <header>
            <span>
                G/01 · <?= esc(public_t(
                    'preferences.label',
                    'Bahasa dan tema'
                )) ?>
            </span>

            <button
                type="button"
                aria-label="<?= esc(
                    public_t(
                        'preferences.close',
                        'Tutup pilihan'
                    ),
                    'attr'
                ) ?>"
                data-g01-preferences-close
            >
                <span></span>
                <span></span>
            </button>
        </header>

        <div class="g01-preferences__group">
            <span>
                <?= esc(public_t(
                    'preferences.language',
                    'Bahasa'
                )) ?>
            </span>

            <div
                class="g01-preferences__choices"
                role="group"
                aria-label="<?= esc(
                    public_t(
                        'preferences.language',
                        'Bahasa'
                    ),
                    'attr'
                ) ?>"
            >
                <a
                    href="<?= esc(
                        public_language_url('id'),
                        'attr'
                    ) ?>"
                    hreflang="id-ID"
                    lang="id"
                    class="<?= $currentLocale === 'id'
                        ? 'is-active'
                        : '' ?>"
                    <?= $currentLocale === 'id'
                        ? 'aria-current="true"'
                        : '' ?>
                    data-g01-locale-choice="id"
                >
                    <strong>ID</strong>
                    <small>Bahasa Indonesia</small>
                </a>

                <a
                    href="<?= esc(
                        public_language_url('en'),
                        'attr'
                    ) ?>"
                    hreflang="en"
                    lang="en"
                    class="<?= $currentLocale === 'en'
                        ? 'is-active'
                        : '' ?>"
                    <?= $currentLocale === 'en'
                        ? 'aria-current="true"'
                        : '' ?>
                    data-g01-locale-choice="en"
                >
                    <strong>EN</strong>
                    <small>English</small>
                </a>
            </div>
        </div>

        <div class="g01-preferences__group">
            <span>
                <?= esc(public_t(
                    'preferences.theme',
                    'Tema'
                )) ?>
            </span>

            <div
                class="g01-preferences__theme-grid"
                role="group"
                aria-label="<?= esc(
                    public_t(
                        'preferences.theme',
                        'Tema'
                    ),
                    'attr'
                ) ?>"
            >
                <button
                    type="button"
                    data-g01-theme-choice="auto"
                >
                    <i
                        class="g01-preferences__swatch g01-preferences__swatch--auto"
                        aria-hidden="true"
                    ></i>
                    <span>
                        <strong><?= esc(public_t(
                            'preferences.auto',
                            'Otomatis'
                        )) ?></strong>
                        <small><?= esc(public_t(
                            'preferences.auto_note',
                            'Mengikuti perangkat'
                        )) ?></small>
                    </span>
                </button>

                <button
                    type="button"
                    data-g01-theme-choice="ivory"
                >
                    <i
                        class="g01-preferences__swatch g01-preferences__swatch--ivory"
                        aria-hidden="true"
                    ></i>
                    <span>
                        <strong>Ivory</strong>
                        <small>Editorial Light</small>
                    </span>
                </button>

                <button
                    type="button"
                    data-g01-theme-choice="midnight"
                >
                    <i
                        class="g01-preferences__swatch g01-preferences__swatch--midnight"
                        aria-hidden="true"
                    ></i>
                    <span>
                        <strong>Midnight</strong>
                        <small>Deep Navy</small>
                    </span>
                </button>
            </div>
        </div>

        <p class="g01-preferences__note">
            <?= esc(public_t(
                'preferences.saved',
                'Pilihan tersimpan di perangkat ini.'
            )) ?>
        </p>
    </section>
</div>
