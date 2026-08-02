<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$canCreateAttendances = auth_can('attendances.create');
$canUpdateAttendances = auth_can('attendances.update');
$canDeleteAttendances = auth_can('attendances.delete');
$hasAttendanceRowActions = $canUpdateAttendances || $canDeleteAttendances;
?>

<div class="page-header">
    <div>
        <h2>Absensi Rapat</h2>
        <p>Kelola data kehadiran anggota dalam setiap agenda rapat.</p>
    </div>

    <?php if ($canCreateAttendances) : ?>
        <a href="<?= base_url('/attendances/create') ?>" class="btn btn-primary">+ Tambah Absensi</a>
    <?php endif; ?>
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
                <?php if ($hasAttendanceRowActions) : ?>
                    <th width="170">Aksi</th>
                <?php endif; ?>
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
                        <?php if ($hasAttendanceRowActions) : ?>
                            <td>
                                <?php if ($canUpdateAttendances) : ?>
                                    <a href="<?= base_url('/attendances/edit/' . $attendance['id']) ?>" class="btn btn-warning">Edit</a>
                                <?php endif; ?>

                                <?php if ($canDeleteAttendances) : ?>
                                    <form
                                        action="<?= base_url('/attendances/delete/' . $attendance['id']) ?>"
                                        method="post"
                                        class="inline-action-form"
                                        onsubmit="return confirm('Yakin ingin menghapus data absensi ini?')"
                                    >
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?= $hasAttendanceRowActions ? 8 : 7 ?>" class="empty">Belum ada data absensi rapat.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>