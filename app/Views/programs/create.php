<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Tambah Program GARDA 01</h2>
        <p>Buat pilar atau program publik baru.</p>
    </div>

    <a href="<?= base_url('/programs') ?>" class="btn btn-secondary">
        Kembali
    </a>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form
        action="<?= base_url('/programs/store') ?>"
        method="post"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>

        <?= view('programs/_form') ?>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                Simpan Program
            </button>

            <a href="<?= base_url('/programs') ?>" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>