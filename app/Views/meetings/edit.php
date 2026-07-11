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
    <a href="<?= base_url('/meetings') ?>">Kembali</a>
</div>

<div class="container">
    <h2>Edit Agenda Rapat</h2>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="<?= base_url('/meetings/update/' . $meeting['id']) ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Judul Rapat</label>
                <input type="text" name="title" value="<?= old('title', $meeting['title']) ?>" required>
            </div>

            <div class="grid">
                <div class="form-group">
                    <label>Tanggal Rapat</label>
                    <input type="date" name="meeting_date" value="<?= old('meeting_date', $meeting['meeting_date']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Tempat</label>
                    <input type="text" name="location" value="<?= old('location', $meeting['location']) ?>">
                </div>
            </div>

            <div class="grid">
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
</div>

</body>
</html>