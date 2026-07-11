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
    <a href="<?= base_url('/structures') ?>">Kembali</a>
</div>

<div class="container">
    <h2>Tambah Struktur Pengurus</h2>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="<?= base_url('/structures/store') ?>" method="post">
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

            <div class="grid">
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

            <div class="grid">
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

            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="<?= base_url('/structures') ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

</body>
</html>