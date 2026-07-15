<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<section class="program-detail-hero">
    <div class="program-detail-copy">

        <a
            href="<?= base_url('/program') ?>"
            class="program-detail-back"
        >
            ← Seluruh Program
        </a>

        <span class="public-kicker">
            <?= esc($program['label']) ?>
        </span>

        <h1><?= esc($program['name']) ?></h1>

        <p class="program-detail-tagline">
            <?= esc($program['tagline']) ?>
        </p>

        <p class="program-detail-description">
            <?= esc($program['description']) ?>
        </p>

        <div class="public-editorial-actions">
            <a
                href="<?= base_url('/kegiatan') ?>"
                class="btn btn-primary"
            >
                Lihat Kegiatan
            </a>

            <a
                href="<?= base_url('/pengurus') ?>"
                class="btn btn-secondary"
            >
                Kenali Pengurus
            </a>
        </div>
    </div>

    <aside class="program-detail-number">
        <span>Pilar</span>
        <strong><?= esc($program['number']) ?></strong>
        <small>GARDA 01</small>
    </aside>
</section>

<section class="public-content-section">
    <div class="program-detail-grid">

        <article class="public-content-card">
            <span class="public-kicker">Fokus Program</span>

            <h2>
                Ruang lingkup gerakan
            </h2>

            <div class="program-focus-grid">
                <?php foreach ($program['focus'] as $index => $focus) : ?>
                    <div>
                        <span>
                            <?= str_pad(
                                (string) ($index + 1),
                                2,
                                '0',
                                STR_PAD_LEFT
                            ) ?>
                        </span>

                        <strong><?= esc($focus) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <aside class="program-campaign-card">
            <span class="public-kicker">
                Program dan Kampanye
            </span>

            <h2>
                Contoh pelaksanaan
            </h2>

            <ul>
                <?php foreach ($program['campaigns'] as $campaign) : ?>
                    <li><?= esc($campaign) ?></li>
                <?php endforeach; ?>
            </ul>

            <a href="<?= base_url('/kegiatan') ?>">
                Lihat dokumentasi kegiatan →
            </a>
        </aside>

    </div>
</section>

<section class="public-page-cta">
    <div>
        <span class="public-kicker">Kolaborasi</span>

        <h2>
            Bergerak bersama untuk RW 01
        </h2>

        <p>
            GARDA 01 terbuka untuk kolaborasi bersama warga,
            komunitas, lembaga, dan mitra sosial.
        </p>
    </div>

    <a href="<?= base_url('/profil') ?>" class="btn btn-primary">
        Tentang GARDA 01
    </a>
</section>

<?= $this->endSection() ?>