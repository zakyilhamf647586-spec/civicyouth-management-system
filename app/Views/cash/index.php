<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Kas Organisasi</h2>
        <p>Kelola pemasukan, pengeluaran, dan saldo kas Karang Taruna RW 01.</p>
    </div>

    <div>
        <a href="<?= base_url('/exports/cash') ?>" class="btn btn-secondary">Export Excel</a>
        <a href="<?= base_url('/cash/create') ?>" class="btn btn-primary">+ Tambah Transaksi</a>
    </div>
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

<div class="filter-card">
    <form action="<?= base_url('/cash') ?>" method="get">
        <div class="filter-grid cash-filter-grid">
            <div class="form-group">
                <label>Cari Transaksi</label>
                <input
                    type="text"
                    name="keyword"
                    value="<?= esc($keyword ?? '') ?>"
                    placeholder="Cari kategori, keterangan, atau pencatat"
                >
            </div>

            <div class="form-group">
                <label>Jenis</label>
                <select name="transaction_type">
                    <option value="">Semua Jenis</option>
                    <option value="income" <?= ($transaction_type ?? '') === 'income' ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="expense" <?= ($transaction_type ?? '') === 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>

            <div class="form-group">
                <label>Dari Tanggal</label>
                <input type="date" name="date_from" value="<?= esc($date_from ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?= esc($date_to ?? '') ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="<?= base_url('/cash') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

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
                <?php
                    $currentPage = $pager->getCurrentPage('cash');
                    $perPage     = $pager->getPerPage('cash');
                    $no          = 1 + ($perPage * ($currentPage - 1));
                ?>

                <?php foreach ($transactions as $transaction) : ?>
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
                    <td colspan="8" class="empty">Data transaksi kas tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->only(['keyword', 'transaction_type', 'date_from', 'date_to'])->links('cash', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>