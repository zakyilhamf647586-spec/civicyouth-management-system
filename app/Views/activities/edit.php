<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Edit Kegiatan</h2>
        <p>Perbarui data kegiatan, lokasi, hasil, dokumentasi, dan status kegiatan.</p>
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
    <form action="<?= base_url('/activities/update/' . $activity['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nama Kegiatan</label>
            <input type="text" name="title" value="<?= old('title', $activity['title']) ?>" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Tanggal Kegiatan</label>
                <input type="date" name="activity_date" value="<?= old('activity_date', $activity['activity_date']) ?>" required>
            </div>

            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="location" value="<?= old('location', $activity['location']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Deskripsi Kegiatan</label>
            <textarea name="description"><?= old('description', $activity['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Hasil Kegiatan</label>
            <textarea name="result"><?= old('result', $activity['result']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Link Dokumentasi</label>
            <input type="text" name="documentation_link" value="<?= old('documentation_link', $activity['documentation_link']) ?>">
        </div>

        <div class="form-group">
            <label>Status Kegiatan</label>
            <select name="status" required>
                <option value="planned" <?= old('status', $activity['status']) === 'planned' ? 'selected' : '' ?>>Direncanakan</option>
                <option value="completed" <?= old('status', $activity['status']) === 'completed' ? 'selected' : '' ?>>Selesai</option>
                <option value="cancelled" <?= old('status', $activity['status']) === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Kegiatan</button>
        <a href="<?= base_url('/activities') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>