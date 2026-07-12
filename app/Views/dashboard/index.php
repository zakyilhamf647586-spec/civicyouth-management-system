<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Dashboard</h2>
        <p>Selamat datang, <?= esc(session()->get('name')) ?>.</p>
    </div>
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
        <h3>Rp<?= number_format($cash_balance, 0, ',', '.') ?></h3>
    </div>
</div>

<div class="section">
    <h3>Menu Sistem</h3>
    <p>Fitur utama Sistem Manajemen Karang Taruna RW 01.</p>

    <div class="menu-list">
        <a href="<?= base_url('/members') ?>" class="menu-item">Data Anggota</a>
        <a href="<?= base_url('/structures') ?>" class="menu-item">Struktur Pengurus</a>
        <a href="<?= base_url('/meetings') ?>" class="menu-item">Agenda Rapat</a>
        <a href="<?= base_url('/attendances') ?>" class="menu-item">Absensi</a>
        <a href="<?= base_url('/cash') ?>" class="menu-item">Kas Organisasi</a>
        <a href="<?= base_url('/activities') ?>" class="menu-item">Kegiatan</a>
        <a href="<?= base_url('/reports') ?>" class="menu-item">Laporan</a>
    </div>
</div>

<?= $this->endSection() ?>