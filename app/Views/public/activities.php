<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="public-site">
    <header class="public-navbar">
        <div class="public-brand">
            <img src="<?= base_url('assets/img/logo-rw01.png') ?>" alt="Logo Karang Taruna RW 01">
            <div>
                <strong>Karang Taruna RW 01</strong>
                <span>Kelurahan Randugarut</span>
            </div>
        </div>

        <nav class="public-nav-links">
            <a href="<?= base_url('/') ?>">Beranda</a>
            <a href="<?= base_url('/#profil') ?>">Profil</a>
            <a href="<?= base_url('/#program') ?>">Program</a>
            <a href="<?= base_url('/login') ?>" class="public-login-btn">Masuk Sistem</a>
        </nav>
    </header>

    <section class="public-page-hero">
        <span class="public-kicker">Dokumentasi Organisasi</span>
        <h1>Kegiatan Karang Taruna RW 01</h1>
        <p>
            Kumpulan kegiatan, agenda, dan dokumentasi aktivitas Karang Taruna RW 01
            Kelurahan Randugarut.
        </p>
    </section>

    <section class="public-section">
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($activities)) : ?>
            <div class="public-activity-grid">
                <?php foreach ($activities as $activity) : ?>
                    <article class="public-activity-card">
                        <?php if (!empty($activity['documentation_file'])) : ?>
                            <img src="<?= base_url('uploads/activities/' . $activity['documentation_file']) ?>" alt="<?= esc($activity['title']) ?>">
                        <?php else : ?>
                            <div class="public-activity-placeholder">
                                Karang Taruna RW 01
                            </div>
                        <?php endif; ?>

                        <div>
                            <span><?= date('d M Y', strtotime($activity['activity_date'])) ?></span>
                            <h3><?= esc($activity['title']) ?></h3>
                            <p><?= esc($activity['location'] ?? 'Randugarut RW 01') ?></p>

                            <a href="<?= base_url('/kegiatan/' . $activity['id']) ?>" class="public-read-more">
                                Lihat Detail
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="pagination-wrapper public-pagination">
                <?= $pager->links('public_activities', 'default_full') ?>
            </div>
        <?php else : ?>
            <div class="public-empty">
                Belum ada kegiatan yang ditampilkan.
            </div>
        <?php endif; ?>
    </section>

    <footer class="public-footer">
        <span>© <?= date('Y') ?> Karang Taruna RW 01 Kelurahan Randugarut</span>
        <strong>@kartar.rw01.randugarut</strong>
    </footer>
</div>

</body>
</html>