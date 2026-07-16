<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Tambah Kegiatan</h2>
        <p>Tambahkan kegiatan baru beserta lokasi, deskripsi, hasil, dan link dokumentasi.</p>
    </div>

    <a href="<?= base_url('/activities') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form action="<?= base_url('/activities/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="program_id">Pilar Program GARDA 01</label>

            <select id="program_id" name="program_id">
                <option value="">Belum Dikategorikan</option>

                <?php foreach ($programs as $program) : ?>
                    <option
                        value="<?= $program['id'] ?>"
                        <?= old('program_id') == $program['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($program['name']) ?>
                        — <?= esc($program['label'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <small>
                Pilih pilar yang menjadi induk kegiatan ini.
            </small>
        </div>

        <div class="form-group">
            <label>Nama Kegiatan</label>
            <input type="text" name="title" value="<?= old('title') ?>" placeholder="Contoh: Kerja Bakti RW 01" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Tanggal Kegiatan</label>
                <input type="date" name="activity_date" value="<?= old('activity_date', date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="location" value="<?= old('location') ?>" placeholder="Contoh: Lingkungan RW 01">
            </div>
        </div>

        <div class="form-group">
            <label>Deskripsi Kegiatan</label>
            <textarea name="description" placeholder="Tulis gambaran kegiatan"><?= old('description') ?></textarea>
        </div>

        <div class="form-group">
            <label>Hasil Kegiatan</label>
            <textarea name="result" placeholder="Tulis hasil, dampak, atau catatan setelah kegiatan selesai"><?= old('result') ?></textarea>
        </div>

        <div class="form-group">
            <label>Link Dokumentasi</label>
            <input type="text" name="documentation_link" value="<?= old('documentation_link') ?>" placeholder="Contoh: Link Google Drive / Instagram / dokumentasi">
        </div>

        <div class="form-group">
            <label>Upload Foto Dokumentasi</label>
            <input type="file" name="documentation_file" accept=".jpg,.jpeg,.png,.webp">
            <small>Format yang didukung: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</small>
        </div>

        <div class="form-group">
            <label>Status Kegiatan</label>
            <select name="status" required>
                <option value="planned" <?= old('status') === 'planned' ? 'selected' : '' ?>>Direncanakan</option>
                <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Selesai</option>
                <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Kegiatan</button>
        <a href="<?= base_url('/activities') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>