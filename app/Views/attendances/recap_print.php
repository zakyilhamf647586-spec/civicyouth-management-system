<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?> - Karang Taruna RW 01</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 32px;
        }

        .actions {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background: #08264a;
            color: white;
            padding: 9px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .kop {
            display: flex;
            align-items: center;
            gap: 18px;
            border-bottom: 4px solid #d9a323;
            padding-bottom: 14px;
            margin-bottom: 24px;
        }

        .kop img {
            width: 78px;
            height: 78px;
            object-fit: contain;
        }

        .kop-text {
            flex: 1;
            text-align: center;
        }

        .kop-text h2,
        .kop-text h3,
        .kop-text p {
            margin: 4px 0;
        }

        .kop-text h2 {
            color: #08264a;
            font-size: 20px;
        }

        .kop-text h3 {
            font-size: 17px;
        }

        .title {
            text-align: center;
            margin-bottom: 22px;
        }

        .title h3 {
            margin: 0;
            font-size: 18px;
            text-decoration: underline;
        }

        .info {
            margin-bottom: 18px;
            font-size: 14px;
            line-height: 1.7;
        }

        .summary-table {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            border: 1px solid #111827;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }

        .center {
            text-align: center;
        }

        .signature-table td {
            border: none;
            font-size: 13px;
        }

        @media print {
            .actions {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="actions">
    <a href="<?= base_url('/attendances/recap/' . $meeting['id']) ?>" class="btn">Kembali</a>
    <a href="#" onclick="window.print()" class="btn">Cetak / Save PDF</a>
</div>

<div class="kop">
    <img src="<?= base_url('assets/img/logo-rw01.png') ?>" alt="Logo Karang Taruna RW 01">

    <div class="kop-text">
        <h2>KARANG TARUNA RW 01</h2>
        <h3>KELURAHAN RANDUGARUT</h3>
        <p>Sistem Manajemen Organisasi Pemuda</p>
    </div>
</div>

<div class="title">
    <h3>REKAP ABSENSI RAPAT</h3>
</div>

<div class="info">
    <strong>Judul Rapat:</strong> <?= esc($meeting['title']) ?><br>
    <strong>Tanggal:</strong> <?= date('d M Y', strtotime($meeting['meeting_date'])) ?><br>
    <strong>Waktu:</strong>
    <?= $meeting['start_time'] ? esc(substr($meeting['start_time'], 0, 5)) : '-' ?>
    -
    <?= $meeting['end_time'] ? esc(substr($meeting['end_time'], 0, 5)) : '-' ?><br>
    <strong>Tempat:</strong> <?= esc($meeting['location'] ?? '-') ?><br>
    <strong>Tanggal Cetak:</strong> <?= date('d M Y H:i') ?>
</div>

<table class="summary-table">
    <thead>
        <tr>
            <th class="center">Total Anggota Aktif</th>
            <th class="center">Hadir</th>
            <th class="center">Izin</th>
            <th class="center">Tidak Hadir</th>
            <th class="center">Belum Dicatat</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="center"><?= esc($summary['total_members']) ?></td>
            <td class="center"><?= esc($summary['present']) ?></td>
            <td class="center"><?= esc($summary['permission']) ?></td>
            <td class="center"><?= esc($summary['absent']) ?></td>
            <td class="center"><?= esc($summary['not_recorded']) ?></td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Anggota</th>
            <th>RT</th>
            <th>Jabatan/Posisi</th>
            <th>Status Kehadiran</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($members)) : ?>
            <?php $no = 1; foreach ($members as $member) : ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($member['full_name']) ?></td>
                    <td><?= esc($member['rt'] ?? '-') ?></td>
                    <td><?= esc($member['position'] ?? '-') ?></td>
                    <td>
                        <?php if ($member['attendance_status'] === 'present') : ?>
                            Hadir
                        <?php elseif ($member['attendance_status'] === 'permission') : ?>
                            Izin
                        <?php elseif ($member['attendance_status'] === 'absent') : ?>
                            Tidak Hadir
                        <?php else : ?>
                            Belum Dicatat
                        <?php endif; ?>
                    </td>
                    <td><?= esc($member['note'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="6" class="center">Belum ada anggota aktif.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br><br>

<table class="signature-table" style="width: 100%;">
    <tr>
        <td style="width: 50%; text-align: center;">
            Mengetahui,<br>
            Ketua Karang Taruna RW 01
            <br><br><br><br>
            <strong>Zaky Ilham Ferdiansyah</strong>
        </td>
        <td style="width: 50%; text-align: center;">
            Randugarut, <?= date('d M Y') ?><br>
            Sekretaris
            <br><br><br><br>
            <strong>________________________</strong>
        </td>
    </tr>
</table>

</body>
</html>