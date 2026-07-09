<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - CivicYouth</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 380px;
            background: #ffffff;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
        }

        .brand {
            margin-bottom: 24px;
        }

        .brand h1 {
            margin: 0;
            font-size: 26px;
            color: #102a43;
        }

        .brand p {
            margin-top: 8px;
            color: #627d98;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #334e68;
            font-size: 14px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            font-size: 14px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0f5132;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #0b3d26;
        }

        .alert {
            padding: 12px;
            background: #ffe3e3;
            color: #842029;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .hint {
            margin-top: 18px;
            font-size: 13px;
            color: #829ab1;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">
        <h1>CivicYouth</h1>
        <p>Sistem Manajemen Karang Taruna RW 01</p>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('/login') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Masukkan email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>

        <button type="submit">Masuk Dashboard</button>
    </form>

    <div class="hint">
        Email: admin@civicyouth.local<br>
        Password: admin123
    </div>
</div>

</body>
</html>