<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?> - Karang Taruna RW 01</title>
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
            <a href="<?= base_url('/kegiatan') ?>">Kegiatan</a>
            <a href="<?= base_url('/login') ?>" class="public-login-btn">Masuk Sistem</a>
        </nav>
    </header>

    <section class="public-detail-wrapper">
        <a href="<?= base_url('/kegiatan') ?>" class="public-back-link">← Kembali ke Kegiatan</a>

        <article class="public-detail-card">
            <?php if (!empty($activity['documentation_file'])) : ?>
                <img
                    src="<?= base_url('uploads/activities/' . $activity['documentation_file']) ?>"
                    alt="<?= esc($activity['title']) ?>"
                    class="public-detail-image"
                >
            <?php else : ?>
                <div class="public-detail-placeholder">
                    Karang Taruna RW 01
                </div>
            <?php endif; ?>

            <div class="public-detail-content">
                <span class="public-kicker">Detail Kegiatan</span>
                <h1><?= esc($activity['title']) ?></h1>

                <div class="public-detail-meta">
                    <div>
                        <strong>Tanggal</strong>
                        <span><?= date('d M Y', strtotime($activity['activity_date'])) ?></span>
                    </div>

                    <div>
                        <strong>Lokasi</strong>
                        <span><?= esc($activity['location'] ?? '-') ?></span>
                    </div>

                    <div>
                        <strong>Status</strong>
                        <span>
                            <?php if ($activity['status'] === 'planned') : ?>
                                Direncanakan
                            <?php elseif ($activity['status'] === 'completed') : ?>
                                Selesai
                            <?php else : ?>
                                Dibatalkan
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($activity['description'])) : ?>
                    <div class="public-detail-section">
                        <h3>Deskripsi</h3>
                        <p><?= nl2br(esc($activity['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($activity['result'])) : ?>
                    <div class="public-detail-section">
                        <h3>Hasil Kegiatan</h3>
                        <p><?= nl2br(esc($activity['result'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($activity['documentation_link'])) : ?>
                    <a href="<?= esc($activity['documentation_link']) ?>" target="_blank" class="btn btn-primary">
                        Buka Dokumentasi
                    </a>
                <?php endif; ?>
            </div>
        </article>
    </section>

    <footer class="public-footer">
        <span>© <?= date('Y') ?> Karang Taruna RW 01 Kelurahan Randugarut</span>
        <strong>@kartar.rw01.randugarut</strong>
    </footer>
</div>

</body>
</html>