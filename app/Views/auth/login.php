<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Karang Taruna RW 01</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<div class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <img src="<?= base_url('assets/img/logo-rw01.png') ?>" alt="Logo Karang Taruna RW 01" class="auth-logo">
                <h1>Karang Taruna RW 01</h1>
                <p>Sistem Manajemen Organisasi Pemuda<br>Kelurahan Randugarut</p>
            </div>

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="auth-alert">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('/login') ?>" method="post" class="auth-form">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= old('email') ?>"
                        placeholder="Masukkan email admin"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                </div>

                <button type="submit" class="auth-btn">Masuk ke Dashboard</button>
            </form>

            <div class="auth-note">
                <strong>Akun Admin Default</strong><br>
                Email: admin@civicyouth.local<br>
                Password: admin123
            </div>

            <div class="auth-footer">
                CivicYouth Management System · Studi Kasus Karang Taruna RW 01
            </div>
        </div>
    </div>
</div>

</body>
</html>