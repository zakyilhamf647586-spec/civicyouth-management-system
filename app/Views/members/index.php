<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Data Anggota</h2>
        <p>Kelola data anggota Karang Taruna RW 01.</p>
    </div>

    <div>
        <a href="<?= base_url('/imports/members') ?>" class="btn btn-secondary">Import Excel</a>
        <a href="<?= base_url('/exports/members') ?>" class="btn btn-secondary">Export Excel</a>
        <a href="<?= base_url('/members/create') ?>" class="btn btn-primary">+ Tambah Anggota</a>
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

<div class="filter-card">
    <form action="<?= base_url('/members') ?>" method="get">
        <div class="filter-grid">
            <div class="form-group">
                <label>Cari Anggota</label>
                <input
                    type="text"
                    name="keyword"
                    value="<?= esc($keyword ?? '') ?>"
                    placeholder="Cari nama, no. HP, atau jabatan"
                >
            </div>

            <div class="form-group">
                <label>Filter RT</label>
                <select name="rt">
                    <option value="">Semua RT</option>
                    <option value="RT 01" <?= ($rt ?? '') === 'RT 01' ? 'selected' : '' ?>>RT 01</option>
                    <option value="RT 02" <?= ($rt ?? '') === 'RT 02' ? 'selected' : '' ?>>RT 02</option>
                    <option value="RT 03" <?= ($rt ?? '') === 'RT 03' ? 'selected' : '' ?>>RT 03</option>
                    <option value="RT 04" <?= ($rt ?? '') === 'RT 04' ? 'selected' : '' ?>>RT 04</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="<?= base_url('/members') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

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
                <?php
                    $currentPage = $pager->getCurrentPage('members');
                    $perPage     = $pager->getPerPage('members');
                    $no          = 1 + ($perPage * ($currentPage - 1));
                ?>

                <?php foreach ($members as $member) : ?>
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
                    <td colspan="7" class="empty">Data anggota tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->only(['keyword', 'rt', 'status'])->links('members', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>