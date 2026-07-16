<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Program GARDA 01</h2>
        <p>Kelola pilar, urutan, dan status publikasi program.</p>
    </div>

    <a href="<?= base_url('/programs/create') ?>" class="btn btn-primary">
        + Tambah Program
    </a>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="filter-card">
    <form action="<?= base_url('/programs') ?>" method="get">
        <div class="program-filter-grid">
            <div class="form-group">
                <label>Cari Program</label>

                <input
                    type="text"
                    name="keyword"
                    value="<?= esc($keyword ?? '') ?>"
                    placeholder="Nama, kategori, atau tagline"
                >
            </div>

            <div class="form-group">
                <label>Status</label>

                <select name="status">
                    <option value="">Semua Status</option>

                    <option
                        value="published"
                        <?= ($status ?? '') === 'published'
                            ? 'selected'
                            : '' ?>
                    >
                        Dipublikasikan
                    </option>

                    <option
                        value="draft"
                        <?= ($status ?? '') === 'draft'
                            ? 'selected'
                            : '' ?>
                    >
                        Draft
                    </option>

                    <option
                        value="archived"
                        <?= ($status ?? '') === 'archived'
                            ? 'selected'
                            : '' ?>
                    >
                        Diarsipkan
                    </option>
                </select>
            </div>

            <div class="program-filter-actions">
                <button type="submit" class="btn btn-primary">
                    Terapkan
                </button>

                <a href="<?= base_url('/programs') ?>" class="btn btn-secondary">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Urutan</th>
                    <th>Program</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Diperbarui</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($programs)) : ?>
                    <?php foreach ($programs as $program) : ?>
                        <tr>
                            <td>
                                <span class="program-order-badge">
                                    <?= str_pad(
                                        (string) $program['display_order'],
                                        2,
                                        '0',
                                        STR_PAD_LEFT
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <div class="program-admin-title">
                                    <?php if (!empty($program['cover_image'])) : ?>
                                        <img
                                            src="<?= base_url(
                                                'uploads/programs/'
                                                . $program['cover_image']
                                            ) ?>"
                                            alt=""
                                        >
                                    <?php else : ?>
                                        <div class="program-admin-placeholder">
                                            G01
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <strong>
                                            <?= esc($program['name']) ?>
                                        </strong>

                                        <span>
                                            /program/<?= esc($program['slug']) ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <?= esc($program['label'] ?: '-') ?>
                            </td>

                            <td>
                                <?php if ($program['status'] === 'published') : ?>
                                    <span class="status-badge status-published">
                                        Dipublikasikan
                                    </span>
                                <?php elseif ($program['status'] === 'draft') : ?>
                                    <span class="status-badge status-draft">
                                        Draft
                                    </span>
                                <?php else : ?>
                                    <span class="status-badge status-archived">
                                        Diarsipkan
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= !empty($program['updated_at'])
                                    ? date(
                                        'd M Y',
                                        strtotime($program['updated_at'])
                                    )
                                    : '-' ?>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="<?= base_url(
                                            '/programs/edit/' . $program['id']
                                        ) ?>"
                                        class="btn btn-warning"
                                    >
                                        Edit
                                    </a>

                                    <?php if ($program['status'] !== 'published') : ?>
                                        <form
                                            action="<?= base_url(
                                                '/programs/publish/'
                                                . $program['id']
                                            ) ?>"
                                            method="post"
                                        >
                                            <?= csrf_field() ?>

                                            <button
                                                type="submit"
                                                class="btn btn-success"
                                            >
                                                Publikasikan
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($program['status'] !== 'archived') : ?>
                                        <form
                                            action="<?= base_url(
                                                '/programs/archive/'
                                                . $program['id']
                                            ) ?>"
                                            method="post"
                                            onsubmit="return confirm(
                                                'Arsipkan program ini?'
                                            )"
                                        >
                                            <?= csrf_field() ?>

                                            <button
                                                type="submit"
                                                class="btn btn-danger"
                                            >
                                                Arsipkan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="empty-text">
                            Data program tidak ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>