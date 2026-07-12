<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Data Anggota</h2>
        <p>Kelola data anggota Karang Taruna RW 01.</p>
    </div>

    <a href="<?= base_url('/members/create') ?>" class="btn btn-primary">+ Tambah Anggota</a>
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
                <th>Nama Lengkap</th>
                <th>RT</th>
                <th>Gender</th>
                <th>Jabatan/Posisi</th>
                <th>Status</th>
                <th width="170">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($members)) : ?>
                <?php $no = 1; foreach ($members as $member) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($member['full_name']) ?></td>
                        <td><?= esc($member['rt'] ?? '-') ?></td>
                        <td>
                            <?php if ($member['gender'] === 'male') : ?>
                                Laki-laki
                            <?php elseif ($member['gender'] === 'female') : ?>
                                Perempuan
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= esc($member['position'] ?? '-') ?></td>
                        <td>
                            <?php if ($member['membership_status'] === 'active') : ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Tidak Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('/members/edit/' . $member['id']) ?>" class="btn btn-warning">Edit</a>
                            <a href="<?= base_url('/members/delete/' . $member['id']) ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Yakin ingin menghapus data ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="empty">Belum ada data anggota.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>