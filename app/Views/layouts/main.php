<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Karang Taruna RW 01') ?> - Karang Taruna RW 01</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="app-shell">
    <header class="app-topbar">
        <div class="brand-area">
            <img src="<?= base_url('assets/img/logo-rw01.png') ?>" alt="Logo Karang Taruna RW 01" class="brand-logo">

            <div class="brand-text">
                <strong>Karang Taruna RW 01</strong>
                <span>Kelurahan Randugarut · Sistem Manajemen Organisasi Pemuda</span>
            </div>
        </div>

        <div class="topbar-actions">
            <button type="button" class="menu-toggle" onclick="toggleMainMenu()">
                <span class="menu-icon">☰</span>
                <span>Menu</span>
            </button>

            <a href="<?= base_url('/logout') ?>" class="logout-btn">Logout</a>
        </div>
    </header>

    <nav class="app-menu-panel" id="mainMenuPanel">
        <div class="menu-panel-header">
            <div>
                <strong>Menu Sistem</strong>
                <span>Pilih modul pengelolaan organisasi</span>
            </div>

            <button type="button" class="menu-close" onclick="toggleMainMenu()">×</button>
        </div>

        <div class="menu-grid">
            <a href="<?= base_url('/dashboard') ?>" class="menu-card">
                <span>Dashboard</span>
                <small>Ringkasan organisasi</small>
            </a>

            <a href="<?= base_url('/members') ?>" class="menu-card">
                <span>Data Anggota</span>
                <small>Kelola anggota RW 01</small>
            </a>

            <a href="<?= base_url('/structures') ?>" class="menu-card">
                <span>Struktur</span>
                <small>Susunan pengurus</small>
            </a>

            <a href="<?= base_url('/meetings') ?>" class="menu-card">
                <span>Rapat</span>
                <small>Agenda dan notulen</small>
            </a>

            <a href="<?= base_url('/attendances') ?>" class="menu-card">
                <span>Absensi</span>
                <small>Kehadiran rapat</small>
            </a>

            <a href="<?= base_url('/cash') ?>" class="menu-card">
                <span>Kas</span>
                <small>Keuangan organisasi</small>
            </a>

            <a href="<?= base_url('/activities') ?>" class="menu-card">
                <span>Kegiatan</span>
                <small>Agenda dan dokumentasi</small>
            </a>

            <a href="<?= base_url('/programs') ?>" class="menu-card">
                <span>Program GARDA 01</span>
                <small>Kelola pilar dan publikasi program</small>
            </a>

            <a href="<?= base_url('/content-studio') ?>" class="menu-card menu-card-highlight">
                <span>AI Content Studio</span>
                <small>Konten Instagram otomatis</small>
            </a>

            <a href="<?= base_url('/reports') ?>" class="menu-card">
                <span>Laporan</span>
                <small>Cetak dan rekap data</small>
            </a>
        </div>
    </nav>

    <main class="app-main">
        <?= $this->renderSection('content') ?>
    </main>
</div>

<script>
    function toggleMainMenu() {
        const panel = document.getElementById('mainMenuPanel');
        panel.classList.toggle('active');
    }

    document.addEventListener('click', function(event) {
        const panel = document.getElementById('mainMenuPanel');
        const toggle = document.querySelector('.menu-toggle');

        if (!panel || !toggle) {
            return;
        }

        if (!panel.contains(event.target) && !toggle.contains(event.target)) {
            panel.classList.remove('active');
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const panel = document.getElementById('mainMenuPanel');
            if (panel) {
                panel.classList.remove('active');
            }
        }
    });
</script>

</body>
</html>