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
            <a href="#profil">Profil</a>
            <a href="#program">Program</a>
            <a href="<?= base_url('/pengurus') ?>">Pengurus</a>
            <a href="<?= base_url('/kegiatan') ?>">Kegiatan</a>
            <a href="<?= base_url('/login') ?>" class="public-login-btn">Masuk Sistem</a>
        </nav>
    </header>

    <section class="public-hero">
        <div class="public-hero-content">
            <span class="public-kicker">Organisasi Pemuda RW 01</span>
            <h1>Karang Taruna RW 01 Randugarut</h1>
            <p>
                Wadah pemuda untuk bergerak bersama dalam kegiatan sosial, kepemudaan,
                olahraga, kreativitas, dan pengembangan lingkungan RW 01 Kelurahan Randugarut.
            </p>

            <div class="public-hero-actions">
                <a href="<?= base_url('/kegiatan') ?>" class="btn btn-primary">Lihat Kegiatan</a>
                <a href="<?= base_url('/login') ?>" class="btn btn-secondary">Masuk Dashboard</a>
            </div>
        </div>

        <div class="public-hero-logo">
            <img src="<?= base_url('assets/img/logo-rw01.png') ?>" alt="Logo Karang Taruna RW 01">
            <strong>RW 01 Randugarut</strong>
            <span>Aktif · Solid · Terdokumentasi</span>
        </div>
    </section>

    <section class="public-stats">
        <div>
            <strong><?= esc($active_members) ?></strong>
            <span>Anggota Aktif</span>
        </div>

        <div>
            <strong><?= esc($total_activities) ?></strong>
            <span>Kegiatan Tercatat</span>
        </div>

        <div>
            <strong><?= esc($total_meetings) ?></strong>
            <span>Agenda Rapat</span>
        </div>
    </section>

    <section class="public-section" id="profil">
        <div class="public-section-header">
            <span class="public-kicker">Profil Organisasi</span>
            <h2>Tumbuh bersama pemuda dan warga RW 01</h2>
            <p>
                Karang Taruna RW 01 hadir sebagai ruang kontribusi pemuda dalam membangun
                kepedulian sosial, kedisiplinan organisasi, dan dokumentasi kegiatan yang lebih tertata.
            </p>
        </div>

        <div class="public-feature-grid">
            <div class="public-feature-card">
                <h3>Sosial & Kepemudaan</h3>
                <p>Mendorong keterlibatan pemuda dalam kegiatan sosial dan kemasyarakatan.</p>
            </div>

            <div class="public-feature-card">
                <h3>Administrasi Tertib</h3>
                <p>Mengelola data anggota, rapat, absensi, kas, dan kegiatan secara lebih rapi.</p>
            </div>

            <div class="public-feature-card">
                <h3>Media & Dokumentasi</h3>
                <p>Mendukung dokumentasi kegiatan dan publikasi media sosial organisasi.</p>
            </div>
        </div>
    </section>

    <section class="public-section" id="program">
        <div class="public-section-header">
            <span class="public-kicker">Fokus Program</span>
            <h2>Ruang gerak pemuda RW 01</h2>
        </div>

        <div class="public-program-grid">
            <div>Penguatan organisasi</div>
            <div>Kegiatan sosial lingkungan</div>
            <div>Olahraga dan kepemudaan</div>
            <div>Dokumentasi dan publikasi</div>
            <div>Kolaborasi warga</div>
            <div>Pengembangan kreativitas</div>
        </div>
    </section>

    <section class="public-section" id="kegiatan">
        <div class="public-section-header">
            <span class="public-kicker">Kegiatan Terbaru</span>
            <h2>Dokumentasi aktivitas organisasi</h2>
        </div>

        <?php if (!empty($latest_activities)) : ?>
            <div class="public-activity-grid">
                <?php foreach ($latest_activities as $activity) : ?>
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
        <?php else : ?>
            <div class="public-empty">
                Belum ada kegiatan yang ditampilkan.
            </div>
        <?php endif; ?>
    </section>

    <section class="public-cta">
        <div>
            <span class="public-kicker">Sistem Internal</span>
            <h2>CivicYouth Management System</h2>
            <p>
                Sistem internal untuk mendukung pengelolaan organisasi Karang Taruna RW 01
                secara lebih tertib, modern, dan terdokumentasi.
            </p>
        </div>

        <a href="<?= base_url('/login') ?>" class="btn btn-primary">Masuk Sistem</a>
    </section>

    <footer class="public-footer">
        <span>© <?= date('Y') ?> Karang Taruna RW 01 Kelurahan Randugarut</span>
        <strong>@kartar.rw01.randugarut</strong>
    </footer>
</div>

</body>
</html>