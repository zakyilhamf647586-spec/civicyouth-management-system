<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Edit Agenda Rapat</h2>
        <p>Perbarui jadwal, tempat, agenda, keputusan, dan status rapat.</p>
    </div>

    <a href="<?= base_url('/meetings') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form action="<?= base_url('/meetings/update/' . $meeting['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Judul Rapat</label>
            <input type="text" name="title" value="<?= old('title', $meeting['title']) ?>" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Tanggal Rapat</label>
                <input type="date" name="meeting_date" value="<?= old('meeting_date', $meeting['meeting_date']) ?>" required>
            </div>

            <div class="form-group">
                <label>Tempat</label>
                <input type="text" name="location" value="<?= old('location', $meeting['location']) ?>">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="time" name="start_time" value="<?= old('start_time', $meeting['start_time']) ?>">
            </div>

            <div class="form-group">
                <label>Waktu Selesai</label>
                <input type="time" name="end_time" value="<?= old('end_time', $meeting['end_time']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Agenda/Pembahasan</label>
            <textarea name="agenda"><?= old('agenda', $meeting['agenda']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Hasil Keputusan</label>
            <textarea name="decisions"><?= old('decisions', $meeting['decisions']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Catatan Tambahan</label>
            <textarea name="notes"><?= old('notes', $meeting['notes']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Status Rapat</label>
            <select name="status">
                <option value="scheduled" <?= old('status', $meeting['status']) === 'scheduled' ? 'selected' : '' ?>>Terjadwal</option>
                <option value="completed" <?= old('status', $meeting['status']) === 'completed' ? 'selected' : '' ?>>Selesai</option>
                <option value="cancelled" <?= old('status', $meeting['status']) === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Agenda</button>
        <a href="<?= base_url('/meetings') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>