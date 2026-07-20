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

<?php
$statusLabels = [
    'active' => 'Aktif',
    'inactive' => 'Nonaktif',
];

$statusClasses = [
    'active' => 'badge-success',
    'inactive' => 'badge-secondary',
];

$roleClasses = [
    'admin' => 'role-admin',
    'ketua' => 'role-ketua',
    'sekretaris' => 'role-sekretaris',
    'bendahara' => 'role-bendahara',
    'pengurus' => 'role-pengurus',
];
?>

<div class="user-management-page">

    <div class="page-header">
        <div>
            <span class="user-management-kicker">
                Keamanan Portal
            </span>

            <h2>Manajemen Akun</h2>

            <p>
                Buat akun pengurus, tetapkan peran, dan kendalikan
                status akses GARDA 01 Portal.
            </p>
        </div>

        <?php if (auth_can('users.create')) : ?>
            <a
                href="<?= base_url('/users/create') ?>"
                class="btn btn-primary"
            >
                + Tambah Akun
            </a>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert-error">
            <?= esc(session()->getFlashdata('error')) ?>
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

    <section class="user-account-summary-grid">
        <article>
            <span>Total Akun</span>
            <strong><?= esc($statistics['total'] ?? 0) ?></strong>
            <small>Seluruh pengguna terdaftar.</small>
        </article>

        <article>
            <span>Akun Aktif</span>
            <strong><?= esc($statistics['active'] ?? 0) ?></strong>
            <small>Dapat masuk ke Portal.</small>
        </article>

        <article>
            <span>Akun Nonaktif</span>
            <strong><?= esc($statistics['inactive'] ?? 0) ?></strong>
            <small>Akses login sedang diblokir.</small>
        </article>

        <article class="featured">
            <span>Admin Aktif</span>
            <strong>
                <?= esc($statistics['active_admins'] ?? 0) ?>
            </strong>
            <small>Penanggung jawab akses penuh.</small>
        </article>
    </section>

    <div class="filter-card">
        <form action="<?= base_url('/users') ?>" method="get">
            <div class="filter-grid user-account-filter-grid">
                <div class="form-group">
                    <label for="keyword">Cari Akun</label>
                    <input
                        type="text"
                        id="keyword"
                        name="keyword"
                        value="<?= esc($keyword ?? '') ?>"
                        placeholder="Nama, email, atau peran"
                    >
                </div>

                <div class="form-group">
                    <label for="role_id">Peran</label>
                    <select id="role_id" name="role_id">
                        <option value="">Semua Peran</option>

                        <?php foreach ($roles as $role) : ?>
                            <option
                                value="<?= (int) $role['id'] ?>"
                                <?= (string) ($selectedRole ?? '')
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
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua Status</option>

                        <?php foreach (
                            $statusLabels as $value => $label
                        ) : ?>
                            <option
                                value="<?= esc($value) ?>"
                                <?= ($selectedStatus ?? '') === $value
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        Terapkan
                    </button>

                    <a
                        href="<?= base_url('/users') ?>"
                        class="btn btn-secondary"
                    >
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div
            class="table-responsive"
            tabindex="0"
            aria-label="Tabel akun pengguna dapat digeser ke samping"
        >
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pengguna</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Diperbarui</th>
                        <th width="230">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($users)) : ?>
                        <?php
                        $currentPage = $pager->getCurrentPage('users');
                        $perPage = $pager->getPerPage('users');
                        $number = 1 + (
                            $perPage * ($currentPage - 1)
                        );
                        ?>

                        <?php foreach ($users as $user) : ?>
                            <?php
                            $roleKey = mb_strtolower(
                                trim((string) (
                                    $user['role_name'] ?? ''
                                ))
                            );

                            $isCurrent =
                                (int) $user['id']
                                === (int) $currentUserId;

                            $status = $user['status'] ?? 'inactive';
                            ?>

                            <tr>
                                <td><?= $number++ ?></td>

                                <td>
                                    <div class="user-account-identity">
                                        <span>
                                            <?= esc(
                                                mb_strtoupper(
                                                    mb_substr(
                                                        $user['name'],
                                                        0,
                                                        1
                                                    )
                                                )
                                            ) ?>
                                        </span>

                                        <div>
                                            <strong>
                                                <?= esc($user['name']) ?>
                                            </strong>

                                            <?php if ($isCurrent) : ?>
                                                <small class="current-account">
                                                    Akun Anda
                                                </small>
                                            <?php endif; ?>

                                            <p><?= esc($user['email']) ?></p>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="user-role-badge <?= esc(
                                            $roleClasses[$roleKey]
                                            ?? 'role-default'
                                        ) ?>"
                                    >
                                        <?= esc(
                                            $user['role_name']
                                            ?? 'Belum Ada Peran'
                                        ) ?>
                                    </span>

                                    <?php if (!empty(
                                        $user['role_description']
                                    )) : ?>
                                        <small class="user-role-description">
                                            <?= esc(
                                                mb_strimwidth(
                                                    $user[
                                                        'role_description'
                                                    ],
                                                    0,
                                                    72,
                                                    '…'
                                                )
                                            ) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span
                                        class="badge <?= esc(
                                            $statusClasses[$status]
                                            ?? 'badge-secondary'
                                        ) ?>"
                                    >
                                        <?= esc(
                                            $statusLabels[$status]
                                            ?? ucfirst($status)
                                        ) ?>
                                    </span>
                                </td>

                                <td>
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
                                </td>

                                <td>
                                    <?php if (auth_can(
                                        'users.update'
                                    )) : ?>
                                        <a
                                            href="<?= base_url(
                                                '/users/edit/'
                                                . $user['id']
                                            ) ?>"
                                            class="btn btn-warning"
                                        >
                                            Edit
                                        </a>
                                    <?php endif; ?>

                                    <?php if (
                                        auth_can('users.status')
                                        && !$isCurrent
                                    ) : ?>
                                        <form
                                            action="<?= base_url(
                                                '/users/'
                                                . $user['id']
                                                . '/status'
                                            ) ?>"
                                            method="post"
                                            class="inline-action-form"
                                            onsubmit="return confirm('Ubah status akses akun ini?')"
                                        >
                                            <?= csrf_field() ?>

                                            <input
                                                type="hidden"
                                                name="status"
                                                value="<?= $status === 'active'
                                                    ? 'inactive'
                                                    : 'active' ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn <?= $status === 'active'
                                                    ? 'btn-danger'
                                                    : 'btn-primary' ?>"
                                            >
                                                <?= $status === 'active'
                                                    ? 'Nonaktifkan'
                                                    : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (
                                        $isCurrent
                                        && !auth_can('users.update')
                                    ) : ?>
                                        <span class="badge badge-secondary">
                                            Akun aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">
                                <div class="user-account-empty-state">
                                    <strong>
                                        Tidak ada akun yang cocok
                                    </strong>

                                    <p>
                                        Ubah filter pencarian atau tambahkan
                                        akun pengurus baru.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($users)) : ?>
        <div class="pagination-wrapper">
            <?= $pager->links('users', 'default_full') ?>
        </div>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>
