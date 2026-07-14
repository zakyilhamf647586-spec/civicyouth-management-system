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
            <a href="<?= base_url('/kegiatan') ?>">Kegiatan</a>
            <a href="<?= base_url('/login') ?>" class="public-login-btn">Masuk Sistem</a>
        </nav>
    </header>

    <section class="public-page-hero">
        <span class="public-kicker">Struktur Organisasi</span>
        <h1>Pengurus Karang Taruna RW 01</h1>
        <p>
            Susunan pengurus Karang Taruna RW 01 Kelurahan Randugarut sebagai bagian
            dari upaya membangun organisasi pemuda yang aktif, tertib, dan terarah.
        </p>
    </section>

    <section class="public-section">
        <div class="org-chart-wrapper">
            <div class="org-chart-title">
                <span class="public-kicker">Diagram Struktur</span>
                <h2>Struktur Pengurus</h2>
                <p>Diagram ringkas kepengurusan berdasarkan data yang tercatat di sistem.</p>
            </div>

            <?php if (!empty($officials)) : ?>
                <?php
                    $ketua = null;
                    $wakil = [];
                    $lainnya = [];

                    foreach ($officials as $official) {
                        $position = strtolower($official['position'] ?? '');

                        if (str_contains($position, 'ketua') && !str_contains($position, 'wakil') && $ketua === null) {
                            $ketua = $official;
                        } elseif (str_contains($position, 'wakil')) {
                            $wakil[] = $official;
                        } else {
                            $lainnya[] = $official;
                        }
                    }
                ?>

                <div class="org-chart">
                    <?php if ($ketua) : ?>
                        <div class="org-level org-level-main">
                            <div class="org-node org-node-primary">
                                <span><?= esc($ketua['position'] ?? '-') ?></span>
                                <strong><?= esc($ketua['name'] ?? $ketua['member_name'] ?? '-') ?></strong>
                                <small><?= esc($ketua['division'] ?? '-') ?></small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($wakil)) : ?>
                        <div class="org-connector"></div>

                        <div class="org-level">
                            <?php foreach ($wakil as $official) : ?>
                                <div class="org-node">
                                    <span><?= esc($official['position'] ?? '-') ?></span>
                                    <strong><?= esc($official['name'] ?? $official['member_name'] ?? '-') ?></strong>
                                    <small><?= esc($official['division'] ?? '-') ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($lainnya)) : ?>
                        <div class="org-connector"></div>

                        <div class="org-level org-level-grid">
                            <?php foreach ($lainnya as $official) : ?>
                                <div class="org-node">
                                    <span><?= esc($official['position'] ?? '-') ?></span>
                                    <strong><?= esc($official['name'] ?? $official['member_name'] ?? '-') ?></strong>
                                    <small><?= esc($official['division'] ?? '-') ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="public-empty">
                    Data pengurus belum tersedia.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="public-section">
        <div class="public-section-header">
            <span class="public-kicker">Profil Pengurus</span>
            <h2>Pengurus dan bidang kerja</h2>
            <p>
                Daftar pengurus yang berperan dalam menjalankan kegiatan, administrasi,
                keuangan, dokumentasi, dan koordinasi organisasi.
            </p>
        </div>

        <?php if (!empty($officials)) : ?>
            <div class="officials-carousel-wrapper">
                <button type="button" class="carousel-btn carousel-prev" onclick="scrollOfficials(-1)">‹</button>

                <div class="officials-carousel" id="officialsCarousel">
                    <?php foreach ($officials as $official) : ?>
                        <?php
                            $officialName = $official['name'] ?? $official['member_name'] ?? '-';
                            $initial = strtoupper(mb_substr($officialName, 0, 1));
                        ?>

                        <article class="official-card">
                            <div class="official-avatar">
                                <?= esc($initial) ?>
                            </div>

                            <div class="official-card-body">
                                <span><?= esc($official['position'] ?? '-') ?></span>
                                <h3><?= esc($officialName) ?></h3>
                                <p><?= esc($official['division'] ?? 'Karang Taruna RW 01') ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="carousel-btn carousel-next" onclick="scrollOfficials(1)">›</button>
            </div>
        <?php else : ?>
            <div class="public-empty">
                Belum ada profil pengurus yang ditampilkan.
            </div>
        <?php endif; ?>
    </section>

    <section class="public-cta">
        <div>
            <span class="public-kicker">Sistem Internal</span>
            <h2>Kelola struktur pengurus dari dashboard</h2>
            <p>
                Data struktur pengurus ini terhubung dengan sistem internal sehingga dapat
                diperbarui melalui dashboard admin.
            </p>
        </div>

        <a href="<?= base_url('/login') ?>" class="btn btn-primary">Masuk Sistem</a>
    </section>

    <footer class="public-footer">
        <span>© <?= date('Y') ?> Karang Taruna RW 01 Kelurahan Randugarut</span>
        <strong>@kartar.rw01.randugarut</strong>
    </footer>
</div>

<script>
    function scrollOfficials(direction) {
        const carousel = document.getElementById('officialsCarousel');

        if (!carousel) {
            return;
        }

        const scrollAmount = 320;
        carousel.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
</script>

</body>
</html>