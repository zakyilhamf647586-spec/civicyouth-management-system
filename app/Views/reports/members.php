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

        .signature-table td {
            border: none;
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
    <h3 style="margin:0;">LAPORAN DATA ANGGOTA</h3>
</div>

<div class="meta">
    <strong>Tanggal Cetak:</strong> <?= date('d M Y H:i') ?><br>
    <strong>Total Data:</strong> <?= count($members) ?> anggota
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>RT</th>
            <th>Jenis Kelamin</th>
            <th>No. HP</th>
            <th>Jabatan/Posisi</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($members)) : ?>
            <?php $no = 1; foreach ($members as $member) : ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($member['full_name']) ?></td>
                    <td><?= esc($member['rt'] ?? '-') ?></td>
                    <td>
                        <?php if ($member['gender'] === 'male') : ?>
                            Laki-laki
                        <?php elseif ($member['gender'] === 'female') : ?>
                            Perempuan
                        <?php else : ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= esc($member['phone'] ?? '-') ?></td>
                    <td><?= esc($member['position'] ?? '-') ?></td>
                    <td><?= $member['membership_status'] === 'active' ? 'Aktif' : 'Tidak Aktif' ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="7" style="text-align:center;">Belum ada data anggota.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br><br>

<table class="signature-table" style="width: 100%;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="text-align: center;">
            Randugarut, <?= date('d M Y') ?><br>
            Ketua Karang Taruna RW 01
            <br><br><br><br>
            <strong>Zaky Ilham Ferdiansyah</strong>
        </td>
    </tr>
</table>

</body>
</html>