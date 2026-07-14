<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Buat Konten AI</h2>
        <p>Upload gambar kegiatan dan isi data utama untuk membuat feed Instagram otomatis.</p>
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
            <small>
                Upload 1–5 gambar. Untuk versi saat ini, urutan gambar akan dipakai sebagai urutan template:<br>
                gambar pertama = foto utama, gambar kedua = foto kecil 1, gambar ketiga = foto kecil 2.
            </small>
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
            <label>Kategori Konten</label>
            <select name="category" required>
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
            <label>Judul Utama Feed</label>
            <input type="text" name="event_title" value="<?= old('event_title') ?>" placeholder="Contoh: Gerak Bersama Pemuda RW 01" required>
            <small>Judul ini akan tampil besar di bagian tengah atas desain.</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tanggal Kegiatan</label>
                <input type="date" name="event_date" value="<?= old('event_date') ?>" required>
            </div>

            <div class="form-group">
                <label>Jam Kegiatan</label>
                <input type="time" name="event_time" value="<?= old('event_time') ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Lokasi Kegiatan</label>
            <input type="text" name="event_location" value="<?= old('event_location') ?>" placeholder="Contoh: Lapangan SDN Randugarut" required>
        </div>

        <div class="form-group">
            <label>Bentuk Kegiatan</label>
            <textarea name="activity_description" rows="5" placeholder="Jelaskan bentuk kegiatan secara singkat namun jelas." required><?= old('activity_description') ?></textarea>
            <small>Bagian ini akan tampil pada blok informasi di kanan bawah template.</small>
        </div>

        <div class="form-group">
            <label>Catatan Tambahan untuk AI (Opsional)</label>
            <textarea name="notes" rows="4" placeholder="Opsional. Misal: tone caption lebih formal, lebih hangat, lebih singkat, dll."><?= old('notes') ?></textarea>
            <small>Catatan ini dipakai AI untuk membantu caption, bukan untuk struktur utama template.</small>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Draft</button>
        <a href="<?= base_url('/content-studio') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>