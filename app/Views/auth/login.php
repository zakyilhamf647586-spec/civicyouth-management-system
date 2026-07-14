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
                <h2>Masuk Sistem Internal</h2>
                <p>Karang Taruna RW 01 Kelurahan Randugarut</p>
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

                <a href="<?= base_url('/') ?>" class="auth-home-link">
                    ← Kembali ke Beranda
                </a>
            </form>

            <?php if (ENVIRONMENT === 'development') : ?>
                <div class="auth-note">
                    <strong>Akun Demo Lokal</strong><br>
                    Email: admin@civicyouth.local<br>
                    Password: admin123
                </div>
            <?php endif; ?>

            <div class="auth-footer">
                CivicYouth Management System<br>
                Karang Taruna RW 01 Kelurahan Randugarut
            </div>
        </div>
    </div>
</div>

</body>
</html>