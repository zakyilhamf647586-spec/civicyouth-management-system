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

$contactEmail = site_setting('contact_email', '');
$contactWhatsapp = site_setting('contact_whatsapp', '');
$contactAddress = site_setting(
    'contact_address',
    'RW 01 Kelurahan Randugarut'
);
$contactVillage = site_setting('contact_village', 'Randugarut');
$contactDistrict = site_setting('contact_district', 'Tugu');
$contactCity = site_setting('contact_city', 'Kota Semarang');
$contactProvince = site_setting('contact_province', 'Jawa Tengah');

$instagramUrl = site_setting('instagram_url', '');
$tiktokUrl = site_setting('tiktok_url', '');
$youtubeUrl = site_setting('youtube_url', '');
$facebookUrl = site_setting('facebook_url', '');

$logoUrl = site_asset_url(
    'site_logo',
    'assets/img/logo-rw01.png'
);

$whatsappUrl = site_whatsapp_url(
    'Halo GARDA 01, saya ingin menghubungi pengurus.'
);
?>

<footer class="garda-public-footer">
    <div class="garda-footer-watermark" aria-hidden="true">
        <?= esc($organizationName) ?>
    </div>

    <div class="garda-footer-inner">
        <div class="garda-footer-grid">

            <section class="garda-footer-identity">
                <a
                    href="<?= base_url('/') ?>"
                    class="garda-footer-brand"
                    aria-label="<?= esc($organizationName) ?> — Beranda"
                >
                    <img
                        src="<?= esc($logoUrl, 'attr') ?>"
                        alt="Logo <?= esc($organizationName) ?>"
                    >

                    <div>
                        <strong><?= esc($footerHeading) ?></strong>
                        <span><?= esc($organizationFullName) ?></span>
                    </div>
                </a>

                <p><?= esc($footerDescription) ?></p>

                <div class="garda-footer-slogan">
                    <?= esc($organizationTagline) ?>
                </div>
            </section>

            <nav
                class="garda-footer-column"
                aria-label="Navigasi footer"
            >
                <h3>Navigasi</h3>

                <a href="<?= base_url('/') ?>">Beranda</a>
                <a href="<?= base_url('/profil') ?>">Tentang GARDA 01</a>
                <a href="<?= base_url('/program') ?>">Pilar Program</a>
                <a href="<?= base_url('/kegiatan') ?>">Kegiatan</a>
                <a href="<?= base_url('/pengurus') ?>">Pengurus</a>
                <a href="<?= base_url('/kontak') ?>">Kontak & Kolaborasi</a>
            </nav>

            <section class="garda-footer-column">
                <h3>Organisasi</h3>

                <span class="garda-footer-legal-name">
                    <?= esc($organizationLegalName) ?>
                </span>

                <span><?= nl2br(esc($contactAddress)) ?></span>

                <span>
                    Kelurahan <?= esc($contactVillage) ?><br>
                    Kecamatan <?= esc($contactDistrict) ?><br>
                    <?= esc($contactCity) ?><br>
                    <?= esc($contactProvince) ?>
                </span>
            </section>

            <section class="garda-footer-column">
                <h3>Hubungi Kami</h3>

                <span>
                    Sampaikan undangan, gagasan, informasi,
                    atau tawaran kolaborasi melalui kanal resmi
                    <?= esc($organizationName) ?>.
                </span>

                <?php if ($contactEmail !== '') : ?>
                    <a
                        href="mailto:<?= esc($contactEmail, 'attr') ?>"
                    >
                        <?= esc($contactEmail) ?>
                    </a>
                <?php endif; ?>

                <?php if (
                    $contactWhatsapp !== ''
                    && $whatsappUrl
                ) : ?>
                    <a
                        href="<?= esc($whatsappUrl, 'attr') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        WhatsApp: <?= esc($contactWhatsapp) ?>
                    </a>
                <?php endif; ?>

                <a href="<?= base_url('/kontak') ?>">
                    Formulir Kontak
                </a>

                <?php if ($instagramUrl !== '') : ?>
                    <a
                        href="<?= esc($instagramUrl, 'attr') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Instagram
                    </a>
                <?php endif; ?>

                <?php if ($tiktokUrl !== '') : ?>
                    <a
                        href="<?= esc($tiktokUrl, 'attr') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        TikTok
                    </a>
                <?php endif; ?>

                <?php if ($youtubeUrl !== '') : ?>
                    <a
                        href="<?= esc($youtubeUrl, 'attr') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        YouTube
                    </a>
                <?php endif; ?>

                <?php if ($facebookUrl !== '') : ?>
                    <a
                        href="<?= esc($facebookUrl, 'attr') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Facebook
                    </a>
                <?php endif; ?>
            </section>

        </div>

        <div class="garda-footer-bottom">
            <div>
                <span>
                    © <?= date('Y') ?> <?= esc($footerCopyright) ?>.
                </span>

                <small><?= esc($footerNote) ?></small>
            </div>

            <div class="garda-footer-bottom-links">
                <a href="<?= base_url('/profil') ?>">
                    Profil Organisasi
                </a>

                <a href="<?= base_url('/kontak') ?>">
                    Kontak
                </a>

                <a href="<?= base_url('/login') ?>">
                    Portal Internal
                </a>
            </div>
        </div>
    </div>
</footer>
