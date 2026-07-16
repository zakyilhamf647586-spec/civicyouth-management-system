<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<section class="public-editorial-hero">
    <div class="public-editorial-hero-copy">
        <span class="public-kicker">Tentang Organisasi</span>

        <h1>
            Generasi Aktif<br>
            Randugarut
        </h1>

        <p>
            GARDA 01 merupakan identitas publik Karang Taruna RW 01
            Kelurahan Randugarut sebagai ruang kolaborasi, pengembangan
            pemuda, dan kontribusi nyata bagi lingkungan.
        </p>

        <div class="public-editorial-actions">
            <a href="<?= base_url('/program') ?>" class="btn btn-primary">
                Lihat Program
            </a>

            <a href="<?= base_url('/pengurus') ?>" class="btn btn-secondary">
                Kenali Pengurus
            </a>
        </div>
    </div>

    <aside class="public-brand-lockup-card">
        <img
            src="<?= esc(site_asset_url('site_logo', 'assets/img/logo-rw01.png'), 'attr') ?>"
            alt="Logo <?= esc(site_setting('organization_name', 'GARDA 01')) ?>"
        >

        <span>Master Brand</span>
        <strong>GARDA 01</strong>
        <p>Generasi Aktif Randugarut</p>

        <div class="public-brand-slogan">
            Guyub <b>•</b> Bergerak <b>•</b> Berdampak
        </div>
    </aside>
</section>

<section class="public-content-section">
    <div class="profile-introduction-grid">

        <article class="public-content-card profile-story-card">
            <span class="public-kicker">Siapa Kami</span>

            <h2>
                Tumbuh bersama pemuda dan warga RW 01
            </h2>

            <p>
                Karang Taruna RW 01 Kelurahan Randugarut merupakan
                organisasi sosial kepemudaan yang menjadi wadah
                partisipasi, pengembangan, dan kolaborasi pemuda
                di lingkungan RW 01.
            </p>

            <p>
                Melalui GARDA 01, organisasi membangun identitas yang
                lebih mudah dikenal sekaligus tetap mempertahankan
                nama resmi Karang Taruna RW 01 dalam administrasi,
                legalitas, dan tata kelola organisasi.
            </p>
        </article>

        <aside class="profile-identity-card">
            <span class="public-kicker">Identitas Resmi</span>

            <dl>
                <div>
                    <dt>Nama Organisasi</dt>
                    <dd>Karang Taruna RW 01 Kelurahan Randugarut</dd>
                </div>

                <div>
                    <dt>Identitas Publik</dt>
                    <dd>GARDA 01</dd>
                </div>

                <div>
                    <dt>Kepanjangan</dt>
                    <dd>Generasi Aktif Randugarut</dd>
                </div>

                <div>
                    <dt>Wilayah</dt>
                    <dd>RW 01 Kelurahan Randugarut</dd>
                </div>

                <div>
                    <dt>Slogan</dt>
                    <dd>Guyub • Bergerak • Berdampak</dd>
                </div>
            </dl>
        </aside>

    </div>
</section>

<section class="public-content-section">
    <div class="public-section-header">
        <span class="public-kicker">Arah Organisasi</span>
        <h2>Visi dan misi GARDA 01</h2>
    </div>

    <div class="profile-direction-grid">
        <article class="profile-vision-card">
            <span>Visi</span>

            <h3>
                Menjadi organisasi pemuda yang guyub, aktif,
                adaptif, dan menghadirkan dampak nyata bagi lingkungan.
            </h3>
        </article>

        <article class="profile-mission-card">
            <span>Misi</span>

            <ol>
                <li>
                    Membangun kebersamaan dan partisipasi pemuda
                    di seluruh wilayah RW 01.
                </li>

                <li>
                    Menyelenggarakan kegiatan sosial, lingkungan,
                    olahraga, pendidikan, usaha, dan kreativitas.
                </li>

                <li>
                    Mengembangkan tata kelola organisasi yang
                    tertib, transparan, dan terdokumentasi.
                </li>

                <li>
                    Membangun kolaborasi dengan warga, komunitas,
                    lembaga, pemerintah, dan mitra.
                </li>
            </ol>
        </article>
    </div>
</section>

<section class="public-content-section">
    <div class="public-section-header">
        <span class="public-kicker">Nilai Organisasi</span>
        <h2>Budaya yang kami bangun bersama</h2>
    </div>

    <div class="profile-value-grid">
        <article>
            <span>01</span>
            <h3>Guyub</h3>
            <p>
                Menjaga kebersamaan, rasa memiliki, dan hubungan
                yang sehat antarpemuda serta warga.
            </p>
        </article>

        <article>
            <span>02</span>
            <h3>Bergerak</h3>
            <p>
                Mengubah gagasan menjadi program, kegiatan,
                kolaborasi, dan karya yang nyata.
            </p>
        </article>

        <article>
            <span>03</span>
            <h3>Berdampak</h3>
            <p>
                Memastikan setiap langkah memberi manfaat dan
                perubahan positif bagi lingkungan.
            </p>
        </article>
    </div>
</section>

<section class="public-page-cta">
    <div>
        <span class="public-kicker">Ruang Kontribusi</span>

        <h2>
            Kenali tujuh pilar gerakan GARDA 01
        </h2>

        <p>
            Setiap pilar menjadi ruang bagi pemuda untuk belajar,
            berkolaborasi, dan menghasilkan kontribusi nyata.
        </p>
    </div>

    <a href="<?= base_url('/program') ?>" class="btn btn-primary">
        Jelajahi Program
    </a>
</section>

<?= $this->endSection() ?>