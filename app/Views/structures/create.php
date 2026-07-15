<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Tambah Struktur Pengurus</h2>
        <p>Tambahkan jabatan atau posisi baru dalam struktur organisasi.</p>
    </div>

    <a href="<?= base_url('/structures') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form action="<?= base_url('/structures/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Jabatan</label>
            <input type="text" name="position_name" value="<?= old('position_name') ?>" placeholder="Contoh: Ketua, Sekretaris, Humas RT 01" required>
        </div>

        <div class="form-group">
            <label>Nama Pengurus</label>
            <select name="member_id">
                <option value="">Belum ditentukan</option>
                <?php foreach ($members as $member) : ?>
                    <option value="<?= $member['id'] ?>" <?= old('member_id') == $member['id'] ? 'selected' : '' ?>>
                        <?= esc($member['full_name']) ?> - <?= esc($member['rt'] ?? '-') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Bidang/Seksi</label>
                <input type="text" name="division" value="<?= old('division') ?>" placeholder="Contoh: Inti, Humas, Olahraga, Sosial">
            </div>

            <div class="form-group">
                <label>RT</label>
                <select name="rt_scope">
                    <option value="">Umum RW</option>
                    <option value="RT 01" <?= old('rt_scope') === 'RT 01' ? 'selected' : '' ?>>RT 01</option>
                    <option value="RT 02" <?= old('rt_scope') === 'RT 02' ? 'selected' : '' ?>>RT 02</option>
                    <option value="RT 03" <?= old('rt_scope') === 'RT 03' ? 'selected' : '' ?>>RT 03</option>
                    <option value="RT 04" <?= old('rt_scope') === 'RT 04' ? 'selected' : '' ?>>RT 04</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Periode</label>
                <input type="text" name="period" value="<?= old('period') ?>" placeholder="Contoh: 2026-2029">
            </div>

            <div class="form-group">
                <label>Urutan Tampilan</label>
                <input type="number" name="sort_order" value="<?= old('sort_order', 0) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Deskripsi Tugas</label>
            <textarea name="description" placeholder="Tulis ringkasan tugas jabatan ini"><?= old('description') ?></textarea>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
            </select>
        </div>

        <div class="form-group">
            <label>Foto Pengurus</label>
            <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
            <small>Opsional. Format JPG, JPEG, PNG, atau WEBP. Maksimal 2MB.</small>
        </div>

        <div class="form-group">
            <label>Biodata Singkat</label>
            <textarea name="short_bio" rows="4" placeholder="Contoh: Aktif dalam kegiatan kepemudaan, sosial, dan koordinasi warga RW 01."><?= old('short_bio') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Data</button>
        <a href="<?= base_url('/structures') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>