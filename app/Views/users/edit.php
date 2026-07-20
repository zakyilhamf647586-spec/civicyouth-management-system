<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/admin-user-management.css'
    ) ?>?v=<?= filemtime(
        FCPATH . 'assets/css/admin-user-management.css'
    ) ?>"
>

<div class="user-management-page">

    <div class="page-header">
        <div>
            <span class="user-management-kicker">
                Keamanan Portal
            </span>

            <h2>Edit Akun Pengguna</h2>

            <p>
                Perbarui identitas, peran, status, dan kredensial
                akun secara aman.
            </p>
        </div>

        <a
            href="<?= base_url('/users') ?>"
            class="btn btn-secondary"
        >
            Kembali
        </a>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert-error">
            <?php foreach (
                session()->getFlashdata('errors') as $error
            ) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($isCurrentUser) : ?>
        <div class="user-account-current-notice">
            <strong>Ini adalah akun yang sedang digunakan.</strong>

            <p>
                Nama, email, dan kata sandi dapat diperbarui.
                Peran serta status tidak dapat diubah dari akun sendiri.
            </p>
        </div>
    <?php endif; ?>

    <div class="user-account-edit-layout">

        <form
            action="<?= base_url(
                '/users/update/' . $user['id']
            ) ?>"
            method="post"
            class="user-account-form"
        >
            <?= csrf_field() ?>

            <section class="form-card">
                <div class="form-card-header">
                    <div>
                        <span>Identitas Akun</span>
                        <h3>Informasi pengguna</h3>
                        <p>
                            Perubahan nama dan email akun aktif akan
                            langsung diterapkan pada session pengguna.
                        </p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nama Pengguna</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?= esc(
                                old('name', $user['name'])
                            ) ?>"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Alamat Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= esc(
                                old('email', $user['email'])
                            ) ?>"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="role_id">Peran Pengguna</label>
                        <select
                            id="role_id"
                            name="role_id"
                            <?= $isCurrentUser ? 'disabled' : '' ?>
                            required
                        >
                            <?php foreach ($roles as $role) : ?>
                                <option
                                    value="<?= (int) $role['id'] ?>"
                                    <?= (string) old(
                                        'role_id',
                                        $user['role_id']
                                    ) === (string) $role['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($role['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($isCurrentUser) : ?>
                            <input
                                type="hidden"
                                name="role_id"
                                value="<?= (int) $user['role_id'] ?>"
                            >
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="status">Status Akun</label>
                        <select
                            id="status"
                            name="status"
                            <?= $isCurrentUser ? 'disabled' : '' ?>
                            required
                        >
                            <option
                                value="active"
                                <?= old(
                                    'status',
                                    $user['status']
                                ) === 'active'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Aktif
                            </option>

                            <option
                                value="inactive"
                                <?= old(
                                    'status',
                                    $user['status']
                                ) === 'inactive'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Nonaktif
                            </option>
                        </select>

                        <?php if ($isCurrentUser) : ?>
                            <input
                                type="hidden"
                                name="status"
                                value="active"
                            >
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="form-card">
                <div class="form-card-header">
                    <div>
                        <span>Opsional</span>
                        <h3>Ganti kata sandi sekaligus</h3>
                        <p>
                            Kosongkan kedua kolom apabila tidak ingin
                            mengubah kata sandi.
                        </p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="password">
                            Kata Sandi Baru
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input
                            type="password"
                            id="password_confirm"
                            name="password_confirm"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                        >
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a
                    href="<?= base_url('/users') ?>"
                    class="btn btn-secondary"
                >
                    Batal
                </a>
            </div>
        </form>

        <?php if (auth_can(
            'users.reset_password'
        )) : ?>
            <aside class="user-account-security-panel">
                <span>Reset Kredensial</span>

                <h3>Atur ulang kata sandi</h3>

                <p>
                    Gunakan ketika pengguna lupa kata sandi.
                    Kata sandi lama tidak dapat dilihat kembali.
                </p>

                <form
                    action="<?= base_url(
                        '/users/'
                        . $user['id']
                        . '/reset-password'
                    ) ?>"
                    method="post"
                >
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="new_password">
                            Kata Sandi Baru
                        </label>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirm">
                            Konfirmasi Kata Sandi
                        </label>
                        <input
                            type="password"
                            id="new_password_confirm"
                            name="new_password_confirm"
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="btn btn-warning"
                        onclick="return confirm('Reset kata sandi akun ini?')"
                    >
                        Reset Kata Sandi
                    </button>
                </form>

                <div class="user-account-metadata">
                    <div>
                        <span>Dibuat</span>
                        <strong>
                            <?= !empty($user['created_at'])
                                ? esc(
                                    date(
                                        'd M Y, H.i',
                                        strtotime(
                                            $user['created_at']
                                        )
                                    )
                                )
                                : '-' ?>
                        </strong>
                    </div>

                    <div>
                        <span>Diperbarui</span>
                        <strong>
                            <?= !empty($user['updated_at'])
                                ? esc(
                                    date(
                                        'd M Y, H.i',
                                        strtotime(
                                            $user['updated_at']
                                        )
                                    )
                                )
                                : '-' ?>
                        </strong>
                    </div>
                </div>
            </aside>
        <?php endif; ?>

    </div>

</div>

<?= $this->endSection() ?>
