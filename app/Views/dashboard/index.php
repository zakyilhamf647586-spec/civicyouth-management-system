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
            color: #ffffff;
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
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            background: rgba(255,255,255,0.15);
            padding: 8px 14px;
            border-radius: 8px;
        }

        .container {
            padding: 32px;
        }

        .welcome {
            margin-bottom: 28px;
        }

        .welcome h2 {
            margin: 0 0 8px 0;
            font-size: 28px;
        }

        .welcome p {
            margin: 0;
            color: #627d98;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .card {
            background: #ffffff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }

        .card span {
            color: #627d98;
            font-size: 14px;
        }

        .card h3 {
            margin: 12px 0 0 0;
            font-size: 32px;
        }

        .section {
            margin-top: 32px;
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }

        .section h3 {
            margin-top: 0;
        }

        .menu-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-top: 18px;
        }

        .menu-item {
            padding: 16px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #f8fafc;
            text-decoration: none;
            color: #102a43;
        }

        @media (max-width: 768px) {
            .cards, .menu-list {
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
    <a href="<?= base_url('/logout') ?>">Logout</a>
</div>

<div class="container">
    <div class="welcome">
        <h2>Dashboard</h2>
        <p>Selamat datang, <?= esc(session()->get('name')) ?>.</p>
    </div>

    <div class="cards">
        <div class="card">
            <span>Total Anggota</span>
            <h3><?= esc($total_members) ?></h3>
        </div>

        <div class="card">
            <span>Anggota Aktif</span>
            <h3><?= esc($active_members) ?></h3>
        </div>

        <div class="card">
            <span>Saldo Kas</span>
            <h3>Rp0</h3>
        </div>
    </div>

    <div class="section">
        <h3>Menu Sistem</h3>
        <p>Fitur utama CivicYouth Management System.</p>

        <div class="menu-list">
            <a href="<?= base_url('/members') ?>" class="menu-item">Data Anggota</a>
            <div class="menu-item">Struktur Pengurus</div>
            <div class="menu-item">Agenda Rapat</div>
            <div class="menu-item">Absensi</div>
            <div class="menu-item">Kas Organisasi</div>
            <div class="menu-item">Laporan</div>
        </div>
    </div>
</div>

</body>
</html>