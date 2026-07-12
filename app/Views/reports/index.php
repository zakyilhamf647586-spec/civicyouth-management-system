<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Laporan</h2>
        <p>Ringkasan dan cetak laporan administrasi Karang Taruna RW 01.</p>
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

<div class="section">
    <h3>Daftar Laporan</h3>
    <p>Pilih jenis laporan yang ingin dibuka dan dicetak.</p>

    <div class="menu-list">
        <a href="<?= base_url('/reports/members') ?>" class="menu-item">
            Laporan Data Anggota
        </a>

        <a href="<?= base_url('/reports/cash') ?>" class="menu-item">
            Laporan Kas Organisasi
        </a>

        <a href="<?= base_url('/reports/meetings') ?>" class="menu-item">
            Laporan Agenda Rapat
        </a>
    </div>
</div>

<?= $this->endSection() ?>