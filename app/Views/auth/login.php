<?php

$flashError   = session()->getFlashdata('error');
$flashErrors  = session()->getFlashdata('errors');
$flashSuccess = session()->getFlashdata('success');

$displayErrors = [];

if (is_array($flashErrors)) {
    $displayErrors = array_values($flashErrors);
}

if (is_string($flashError) && trim($flashError) !== '') {
    $displayErrors[] = $flashError;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="#04172d"
    >

    <meta
        name="description"
        content="Portal manajemen internal GARDA 01, Karang Taruna RW 01 Kelurahan Randugarut."
    >

    <title>
        Masuk — GARDA 01 Portal
    </title>

    <link
        rel="icon"
        type="image/png"
        href="<?= base_url('assets/img/logo-rw01.png') ?>"
    >

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/app.css') ?>"
    >
</head>

<body class="garda-login-body">

<div class="garda-login-shell">

    <!-- PANEL IDENTITAS -->
    <section class="garda-login-brand-panel">

        <div
            class="garda-login-brand-glow"
            aria-hidden="true"
        ></div>

        <div
            class="garda-login-brand-watermark"
            aria-hidden="true"
        >
            G01
        </div>

        <header class="garda-login-brand-header">

            <a
                href="<?= base_url('/') ?>"
                class="garda-login-brand"
                aria-label="Kembali ke website GARDA 01"
            >
                <img
                    src="<?= base_url('assets/img/logo-rw01.png') ?>"
                    alt="Logo GARDA 01"
                >

                <div>
                    <strong>GARDA 01</strong>
                    <span>Portal Manajemen</span>
                </div>
            </a>

            <a
                href="<?= base_url('/') ?>"
                class="garda-login-public-link"
            >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M3 12h18"></path>
                    <path d="M12 3a15 15 0 0 1 0 18"></path>
                    <path d="M12 3a15 15 0 0 0 0 18"></path>
                </svg>

                <span>Website Publik</span>
            </a>

        </header>

        <div class="garda-login-brand-content">

            <span class="garda-login-eyebrow">
                Karang Taruna RW 01 Randugarut
            </span>

            <h1>
                Ruang kerja digital<br>
                pengurus GARDA 01
            </h1>

            <p>
                Kelola organisasi, kegiatan, keuangan, publikasi,
                dokumentasi, dan komunikasi warga dalam satu portal
                yang tertib dan terintegrasi.
            </p>

            <div class="garda-login-values">

                <article>
                    <span>01</span>

                    <div>
                        <strong>Guyub</strong>
                        <small>Terhubung dalam kebersamaan.</small>
                    </div>
                </article>

                <article>
                    <span>02</span>

                    <div>
                        <strong>Bergerak</strong>
                        <small>Mengubah rencana menjadi aksi.</small>
                    </div>
                </article>

                <article>
                    <span>03</span>

                    <div>
                        <strong>Berdampak</strong>
                        <small>Menghadirkan manfaat nyata.</small>
                    </div>
                </article>

            </div>

        </div>

        <footer class="garda-login-brand-footer">

            <div>
                <span>GARDA 01</span>

                <small>
                    Generasi Aktif Randugarut
                </small>
            </div>

            <p>
                © <?= date('Y') ?> Karang Taruna RW 01
            </p>

        </footer>

    </section>

    <!-- PANEL FORM LOGIN -->
    <main class="garda-login-form-panel">

        <div class="garda-login-mobile-brand">

            <img
                src="<?= base_url('assets/img/logo-rw01.png') ?>"
                alt="Logo GARDA 01"
            >

            <div>
                <strong>GARDA 01</strong>
                <span>Portal Manajemen</span>
            </div>

        </div>

        <div class="garda-login-card">

            <div class="garda-login-card-heading">

                <span class="garda-login-card-kicker">
                    Portal Internal
                </span>

                <h2>Masuk ke akun Anda</h2>

                <p>
                    Gunakan akun pengurus yang telah terdaftar untuk
                    mengakses sistem manajemen organisasi.
                </p>

            </div>

            <?php if ($flashSuccess) : ?>
                <div
                    class="garda-login-alert success"
                    role="status"
                >
                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="m8 12 3 3 5-6"></path>
                    </svg>

                    <div>
                        <?= esc($flashSuccess) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($displayErrors)) : ?>
                <div
                    class="garda-login-alert error"
                    role="alert"
                >
                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 7v6"></path>
                        <path d="M12 17h.01"></path>
                    </svg>

                    <div>
                        <?php foreach ($displayErrors as $error) : ?>
                            <p><?= esc($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form
                action="<?= base_url('login') ?>"
                method="post"
                class="garda-login-form"
                id="gardaLoginForm"
            >
                <?= csrf_field() ?>

                <div class="garda-login-field">

                    <label for="email">
                        Alamat Email
                    </label>

                    <div class="garda-login-input-wrapper">

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <rect
                                x="3"
                                y="5"
                                width="18"
                                height="14"
                                rx="2"
                            ></rect>

                            <path d="m4 7 8 6 8-6"></path>
                        </svg>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= esc(old('email')) ?>"
                            placeholder="nama@organisasi.id"
                            autocomplete="username"
                            inputmode="email"
                            maxlength="150"
                            required
                            autofocus
                        >

                    </div>

                </div>

                <div class="garda-login-field">

                    <div class="garda-login-label-row">
                        <label for="password">
                            Kata Sandi
                        </label>

                        <span>
                            Akses khusus pengurus
                        </span>
                    </div>

                    <div class="garda-login-input-wrapper">

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path d="M7 10V8a5 5 0 0 1 10 0v2"></path>

                            <rect
                                x="5"
                                y="10"
                                width="14"
                                height="10"
                                rx="2.5"
                            ></rect>

                            <circle
                                cx="12"
                                cy="15"
                                r="1.2"
                            ></circle>
                        </svg>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            minlength="6"
                            required
                        >

                        <button
                            type="button"
                            class="garda-login-password-toggle"
                            id="gardaPasswordToggle"
                            aria-label="Tampilkan kata sandi"
                            aria-pressed="false"
                        >
                            <svg
                                class="garda-password-eye-open"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                ></path>

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.5"
                                ></circle>
                            </svg>

                            <svg
                                class="garda-password-eye-closed"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path d="m3 3 18 18"></path>

                                <path
                                    d="M10.7 6.2A9.7 9.7 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.1 2.8"
                                ></path>

                                <path
                                    d="M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 6 9.5 6a9.6 9.6 0 0 0 4.1-.9"
                                ></path>

                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path>
                            </svg>
                        </button>

                    </div>

                </div>

                <button
                    type="submit"
                    class="garda-login-submit"
                    id="gardaLoginSubmit"
                >
                    <span
                        class="garda-login-submit-text"
                        id="gardaLoginSubmitText"
                    >
                        Masuk ke Portal
                    </span>

                    <span
                        class="garda-login-submit-loader"
                        id="gardaLoginSubmitLoader"
                        aria-hidden="true"
                    ></span>

                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                        class="garda-login-submit-arrow"
                    >
                        <path d="M5 12h14"></path>
                        <path d="m14 7 5 5-5 5"></path>
                    </svg>
                </button>

            </form>

            <div class="garda-login-security-note">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6l-7-3Z"></path>
                    <path d="m9 12 2 2 4-5"></path>
                </svg>

                <div>
                    <strong>Akses internal organisasi</strong>

                    <p>
                        Jangan membagikan email, kata sandi, maupun
                        akses portal kepada pihak yang tidak berwenang.
                    </p>
                </div>

            </div>

        </div>

        <div class="garda-login-form-footer">

            <a href="<?= base_url('/') ?>">
                ← Kembali ke Website GARDA 01
            </a>

            <span>
                Guyub • Bergerak • Berdampak
            </span>

        </div>

    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById(
        'gardaPasswordToggle'
    );

    const loginForm = document.getElementById(
        'gardaLoginForm'
    );

    const submitButton = document.getElementById(
        'gardaLoginSubmit'
    );

    const submitText = document.getElementById(
        'gardaLoginSubmitText'
    );

    if (passwordInput && passwordToggle) {
        passwordToggle.addEventListener('click', function () {
            const isVisible =
                passwordInput.type === 'text';

            passwordInput.type = isVisible
                ? 'password'
                : 'text';

            passwordToggle.classList.toggle(
                'password-visible',
                !isVisible
            );

            passwordToggle.setAttribute(
                'aria-pressed',
                String(!isVisible)
            );

            passwordToggle.setAttribute(
                'aria-label',
                isVisible
                    ? 'Tampilkan kata sandi'
                    : 'Sembunyikan kata sandi'
            );

            passwordInput.focus();
        });
    }

    if (loginForm && submitButton) {
        loginForm.addEventListener('submit', function () {
            submitButton.disabled = true;
            submitButton.classList.add('loading');

            if (submitText) {
                submitText.textContent =
                    'Memverifikasi akun...';
            }
        });
    }
});
</script>

</body>
</html>