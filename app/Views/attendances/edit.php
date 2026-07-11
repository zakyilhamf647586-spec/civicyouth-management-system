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
    <a href="<?= base_url('/attendances') ?>">Kembali</a>
</div>

<div class="container">
    <h2>Edit Absensi Rapat</h2>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
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
            <a href="<?= base_url('/attendances') ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

</body>
</html>