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

$contactEmail = trim((string) site_setting('contact_email', ''));
$contactWhatsapp = trim((string) site_setting('contact_whatsapp', ''));
$contactAddress = site_setting(
    'contact_address',
    'RW 01 Kelurahan Randugarut'
);
$contactVillage = site_setting('contact_village', 'Randugarut');
$contactDistrict = site_setting('contact_district', 'Tugu');
$contactCity = site_setting('contact_city', 'Kota Semarang');
$contactProvince = site_setting('contact_province', 'Jawa Tengah');

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
                <h2>Navigasi</h2>

                <a href="<?= base_url('/') ?>">Beranda</a>
                <a href="<?= base_url('/profil') ?>">Tentang GARDA 01</a>
                <a href="<?= base_url('/program') ?>">Pilar Program</a>
                <a href="<?= base_url('/kegiatan') ?>">Kegiatan</a>
                <a href="<?= base_url('/pengurus') ?>">Pengurus</a>
                <a href="<?= base_url('/kontak') ?>">Kontak & Kolaborasi</a>
            </nav>

            <section class="g01-footer__column g01-footer__organization">
                <h2>Organisasi</h2>

                <strong><?= esc($organizationLegalName) ?></strong>

                <p><?= nl2br(esc($contactAddress)) ?></p>

                <address>
                    Kelurahan <?= esc($contactVillage) ?><br>
                    Kecamatan <?= esc($contactDistrict) ?><br>
                    <?= esc($contactCity) ?><br>
                    <?= esc($contactProvince) ?>
                </address>
            </section>

            <section class="g01-footer__contact">
                <h2>Hubungi Kami</h2>

                <p class="g01-footer__contact-intro">
                    Sampaikan undangan, gagasan, informasi, atau
                    tawaran kolaborasi melalui kanal resmi
                    <?= esc($organizationName) ?>.
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
