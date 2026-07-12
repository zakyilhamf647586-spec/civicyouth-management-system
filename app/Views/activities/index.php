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

        .planned {
            background: #fff3cd;
            color: #664d03;
        }

        .completed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .cancelled {
            background: #ffe3e3;
            color: #842029;
        }

        .empty {
            text-align: center;
            color: #627d98;
            padding: 32px;
        }

        .link-doc {
            color: #0f5132;
            font-weight: bold;
            text-decoration: none;
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
        <h2>Kegiatan</h2>
        <a href="<?= base_url('/activities/create') ?>" class="btn btn-primary">+ Tambah Kegiatan</a>
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
                    <th>Nama Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Dokumentasi</th>
                    <th width="170">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($activities)) : ?>
                    <?php $no = 1; foreach ($activities as $activity) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <strong><?= esc($activity['title']) ?></strong><br>
                                <small>
                                    <?= esc($activity['description'] ? substr($activity['description'], 0, 90) . '...' : '-') ?>
                                </small>
                            </td>
                            <td><?= date('d M Y', strtotime($activity['activity_date'])) ?></td>
                            <td><?= esc($activity['location'] ?? '-') ?></td>
                            <td>
                                <?php
                                    $statusText = [
                                        'planned'   => 'Direncanakan',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan'
                                    ];
                                ?>
                                <span class="badge <?= esc($activity['status']) ?>">
                                    <?= esc($statusText[$activity['status']] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($activity['documentation_link'])) : ?>
                                    <a href="<?= esc($activity['documentation_link']) ?>" target="_blank" class="link-doc">Lihat</a>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('/activities/edit/' . $activity['id']) ?>" class="btn btn-warning">Edit</a>
                                <a href="<?= base_url('/activities/delete/' . $activity['id']) ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('Yakin ingin menghapus data kegiatan ini?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="empty">Belum ada data kegiatan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>