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

            <h2>Tambah Akun Pengguna</h2>

            <p>
                Buat akses terpisah untuk Ketua, Sekretaris,
                Bendahara, atau Pengurus.
            </p>
        </div>

        <a
            href="<?= base_url('/users') ?>"
            class="btn btn-secondary"
        >
            Kembali
        </a>
    </div>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert-error">
            <?php foreach (
                session()->getFlashdata('errors') as $error
            ) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form
        action="<?= base_url('/users/store') ?>"
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
                        Gunakan email pribadi atau email organisasi
                        yang benar-benar dapat dikenali.
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
                        value="<?= esc(old('name')) ?>"
                        maxlength="150"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= esc(old('email')) ?>"
                        maxlength="150"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="role_id">Peran Pengguna</label>
                    <select
                        id="role_id"
                        name="role_id"
                        required
                    >
                        <option value="">Pilih Peran</option>

                        <?php foreach ($roles as $role) : ?>
                            <option
                                value="<?= (int) $role['id'] ?>"
                                <?= (string) old('role_id')
                                    === (string) $role['id']
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= esc($role['role_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status Awal</label>
                    <select id="status" name="status">
                        <option
                            value="active"
                            <?= old('status', 'active') === 'active'
                                ? 'selected'
                                : '' ?>
                        >
                            Aktif
                        </option>

                        <option
                            value="inactive"
                            <?= old('status') === 'inactive'
                                ? 'selected'
                                : '' ?>
                        >
                            Nonaktif
                        </option>
                    </select>
                </div>
            </div>
        </section>

        <section class="form-card">
            <div class="form-card-header">
                <div>
                    <span>Keamanan</span>
                    <h3>Kata sandi awal</h3>
                    <p>
                        Gunakan minimal delapan karakter dan sampaikan
                        kepada pemilik akun secara pribadi.
                    </p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirm">
                        Konfirmasi Kata Sandi
                    </label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>
            </div>
        </section>

        <section class="user-role-guide">
            <strong>Panduan peran</strong>

            <div>
                <?php foreach ($roles as $role) : ?>
                    <article>
                        <span><?= esc($role['role_name']) ?></span>
                        <p>
                            <?= esc(
                                $role['description']
                                ?: 'Hak akses mengikuti kebijakan Portal.'
                            ) ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                Simpan Akun
            </button>

            <a
                href="<?= base_url('/users') ?>"
                class="btn btn-secondary"
            >
                Batal
            </a>
        </div>
    </form>

</div>

<?= $this->endSection() ?>
