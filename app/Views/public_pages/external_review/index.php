<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('public_pages/_assets') ?>

<?php
$statusLabels = $statusLabels ?? [];
$decisionLabels = $decisionLabels ?? [];
$newLink = session()->getFlashdata(
    'external_preview_link'
);
?>

<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/admin-external-page-review.css'
    ) ?>?v=<?= filemtime(
        FCPATH
        . 'assets/css/admin-external-page-review.css'
    ) ?>"
>

<div class="external-review-admin">

<div class="page-header public-cms-page-header">
    <div>
        <span class="public-cms-eyebrow">
            Secure External Review
        </span>

        <h2>
            Tautan Review — <?= esc($page['name']) ?>
        </h2>

        <p>
            Bagikan snapshot draft tertentu tanpa memberikan akses
            Portal. Tautan memiliki masa berlaku, batas akses, dan
            dapat dicabut kapan saja.
        </p>
    </div>

    <div class="public-cms-header-actions">
        <a
            href="<?= base_url(
                '/website/pages/edit/' . $pageKey
            ) ?>"
            class="btn btn-secondary"
        >
            Kembali ke Editor
        </a>

        <a
            href="<?= base_url(
                '/website/pages/revisions/'
                . $pageKey
            ) ?>"
            class="btn btn-secondary"
        >
            Riwayat Versi
        </a>
    </div>
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

<?php if ($newLink) : ?>
    <section class="external-review-new-link">
        <div>
            <span>Tautan Baru — Salin Sekarang</span>

            <h3>
                Token mentah hanya ditampilkan satu kali
            </h3>

            <p>
                Database hanya menyimpan hash token. Simpan tautan
                melalui kanal yang aman dan jangan memublikasikannya
                di grup terbuka.
            </p>
        </div>

        <div class="external-review-copy-row">
            <input
                id="external-review-link"
                type="text"
                value="<?= esc($newLink, 'attr') ?>"
                readonly
            >

            <button
                type="button"
                class="btn btn-primary"
                data-copy-external-review
                data-copy-target="#external-review-link"
            >
                Salin Tautan
            </button>
        </div>
    </section>
<?php endif; ?>

<section class="external-review-security-note">
    <strong>Tautan mengarah ke snapshot yang dikunci</strong>

    <p>
        Perubahan draft setelah tautan dibuat tidak mengubah isi yang
        sedang ditinjau reviewer. Keputusan eksternal juga tidak
        memublikasikan halaman dan tidak menggantikan approval internal.
    </p>
</section>

<section class="external-review-create-card">
    <header>
        <span>Buat Tautan Baru</span>
        <h3>Tentukan penerima dan batas akses</h3>
    </header>

    <form
        action="<?= base_url(
            '/website/pages/external-review/'
            . $pageKey
            . '/create'
        ) ?>"
        method="post"
    >
        <?= csrf_field() ?>

        <div class="external-review-form-grid">
            <div class="form-group is-wide">
                <label for="label">
                    Label Tautan
                </label>

                <input
                    id="label"
                    name="label"
                    type="text"
                    maxlength="120"
                    value="<?= esc(old(
                        'label',
                        'Review '
                        . $page['name']
                        . ' — '
                        . date('d M Y')
                    )) ?>"
                    required
                >

                <small>
                    Digunakan untuk membedakan penerima atau tujuan.
                </small>
            </div>

            <div class="form-group">
                <label for="reviewer_name">
                    Nama Reviewer
                </label>

                <input
                    id="reviewer_name"
                    name="reviewer_name"
                    type="text"
                    maxlength="120"
                    value="<?= esc(old(
                        'reviewer_name'
                    )) ?>"
                    placeholder="Opsional"
                >
            </div>

            <div class="form-group">
                <label for="reviewer_email">
                    Email Reviewer
                </label>

                <input
                    id="reviewer_email"
                    name="reviewer_email"
                    type="email"
                    maxlength="150"
                    value="<?= esc(old(
                        'reviewer_email'
                    )) ?>"
                    placeholder="Opsional"
                >
            </div>

            <div class="form-group">
                <label for="expires_in_days">
                    Berlaku Selama
                </label>

                <select
                    id="expires_in_days"
                    name="expires_in_days"
                >
                    <?php foreach ([
                        1 => '1 hari',
                        3 => '3 hari',
                        7 => '7 hari',
                        14 => '14 hari',
                        30 => '30 hari',
                    ] as $value => $label) : ?>
                        <option
                            value="<?= $value ?>"
                            <?= (int) old(
                                'expires_in_days',
                                7
                            ) === $value
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="max_views">
                    Batas Tampilan
                </label>

                <input
                    id="max_views"
                    name="max_views"
                    type="number"
                    min="5"
                    max="500"
                    value="<?= (int) old(
                        'max_views',
                        100
                    ) ?>"
                    required
                >
            </div>
        </div>

        <label class="external-review-checkbox">
            <input
                type="checkbox"
                name="allow_decision"
                value="1"
                <?= old(
                    'allow_decision',
                    '1'
                ) ? 'checked' : '' ?>
            >

            <span>
                Izinkan reviewer memilih “Layak” atau
                “Memerlukan Perbaikan”.
            </span>
        </label>

        <div class="external-review-form-actions">
            <button
                type="submit"
                class="btn btn-primary"
                <?= empty(
                    $page['has_unpublished_changes']
                ) ? 'disabled' : '' ?>
            >
                Buat Tautan Aman
            </button>

            <?php if (empty(
                $page['has_unpublished_changes']
            )) : ?>
                <span>
                    Simpan perubahan draft terlebih dahulu.
                </span>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="external-review-list-card">
    <header>
        <div>
            <span>Issued Links</span>
            <h3>Riwayat tautan review</h3>
        </div>

        <small><?= count($tokens) ?> tautan</small>
    </header>

    <?php if ($tokens === []) : ?>
        <div class="external-review-empty">
            <strong>Belum ada tautan review</strong>

            <p>
                Buat tautan saat draft siap diperiksa pihak di luar
                Portal.
            </p>
        </div>
    <?php else : ?>
        <div class="external-review-token-list">
            <?php foreach ($tokens as $token) : ?>
                <?php
                $status = (string) (
                    $token['derived_status']
                    ?? 'active'
                );

                $latestReview = is_array(
                    $token['latest_review'] ?? null
                )
                    ? $token['latest_review']
                    : null;
                ?>

                <article class="external-review-token">
                    <header>
                        <div>
                            <span>
                                Snapshot Versi
                                #<?= (int) (
                                    $token[
                                        'revision_version_number'
                                    ] ?? 0
                                ) ?>
                            </span>

                            <h4><?= esc($token['label']) ?></h4>
                        </div>

                        <span class="external-token-status status-<?= esc(
                            $status,
                            'attr'
                        ) ?>">
                            <?= esc(
                                $statusLabels[$status]
                                ?? $status
                            ) ?>
                        </span>
                    </header>

                    <dl>
                        <div>
                            <dt>Reviewer</dt>
                            <dd>
                                <?= esc(
                                    $token['reviewer_name']
                                    ?: 'Belum ditentukan'
                                ) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Kedaluwarsa</dt>
                            <dd>
                                <?= esc(date(
                                    'd M Y · H.i',
                                    strtotime(
                                        $token['expires_at']
                                    )
                                )) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Akses</dt>
                            <dd>
                                <?= (int) $token['view_count'] ?>
                                /
                                <?= (int) $token['max_views'] ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Terakhir Dibuka</dt>
                            <dd>
                                <?= !empty(
                                    $token[
                                        'last_accessed_at'
                                    ]
                                )
                                    ? esc(date(
                                        'd M Y · H.i',
                                        strtotime(
                                            $token[
                                                'last_accessed_at'
                                            ]
                                        )
                                    ))
                                    : 'Belum pernah' ?>
                            </dd>
                        </div>
                    </dl>

                    <?php if ($latestReview) : ?>
                        <div class="external-review-response">
                            <span>
                                <?= esc(
                                    $decisionLabels[
                                        $latestReview[
                                            'decision'
                                        ]
                                    ]
                                    ?? $latestReview['decision']
                                ) ?>
                                ·
                                <?= esc(
                                    $latestReview[
                                        'reviewer_name'
                                    ]
                                ) ?>
                            </span>

                            <p>
                                <?= esc(
                                    $latestReview['comment']
                                ) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <footer>
                        <small>
                            Dibuat
                            <?= esc(date(
                                'd M Y · H.i',
                                strtotime(
                                    $token['created_at']
                                )
                            )) ?>
                        </small>

                        <?php if (!in_array(
                            $status,
                            ['revoked', 'expired'],
                            true
                        )) : ?>
                            <form
                                action="<?= base_url(
                                    '/website/pages/external-review/'
                                    . $pageKey
                                    . '/'
                                    . $token['id']
                                    . '/revoke'
                                ) ?>"
                                method="post"
                                onsubmit="return confirm(
                                    'Cabut tautan review ini?'
                                )"
                            >
                                <?= csrf_field() ?>

                                <button
                                    type="submit"
                                    class="btn btn-secondary"
                                >
                                    Cabut Tautan
                                </button>
                            </form>
                        <?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

</div>

<script
    src="<?= base_url(
        'assets/js/admin-external-page-review.js'
    ) ?>?v=<?= filemtime(
        FCPATH
        . 'assets/js/admin-external-page-review.js'
    ) ?>"
    defer
></script>

<?= $this->endSection() ?>
