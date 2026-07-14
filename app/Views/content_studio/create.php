<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Buat Konten AI</h2>
        <p>Upload gambar kegiatan dan biarkan AI membantu membuat draft konten media sosial.</p>
    </div>

    <a href="<?= base_url('/content-studio') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form action="<?= base_url('/content-studio/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Upload Gambar</label>
            <input type="file" name="content_images[]" accept=".jpg,.jpeg,.png,.webp" multiple required>
            <small>Upload 1–5 gambar. Format JPG, JPEG, PNG, atau WEBP. Maksimal 4MB per gambar.</small>
        </div>

        <div class="form-group">
            <label>Kategori Konten</label>
            <select name="category" required>
                <option value="auto_detect">Deteksi Otomatis oleh AI</option>
                <option value="dokumentasi_kegiatan">Dokumentasi Kegiatan</option>
                <option value="pengumuman">Pengumuman</option>
                <option value="undangan">Undangan</option>
                <option value="hari_besar">Ucapan Hari Besar</option>
                <option value="edukasi">Edukasi</option>
                <option value="apresiasi">Apresiasi</option>
                <option value="laporan_singkat">Laporan Singkat</option>
                <option value="umum">Umum</option>
            </select>
        </div>

        <div class="form-group">
            <label>Template Visual</label>
            <input type="hidden" name="template_type" value="feed_portrait_permanent">

            <div class="template-locked-box">
                <strong>Feed Instagram 4:5</strong><br>
                Template Permanen Karang Taruna RW 01 · 1080 × 1350 px
            </div>

            <small>
                Template visual dikunci agar seluruh konten Instagram organisasi tetap konsisten.
            </small>
        </div>

        <div class="form-group">
            <label>Catatan Tambahan</label>
            <textarea name="notes" placeholder="Contoh: kegiatan kerja bakti RW 01, diikuti pemuda dan warga, suasana gotong royong."><?= old('notes') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Draft</button>
        <a href="<?= base_url('/content-studio') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>