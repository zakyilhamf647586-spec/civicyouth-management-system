<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('public_pages/_assets') ?>

<?php
$sourceLabels = $sourceLabels ?? [];
?>

<div class="public-cms-admin public-cms-revision-page">

<div class="page-header public-cms-page-header">
    <div>
        <span class="public-cms-eyebrow">
            Version Control
        </span>

        <h2>
            Riwayat Versi <?= esc($page['name']) ?>
        </h2>

        <p>
            Setiap snapshot menyimpan metadata SEO, status section,
            dan seluruh isi halaman pada saat review atau publikasi.
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
                '/website/pages/preview/' . $pageKey
            ) ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-secondary"
        >
            Preview Draft ↗
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

<section class="public-cms-revision-summary">
    <article>
        <span>Total Versi</span>
        <strong><?= count($revisions) ?></strong>
    </article>

    <article>
        <span>Halaman</span>
        <strong><?= esc($page['route_path']) ?></strong>
    </article>

    <article>
        <span>Status Saat Ini</span>
        <strong>
            <?= esc(
                ucfirst(str_replace(
                    '_',
                    ' ',
                    (string) (
                        $page['workflow_status']
                        ?? 'draft'
                    )
                ))
            ) ?>
        </strong>
    </article>

    <article>
        <span>Versi Publik Terakhir</span>
        <strong>
            <?= !empty($page['published_at'])
                ? esc(date(
                    'd M Y · H.i',
                    strtotime($page['published_at'])
                ))
                : 'Belum tersedia' ?>
        </strong>
    </article>
</section>

<section class="public-cms-revision-guidance">
    <strong>Pemulihan bersifat aman</strong>

    <p>
        Memilih versi lama tidak langsung mengganti website publik.
        Sistem memulihkannya sebagai draft baru yang tetap harus
        dipreview, direview, disetujui, dan dipublikasikan.
    </p>
</section>

<?php if ($revisions === []) : ?>
    <section class="public-cms-review-empty">
        <strong>Belum ada riwayat versi</strong>

        <p>
            Snapshot akan dibuat saat draft dikirim untuk review
            atau halaman dipublikasikan.
        </p>
    </section>
<?php else : ?>
    <section class="public-cms-revision-list">
        <?php foreach ($revisions as $revision) : ?>
            <?php
            $createdBy = !empty(
                $revision['created_by']
            )
                ? (
                    $userNames[
                        (int) $revision['created_by']
                    ] ?? 'Pengguna Portal'
                )
                : 'Sistem';

            $sourceType = (string) (
                $revision['source_type'] ?? ''
            );
            ?>

            <article class="public-cms-revision-card">
                <div class="public-cms-revision-number">
                    <span>Versi</span>
                    <strong>
                        #<?= (int) (
                            $revision['version_number']
                            ?? 0
                        ) ?>
                    </strong>
                </div>

                <div class="public-cms-revision-main">
                    <header>
                        <div>
                            <span>
                                <?= esc(
                                    $sourceLabels[$sourceType]
                                    ?? $sourceType
                                ) ?>
                            </span>

                            <h3>
                                <?= esc(
                                    $revision['revision_note']
                                    ?: 'Tanpa catatan versi'
                                ) ?>
                            </h3>
                        </div>

                        <span class="revision-mode mode-<?= esc(
                            $revision['snapshot_mode'],
                            'attr'
                        ) ?>">
                            <?= $revision['snapshot_mode']
                                === 'published'
                                    ? 'Versi Publik'
                                    : 'Versi Draft' ?>
                        </span>
                    </header>

                    <dl>
                        <div>
                            <dt>Dibuat oleh</dt>
                            <dd><?= esc($createdBy) ?></dd>
                        </div>

                        <div>
                            <dt>Waktu</dt>
                            <dd>
                                <?= !empty(
                                    $revision['created_at']
                                )
                                    ? esc(date(
                                        'd M Y · H.i',
                                        strtotime(
                                            $revision[
                                                'created_at'
                                            ]
                                        )
                                    ))
                                    : '-' ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Status saat snapshot</dt>
                            <dd>
                                <?= esc(ucfirst(str_replace(
                                    '_',
                                    ' ',
                                    (string) (
                                        $revision[
                                            'workflow_status'
                                        ] ?? '-'
                                    )
                                ))) ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="public-cms-revision-actions">
                    <a
                        href="<?= base_url(
                            '/website/pages/revisions/'
                            . $pageKey
                            . '/'
                            . $revision['id']
                        ) ?>"
                        class="btn btn-secondary"
                    >
                        Lihat Detail
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

</div>

<?= $this->endSection() ?>
