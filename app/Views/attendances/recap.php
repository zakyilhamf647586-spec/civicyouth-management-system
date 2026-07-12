<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Rekap Absensi Rapat</h2>
        <p>Ringkasan kehadiran anggota pada agenda rapat tertentu.</p>
    </div>

    <div>
        <a href="<?= base_url('/attendances/bulk/' . $meeting['id']) ?>" class="btn btn-primary">
            Input Absensi Massal
        </a>

        <a href="<?= base_url('/attendances/recap/' . $meeting['id'] . '/print') ?>" class="btn btn-secondary" target="_blank">
            Cetak / Save PDF
        </a>

        <a href="<?= base_url('/meetings') ?>" class="btn btn-secondary">
            Kembali ke Rapat
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="section">
    <h3><?= esc($meeting['title']) ?></h3>
    <p>
        <strong>Tanggal:</strong> <?= date('d M Y', strtotime($meeting['meeting_date'])) ?><br>
        <strong>Waktu:</strong>
        <?= $meeting['start_time'] ? esc(substr($meeting['start_time'], 0, 5)) : '-' ?>
        -
        <?= $meeting['end_time'] ? esc(substr($meeting['end_time'], 0, 5)) : '-' ?><br>
        <strong>Tempat:</strong> <?= esc($meeting['location'] ?? '-') ?><br>
        <strong>Status Rapat:</strong>
        <?php if ($meeting['status'] === 'scheduled') : ?>
            <span class="badge badge-warning">Terjadwal</span>
        <?php elseif ($meeting['status'] === 'completed') : ?>
            <span class="badge badge-success">Selesai</span>
        <?php else : ?>
            <span class="badge badge-danger">Dibatalkan</span>
        <?php endif; ?>
    </p>
</div>

<br>

<div class="cards recap-cards">
    <div class="card">
        <span>Total Anggota Aktif</span>
        <h3><?= esc($summary['total_members']) ?></h3>
    </div>

    <div class="card">
        <span>Hadir</span>
        <h3><?= esc($summary['present']) ?></h3>
    </div>

    <div class="card">
        <span>Izin</span>
        <h3><?= esc($summary['permission']) ?></h3>
    </div>

    <div class="card">
        <span>Tidak Hadir</span>
        <h3><?= esc($summary['absent']) ?></h3>
    </div>

    <div class="card">
        <span>Belum Dicatat</span>
        <h3><?= esc($summary['not_recorded']) ?></h3>
    </div>
</div>

<br>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Anggota</th>
                <th>RT</th>
                <th>Jabatan/Posisi</th>
                <th>Status Absensi</th>
                <th>Catatan</th>
                <th width="180">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($members)) : ?>
                <?php $no = 1; foreach ($members as $member) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= esc($member['full_name']) ?></strong></td>
                        <td><?= esc($member['rt'] ?? '-') ?></td>
                        <td><?= esc($member['position'] ?? '-') ?></td>
                        <td>
                            <?php if ($member['attendance_status'] === 'present') : ?>
                                <span class="badge badge-success">Hadir</span>
                            <?php elseif ($member['attendance_status'] === 'permission') : ?>
                                <span class="badge badge-warning">Izin</span>
                            <?php elseif ($member['attendance_status'] === 'absent') : ?>
                                <span class="badge badge-danger">Tidak Hadir</span>
                            <?php else : ?>
                                <span class="badge badge-secondary">Belum Dicatat</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($member['note'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($member['attendance_id'])) : ?>
                                <a href="<?= base_url('/attendances/edit/' . $member['attendance_id']) ?>" class="btn btn-warning">Edit</a>
                            <?php else : ?>
                                <a href="<?= base_url('/attendances/create?meeting_id=' . $meeting['id'] . '&member_id=' . $member['member_id']) ?>" class="btn btn-primary">Catat</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="empty">Belum ada anggota aktif.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>