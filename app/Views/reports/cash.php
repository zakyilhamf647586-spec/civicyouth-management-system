<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?> - CivicYouth</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 32px;
        }

        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .header h2, .header h3 {
            margin: 4px 0;
        }

        .summary {
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

        .actions {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background: #0f5132;
            color: white;
            padding: 9px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
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

<div class="header">
    <h2>LAPORAN KAS ORGANISASI</h2>
    <h3>KARANG TARUNA RW 01</h3>
    <p>Kelurahan Randugarut</p>
</div>

<div class="summary">
    <strong>Tanggal Cetak:</strong> <?= date('d M Y H:i') ?><br>
    <strong>Total Pemasukan:</strong> Rp<?= number_format($total_income, 0, ',', '.') ?><br>
    <strong>Total Pengeluaran:</strong> Rp<?= number_format($total_expense, 0, ',', '.') ?><br>
    <strong>Saldo Akhir:</strong> Rp<?= number_format($balance, 0, ',', '.') ?>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Kategori</th>
            <th>Nominal</th>
            <th>Keterangan</th>
            <th>Dicatat Oleh</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($transactions)) : ?>
            <?php $no = 1; foreach ($transactions as $transaction) : ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d M Y', strtotime($transaction['transaction_date'])) ?></td>
                    <td><?= $transaction['transaction_type'] === 'income' ? 'Pemasukan' : 'Pengeluaran' ?></td>
                    <td><?= esc($transaction['category'] ?? '-') ?></td>
                    <td>Rp<?= number_format($transaction['amount'], 0, ',', '.') ?></td>
                    <td><?= esc($transaction['description'] ?? '-') ?></td>
                    <td><?= esc($transaction['created_by'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="7" style="text-align:center;">Belum ada transaksi kas.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br><br>

<table style="width: 100%; border: none;">
    <tr>
        <td style="border: none; width: 50%; text-align: center;">
            Mengetahui,<br>
            Ketua Karang Taruna RW 01
            <br><br><br><br>
            <strong>Zaky Ilham Ferdiansyah</strong>
        </td>
        <td style="border: none; width: 50%; text-align: center;">
            Randugarut, <?= date('d M Y') ?><br>
            Bendahara
            <br><br><br><br>
            <strong>________________________</strong>
        </td>
    </tr>
</table>

</body>
</html>