<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Edit Absensi Rapat</h2>
        <p>Perbarui agenda rapat, anggota, status kehadiran, atau catatan absensi.</p>
    </div>

    <a href="<?= base_url('/attendances/recap/' . $attendance['meeting_id']) ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form action="<?= base_url('/attendances/update/' . $attendance['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Agenda Rapat</label>
            <select name="meeting_id" required>
                <option value="">Pilih Rapat</option>
                <?php foreach ($meetings as $meeting) : ?>
                    <option value="<?= $meeting['id'] ?>" <?= old('meeting_id', $attendance['meeting_id']) == $meeting['id'] ? 'selected' : '' ?>>
                        <?= esc($meeting['title']) ?> - <?= date('d M Y', strtotime($meeting['meeting_date'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Nama Anggota</label>
            <select name="member_id" required>
                <option value="">Pilih Anggota</option>
                <?php foreach ($members as $member) : ?>
                    <option value="<?= $member['id'] ?>" <?= old('member_id', $attendance['member_id']) == $member['id'] ? 'selected' : '' ?>>
                        <?= esc($member['full_name']) ?> - <?= esc($member['rt'] ?? '-') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Status Kehadiran</label>
            <select name="attendance_status" required>
                <option value="present" <?= old('attendance_status', $attendance['attendance_status']) === 'present' ? 'selected' : '' ?>>Hadir</option>
                <option value="permission" <?= old('attendance_status', $attendance['attendance_status']) === 'permission' ? 'selected' : '' ?>>Izin</option>
                <option value="absent" <?= old('attendance_status', $attendance['attendance_status']) === 'absent' ? 'selected' : '' ?>>Tidak Hadir</option>
            </select>
        </div>

        <div class="form-group">
            <label>Catatan</label>
            <textarea name="note"><?= old('note', $attendance['note']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Absensi</button>
        <a href="<?= base_url('/attendances/recap/' . $attendance['meeting_id']) ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>