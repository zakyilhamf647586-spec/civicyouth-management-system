<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Edit Anggota</h2>
        <p>Perbarui data anggota yang sudah tersimpan.</p>
    </div>

    <a href="<?= base_url('/members') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form action="<?= base_url('/members/update/' . $member['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="full_name" value="<?= old('full_name', $member['full_name']) ?>" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>RT</label>
                <select name="rt">
                    <option value="">Pilih RT</option>
                    <option value="RT 01" <?= old('rt', $member['rt']) === 'RT 01' ? 'selected' : '' ?>>RT 01</option>
                    <option value="RT 02" <?= old('rt', $member['rt']) === 'RT 02' ? 'selected' : '' ?>>RT 02</option>
                    <option value="RT 03" <?= old('rt', $member['rt']) === 'RT 03' ? 'selected' : '' ?>>RT 03</option>
                    <option value="RT 04" <?= old('rt', $member['rt']) === 'RT 04' ? 'selected' : '' ?>>RT 04</option>
                </select>
            </div>

            <div class="form-group">
                <label>Jenis Kelamin</label>
                <select name="gender">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="male" <?= old('gender', $member['gender']) === 'male' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="female" <?= old('gender', $member['gender']) === 'female' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Tanggal Lahir</label>
                <input type="date" name="birth_date" value="<?= old('birth_date', $member['birth_date']) ?>">
            </div>

            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="phone" value="<?= old('phone', $member['phone']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Jabatan/Posisi</label>
            <input type="text" name="position" value="<?= old('position', $member['position']) ?>">
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="address"><?= old('address', $member['address']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Status Keanggotaan</label>
            <select name="membership_status">
                <option value="active" <?= old('membership_status', $member['membership_status']) === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= old('membership_status', $member['membership_status']) === 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Data</button>
        <a href="<?= base_url('/members') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>