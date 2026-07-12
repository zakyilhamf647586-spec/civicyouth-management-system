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
            max-width: 900px;
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
            min-height: 100px;
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
    <a href="<?= base_url('/activities') ?>">Kembali</a>
</div>

<div class="container">
    <h2>Tambah Kegiatan</h2>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="<?= base_url('/activities/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Nama Kegiatan</label>
                <input type="text" name="title" value="<?= old('title') ?>" placeholder="Contoh: Kerja Bakti RW 01" required>
            </div>

            <div class="grid">
                <div class="form-group">
                    <label>Tanggal Kegiatan</label>
                    <input type="date" name="activity_date" value="<?= old('activity_date', date('Y-m-d')) ?>" required>
                </div>

                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" name="location" value="<?= old('location') ?>" placeholder="Contoh: Balai RW 01">
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
</div>

</body>
</html>