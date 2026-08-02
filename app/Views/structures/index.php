<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$canCreateStructures = auth_can('structures.create');
$canUpdateStructures = auth_can('structures.update');
$canDeleteStructures = auth_can('structures.delete');
$hasStructureRowActions = $canUpdateStructures || $canDeleteStructures;
?>

<div class="page-header">
    <div>
        <h2>Struktur Pengurus</h2>
        <p>Kelola susunan kepengurusan Karang Taruna RW 01.</p>
    </div>

    <?php if ($canCreateStructures) : ?>
        <a href="<?= base_url('/structures/create') ?>" class="btn btn-primary">+ Tambah Struktur</a>
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
                <th>Foto</th>
                <th>Jabatan</th>
                <th>Nama Pengurus</th>
                <th>Bidang/Seksi</th>
                <th>RT</th>
                <th>Periode</th>
                <th>Status</th>
                <?php if ($hasStructureRowActions) : ?>
                    <th width="170">Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($structures)) : ?>
                <?php $no = 1; foreach ($structures as $structure) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if (!empty($structure['photo'])) : ?>
                                <img
                                    src="<?= base_url('uploads/officials/' . $structure['photo']) ?>"
                                    alt="Foto Pengurus"
                                    class="official-thumb"
                                >
                            <?php else : ?>
                                <div class="official-thumb-placeholder">
                                    <?= esc(strtoupper(mb_substr($structure['name'] ?? '-', 0, 1))) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= esc($structure['position_name']) ?></strong></td>
                        <td><?= esc($structure['full_name'] ?? 'Belum ditentukan') ?></td>
                        <td><?= esc($structure['division'] ?? '-') ?></td>
                        <td><?= esc($structure['rt_scope'] ?? '-') ?></td>
                        <td><?= esc($structure['period'] ?? '-') ?></td>
                        <td>
                            <?php if ($structure['status'] === 'active') : ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Tidak Aktif</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($hasStructureRowActions) : ?>
                            <td>
                                <?php if ($canUpdateStructures) : ?>
                                    <a href="<?= base_url('/structures/edit/' . $structure['id']) ?>" class="btn btn-warning">Edit</a>
                                <?php endif; ?>

                                <?php if ($canDeleteStructures) : ?>
                                    <form
                                        action="<?= base_url('/structures/delete/' . $structure['id']) ?>"
                                        method="post"
                                        class="inline-action-form"
                                        onsubmit="return confirm('Yakin ingin menghapus data struktur ini?')"
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
                    <td colspan="<?= $hasStructureRowActions ? 9 : 8 ?>" class="empty">Belum ada data struktur pengurus.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>