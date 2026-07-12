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

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }

        .card span {
            color: #627d98;
            font-size: 14px;
        }

        .card h3 {
            margin: 10px 0 0 0;
            font-size: 28px;
        }

        .report-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .report-item {
            background: white;
            border-radius: 16px;
            padding: 24px;
            text-decoration: none;
            color: #102a43;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            display: block;
        }

        .report-item h3 {
            margin-top: 0;
        }

        .report-item p {
            color: #627d98;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            margin-top: 8px;
            background: #0f5132;
            color: white;
            padding: 9px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .summary, .report-list {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 20px;
            }
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
    <h2>Laporan Sistem</h2>
    <p>Ringkasan data administrasi CivicYouth Management System.</p>

    <div class="summary">
        <div class="card">
            <span>Total Anggota</span>
            <h3><?= esc($total_members) ?></h3>
        </div>

        <div class="card">
            <span>Anggota Aktif</span>
            <h3><?= esc($active_members) ?></h3>
        </div>

        <div class="card">
            <span>Total Rapat</span>
            <h3><?= esc($total_meetings) ?></h3>
        </div>

        <div class="card">
            <span>Total Kegiatan</span>
            <h3><?= esc($total_activities) ?></h3>
        </div>

        <div class="card">
            <span>Total Pemasukan</span>
            <h3>Rp<?= number_format($total_income, 0, ',', '.') ?></h3>
        </div>

        <div class="card">
            <span>Saldo Kas</span>
            <h3>Rp<?= number_format($balance, 0, ',', '.') ?></h3>
        </div>
    </div>

    <h2>Daftar Laporan</h2>

    <div class="report-list">
        <a href="<?= base_url('/reports/members') ?>" class="report-item">
            <h3>Laporan Anggota</h3>
            <p>Menampilkan daftar anggota, RT, jabatan, status, dan kontak anggota.</p>
            <span class="btn">Buka Laporan</span>
        </a>

        <a href="<?= base_url('/reports/cash') ?>" class="report-item">
            <h3>Laporan Kas</h3>
            <p>Menampilkan pemasukan, pengeluaran, dan saldo kas organisasi.</p>
            <span class="btn">Buka Laporan</span>
        </a>

        <a href="<?= base_url('/reports/meetings') ?>" class="report-item">
            <h3>Laporan Rapat</h3>
            <p>Menampilkan agenda rapat, tanggal, tempat, status, dan hasil keputusan.</p>
            <span class="btn">Buka Laporan</span>
        </a>
    </div>
</div>

</body>
</html>