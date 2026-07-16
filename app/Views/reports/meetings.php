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

        .meta {
            margin-bottom: 18px;
            font-size: 14px;
            line-height: 1.7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #111827;
            padding: 7px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
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
    <a href="<?= base_url('/reports') ?>" class="btn">Kembali</a>
    <a href="#" onclick="window.print()" class="btn">Cetak / Save PDF</a>
</div>

<div class="kop">
    <img src="<?= esc(site_asset_url('site_logo', 'assets/img/logo-rw01.png'), 'attr') ?>" alt="Logo Karang Taruna RW 01">

    <div class="kop-text">
        <h2>KARANG TARUNA RW 01</h2>
        <h3>KELURAHAN RANDUGARUT</h3>
        <p>Sistem Manajemen Organisasi Pemuda</p>
    </div>
</div>

<div style="text-align:center; margin-bottom: 24px;">
    <h3 style="margin:0;">LAPORAN AGENDA RAPAT</h3>
</div>

<div class="meta">
    <strong>Tanggal Cetak:</strong> <?= date('d M Y H:i') ?><br>
    <strong>Total Rapat:</strong> <?= count($meetings) ?> rapat
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Judul Rapat</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Tempat</th>
            <th>Agenda</th>
            <th>Keputusan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($meetings)) : ?>
            <?php $no = 1; foreach ($meetings as $meeting) : ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($meeting['title']) ?></td>
                    <td><?= date('d M Y', strtotime($meeting['meeting_date'])) ?></td>
                    <td>
                        <?= $meeting['start_time'] ? esc(substr($meeting['start_time'], 0, 5)) : '-' ?>
                        -
                        <?= $meeting['end_time'] ? esc(substr($meeting['end_time'], 0, 5)) : '-' ?>
                    </td>
                    <td><?= esc($meeting['location'] ?? '-') ?></td>
                    <td><?= nl2br(esc($meeting['agenda'] ?? '-')) ?></td>
                    <td><?= nl2br(esc($meeting['decisions'] ?? '-')) ?></td>
                    <td>
                        <?php
                            $statusText = [
                                'scheduled' => 'Terjadwal',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                        ?>
                        <?= esc($statusText[$meeting['status']] ?? '-') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="8" style="text-align:center;">Belum ada agenda rapat.</td>
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