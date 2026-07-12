<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Edit Transaksi Kas</h2>
        <p>Perbarui tanggal, jenis, kategori, nominal, atau keterangan transaksi kas.</p>
    </div>

    <a href="<?= base_url('/cash') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form action="<?= base_url('/cash/update/' . $transaction['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="grid-2">
            <div class="form-group">
                <label>Tanggal Transaksi</label>
                <input type="date" name="transaction_date" value="<?= old('transaction_date', $transaction['transaction_date']) ?>" required>
            </div>

            <div class="form-group">
                <label>Jenis Transaksi</label>
                <select name="transaction_type" required>
                    <option value="">Pilih Jenis</option>
                    <option value="income" <?= old('transaction_type', $transaction['transaction_type']) === 'income' ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="expense" <?= old('transaction_type', $transaction['transaction_type']) === 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Kategori</label>
                <input type="text" name="category" value="<?= old('category', $transaction['category']) ?>" placeholder="Contoh: Iuran, Donasi, Konsumsi, Perlengkapan">
            </div>

            <div class="form-group">
                <label>Nominal</label>
                <input type="number" name="amount" value="<?= old('amount', $transaction['amount']) ?>" min="1" required>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="description"><?= old('description', $transaction['description']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Transaksi</button>
        <a href="<?= base_url('/cash') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>