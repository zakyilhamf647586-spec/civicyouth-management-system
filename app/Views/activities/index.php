<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Kegiatan</h2>
        <p>Kelola agenda kegiatan, dokumentasi, hasil, dan status kegiatan Karang Taruna RW 01.</p>
    </div>

    <a href="<?= base_url('/activities/create') ?>" class="btn btn-primary">+ Tambah Kegiatan</a>
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
                <th>Nama Kegiatan</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th>Dokumentasi</th>
                <th width="170">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($activities)) : ?>
                <?php $no = 1; foreach ($activities as $activity) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= esc($activity['title']) ?></strong><br>
                            <small>
                                <?= esc($activity['description'] ? substr($activity['description'], 0, 90) . '...' : '-') ?>
                            </small>
                        </td>
                        <td><?= date('d M Y', strtotime($activity['activity_date'])) ?></td>
                        <td><?= esc($activity['location'] ?? '-') ?></td>
                        <td>
                            <?php if ($activity['status'] === 'planned') : ?>
                                <span class="badge badge-warning">Direncanakan</span>
                            <?php elseif ($activity['status'] === 'completed') : ?>
                                <span class="badge badge-success">Selesai</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Dibatalkan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($activity['documentation_link'])) : ?>
                                <a href="<?= esc($activity['documentation_link']) ?>" target="_blank" class="btn btn-primary">Lihat</a>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('/activities/edit/' . $activity['id']) ?>" class="btn btn-warning">Edit</a>
                            <a href="<?= base_url('/activities/delete/' . $activity['id']) ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Yakin ingin menghapus data kegiatan ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="empty">Belum ada data kegiatan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>