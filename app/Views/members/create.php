<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?> - CivicYouth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #102a43;
        }

        .navbar {
            background: #0f5132;
            color: white;
            padding: 18px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            margin: 0;
            font-size: 22px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.15);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
        }

        .container {
            padding: 32px;
            max-width: 850px;
            margin: auto;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #334e68;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            font-size: 14px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 90px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 11px 16px;
            border-radius: 9px;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #0f5132;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .alert-error {
            background: #ffe3e3;
            color: #842029;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>CivicYouth</h1>
    <a href="<?= base_url('/members') ?>">Kembali</a>
</div>

<div class="container">
    <h2>Tambah Anggota</h2>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="<?= base_url('/members/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="full_name" value="<?= old('full_name') ?>" required>
            </div>

            <div class="grid">
                <div class="form-group">
                    <label>RT</label>
                    <select name="rt">
                        <option value="">Pilih RT</option>
                        <option value="RT 01" <?= old('rt') === 'RT 01' ? 'selected' : '' ?>>RT 01</option>
                        <option value="RT 02" <?= old('rt') === 'RT 02' ? 'selected' : '' ?>>RT 02</option>
                        <option value="RT 03" <?= old('rt') === 'RT 03' ? 'selected' : '' ?>>RT 03</option>
                        <option value="RT 04" <?= old('rt') === 'RT 04' ? 'selected' : '' ?>>RT 04</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="gender">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid">
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="<?= old('birth_date') ?>">
                </div>

                <div class="form-group">
                    <label>No. HP</label>
                    <input type="text" name="phone" value="<?= old('phone') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Jabatan/Posisi</label>
                <input type="text" name="position" value="<?= old('position') ?>" placeholder="Contoh: Ketua, Sekretaris, Humas RT 01, Anggota">
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="address"><?= old('address') ?></textarea>
            </div>

            <div class="form-group">
                <label>Status Keanggotaan</label>
                <select name="membership_status">
                    <option value="active" <?= old('membership_status') === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= old('membership_status') === 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="<?= base_url('/members') ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

</body>
</html>