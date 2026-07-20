<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page">

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">Media Operations</span>
        <h2>Buat Rencana Publikasi</h2>
        <p>Mulai dari brief, pilih master Canva, lalu kelola sampai konten tayang.</p>
    </div>

    <a href="<?= base_url('/publications') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?= $this->include('publications/_form') ?>

</div>

<?= $this->endSection() ?>
