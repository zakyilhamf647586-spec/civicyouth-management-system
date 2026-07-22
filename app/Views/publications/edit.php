<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page publication-simple-editor">

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">
            Konten Instagram
        </span>

        <h2>
            Edit <?= esc(
                $post['content_code'] ?? 'Publikasi'
            ) ?>
        </h2>

        <p>
            Perbarui naskah, Canva, PIC, atau target tayang tanpa
            harus membuka seluruh fitur lanjutan.
        </p>
    </div>

    <div class="publication-header-actions">
        <a
            href="<?= base_url('/publications/guide') ?>"
            class="btn btn-secondary"
        >
            Lihat Panduan
        </a>

        <a
            href="<?= base_url(
                '/publications/' . $post['id']
            ) ?>"
            class="btn btn-secondary"
        >
            Kembali
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<?= $this->include('publications/_form') ?>

</div>

<?= $this->endSection() ?>
