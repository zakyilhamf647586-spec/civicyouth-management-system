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

<?php if (!empty($autoBrief)) : ?>
    <section class="publication-auto-brief-banner">
        <div class="publication-auto-brief-banner__icon">
            ✦
        </div>

        <div>
            <span>Brief Otomatis Aktif</span>

            <h3>
                Data awal diambil dari kegiatan:
                <?= esc(
                    $autoBrief['activity']['title']
                    ?? 'Kegiatan GARDA 01'
                ) ?>
            </h3>

            <p>
                Sistem sudah mengisi identitas kegiatan, kategori,
                format, master Canva, tujuan konten, caption awal,
                hashtag, dan alt text. Tetap lakukan pemeriksaan
                editorial sebelum menyimpan.
            </p>

            <?php if (
                (int) (
                    $autoBrief['existing_count'] ?? 0
                ) > 0
            ) : ?>
                <small>
                    Perhatian: kegiatan ini sudah memiliki
                    <?= (int) $autoBrief['existing_count'] ?>
                    record publikasi. Brief baru tetap dapat dibuat
                    untuk format atau sudut komunikasi yang berbeda.
                </small>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?= $this->include('publications/_form') ?>

</div>

<?= $this->endSection() ?>
