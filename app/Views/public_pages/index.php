<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('public_pages/_assets') ?>

<?php
$statusLabels = $workflowLabels ?? [
    'draft' => 'Draft',
    'in_review' => 'Menunggu Review',
    'changes_requested' => 'Perlu Revisi',
    'approved' => 'Disetujui',
    'published' => 'Terpublikasi',
];
?>

<div class="public-cms-admin public-cms-index">

<div class="page-header public-cms-page-header">
    <div>
        <span class="public-cms-eyebrow">
            Website Content Management
        </span>

        <h2>Kelola Halaman Publik</h2>

        <p>
            Kelola draft, preview, review, persetujuan, metadata SEO,
            dan konten utama Beranda, Profil, serta Kontak.
        </p>
    </div>

    <div class="public-cms-header-actions">
        <?php if (
            $reviewReady
            && auth_can('website.pages.review')
        ) : ?>
            <a
                href="<?= base_url(
                    '/website/pages/review'
                ) ?>"
                class="btn btn-primary"
            >
                Antrian Review
            </a>
        <?php endif; ?>

        <a
            href="<?= base_url('/') ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-secondary"
        >
            Lihat Website ↗
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

<?php if (!$ready) : ?>
    <section class="public-cms-migration-notice">
        <div>
            <span>Migration Diperlukan</span>

            <h3>Fondasi CMS publik belum aktif</h3>

            <p>
                Jalankan migration terbaru terlebih dahulu.
                Website publik tetap memakai konten fallback.
            </p>
        </div>

        <code>php spark migrate</code>
    </section>
<?php else : ?>

    <?php if (!$reviewReady) : ?>
        <section class="public-cms-migration-notice">
            <div>
                <span>Workflow Review Belum Aktif</span>

                <h3>Migration Fase 3A diperlukan</h3>

                <p>
                    CMS tetap dapat digunakan dalam mode lama sampai
                    migration workflow review dijalankan.
                </p>
            </div>

            <code>php spark migrate</code>
        </section>
    <?php endif; ?>

    <section class="public-cms-intro-card is-workflow">
        <div>
            <span>Alur Editorial</span>
            <h3>
                Draft → Preview → Review → Approve → Publish
            </h3>

            <p>
                Editor menyiapkan perubahan, reviewer memeriksa,
                dan halaman hanya dapat diterbitkan setelah disetujui.
            </p>
        </div>

        <ol>
            <li>
                <b>1</b>
                <span>Edit draft</span>
            </li>

            <li>
                <b>2</b>
                <span>Preview</span>
            </li>

            <li>
                <b>3</b>
                <span>Kirim review</span>
            </li>

            <li>
                <b>4</b>
                <span>Setujui</span>
            </li>

            <li>
                <b>5</b>
                <span>Publikasikan</span>
            </li>
        </ol>
    </section>

    <?php if ($reviewReady) : ?>
        <section class="public-cms-workflow-summary">
            <?php foreach ([
                'draft',
                'in_review',
                'changes_requested',
                'approved',
                'published',
            ] as $status) : ?>
                <article class="status-<?= esc(
                    $status,
                    'attr'
                ) ?>">
                    <span>
                        <?= esc(
                            $statusLabels[$status]
                            ?? $status
                        ) ?>
                    </span>

                    <strong>
                        <?= (int) (
                            $workflowCounts[$status] ?? 0
                        ) ?>
                    </strong>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <section class="public-cms-page-grid">
        <?php foreach ($pages as $page) : ?>
            <?php
            $hasChanges = !empty(
                $page['has_unpublished_changes']
            );

            $workflowStatus = (string) (
                $page['workflow_status'] ?? 'draft'
            );
            ?>

            <article class="public-cms-page-card">
                <header>
                    <div class="public-cms-page-card__icon">
                        <?= esc(
                            mb_strtoupper(
                                mb_substr(
                                    $page['name'],
                                    0,
                                    1
                                )
                            )
                        ) ?>
                    </div>

                    <div>
                        <span>
                            <?= esc($page['route_path']) ?>
                        </span>

                        <h3><?= esc($page['name']) ?></h3>
                    </div>

                    <div class="public-cms-page-card__status">
                        <span class="workflow-status status-<?= esc(
                            $workflowStatus,
                            'attr'
                        ) ?>">
                            <?= esc(
                                $statusLabels[$workflowStatus]
                                ?? $workflowStatus
                            ) ?>
                        </span>

                        <small>
                            <?= $hasChanges
                                ? 'Ada perubahan belum tayang'
                                : 'Draft sama dengan versi publik' ?>
                        </small>
                    </div>
                </header>

                <div class="public-cms-page-card__body">
                    <dl>
                        <div>
                            <dt>Section</dt>
                            <dd>
                                <?= (int) (
                                    $page['section_count'] ?? 0
                                ) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Terakhir Diperbarui</dt>
                            <dd>
                                <?= !empty($page['updated_at'])
                                    ? esc(date(
                                        'd M Y · H.i',
                                        strtotime(
                                            $page['updated_at']
                                        )
                                    ))
                                    : '-' ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Terakhir Tayang</dt>
                            <dd>
                                <?= !empty($page['published_at'])
                                    ? esc(date(
                                        'd M Y · H.i',
                                        strtotime(
                                            $page['published_at']
                                        )
                                    ))
                                    : 'Belum pernah' ?>
                            </dd>
                        </div>

                        <?php if ($revisionReady) : ?>
                            <div>
                                <dt>Riwayat Versi</dt>
                                <dd>
                                    <?= (int) (
                                        $page['revision_count']
                                        ?? 0
                                    ) ?>
                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>

                    <?php if (
                        $workflowStatus
                            === 'changes_requested'
                        && !empty($page['review_note'])
                    ) : ?>
                        <div class="public-cms-review-note">
                            <span>Catatan Reviewer</span>
                            <p><?= esc($page['review_note']) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="public-cms-page-card__seo">
                        <span>Judul SEO Draft</span>

                        <strong>
                            <?= esc(
                                $page['draft_title']
                                ?: 'Belum diisi'
                            ) ?>
                        </strong>
                    </div>
                </div>

                <footer>
                    <?php if (auth_can(
                        'website.pages.update'
                    )) : ?>
                        <a
                            href="<?= base_url(
                                '/website/pages/edit/'
                                . $page['page_key']
                            ) ?>"
                            class="btn btn-primary"
                        >
                            Kelola Halaman
                        </a>
                    <?php endif; ?>

                    <?php if (auth_can(
                        'website.pages.preview'
                    )) : ?>
                        <a
                            href="<?= base_url(
                                '/website/pages/preview/'
                                . $page['page_key']
                            ) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-secondary"
                        >
                            Preview Draft ↗
                        </a>
                    <?php endif; ?>

                    <?php if (
                        $revisionReady
                        && auth_can(
                            'website.pages.revisions.view'
                        )
                    ) : ?>
                        <a
                            href="<?= base_url(
                                '/website/pages/revisions/'
                                . $page['page_key']
                            ) ?>"
                            class="btn btn-secondary"
                        >
                            Riwayat Versi
                        </a>
                    <?php endif; ?>

                    <?php if (auth_can(
                        'website.pages.external_preview.manage'
                    )) : ?>
                        <a
                            href="<?= base_url(
                                '/website/pages/external-review/'
                                . $page['page_key']
                            ) ?>"
                            class="btn btn-secondary"
                        >
                            Review Eksternal
                        </a>
                    <?php endif; ?>
                </footer>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="public-cms-scope-note">
        <strong>Workflow Fase 3A</strong>

        <p>
            Halaman yang sedang ditinjau atau sudah disetujui
            dikunci dari penyuntingan. Snapshot versi dibuat saat
            draft dikirim untuk review dan ketika halaman
            dipublikasikan. Versi lama dapat dipulihkan hanya sebagai
            draft baru.
        </p>
    </section>

<?php endif; ?>

</div>

<?= $this->endSection() ?>
