<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$categoryLabels = [
    'collaboration' => 'Kolaborasi dan Kemitraan',
    'activity'      => 'Undangan atau Kegiatan',
    'social'        => 'Sosial dan Kemanusiaan',
    'business'      => 'Usaha dan UMKM',
    'media'         => 'Media dan Publikasi',
    'general'       => 'Informasi Umum',
];

$statusLabels = [
    'unread'   => 'Belum Dibaca',
    'read'     => 'Sudah Dibaca',
    'replied'  => 'Sudah Dibalas',
    'archived' => 'Diarsipkan',
];

$telephoneNumber = preg_replace(
    '/[^0-9+]/',
    '',
    $message['phone'] ?? ''
);
?>

<div class="page-header">
    <div>
        <span class="message-detail-kicker">
            Pesan Masuk
        </span>

        <h2><?= esc($message['subject']) ?></h2>

        <p>
            Dikirim
            <?= !empty($message['created_at'])
                ? date(
                    'd M Y, H:i',
                    strtotime($message['created_at'])
                )
                : '-' ?>
        </p>
    </div>

    <a
        href="<?= base_url('/messages') ?>"
        class="btn btn-secondary"
    >
        Kembali
    </a>
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

<section class="message-detail-layout">

    <article class="message-detail-card">

        <div class="message-detail-header">

            <div class="message-detail-avatar">
                <?= esc(
                    mb_strtoupper(
                        mb_substr(
                            $message['name'],
                            0,
                            1
                        )
                    )
                ) ?>
            </div>

            <div>
                <strong>
                    <?= esc($message['name']) ?>
                </strong>

                <span>
                    <?= esc(
                        $categoryLabels[
                            $message['category']
                        ] ?? 'Informasi Umum'
                    ) ?>
                </span>
            </div>

        </div>

        <div class="message-detail-body">
            <?= nl2br(
                esc($message['message'])
            ) ?>
        </div>

    </article>

    <aside class="message-detail-sidebar">

        <div class="message-contact-card">
            <h3>Informasi Pengirim</h3>

            <dl>
                <div>
                    <dt>Nama</dt>
                    <dd><?= esc($message['name']) ?></dd>
                </div>

                <div>
                    <dt>WhatsApp</dt>

                    <dd>
                        <a
                            href="tel:<?= esc(
                                $telephoneNumber,
                                'attr'
                            ) ?>"
                        >
                            <?= esc($message['phone']) ?>
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Email</dt>

                    <dd>
                        <?php if (
                            !empty($message['email'])
                        ) : ?>
                            <a
                                href="mailto:<?= esc(
                                    $message['email'],
                                    'attr'
                                ) ?>"
                            >
                                <?= esc(
                                    $message['email']
                                ) ?>
                            </a>
                        <?php else : ?>
                            Tidak dicantumkan
                        <?php endif; ?>
                    </dd>
                </div>

                <div>
                    <dt>Kategori</dt>

                    <dd>
                        <?= esc(
                            $categoryLabels[
                                $message['category']
                            ] ?? '-'
                        ) ?>
                    </dd>
                </div>

                <div>
                    <dt>Status</dt>

                    <dd>
                        <?= esc(
                            $statusLabels[
                                $message['status']
                            ] ?? '-'
                        ) ?>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="message-status-card">
            <h3>Perbarui Status</h3>

            <form
                action="<?= base_url(
                    '/messages/'
                    . $message['id']
                    . '/status'
                ) ?>"
                method="post"
            >
                <?= csrf_field() ?>

                <div class="form-group">
                    <select
                        name="status"
                        required
                    >
                        <?php foreach (
                            $statusLabels as
                            $value => $label
                        ) : ?>
                            <option
                                value="<?= esc($value) ?>"
                                <?= $message['status'] === $value
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Simpan Status
                </button>
            </form>
        </div>

    </aside>

</section>

<?= $this->endSection() ?>