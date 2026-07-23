<?php

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

$footerHeading = site_setting(
    'footer_heading',
    $organizationName
);

$footerDescription = site_setting(
    'footer_description',
    'Generasi Aktif Randugarut. Ruang kolaborasi pemuda untuk tumbuh, bergerak, dan memberi dampak bagi lingkungan.'
);

$footerNote = site_setting(
    'footer_note',
    'Website resmi Karang Taruna RW 01 Kelurahan Randugarut.'
);

$footerCopyright = site_setting(
    'footer_copyright',
    'Karang Taruna RW 01 Randugarut'
);

$footerNavigationHeading = site_setting(
    'footer_navigation_heading',
    'Navigasi'
);

$footerLocationHeading = site_setting(
    'footer_location_heading',
    'Organisasi & Lokasi'
);

$footerContactHeading = site_setting(
    'footer_contact_heading',
    'Hubungi Kami'
);

$footerContactIntro = site_setting(
    'footer_contact_intro',
    'Sampaikan undangan, gagasan, informasi, atau tawaran kolaborasi melalui kanal resmi GARDA 01.'
);

$footerMapLabel = site_setting(
    'footer_map_label',
    'Lokasi Organisasi'
);

$footerMapAction = site_setting(
    'footer_map_action',
    'Buka di Google Maps'
);

$footerNavigationItems =
    website_navigation_items('footer');

$contactEmail = trim((string) site_setting('contact_email', ''));
$contactWhatsapp = trim((string) site_setting('contact_whatsapp', ''));
$contactAddress = trim((string) site_setting(
    'contact_address',
    'RW 01 Kelurahan Randugarut'
));
$contactVillage = trim((string) site_setting('contact_village', 'Randugarut'));
$contactDistrict = trim((string) site_setting('contact_district', 'Tugu'));
$contactCity = trim((string) site_setting('contact_city', 'Kota Semarang'));
$contactProvince = trim((string) site_setting('contact_province', 'Jawa Tengah'));

$instagramUrl = trim((string) site_setting('instagram_url', ''));
$tiktokUrl = trim((string) site_setting('tiktok_url', ''));
$youtubeUrl = trim((string) site_setting('youtube_url', ''));
$facebookUrl = trim((string) site_setting('facebook_url', ''));

$logoUrl = site_asset_url(
    'site_logo',
    'assets/img/logo-rw01.png'
);

$whatsappUrl = site_whatsapp_url(
    'Halo GARDA 01, saya ingin menghubungi pengurus.'
);

$extractSocialHandle = static function (
    string $url,
    string $fallback
): string {
    if ($url === '') {
        return $fallback;
    }

    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

    if ($path === '') {
        return $fallback;
    }

    $segments = array_values(array_filter(explode('/', $path)));
    $handle = end($segments);

    if (!is_string($handle) || $handle === '') {
        return $fallback;
    }

    return '@' . ltrim($handle, '@');
};

$formatPhone = static function (string $number): string {
    $digits = preg_replace('/\D+/', '', $number) ?: '';

    if ($digits === '') {
        return $number;
    }

    if (str_starts_with($digits, '62')) {
        $local = '0' . substr($digits, 2);
    } else {
        $local = $digits;
    }

    if (strlen($local) >= 11) {
        return trim(chunk_split($local, 4, ' '));
    }

    return $number;
};

$instagramHandle = $extractSocialHandle(
    $instagramUrl,
    'Instagram GARDA 01'
);

$otherSocials = array_values(array_filter([
    $tiktokUrl !== '' ? [
        'label' => 'TikTok',
        'url' => $tiktokUrl,
    ] : null,
    $youtubeUrl !== '' ? [
        'label' => 'YouTube',
        'url' => $youtubeUrl,
    ] : null,
    $facebookUrl !== '' ? [
        'label' => 'Facebook',
        'url' => $facebookUrl,
    ] : null,
]));

$locationDescription = trim((string) site_setting(
    'contact_location_description',
    'Basis gerakan pemuda GARDA 01 di wilayah Randugarut.'
));

$mapQueryParts = array_values(array_filter([
    $contactAddress,
    $contactVillage !== '' ? 'Kelurahan ' . $contactVillage : '',
    $contactDistrict !== '' ? 'Kecamatan ' . $contactDistrict : '',
    $contactCity,
    $contactProvince,
]));

$mapQuery = implode(', ', array_unique($mapQueryParts));
$mapsUrl = trim((string) site_setting('contact_maps_url', ''));

if ($mapsUrl === '') {
    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='
        . rawurlencode($mapQuery);
}

$cityChip = preg_replace(
    '/^(Kota|Kabupaten)\s+/i',
    '',
    $contactCity
) ?: $contactCity;

$locationTags = array_values(array_unique(array_filter([
    'RW 01',
    $contactVillage,
    $contactDistrict,
    $cityChip,
])));

$mapLabelParts = array_values(array_filter([
    $contactVillage,
    $contactDistrict,
]));

$mapLabel = $mapLabelParts !== []
    ? implode(', ', $mapLabelParts)
    : 'Randugarut, Tugu';
?>

<footer class="g01-footer">
    <div class="g01-footer__watermark" aria-hidden="true">
        <?= esc($organizationName) ?>
    </div>

    <div class="g01-footer__inner">
        <div class="g01-footer__grid">

            <section class="g01-footer__identity">
                <a
                    href="<?= base_url('/') ?>"
                    class="g01-footer__brand"
                    aria-label="<?= esc($organizationName) ?> — Beranda"
                >
                    <span class="g01-footer__brand-mark">
                        <img
                            src="<?= esc($logoUrl, 'attr') ?>"
                            alt="Logo <?= esc($organizationName) ?>"
                        >
                    </span>

                    <span class="g01-footer__brand-copy">
                        <strong><?= esc($footerHeading) ?></strong>
                        <small><?= esc($organizationFullName) ?></small>
                    </span>
                </a>

                <p class="g01-footer__description">
                    <?= esc($footerDescription) ?>
                </p>

                <p class="g01-footer__tagline">
                    <?= esc($organizationTagline) ?>
                </p>
            </section>

            <nav
                class="g01-footer__column"
                aria-label="Navigasi footer"
            >
                <h2><?= esc($footerNavigationHeading) ?></h2>

                <?php foreach (
                    $footerNavigationItems as $item
                ) : ?>
                    <?php
                    $targetBlank =
                        ($item['target'] ?? 'self')
                        === 'blank';
                    ?>

                    <a
                        href="<?= esc(
                            website_navigation_url(
                                (string) $item['url']
                            ),
                            'attr'
                        ) ?>"
                        <?= $targetBlank
                            ? 'target="_blank" rel="noopener noreferrer"'
                            : '' ?>
                    >
                        <?= esc($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <section class="g01-footer__location">
                <h2><?= esc($footerLocationHeading) ?></h2>

                <div class="g01-footer__location-card">
                    <div class="g01-footer__location-heading">
                        <span class="g01-footer__location-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 21s6-5.1 6-11a6 6 0 1 0-12 0c0 5.9 6 11 6 11Z"></path>
                                <circle cx="12" cy="10" r="2.2"></circle>
                            </svg>
                        </span>

                        <span class="g01-footer__location-copy">
                            <strong><?= esc($organizationLegalName) ?></strong>
                            <small><?= esc($locationDescription) ?></small>
                        </span>
                    </div>

                    <div class="g01-footer__location-address">
                        <span><?= esc($contactAddress) ?></span>

                        <?php if ($contactDistrict !== '') : ?>
                            <span>Kecamatan <?= esc($contactDistrict) ?></span>
                        <?php endif; ?>

                        <?php if ($contactCity !== '') : ?>
                            <span><?= esc($contactCity) ?></span>
                        <?php endif; ?>

                        <?php if ($contactProvince !== '') : ?>
                            <span><?= esc($contactProvince) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="g01-footer__location-tags">
                        <?php foreach ($locationTags as $tag) : ?>
                            <span><?= esc($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a
                    href="<?= esc($mapsUrl, 'attr') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="g01-footer__map-preview"
                    aria-label="Buka lokasi <?= esc($organizationName) ?> di Google Maps"
                >
                    <span class="g01-footer__map-visual">
                        <span class="g01-footer__map-road road-one"></span>
                        <span class="g01-footer__map-road road-two"></span>
                        <span class="g01-footer__map-road road-three"></span>

                        <span class="g01-footer__map-pin">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 21s6-5.1 6-11a6 6 0 1 0-12 0c0 5.9 6 11 6 11Z"></path>
                                <circle cx="12" cy="10" r="2.1"></circle>
                            </svg>
                        </span>

                        <span class="g01-footer__map-label">
                            <?= esc($mapLabel) ?>
                        </span>
                    </span>

                    <span class="g01-footer__map-action">
                        <span>
                            <small><?= esc($footerMapLabel) ?></small>
                            <strong><?= esc($footerMapAction) ?></strong>
                        </span>

                        <span aria-hidden="true">↗</span>
                    </span>
                </a>
            </section>

            <section class="g01-footer__contact">
                <h2><?= esc($footerContactHeading) ?></h2>

                <p class="g01-footer__contact-intro">
                    <?= esc($footerContactIntro) ?>
                </p>

                <div class="g01-footer__contact-list">
                    <?php if ($instagramUrl !== '') : ?>
                        <a
                            href="<?= esc($instagramUrl, 'attr') ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="g01-footer__contact-item"
                        >
                            <span class="g01-footer__contact-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="3.5" y="3.5" width="17" height="17" rx="5"></rect>
                                    <circle cx="12" cy="12" r="4"></circle>
                                    <circle cx="17.4" cy="6.8" r="1"></circle>
                                </svg>
                            </span>

                            <span class="g01-footer__contact-copy">
                                <small>Instagram</small>
                                <strong><?= esc($instagramHandle) ?></strong>
                            </span>

                            <span class="g01-footer__contact-arrow" aria-hidden="true">↗</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($contactEmail !== '') : ?>
                        <a
                            href="mailto:<?= esc($contactEmail, 'attr') ?>"
                            class="g01-footer__contact-item"
                        >
                            <span class="g01-footer__contact-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="3"></rect>
                                    <path d="m4.5 7 7.5 5.7L19.5 7"></path>
                                </svg>
                            </span>

                            <span class="g01-footer__contact-copy">
                                <small>Email</small>
                                <strong><?= esc($contactEmail) ?></strong>
                            </span>

                            <span class="g01-footer__contact-arrow" aria-hidden="true">↗</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($contactWhatsapp !== '' && $whatsappUrl) : ?>
                        <a
                            href="<?= esc($whatsappUrl, 'attr') ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="g01-footer__contact-item"
                        >
                            <span class="g01-footer__contact-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M20 11.7a8 8 0 0 1-11.8 7L4 20l1.3-4.1A8 8 0 1 1 20 11.7Z"></path>
                                    <path d="M9 8.5c.5 2.4 2.1 4 4.5 4.8"></path>
                                </svg>
                            </span>

                            <span class="g01-footer__contact-copy">
                                <small>WhatsApp</small>
                                <strong><?= esc($formatPhone($contactWhatsapp)) ?></strong>
                            </span>

                            <span class="g01-footer__contact-arrow" aria-hidden="true">↗</span>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($otherSocials !== []) : ?>
                    <div class="g01-footer__social-links">
                        <?php foreach ($otherSocials as $social) : ?>
                            <a
                                href="<?= esc($social['url'], 'attr') ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <?= esc($social['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <a
                    href="<?= base_url('/kontak') ?>"
                    class="g01-footer__contact-cta"
                >
                    <span>Hubungi <?= esc($organizationName) ?></span>
                    <span aria-hidden="true">→</span>
                </a>
            </section>

        </div>

        <div class="g01-footer__bottom">
            <div>
                <p>
                    © <?= date('Y') ?> <?= esc($footerCopyright) ?>.
                </p>
                <small><?= esc($footerNote) ?></small>
            </div>

            <nav aria-label="Navigasi legal footer">
                <a href="<?= base_url('/profil') ?>">Profil Organisasi</a>
                <a href="<?= base_url('/kontak') ?>">Kontak</a>
                <a href="<?= base_url('/login') ?>">Portal Internal</a>
            </nav>
        </div>
    </div>
</footer>
