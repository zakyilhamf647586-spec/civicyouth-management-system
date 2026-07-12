<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Kas Organisasi</h2>
        <p>Kelola pemasukan, pengeluaran, dan saldo kas Karang Taruna RW 01.</p>
    </div>

    <a href="<?= base_url('/cash/create') ?>" class="btn btn-primary">+ Tambah Transaksi</a>
</div>

<div class="cards">
    <div class="card">
        <span>Total Pemasukan</span>
        <h3>Rp<?= number_format($total_income, 0, ',', '.') ?></h3>
    </div>

    <div class="card">
        <span>Total Pengeluaran</span>
        <h3>Rp<?= number_format($total_expense, 0, ',', '.') ?></h3>
    </div>

    <div class="card">
        <span>Saldo Kas</span>
        <h3>Rp<?= number_format($balance, 0, ',', '.') ?></h3>
    </div>
</div>

<br>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="table-card">
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
                <th width="170">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transactions)) : ?>
                <?php $no = 1; foreach ($transactions as $transaction) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d M Y', strtotime($transaction['transaction_date'])) ?></td>
                        <td>
                            <?php if ($transaction['transaction_type'] === 'income') : ?>
                                <span class="badge badge-success">Pemasukan</span>
                            <?php else : ?>
                                <span class="badge badge-danger">Pengeluaran</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($transaction['category'] ?? '-') ?></td>
                        <td>
                            <strong>Rp<?= number_format($transaction['amount'], 0, ',', '.') ?></strong>
                        </td>
                        <td><?= esc($transaction['description'] ?? '-') ?></td>
                        <td><?= esc($transaction['created_by'] ?? '-') ?></td>
                        <td>
                            <a href="<?= base_url('/cash/edit/' . $transaction['id']) ?>" class="btn btn-warning">Edit</a>
                            <a href="<?= base_url('/cash/delete/' . $transaction['id']) ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" class="empty">Belum ada transaksi kas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>