<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$categoryLabels = [
    'collaboration' => 'Kolaborasi',
    'activity'      => 'Kegiatan',
    'social'        => 'Sosial',
    'business'      => 'Usaha/UMKM',
    'media'         => 'Media',
    'general'       => 'Umum',
];

$statusLabels = [
    'unread'   => 'Belum Dibaca',
    'read'     => 'Sudah Dibaca',
    'replied'  => 'Sudah Dibalas',
    'archived' => 'Diarsipkan',
];
?>

<div class="page-header">
    <div>
        <h2>Pesan Masuk</h2>

        <p>
            Kelola pesan, undangan, dan tawaran kolaborasi
            dari website publik.
        </p>
    </div>
</div>

<?php if (
    session()->getFlashdata('success')
) : ?>
    <div class="alert-success">
        <?= esc(
            session()->getFlashdata('success')
        ) ?>
    </div>
<?php endif; ?>

<?php if (
    session()->getFlashdata('error')
) : ?>
    <div class="alert-error">
        <?= esc(
            session()->getFlashdata('error')
        ) ?>
    </div>
<?php endif; ?>

<div class="filter-card">

    <form
        action="<?= base_url('/messages') ?>"
        method="get"
    >
        <div class="message-filter-grid">

            <div class="form-group">
                <label for="keyword">
                    Cari Pesan
                </label>

                <input
                    type="text"
                    id="keyword"
                    name="keyword"
                    value="<?= esc($keyword ?? '') ?>"
                    placeholder="Nama, nomor, subjek, atau isi pesan"
                >
            </div>

            <div class="form-group">
                <label for="category">
                    Kategori
                </label>

                <select
                    id="category"
                    name="category"
                >
                    <option value="">
                        Semua Kategori
                    </option>

                    <?php foreach (
                        $categoryLabels as
                        $value => $label
                    ) : ?>
                        <option
                            value="<?= esc($value) ?>"
                            <?= ($category ?? '') === $value
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                >
                    <option value="">
                        Semua Status
                    </option>

                    <?php foreach (
                        $statusLabels as
                        $value => $label
                    ) : ?>
                        <option
                            value="<?= esc($value) ?>"
                            <?= ($status ?? '') === $value
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="message-filter-actions">
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Terapkan
                </button>

                <a
                    href="<?= base_url('/messages') ?>"
                    class="btn btn-secondary"
                >
                    Reset
                </a>
            </div>

        </div>
    </form>

</div>

<div class="table-card">
    <div class="table-responsive">

        <table class="message-inbox-table">
            <thead>
                <tr>
                    <th>Pengirim</th>
                    <th>Subjek</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Diterima</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($messages)) : ?>

                    <?php foreach (
                        $messages as $message
                    ) : ?>

                        <tr
                            class="<?= $message['status'] === 'unread'
                                ? 'message-row-unread'
                                : '' ?>"
                        >
                            <td>
                                <div class="message-sender">
                                    <strong>
                                        <?= esc($message['name']) ?>
                                    </strong>

                                    <span>
                                        <?= esc($message['phone']) ?>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="message-subject">
                                    <strong>
                                        <?= esc($message['subject']) ?>
                                    </strong>

                                    <span>
                                        <?= esc(
                                            mb_strimwidth(
                                                $message['message'],
                                                0,
                                                80,
                                                '…'
                                            )
                                        ) ?>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="message-category-badge">
                                    <?= esc(
                                        $categoryLabels[
                                            $message['category']
                                        ] ?? 'Umum'
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <span
                                    class="message-status-badge
                                    message-status-<?= esc(
                                        $message['status']
                                    ) ?>"
                                >
                                    <?= esc(
                                        $statusLabels[
                                            $message['status']
                                        ] ?? '-'
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <?= !empty($message['created_at'])
                                    ? date(
                                        'd M Y H:i',
                                        strtotime(
                                            $message['created_at']
                                        )
                                    )
                                    : '-' ?>
                            </td>

                            <td>
                                <a
                                    href="<?= base_url(
                                        '/messages/'
                                        . $message['id']
                                    ) ?>"
                                    class="btn btn-primary"
                                >
                                    Buka
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else : ?>

                    <tr>
                        <td
                            colspan="6"
                            class="empty-text"
                        >
                            Pesan tidak ditemukan.
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <?php if (isset($pager)) : ?>
        <div class="pagination-wrapper">
            <?= $pager->links(
                'contact_messages',
                'default_full'
            ) ?>
        </div>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>