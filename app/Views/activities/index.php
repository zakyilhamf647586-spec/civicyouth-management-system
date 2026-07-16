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

<div class="filter-card">
    <form action="<?= base_url('/activities') ?>" method="get">
        <div class="filter-grid activity-filter-grid">
            <div class="form-group">
                <label>Cari Kegiatan</label>
                <input
                    type="text"
                    name="keyword"
                    value="<?= esc($keyword ?? '') ?>"
                    placeholder="Cari nama, lokasi, deskripsi, atau hasil kegiatan"
                >
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="planned" <?= ($status ?? '') === 'planned' ? 'selected' : '' ?>>Direncanakan</option>
                    <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Dari Tanggal</label>
                <input type="date" name="date_from" value="<?= esc($date_from ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?= esc($date_to ?? '') ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="<?= base_url('/activities') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

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
                <?php
                    $currentPage = $pager->getCurrentPage('activities');
                    $perPage     = $pager->getPerPage('activities');
                    $no          = 1 + ($perPage * ($currentPage - 1));
                ?>

                <?php foreach ($activities as $activity) : ?>
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
                            <?php if (!empty($activity['documentation_file'])) : ?>
                                <a href="<?= base_url('uploads/activities/' . $activity['documentation_file']) ?>" target="_blank">
                                    <img
                                        src="<?= base_url('uploads/activities/' . $activity['documentation_file']) ?>"
                                        alt="Dokumentasi"
                                        class="activity-thumbnail"
                                    >
                                </a>
                            <?php elseif (!empty($activity['documentation_link'])) : ?>
                                <a href="<?= esc($activity['documentation_link']) ?>" target="_blank" class="btn btn-primary">Lihat Link</a>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('/activities/gallery/' . $activity['id']) ?>" class="btn btn-primary">Galeri</a>
                            <a href="<?= base_url('/activities/edit/' . $activity['id']) ?>" class="btn btn-warning">Edit</a>
                            <form
                                action="<?= base_url('/activities/delete/' . $activity['id']) ?>"
                                method="post"
                                class="inline-action-form"
                                onsubmit="return confirm('Yakin ingin menghapus data kegiatan ini?')"
                            >
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="empty">Data kegiatan tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->only(['keyword', 'status', 'date_from', 'date_to'])->links('activities', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>