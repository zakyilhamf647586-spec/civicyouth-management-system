<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Agenda Rapat</h2>
        <p>Kelola jadwal, pembahasan, keputusan, dan catatan rapat organisasi.</p>
    </div>

    <a href="<?= base_url('/meetings/create') ?>" class="btn btn-primary">+ Tambah Rapat</a>
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
                <th>Judul Rapat</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Tempat</th>
                <th>Status</th>
                <th width="170">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($meetings)) : ?>
                <?php $no = 1; foreach ($meetings as $meeting) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= esc($meeting['title']) ?></strong><br>
                            <small>
                                <?= esc($meeting['agenda'] ? substr($meeting['agenda'], 0, 90) . '...' : '-') ?>
                            </small>
                        </td>
                        <td><?= date('d M Y', strtotime($meeting['meeting_date'])) ?></td>
                        <td>
                            <?= $meeting['start_time'] ? esc(substr($meeting['start_time'], 0, 5)) : '-' ?>
                            -
                            <?= $meeting['end_time'] ? esc(substr($meeting['end_time'], 0, 5)) : '-' ?>
                        </td>
                        <td><?= esc($meeting['location'] ?? '-') ?></td>
                        <td>
                            <?php if ($meeting['status'] === 'scheduled') : ?>
                                <span class="badge badge-warning">Terjadwal</span>
                            <?php elseif ($meeting['status'] === 'completed') : ?>
                                <span class="badge badge-success">Selesai</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Dibatalkan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('/meetings/edit/' . $meeting['id']) ?>" class="btn btn-warning">Edit</a>
                            <a href="<?= base_url('/meetings/delete/' . $meeting['id']) ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Yakin ingin menghapus agenda rapat ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="empty">Belum ada agenda rapat.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>