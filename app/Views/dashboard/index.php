<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<section class="portal-hero">
    <div class="portal-hero-content">
        <span class="portal-kicker">Sistem Manajemen Organisasi Pemuda</span>
        <h1>Karang Taruna RW 01 Randugarut</h1>
        <p>
            Portal internal untuk mengelola data anggota, struktur pengurus, rapat, absensi,
            kas organisasi, kegiatan, laporan, dan konten media sosial organisasi.
        </p>

        <div class="portal-hero-actions">
            <a href="<?= base_url('/members') ?>" class="btn btn-primary">Kelola Anggota</a>
            <a href="<?= base_url('/content-studio') ?>" class="btn btn-secondary">AI Content Studio</a>
        </div>
    </div>

    <div class="portal-hero-card">
        <img src="<?= base_url('assets/img/logo-rw01.png') ?>" alt="Logo Karang Taruna RW 01">
        <strong>RW 01 Kelurahan Randugarut</strong>
        <span>Aktif · Tertib · Terdata · Terdokumentasi</span>
    </div>
</section>

<section class="quick-actions-section">
    <div class="quick-action-card">
        <span>Anggota</span>
        <strong><?= esc($active_members) ?></strong>
        <small>Anggota aktif tercatat</small>
    </div>

    <div class="quick-action-card">
        <span>Rapat</span>
        <strong><?= esc($total_meetings) ?></strong>
        <small>Agenda rapat organisasi</small>
    </div>

    <div class="quick-action-card">
        <span>Kegiatan</span>
        <strong><?= esc($total_activities) ?></strong>
        <small>Kegiatan terdokumentasi</small>
    </div>

    <div class="quick-action-card dark">
        <span>Saldo Kas</span>
        <strong>Rp<?= number_format($cash_balance, 0, ',', '.') ?></strong>
        <small>Saldo akhir organisasi</small>
    </div>
</section>

<section class="portal-grid">
    <div class="portal-panel">
        <div class="panel-header">
            <div>
                <h3>Keuangan Organisasi</h3>
                <p>Ringkasan pemasukan, pengeluaran, dan saldo kas.</p>
            </div>
            <a href="<?= base_url('/cash') ?>">Buka Kas</a>
        </div>

        <div class="finance-summary">
            <div>
                <span>Total Pemasukan</span>
                <strong>Rp<?= number_format($total_income, 0, ',', '.') ?></strong>
            </div>

            <div>
                <span>Total Pengeluaran</span>
                <strong>Rp<?= number_format($total_expense, 0, ',', '.') ?></strong>
            </div>

            <div class="finance-balance">
                <span>Saldo Kas</span>
                <strong>Rp<?= number_format($cash_balance, 0, ',', '.') ?></strong>
            </div>
        </div>
    </div>

    <div class="portal-panel">
        <div class="panel-header">
            <div>
                <h3>Anggota per RT</h3>
                <p>Distribusi anggota berdasarkan wilayah RT.</p>
            </div>
            <a href="<?= base_url('/members') ?>">Data Anggota</a>
        </div>

        <?php
            $maxRt = max($members_by_rt ?: [1]);
            if ($maxRt < 1) {
                $maxRt = 1;
            }
        ?>

        <?php foreach ($members_by_rt as $rtName => $count) : ?>
            <?php $percent = ($count / $maxRt) * 100; ?>
            <div class="portal-stat-row">
                <div>
                    <span><?= esc($rtName) ?></span>
                    <strong><?= esc($count) ?></strong>
                </div>
                <div class="portal-progress">
                    <span style="width: <?= $percent ?>%"></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="portal-grid">
    <div class="portal-panel">
        <div class="panel-header">
            <div>
                <h3>Rapat Terbaru</h3>
                <p>Agenda rapat yang terakhir tercatat.</p>
            </div>
            <a href="<?= base_url('/meetings') ?>">Lihat Semua</a>
        </div>

        <?php if (!empty($latest_meetings)) : ?>
            <div class="portal-list">
                <?php foreach ($latest_meetings as $meeting) : ?>
                    <div class="portal-list-item">
                        <div>
                            <strong><?= esc($meeting['title']) ?></strong>
                            <span><?= date('d M Y', strtotime($meeting['meeting_date'])) ?> · <?= esc($meeting['location'] ?? '-') ?></span>
                        </div>

                        <?php if ($meeting['status'] === 'scheduled') : ?>
                            <span class="badge badge-warning">Terjadwal</span>
                        <?php elseif ($meeting['status'] === 'completed') : ?>
                            <span class="badge badge-success">Selesai</span>
                        <?php else : ?>
                            <span class="badge badge-danger">Dibatalkan</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="empty-text">Belum ada data rapat.</p>
        <?php endif; ?>
    </div>

    <div class="portal-panel">
        <div class="panel-header">
            <div>
                <h3>Kegiatan Terbaru</h3>
                <p>Dokumentasi aktivitas organisasi terbaru.</p>
            </div>
            <a href="<?= base_url('/activities') ?>">Lihat Semua</a>
        </div>

        <?php if (!empty($latest_activities)) : ?>
            <div class="portal-list">
                <?php foreach ($latest_activities as $activity) : ?>
                    <div class="portal-list-item">
                        <div>
                            <strong><?= esc($activity['title']) ?></strong>
                            <span><?= date('d M Y', strtotime($activity['activity_date'])) ?> · <?= esc($activity['location'] ?? '-') ?></span>
                        </div>

                        <?php if ($activity['status'] === 'planned') : ?>
                            <span class="badge badge-warning">Direncanakan</span>
                        <?php elseif ($activity['status'] === 'completed') : ?>
                            <span class="badge badge-success">Selesai</span>
                        <?php else : ?>
                            <span class="badge badge-danger">Dibatalkan</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="empty-text">Belum ada data kegiatan.</p>
        <?php endif; ?>
    </div>
</section>

<section class="ai-highlight-section">
    <div>
        <span class="portal-kicker">Fitur Modern</span>
        <h3>AI Content Studio</h3>
        <p>
            Buat draft caption, hashtag, dan visual feed Instagram 4:5 berbasis template resmi
            Karang Taruna RW 01.
        </p>
    </div>

    <a href="<?= base_url('/content-studio') ?>" class="btn btn-primary">Buka AI Studio</a>
</section>

<?= $this->endSection() ?>