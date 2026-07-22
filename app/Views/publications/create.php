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
            <?= !empty($autoBrief)
                ? 'Buat Konten dari Kegiatan'
                : 'Buat Konten Baru' ?>
        </h2>

        <p>
            Isi bagian utama terlebih dahulu. Bagian bertanda
            opsional dapat dibuka hanya ketika diperlukan.
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
            href="<?= base_url('/publications') ?>"
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

<?php if (!empty($autoBrief)) : ?>
    <section class="publication-auto-brief-banner">
        <div class="publication-auto-brief-banner__icon">
            ✦
        </div>

        <div>
            <span>Brief Otomatis Aktif</span>

            <h3>
                Data awal diambil dari:
                <?= esc(
                    $autoBrief['activity']['title']
                    ?? 'Kegiatan GARDA 01'
                ) ?>
            </h3>

            <p>
                Sistem sudah mengisi beberapa field. Periksa judul,
                caption, foto, Canva, PIC, dan target tayang sebelum
                menyimpan.
            </p>

            <?php if (
                (int) (
                    $autoBrief['existing_count'] ?? 0
                ) > 0
            ) : ?>
                <small>
                    Kegiatan ini sudah memiliki
                    <?= (int) $autoBrief['existing_count'] ?>
                    record publikasi. Brief tambahan tetap dapat
                    dibuat untuk format yang berbeda.
                </small>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?= $this->include('publications/_form') ?>

</div>

<?= $this->endSection() ?>
