<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<?php
$cmsPage = $cmsPage ?? null;
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

        <div class="contact-public-values">
            <span>Guyub</span>
            <b>•</b>
            <span>Bergerak</span>
            <b>•</b>
            <span>Berdampak</span>
        </div>
    </div>

    <aside class="contact-public-identity">
        <img
            src="<?= esc(site_asset_url('site_logo', 'assets/img/logo-rw01.png'), 'attr') ?>"
            alt="Logo <?= esc(site_setting('organization_name', 'GARDA 01')) ?>"
        >

        <span>Kanal Resmi Organisasi</span>

        <strong>GARDA 01</strong>

        <p>
            Generasi Aktif Randugarut<br>
            Karang Taruna RW 01
        </p>
    </aside>

</section>

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

        <?php if (
            session()->getFlashdata('success')
        ) : ?>
            <div class="alert-success">
                <?= esc(
                    session()->getFlashdata('success')
                ) ?>
            </div>
        <?php endif; ?>

        <?php if (
            session()->getFlashdata('error')
        ) : ?>
            <div class="alert-error">
                <?= esc(
                    session()->getFlashdata('error')
                ) ?>
            </div>
        <?php endif; ?>

        <?php if (
            session()->getFlashdata('errors')
        ) : ?>
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
                <label for="website">
                    Website
                </label>

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
                    <label for="name">
                        Nama Lengkap
                    </label>

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
                    <label for="phone">
                        Nomor WhatsApp
                    </label>

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
                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= esc(old('email')) ?>"
                        placeholder="Opsional"
                    >
                </div>

                <div class="form-group">
                    <label for="category">
                        Kategori Pesan
                    </label>

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
                        <option
                            value="collaboration"
                            <?= $selectedCategory === 'collaboration'
                                ? 'selected'
                                : '' ?>
                        >
                            Kolaborasi dan Kemitraan
                        </option>

                        <option
                            value="activity"
                            <?= $selectedCategory === 'activity'
                                ? 'selected'
                                : '' ?>
                        >
                            Undangan atau Kegiatan
                        </option>

                        <option
                            value="social"
                            <?= $selectedCategory === 'social'
                                ? 'selected'
                                : '' ?>
                        >
                            Sosial dan Kemanusiaan
                        </option>

                        <option
                            value="business"
                            <?= $selectedCategory === 'business'
                                ? 'selected'
                                : '' ?>
                        >
                            Usaha dan UMKM
                        </option>

                        <option
                            value="media"
                            <?= $selectedCategory === 'media'
                                ? 'selected'
                                : '' ?>
                        >
                            Media dan Publikasi
                        </option>

                        <option
                            value="general"
                            <?= $selectedCategory === 'general'
                                ? 'selected'
                                : '' ?>
                        >
                            Informasi Umum
                        </option>
                    </select>
                </div>

            </div>

            <div class="form-group">
                <label for="subject">
                    Subjek
                </label>

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
                <label for="message">
                    Isi Pesan
                </label>

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
                    Kirim Pesan
                </button>

                <p>
                    Pesan akan masuk ke Portal Pengurus GARDA 01.
                </p>
            </div>

        </form>

    </article>

    <aside class="contact-public-sidebar">

        <div class="contact-public-sidebar-card">
            <span class="public-kicker">
                Ruang Kolaborasi
            </span>

            <h2>Kami terbuka untuk bergerak bersama</h2>

            <ul>
                <li>Program sosial dan kemanusiaan</li>
                <li>Kegiatan lingkungan dan kebersihan</li>
                <li>Olahraga serta kepemudaan</li>
                <li>Pendidikan dan keterampilan</li>
                <li>Media, desain, dan dokumentasi</li>
                <li>Usaha produktif dan UMKM</li>
            </ul>
        </div>

        <div class="contact-public-location-card">
            <span>Wilayah Organisasi</span>

            <strong>
                RW 01 Kelurahan Randugarut
            </strong>

            <p>
                Kecamatan Tugu<br>
                Kota Semarang
            </p>
        </div>

        <div class="contact-public-note">
            <strong>Catatan</strong>

            <p>
                Untuk kondisi darurat, layanan pemerintahan,
                keamanan, atau kesehatan, silakan menghubungi
                instansi resmi yang berwenang.
            </p>
        </div>

    </aside>

</section>

<?= $this->endSection() ?>