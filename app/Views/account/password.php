<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/admin-account-security.css'
    ) ?>?v=<?= filemtime(
        FCPATH . 'assets/css/admin-account-security.css'
    ) ?>"
>

<div class="account-security-page">
    <header class="account-security-heading">
        <div>
            <span>Keamanan Personal</span>
            <h2>Lindungi akses Portal Anda</h2>
            <p>
                Kelola kata sandi dan cabut sesi lama tanpa mengubah
                peran atau data organisasi.
            </p>
        </div>

        <div class="account-security-identity">
            <strong><?= esc($user['name']) ?></strong>
            <span><?= esc($user['email']) ?></span>
            <small><?= esc($user['role_name'] ?? 'Pengguna Portal') ?></small>
        </div>
    </header>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="account-security-alert success" role="status">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="account-security-alert error" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="account-security-alert error" role="alert">
            <?php foreach (
                session()->getFlashdata('errors') as $error
            ) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($mustChangePassword) : ?>
        <section class="account-security-required" role="alert">
            <span>Perlu Diselesaikan</span>
            <h3>Buat kata sandi pribadi sebelum melanjutkan</h3>
            <p>
                Akun ini memakai kata sandi awal atau hasil reset Admin.
                Setelah diperbarui, akses Portal akan terbuka dan seluruh
                sesi lama otomatis tidak berlaku.
            </p>
        </section>
    <?php endif; ?>

    <?php if (!$securityReady) : ?>
        <section class="account-security-required" role="alert">
            <span>Migration Diperlukan</span>
            <h3>Fondasi pencabutan sesi belum aktif</h3>
            <p>
                Jalankan <code>php spark migrate</code>, lalu muat ulang
                halaman ini sebelum mengelola sesi.
            </p>
        </section>
    <?php endif; ?>

    <div class="account-security-grid">
        <form
            action="<?= base_url('/account/password') ?>"
            method="post"
            class="account-security-card primary"
        >
            <?= csrf_field() ?>

            <div class="account-security-card-heading">
                <span>01 / Kredensial</span>
                <h3>Perbarui kata sandi</h3>
                <p>
                    Gunakan minimal 12 karakter. Frasa yang panjang,
                    unik, dan mudah Anda ingat lebih aman.
                </p>
            </div>

            <div class="account-security-field">
                <label for="current_password">Kata Sandi Saat Ini</label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    maxlength="72"
                    autocomplete="current-password"
                    required
                >
            </div>

            <div class="account-security-field-row">
                <div class="account-security-field">
                    <label for="new_password">Kata Sandi Baru</label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        minlength="12"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="account-security-field">
                    <label for="new_password_confirm">Konfirmasi</label>
                    <input
                        type="password"
                        id="new_password_confirm"
                        name="new_password_confirm"
                        minlength="12"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Kata Sandi Baru
            </button>
        </form>

        <aside class="account-security-side">
            <section class="account-security-card status">
                <div class="account-security-card-heading">
                    <span>02 / Status</span>
                    <h3>Ringkasan keamanan</h3>
                </div>

                <dl class="account-security-facts">
                    <div>
                        <dt>Kata sandi diperbarui</dt>
                        <dd>
                            <?= !empty($user['password_changed_at'])
                                ? esc(date(
                                    'd M Y, H.i',
                                    strtotime($user['password_changed_at'])
                                ))
                                : 'Belum tercatat' ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Login terakhir</dt>
                        <dd>
                            <?= !empty($user['last_login_at'])
                                ? esc(date(
                                    'd M Y, H.i',
                                    strtotime($user['last_login_at'])
                                ))
                                : 'Belum tercatat' ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Status akun</dt>
                        <dd><?= ($user['status'] ?? '') === 'active'
                            ? 'Aktif'
                            : 'Nonaktif' ?></dd>
                    </div>
                </dl>
            </section>

            <form
                action="<?= base_url('/account/sessions/revoke') ?>"
                method="post"
                class="account-security-card revoke"
                onsubmit="return confirm('Cabut seluruh sesi lain untuk akun ini?')"
            >
                <?= csrf_field() ?>

                <div class="account-security-card-heading">
                    <span>03 / Sesi</span>
                    <h3>Keluar dari perangkat lain</h3>
                    <p>
                        Semua browser lain akan diminta masuk kembali.
                        Perangkat ini tetap aktif dengan sesi baru.
                    </p>
                </div>

                <div class="account-security-field">
                    <label for="revoke_current_password">
                        Konfirmasi Kata Sandi Saat Ini
                    </label>
                    <input
                        type="password"
                        id="revoke_current_password"
                        name="current_password"
                        maxlength="72"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-secondary"
                    <?= !$securityReady ? 'disabled' : '' ?>
                >
                    Cabut Sesi Lain
                </button>
            </form>
        </aside>
    </div>
</div>

<?= $this->endSection() ?>
