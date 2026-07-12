<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Dashboard</h2>
        <p>Ringkasan kondisi organisasi Karang Taruna RW 01.</p>
    </div>
</div>

<div class="dashboard-cards">
    <div class="dashboard-card">
        <span>Anggota Aktif</span>
        <h3><?= esc($active_members) ?></h3>
        <p>Total anggota yang masih aktif.</p>
    </div>

    <div class="dashboard-card">
        <span>Anggota Tidak Aktif</span>
        <h3><?= esc($inactive_members) ?></h3>
        <p>Data anggota nonaktif.</p>
    </div>

    <div class="dashboard-card">
        <span>Total Rapat</span>
        <h3><?= esc($total_meetings) ?></h3>
        <p>Seluruh agenda rapat tercatat.</p>
    </div>

    <div class="dashboard-card">
        <span>Total Kegiatan</span>
        <h3><?= esc($total_activities) ?></h3>
        <p>Seluruh kegiatan organisasi.</p>
    </div>
</div>

<br>

<div class="dashboard-cards finance-cards">
    <div class="dashboard-card">
        <span>Total Pemasukan</span>
        <h3>Rp<?= number_format($total_income, 0, ',', '.') ?></h3>
        <p>Akumulasi kas masuk.</p>
    </div>

    <div class="dashboard-card">
        <span>Total Pengeluaran</span>
        <h3>Rp<?= number_format($total_expense, 0, ',', '.') ?></h3>
        <p>Akumulasi kas keluar.</p>
    </div>

    <div class="dashboard-card dashboard-card-highlight">
        <span>Saldo Kas</span>
        <h3>Rp<?= number_format($cash_balance, 0, ',', '.') ?></h3>
        <p>Saldo akhir organisasi.</p>
    </div>
</div>

<br>

<div class="dashboard-grid">
    <div class="panel-card">
        <h3>Anggota per RT</h3>

        <?php
            $maxRt = max($members_by_rt ?: [1]);
            if ($maxRt < 1) {
                $maxRt = 1;
            }
        ?>

        <?php foreach ($members_by_rt as $rtName => $count) : ?>
            <?php $percent = ($count / $maxRt) * 100; ?>
            <div class="stat-row">
                <div class="stat-label">
                    <span><?= esc($rtName) ?></span>
                    <strong><?= esc($count) ?></strong>
                </div>
                <div class="mini-bar">
                    <div style="width: <?= $percent ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="panel-card">
        <h3>Status Kegiatan</h3>

        <?php
            $maxActivity = max($activities_status ?: [1]);
            if ($maxActivity < 1) {
                $maxActivity = 1;
            }
        ?>

        <div class="stat-row">
            <div class="stat-label">
                <span>Direncanakan</span>
                <strong><?= esc($activities_status['planned']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($activities_status['planned'] / $maxActivity) * 100 ?>%"></div>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat-label">
                <span>Selesai</span>
                <strong><?= esc($activities_status['completed']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($activities_status['completed'] / $maxActivity) * 100 ?>%"></div>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat-label">
                <span>Dibatalkan</span>
                <strong><?= esc($activities_status['cancelled']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($activities_status['cancelled'] / $maxActivity) * 100 ?>%"></div>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <h3>Status Rapat</h3>

        <?php
            $maxMeeting = max($meetings_status ?: [1]);
            if ($maxMeeting < 1) {
                $maxMeeting = 1;
            }
        ?>

        <div class="stat-row">
            <div class="stat-label">
                <span>Terjadwal</span>
                <strong><?= esc($meetings_status['scheduled']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($meetings_status['scheduled'] / $maxMeeting) * 100 ?>%"></div>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat-label">
                <span>Selesai</span>
                <strong><?= esc($meetings_status['completed']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($meetings_status['completed'] / $maxMeeting) * 100 ?>%"></div>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat-label">
                <span>Dibatalkan</span>
                <strong><?= esc($meetings_status['cancelled']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($meetings_status['cancelled'] / $maxMeeting) * 100 ?>%"></div>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <h3>Ringkasan Absensi</h3>

        <?php
            $maxAttendance = max($attendance_summary ?: [1]);
            if ($maxAttendance < 1) {
                $maxAttendance = 1;
            }
        ?>

        <div class="stat-row">
            <div class="stat-label">
                <span>Hadir</span>
                <strong><?= esc($attendance_summary['present']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($attendance_summary['present'] / $maxAttendance) * 100 ?>%"></div>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat-label">
                <span>Izin</span>
                <strong><?= esc($attendance_summary['permission']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($attendance_summary['permission'] / $maxAttendance) * 100 ?>%"></div>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat-label">
                <span>Tidak Hadir</span>
                <strong><?= esc($attendance_summary['absent']) ?></strong>
            </div>
            <div class="mini-bar">
                <div style="width: <?= ($attendance_summary['absent'] / $maxAttendance) * 100 ?>%"></div>
            </div>
        </div>
    </div>
</div>

<br>

<div class="dashboard-grid">
    <div class="panel-card">
        <div class="panel-header">
            <h3>Rapat Terbaru</h3>
            <a href="<?= base_url('/meetings') ?>">Lihat semua</a>
        </div>

        <?php if (!empty($latest_meetings)) : ?>
            <div class="simple-list">
                <?php foreach ($latest_meetings as $meeting) : ?>
                    <div class="simple-list-item">
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

    <div class="panel-card">
        <div class="panel-header">
            <h3>Kegiatan Terbaru</h3>
            <a href="<?= base_url('/activities') ?>">Lihat semua</a>
        </div>

        <?php if (!empty($latest_activities)) : ?>
            <div class="simple-list">
                <?php foreach ($latest_activities as $activity) : ?>
                    <div class="simple-list-item">
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
</div>

<?= $this->endSection() ?>