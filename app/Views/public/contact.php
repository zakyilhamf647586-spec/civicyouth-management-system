<?= $this->extend('layouts/public') ?>

<?= $this->section('head') ?>
<?php
$contactCssPath = FCPATH
    . 'assets/css/public-cms-contact.css';

$contactCssVersion = is_file($contactCssPath)
    ? (string) filemtime($contactCssPath)
    : '1';
?>
<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/public-cms-contact.css'
    ) ?>?v=<?= esc($contactCssVersion, 'attr') ?>"
>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$cmsPage = $cmsPage ?? null;

$contactEmail = trim((string) site_setting(
    'contact_email',
    ''
));

$contactWhatsapp = trim((string) site_setting(
    'contact_whatsapp',
    ''
));

$contactAddress = trim((string) site_setting(
    'contact_address',
    'RW 01 Kelurahan Randugarut'
));

$contactVillage = trim((string) site_setting(
    'contact_village',
    'Randugarut'
));

$contactDistrict = trim((string) site_setting(
    'contact_district',
    'Tugu'
));

$contactCity = trim((string) site_setting(
    'contact_city',
    'Kota Semarang'
));

$contactProvince = trim((string) site_setting(
    'contact_province',
    'Jawa Tengah'
));

$locationDescription = trim((string) site_setting(
    'contact_location_description',
    'Basis gerakan pemuda GARDA 01 di wilayah Randugarut.'
));

$officeHours = trim((string) site_setting(
    'contact_office_hours',
    'Setiap hari, respons menyesuaikan ketersediaan pengurus.'
));

$responseNote = trim((string) site_setting(
    'contact_response_note',
    'Pesan akan ditinjau oleh pengurus dan ditindaklanjuti sesuai kebutuhan.'
));

$whatsappUrl = site_whatsapp_url(
    'Halo GARDA 01, saya ingin menghubungi pengurus.'
);

$mapParts = array_values(array_filter([
    $contactAddress,
    $contactVillage !== ''
        ? 'Kelurahan ' . $contactVillage
        : '',
    $contactDistrict !== ''
        ? 'Kecamatan ' . $contactDistrict
        : '',
    $contactCity,
    $contactProvince,
]));

$mapsUrl = trim((string) site_setting(
    'contact_maps_url',
    ''
));

if ($mapsUrl === '') {
    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='
        . rawurlencode(implode(', ', array_unique($mapParts)));
}

$formatPhone = static function (string $number): string {
    $digits = preg_replace('/\D+/', '', $number) ?: '';

    if ($digits === '') {
        return $number;
    }

    if (str_starts_with($digits, '62')) {
        $digits = '0' . substr($digits, 2);
    }

    return trim(chunk_split($digits, 4, ' '));
};

$collaborationItems = public_cms_lines(
    $cmsPage,
    'collaboration',
    'items',
    "Program sosial dan kemanusiaan\nKegiatan lingkungan dan kebersihan\nOlahraga serta kepemudaan\nPendidikan dan keterampilan\nMedia, desain, dan dokumentasi\nUsaha produktif dan UMKM"
);

$valueItems = array_values(array_filter(array_map(
    'trim',
    explode(
        '•',
        public_cms_value(
            $cmsPage,
            'hero',
            'values_line',
            'Guyub • Bergerak • Berdampak'
        )
    )
)));
?>

<section class="contact-public-hero">
    <div class="contact-public-hero-copy">
        <span class="public-kicker">
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'kicker',
                'Kontak dan Kolaborasi'
            )) ?>
        </span>

        <h1>
            <?= nl2br(esc(public_cms_value(
                $cmsPage,
                'hero',
                'title',
                "Bergerak bersama\nGARDA 01"
            ))) ?>
        </h1>

        <p>
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'body',
                'Sampaikan gagasan, undangan, tawaran kolaborasi, kebutuhan sosial, maupun informasi kegiatan kepada Karang Taruna RW 01 Randugarut.'
            )) ?>
        </p>

        <?php if ($valueItems !== []) : ?>
            <div class="contact-public-values">
                <?php foreach ($valueItems as $index => $value) : ?>
                    <?php if ($index > 0) : ?>
                        <b aria-hidden="true">•</b>
                    <?php endif; ?>

                    <span><?= esc($value) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <aside class="contact-public-identity">
        <img
            src="<?= esc(site_asset_url(
                'site_logo',
                'assets/img/logo-rw01.png'
            ), 'attr') ?>"
            alt="Logo <?= esc(site_setting(
                'organization_name',
                'GARDA 01'
            )) ?>"
        >

        <span>
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'identity_kicker',
                'Kanal Resmi Organisasi'
            )) ?>
        </span>

        <strong>
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'identity_title',
                'GARDA 01'
            )) ?>
        </strong>

        <p>
            <?= nl2br(esc(public_cms_value(
                $cmsPage,
                'hero',
                'identity_subtitle',
                "Generasi Aktif Randugarut\nKarang Taruna RW 01"
            ))) ?>
        </p>
    </aside>
</section>

<?php if (public_cms_section_enabled(
    $cmsPage,
    'channels',
    true
)) : ?>
    <section class="contact-channel-section">
        <div class="public-section-header">
            <span class="public-kicker">
                <?= esc(public_cms_value(
                    $cmsPage,
                    'channels',
                    'kicker',
                    'Kanal Resmi'
                )) ?>
            </span>

            <h2>
                <?= esc(public_cms_value(
                    $cmsPage,
                    'channels',
                    'title',
                    'Pilih cara paling nyaman untuk terhubung'
                )) ?>
            </h2>

            <p>
                <?= esc(public_cms_value(
                    $cmsPage,
                    'channels',
                    'body',
                    'Gunakan kanal resmi berikut untuk menyampaikan kebutuhan, undangan, atau peluang kolaborasi.'
                )) ?>
            </p>
        </div>

        <div class="contact-channel-grid">
            <article>
                <span>Email</span>
                <strong>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'channels',
                        'email_label',
                        'Email Resmi'
                    )) ?>
                </strong>

                <p>
                    <?= esc($contactEmail !== ''
                        ? $contactEmail
                        : public_cms_value(
                            $cmsPage,
                            'channels',
                            'empty_value',
                            'Belum dicantumkan oleh pengurus.'
                        )) ?>
                </p>

                <?php if ($contactEmail !== '') : ?>
                    <a href="mailto:<?= esc(
                        $contactEmail,
                        'attr'
                    ) ?>">
                        Kirim email
                        <span aria-hidden="true">↗</span>
                    </a>
                <?php endif; ?>
            </article>

            <article>
                <span>WhatsApp</span>
                <strong>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'channels',
                        'whatsapp_label',
                        'WhatsApp'
                    )) ?>
                </strong>

                <p>
                    <?= esc($contactWhatsapp !== ''
                        ? $formatPhone($contactWhatsapp)
                        : public_cms_value(
                            $cmsPage,
                            'channels',
                            'empty_value',
                            'Belum dicantumkan oleh pengurus.'
                        )) ?>
                </p>

                <?php if (
                    $contactWhatsapp !== ''
                    && $whatsappUrl !== ''
                ) : ?>
                    <a
                        href="<?= esc($whatsappUrl, 'attr') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Buka WhatsApp
                        <span aria-hidden="true">↗</span>
                    </a>
                <?php endif; ?>
            </article>

            <article>
                <span>Lokasi</span>
                <strong>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'channels',
                        'location_label',
                        'Lokasi Organisasi'
                    )) ?>
                </strong>

                <p><?= esc($contactAddress) ?></p>

                <a
                    href="<?= esc($mapsUrl, 'attr') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Lihat lokasi
                    <span aria-hidden="true">↗</span>
                </a>
            </article>
        </div>

        <div class="contact-response-strip">
            <strong><?= esc($officeHours) ?></strong>
            <span><?= esc($responseNote) ?></span>
        </div>
    </section>
<?php endif; ?>

<section class="contact-public-wrapper">
    <article class="contact-public-form-card">
        <?php if (public_cms_section_enabled(
            $cmsPage,
            'form_intro',
            true
        )) : ?>
            <div class="contact-public-heading">
                <span class="public-kicker">
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'form_intro',
                        'kicker',
                        'Kirim Pesan'
                    )) ?>
                </span>

                <h2>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'form_intro',
                        'title',
                        'Apa yang ingin Anda sampaikan?'
                    )) ?>
                </h2>

                <p>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'form_intro',
                        'body',
                        'Isi informasi dengan lengkap agar tim GARDA 01 dapat menindaklanjuti pesan dengan tepat.'
                    )) ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')) : ?>
            <div class="alert-error">
                <?php foreach (
                    session()->getFlashdata('errors')
                    as $error
                ) : ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form
            action="<?= base_url('/kontak/kirim') ?>"
            method="post"
            class="contact-public-form"
        >
            <?= csrf_field() ?>

            <div
                class="contact-honeypot"
                aria-hidden="true"
            >
                <label for="website">Website</label>
                <input
                    type="text"
                    id="website"
                    name="website"
                    tabindex="-1"
                    autocomplete="off"
                >
            </div>

            <div class="contact-form-grid">
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= esc(old('name')) ?>"
                        placeholder="Nama Anda"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Nomor WhatsApp</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?= esc(old('phone')) ?>"
                        placeholder="Contoh: 081234567890"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= esc(old('email')) ?>"
                        placeholder="Opsional"
                    >
                </div>

                <div class="form-group">
                    <label for="category">Kategori Pesan</label>

                    <?php
                    $selectedCategory = old(
                        'category',
                        'collaboration'
                    );
                    ?>

                    <select
                        id="category"
                        name="category"
                        required
                    >
                        <?php foreach ([
                            'collaboration' =>
                                'Kolaborasi dan Kemitraan',
                            'activity' =>
                                'Undangan atau Kegiatan',
                            'social' =>
                                'Sosial dan Kemanusiaan',
                            'business' =>
                                'Usaha dan UMKM',
                            'media' =>
                                'Media dan Publikasi',
                            'general' =>
                                'Informasi Umum',
                        ] as $value => $label) : ?>
                            <option
                                value="<?= esc($value, 'attr') ?>"
                                <?= $selectedCategory === $value
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="subject">Subjek</label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="<?= esc(old('subject')) ?>"
                    placeholder="Ringkasan kebutuhan atau tujuan Anda"
                    required
                >
            </div>

            <div class="form-group">
                <label for="message">Isi Pesan</label>
                <textarea
                    id="message"
                    name="message"
                    rows="8"
                    maxlength="2000"
                    placeholder="Tuliskan informasi selengkap mungkin."
                    required
                ><?= esc(old('message')) ?></textarea>
            </div>

            <div class="contact-public-submit">
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'form_intro',
                        'submit_label',
                        'Kirim Pesan'
                    )) ?>
                </button>

                <p>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'form_intro',
                        'submit_note',
                        'Pesan akan masuk ke Portal Pengurus GARDA 01.'
                    )) ?>
                </p>
            </div>
        </form>
    </article>

    <aside class="contact-public-sidebar">
        <?php if (public_cms_section_enabled(
            $cmsPage,
            'collaboration',
            true
        )) : ?>
            <div class="contact-public-sidebar-card">
                <span class="public-kicker">
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'collaboration',
                        'kicker',
                        'Ruang Kolaborasi'
                    )) ?>
                </span>

                <h2>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'collaboration',
                        'title',
                        'Kami terbuka untuk bergerak bersama'
                    )) ?>
                </h2>

                <ul>
                    <?php foreach (
                        $collaborationItems as $item
                    ) : ?>
                        <li><?= esc($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (public_cms_section_enabled(
            $cmsPage,
            'location',
            true
        )) : ?>
            <div class="contact-public-location-card contact-location-complete">
                <span>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'location',
                        'kicker',
                        'Wilayah Organisasi'
                    )) ?>
                </span>

                <strong>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'location',
                        'title',
                        'RW 01 Kelurahan Randugarut'
                    )) ?>
                </strong>

                <p>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'location',
                        'body',
                        $locationDescription
                    )) ?>
                </p>

                <address>
                    <?= esc($contactAddress) ?><br>
                    <?php if ($contactDistrict !== '') : ?>
                        Kecamatan <?= esc($contactDistrict) ?><br>
                    <?php endif; ?>
                    <?= esc($contactCity) ?>
                    <?php if ($contactProvince !== '') : ?>
                        <br><?= esc($contactProvince) ?>
                    <?php endif; ?>
                </address>

                <a
                    href="<?= esc($mapsUrl, 'attr') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'location',
                        'map_label',
                        'Buka Google Maps'
                    )) ?>
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        <?php endif; ?>

        <?php if (public_cms_section_enabled(
            $cmsPage,
            'notice',
            true
        )) : ?>
            <div class="contact-public-note">
                <strong>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'notice',
                        'title',
                        'Catatan'
                    )) ?>
                </strong>

                <p>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'notice',
                        'body',
                        'Untuk kondisi darurat, layanan pemerintahan, keamanan, atau kesehatan, silakan menghubungi instansi resmi yang berwenang.'
                    )) ?>
                </p>
            </div>
        <?php endif; ?>
    </aside>
</section>

<?= $this->endSection() ?>
