<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<section class="public-editorial-hero public-program-hero">
    <div class="public-editorial-hero-copy">
        <span class="public-kicker">Pilar Gerakan</span>

        <h1>
            Program<br>
            GARDA 01
        </h1>

        <p>
            Tujuh pilar program yang menjadi ruang gerak pemuda
            dalam bidang sosial, lingkungan, olahraga, kreativitas,
            usaha, pendidikan, dan keagamaan.
        </p>

        <div class="public-editorial-actions">
            <a href="#daftar-program" class="btn btn-primary">
                Lihat Seluruh Pilar
            </a>

            <a href="<?= base_url('/profil') ?>" class="btn btn-secondary">
                Tentang GARDA 01
            </a>
        </div>
    </div>

    <aside class="public-program-summary">
        <strong><?= count($programs) ?></strong>
        <span>Pilar Program</span>

        <div>
            <small>Master Brand</small>
            <b>GARDA 01</b>
        </div>

        <p>
            Guyub • Bergerak • Berdampak
        </p>
    </aside>
</section>

<section
    class="public-content-section"
    id="daftar-program"
>
    <div class="public-section-header">
        <span class="public-kicker">Arsitektur Program</span>

        <h2>
            Tujuh ruang kontribusi pemuda
        </h2>

        <p>
            Setiap pilar memiliki fokus yang jelas, tetapi tetap
            bergerak dalam satu identitas GARDA 01.
        </p>
    </div>

    <div class="program-pillar-grid">
        <?php foreach ($programs as $program) : ?>
            <article class="program-pillar-card">
                <div class="program-pillar-number">
                    <?= esc($program['number']) ?>
                </div>

                <span class="program-pillar-label">
                    <?= esc($program['label']) ?>
                </span>

                <h3><?= esc($program['name']) ?></h3>

                <p><?= esc($program['short_description']) ?></p>

                <a href="<?= base_url(
                    '/program/' . $program['slug']
                ) ?>">
                    Lihat Program
                    <span aria-hidden="true">→</span>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="public-page-cta">
    <div>
        <span class="public-kicker">Dari Program Menjadi Aksi</span>

        <h2>
            Lihat pelaksanaan kegiatan GARDA 01
        </h2>

        <p>
            Program menjadi arah gerak, sedangkan kegiatan menjadi
            dokumentasi pelaksanaannya di lingkungan masyarakat.
        </p>
    </div>

    <a href="<?= base_url('/kegiatan') ?>" class="btn btn-primary">
        Lihat Kegiatan
    </a>
</section>

<?= $this->endSection() ?>