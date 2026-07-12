<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Import Data Anggota</h2>
        <p>Upload data anggota dari file Excel sesuai format template.</p>
    </div>

    <a href="<?= base_url('/members') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <div class="import-guide">
        <h3>Format File Excel</h3>
        <p>Gunakan template agar proses import berjalan aman dan rapi.</p>

        <table>
            <thead>
                <tr>
                    <th>Kolom</th>
                    <th>Contoh Isi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nama Lengkap</td>
                    <td>Zaky Ilham Ferdiansyah</td>
                    <td>Wajib diisi</td>
                </tr>
                <tr>
                    <td>RT</td>
                    <td>RT 01</td>
                    <td>Gunakan RT 01, RT 02, RT 03, atau RT 04</td>
                </tr>
                <tr>
                    <td>Gender</td>
                    <td>male / female</td>
                    <td>male untuk laki-laki, female untuk perempuan</td>
                </tr>
                <tr>
                    <td>Tanggal Lahir</td>
                    <td>2005-01-31</td>
                    <td>Format tahun-bulan-tanggal</td>
                </tr>
                <tr>
                    <td>No HP</td>
                    <td>081234567890</td>
                    <td>Boleh kosong</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>Randugarut RW 01</td>
                    <td>Boleh kosong</td>
                </tr>
                <tr>
                    <td>Jabatan/Posisi</td>
                    <td>Anggota</td>
                    <td>Jika kosong otomatis menjadi Anggota</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>active / inactive</td>
                    <td>Jika kosong otomatis active</td>
                </tr>
            </tbody>
        </table>

        <br>

        <a href="<?= base_url('/imports/members/template') ?>" class="btn btn-primary">
            Download Template Excel
        </a>
    </div>
</div>

<br>

<div class="form-card">
    <form action="<?= base_url('/imports/members') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Upload File Excel</label>
            <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" required>
        </div>

        <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin mengimport data anggota dari file ini?')">
            Import Data Anggota
        </button>

        <a href="<?= base_url('/members') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>