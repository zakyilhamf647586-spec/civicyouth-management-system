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
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .header h2 {
            margin: 0;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #0f5132;
            color: white;
        }

        .btn-warning {
            background: #f0ad4e;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
            vertical-align: top;
        }

        th {
            color: #334e68;
            background: #f8fafc;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .alert-error {
            background: #ffe3e3;
            color: #842029;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .badge {
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            display: inline-block;
        }

        .present {
            background: #d1e7dd;
            color: #0f5132;
        }

        .permission {
            background: #fff3cd;
            color: #664d03;
        }

        .absent {
            background: #ffe3e3;
            color: #842029;
        }

        .empty {
            text-align: center;
            color: #627d98;
            padding: 32px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>CivicYouth</h1>
    <div>
        <a href="<?= base_url('/dashboard') ?>">Dashboard</a>
        <a href="<?= base_url('/logout') ?>">Logout</a>
    </div>
</div>

<div class="container">
    <div class="header">
        <h2>Absensi Rapat</h2>
        <a href="<?= base_url('/attendances/create') ?>" class="btn btn-primary">+ Tambah Absensi</a>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert-error">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Rapat</th>
                    <th>Tanggal</th>
                    <th>Nama Anggota</th>
                    <th>RT</th>
                    <th>Status Kehadiran</th>
                    <th>Catatan</th>
                    <th width="170">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($attendances)) : ?>
                    <?php $no = 1; foreach ($attendances as $attendance) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($attendance['meeting_title']) ?></td>
                            <td><?= date('d M Y', strtotime($attendance['meeting_date'])) ?></td>
                            <td><?= esc($attendance['full_name']) ?></td>
                            <td><?= esc($attendance['rt'] ?? '-') ?></td>
                            <td>
                                <?php
                                    $statusText = [
                                        'present'    => 'Hadir',
                                        'permission' => 'Izin',
                                        'absent'     => 'Tidak Hadir'
                                    ];
                                ?>
                                <span class="badge <?= esc($attendance['attendance_status']) ?>">
                                    <?= esc($statusText[$attendance['attendance_status']] ?? '-') ?>
                                </span>
                            </td>
                            <td><?= esc($attendance['note'] ?? '-') ?></td>
                            <td>
                                <a href="<?= base_url('/attendances/edit/' . $attendance['id']) ?>" class="btn btn-warning">Edit</a>
                                <a href="<?= base_url('/attendances/delete/' . $attendance['id']) ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('Yakin ingin menghapus data absensi ini?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="empty">Belum ada data absensi rapat.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>