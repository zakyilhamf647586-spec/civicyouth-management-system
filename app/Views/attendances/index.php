<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Absensi Rapat</h2>
        <p>Kelola data kehadiran anggota dalam setiap agenda rapat.</p>
    </div>

    <a href="<?= base_url('/attendances/create') ?>" class="btn btn-primary">+ Tambah Absensi</a>
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

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Rapat</th>
                <th>Tanggal</th>
                <th>Nama Anggota</th>
                <th>RT</th>
                <th>Status Kehadiran</th>
                <th>Catatan</th>
                <th width="170">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($attendances)) : ?>
                <?php $no = 1; foreach ($attendances as $attendance) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= esc($attendance['meeting_title']) ?></strong></td>
                        <td><?= date('d M Y', strtotime($attendance['meeting_date'])) ?></td>
                        <td><?= esc($attendance['full_name']) ?></td>
                        <td><?= esc($attendance['rt'] ?? '-') ?></td>
                        <td>
                            <?php if ($attendance['attendance_status'] === 'present') : ?>
                                <span class="badge badge-success">Hadir</span>
                            <?php elseif ($attendance['attendance_status'] === 'permission') : ?>
                                <span class="badge badge-warning">Izin</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Tidak Hadir</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($attendance['note'] ?? '-') ?></td>
                        <td>
                            <a href="<?= base_url('/attendances/edit/' . $attendance['id']) ?>" class="btn btn-warning">Edit</a>
                            <a href="<?= base_url('/attendances/delete/' . $attendance['id']) ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Yakin ingin menghapus data absensi ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" class="empty">Belum ada data absensi rapat.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>