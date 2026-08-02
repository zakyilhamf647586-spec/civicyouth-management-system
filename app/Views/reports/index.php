<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$canViewMemberReports = isset($can_view_member_reports)
    ? !empty($can_view_member_reports)
    : auth_can('reports.members');
$canViewCashReports = isset($can_view_cash_reports)
    ? !empty($can_view_cash_reports)
    : auth_can('reports.cash');
$canViewMeetingReports = isset($can_view_meeting_reports)
    ? !empty($can_view_meeting_reports)
    : auth_can('reports.meetings');
$canViewActivitySummary = isset($can_view_activity_summary)
    ? !empty($can_view_activity_summary)
    : auth_can('activities.view');
$hasSpecificReports = $canViewMemberReports
    || $canViewCashReports
    || $canViewMeetingReports;
?>

<div class="page-header">
    <div>
        <h2>Laporan</h2>
        <p>Ringkasan dan cetak laporan administrasi Karang Taruna RW 01.</p>
    </div>
</div>

<div class="cards">
    <?php if ($canViewMemberReports) : ?>
        <div class="card">
            <span>Total Anggota</span>
            <h3><?= esc($total_members) ?></h3>
        </div>

        <div class="card">
            <span>Anggota Aktif</span>
            <h3><?= esc($active_members) ?></h3>
        </div>
    <?php endif; ?>

    <?php if ($canViewMeetingReports) : ?>
        <div class="card">
            <span>Total Rapat</span>
            <h3><?= esc($total_meetings) ?></h3>
        </div>
    <?php endif; ?>

    <?php if ($canViewActivitySummary) : ?>
        <div class="card">
            <span>Total Kegiatan</span>
            <h3><?= esc($total_activities) ?></h3>
        </div>
    <?php endif; ?>

    <?php if ($canViewCashReports) : ?>
        <div class="card">
            <span>Total Pemasukan</span>
            <h3>Rp<?= number_format((float) $total_income, 0, ',', '.') ?></h3>
        </div>

        <div class="card">
            <span>Saldo Kas</span>
            <h3>Rp<?= number_format((float) $balance, 0, ',', '.') ?></h3>
        </div>
    <?php endif; ?>
</div>

<div class="section">
    <h3>Daftar Laporan</h3>
    <p>Pilih jenis laporan yang tersedia sesuai kewenangan akun Anda.</p>

    <?php if ($hasSpecificReports) : ?>
        <div class="menu-list">
            <?php if ($canViewMemberReports) : ?>
                <a href="<?= base_url('/reports/members') ?>" class="menu-item">
                    Laporan Data Anggota
                </a>
            <?php endif; ?>

            <?php if ($canViewCashReports) : ?>
                <a href="<?= base_url('/reports/cash') ?>" class="menu-item">
                    Laporan Kas Organisasi
                </a>
            <?php endif; ?>

            <?php if ($canViewMeetingReports) : ?>
                <a href="<?= base_url('/reports/meetings') ?>" class="menu-item">
                    Laporan Agenda Rapat
                </a>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <div class="empty">
            Tidak ada laporan rinci yang tersedia untuk peran akun ini.
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
