<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#04172d">

    <title>Akses Dibatasi — GARDA 01 Portal</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            display: grid;
            place-items: center;
            color: #102a43;
            background:
                radial-gradient(
                    circle at 90% 5%,
                    rgba(217, 163, 22, 0.14),
                    transparent 30%
                ),
                #f7f3e9;
            font-family: Arial, Helvetica, sans-serif;
        }

        .access-card {
            width: min(100%, 660px);
            padding: clamp(28px, 6vw, 54px);
            border: 1px solid rgba(16, 42, 67, 0.10);
            border-top: 5px solid #d9a316;
            border-radius: 25px;
            background: #ffffff;
            box-shadow: 0 30px 80px rgba(4, 23, 45, 0.14);
        }

        .access-code {
            color: #b88408;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        h1 {
            margin: 12px 0 14px;
            color: #04172d;
            font-size: clamp(30px, 6vw, 48px);
            line-height: 1.08;
        }

        p {
            margin: 0;
            color: #627d98;
            font-size: 15px;
            line-height: 1.75;
        }

        .access-detail {
            margin-top: 24px;
            padding: 17px 18px;
            border-radius: 14px;
            background: #f5f8fb;
            border-left: 4px solid #d9a316;
        }

        .access-detail strong,
        .access-detail span {
            display: block;
        }

        .access-detail strong {
            color: #102a43;
            font-size: 13px;
        }

        .access-detail span {
            margin-top: 5px;
            color: #627d98;
            font-size: 12px;
            line-height: 1.55;
        }

        .access-actions {
            margin-top: 28px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .access-actions a {
            min-height: 44px;
            padding: 11px 17px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 850;
        }

        .access-actions .primary {
            color: #ffffff;
            background: #04172d;
        }

        .access-actions .secondary {
            color: #04172d;
            background: #ffffff;
            border: 1px solid #d9e2ec;
        }

        @media (max-width: 520px) {
            .access-actions {
                flex-direction: column;
            }

            .access-actions a {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<main class="access-card">
    <span class="access-code">403 · Akses Dibatasi</span>

    <h1>Peran Anda belum memiliki izin.</h1>

    <p>
        Sistem GARDA 01 melindungi data dan tindakan penting berdasarkan
        tanggung jawab setiap pengurus. Akses ini tidak menghapus data dan
        bukan berarti akun Anda bermasalah.
    </p>

    <div class="access-detail">
        <strong><?= esc($roleName ?? 'Tidak diketahui') ?></strong>
        <span>
            Izin yang diperlukan:
            <?= esc($permissionLabel ?? 'akses khusus') ?>.
        </span>
    </div>

    <div class="access-actions">
        <a class="primary" href="<?= base_url('/dashboard') ?>">
            Kembali ke Dashboard
        </a>

        <a class="secondary" href="<?= base_url('/') ?>">
            Lihat Website Publik
        </a>
    </div>
</main>

</body>
</html>
