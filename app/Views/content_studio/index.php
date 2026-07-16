<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>AI Content Studio</h2>
        <p>Buat draft caption, hashtag, dan konten media sosial berdasarkan gambar kegiatan.</p>
    </div>

    <a href="<?= base_url('/content-studio/create') ?>" class="btn btn-primary">+ Buat Konten</a>
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
                <th>Judul</th>
                <th>Kategori</th>
                <th>Template</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th width="220">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($posts)) : ?>
                <?php
                    $currentPage = $pager->getCurrentPage('content_posts');
                    $perPage     = $pager->getPerPage('content_posts');
                    $no          = 1 + ($perPage * ($currentPage - 1));
                ?>

                <?php foreach ($posts as $post) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <strong><?= esc($post['title'] ?? 'Draft Belum Digenerate') ?></strong><br>
                            <small><?= esc(substr($post['notes'] ?? '-', 0, 80)) ?></small>
                        </td>
                        <td><?= esc($post['category']) ?></td>
                        <td><?= esc($post['template_type']) ?></td>
                        <td>
                            <?php if ($post['status'] === 'generated') : ?>
                                <span class="badge badge-success">Generated</span>
                            <?php elseif ($post['status'] === 'edited') : ?>
                                <span class="badge badge-warning">Edited</span>
                            <?php else : ?>
                                <span class="badge badge-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($post['created_at'] ?? '-') ?></td>
                        <td>
                            <a href="<?= base_url('/content-studio/show/' . $post['id']) ?>" class="btn btn-primary">Buka</a>
                            <form
                                action="<?= base_url('/content-studio/delete/' . $post['id']) ?>"
                                method="post"
                                class="inline-action-form"
                                onsubmit="return confirm('Yakin ingin menghapus konten ini?')"
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
                    <td colspan="7" class="empty">Belum ada draft konten.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->links('content_posts', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>