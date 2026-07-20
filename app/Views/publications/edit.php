<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page">

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">Media Operations</span>
        <h2>Edit <?= esc($post['content_code'] ?? 'Publikasi') ?></h2>
        <p>Perbarui brief, tautan Canva, penanggung jawab, dan jadwal penayangan.</p>
    </div>

    <a href="<?= base_url('/publications/' . $post['id']) ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?= $this->include('publications/_form') ?>

</div>

<?= $this->endSection() ?>
