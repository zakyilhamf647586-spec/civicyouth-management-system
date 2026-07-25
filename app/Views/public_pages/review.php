<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('public_pages/_assets') ?>

<?php
$statusLabels = $workflowLabels ?? [];
?>

<div class="public-cms-admin public-cms-review-page">

<div class="page-header public-cms-page-header">
    <div>
        <span class="public-cms-eyebrow">
            Editorial Review
        </span>

        <h2>Antrian Review Halaman</h2>

        <p>
            Periksa preview draft, berikan catatan revisi, setujui,
            lalu publikasikan halaman yang telah dinyatakan siap.
        </p>
    </div>

    <div class="public-cms-header-actions">
        <a
            href="<?= base_url('/website/pages') ?>"
            class="btn btn-secondary"
        >
            Kembali
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

<section class="public-cms-review-principles">
    <article>
        <b>1</b>
        <div>
            <strong>Buka Preview</strong>
            <span>Periksa seluruh isi seperti pengunjung.</span>
        </div>
    </article>

    <article>
        <b>2</b>
        <div>
            <strong>Nilai Kesiapan</strong>
            <span>Periksa fakta, bahasa, tautan, dan tampilan.</span>
        </div>
    </article>

    <article>
        <b>3</b>
        <div>
            <strong>Putuskan</strong>
            <span>Minta revisi atau setujui untuk publikasi.</span>
        </div>
    </article>
</section>

<?php if ($pages === []) : ?>
    <section class="public-cms-review-empty">
        <strong>Tidak ada halaman dalam antrian review</strong>

        <p>
            Halaman yang dikirim editor akan muncul di sini.
        </p>
    </section>
<?php else : ?>
    <section class="public-cms-review-list">
        <?php foreach ($pages as $page) : ?>
            <?php
            $status = (string) (
                $page['workflow_status'] ?? 'draft'
            );

            $submittedBy = !empty(
                $page['submitted_by']
            )
                ? (
                    $userNames[
                        (int) $page['submitted_by']
                    ] ?? 'Pengguna Portal'
                )
                : '-';

            $reviewedBy = !empty(
                $page['reviewed_by']
            )
                ? (
                    $userNames[
                        (int) $page['reviewed_by']
                    ] ?? 'Pengguna Portal'
                )
                : '-';
            ?>

            <article class="public-cms-review-card">
                <header>
                    <div>
                        <span><?= esc($page['route_path']) ?></span>
                        <h3><?= esc($page['name']) ?></h3>
                    </div>

                    <span class="workflow-status status-<?= esc(
                        $status,
                        'attr'
                    ) ?>">
                        <?= esc(
                            $statusLabels[$status]
                            ?? $status
                        ) ?>
                    </span>
                </header>

                <div class="public-cms-review-card__body">
                    <div class="public-cms-review-meta">
                        <div>
                            <span>Dikirim oleh</span>
                            <strong><?= esc($submittedBy) ?></strong>
                        </div>

                        <div>
                            <span>Waktu pengiriman</span>
                            <strong>
                                <?= !empty($page['submitted_at'])
                                    ? esc(date(
                                        'd M Y · H.i',
                                        strtotime(
                                            $page['submitted_at']
                                        )
                                    ))
                                    : '-' ?>
                            </strong>
                        </div>

                        <div>
                            <span>Catatan editor</span>
                            <strong>
                                <?= esc(
                                    $page['revision_note']
                                    ?: 'Tidak ada catatan'
                                ) ?>
                            </strong>
                        </div>
                    </div>

                    <?php if (
                        $status === 'changes_requested'
                    ) : ?>
                        <div class="public-cms-review-feedback">
                            <span>Revisi diminta oleh <?= esc(
                                $reviewedBy
                            ) ?></span>

                            <p>
                                <?= esc(
                                    $page['review_note']
                                    ?: 'Tidak ada catatan reviewer.'
                                ) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($status === 'approved') : ?>
                        <div class="public-cms-review-approved">
                            <strong>Draft telah disetujui</strong>

                            <span>
                                Publikasi dapat dilakukan tanpa
                                mengubah isi draft.
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <footer>
                    <a
                        href="<?= base_url(
                            '/website/pages/preview/'
                            . $page['page_key']
                        ) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-secondary"
                    >
                        Buka Preview ↗
                    </a>

                    <a
                        href="<?= base_url(
                            '/website/pages/edit/'
                            . $page['page_key']
                        ) ?>"
                        class="btn btn-secondary"
                    >
                        Lihat Editor
                    </a>
                </footer>

                <?php if (
                    $status === 'in_review'
                    && auth_can('website.pages.review')
                ) : ?>
                    <div class="public-cms-review-actions">
                        <form
                            action="<?= base_url(
                                '/website/pages/request-changes/'
                                . $page['page_key']
                            ) ?>"
                            method="post"
                        >
                            <?= csrf_field() ?>

                            <label>
                                Catatan revisi untuk editor
                            </label>

                            <textarea
                                name="review_note"
                                rows="4"
                                maxlength="1000"
                                placeholder="Jelaskan bagian yang harus diperbaiki secara spesifik."
                                required
                            ></textarea>

                            <button
                                type="submit"
                                class="btn btn-secondary"
                                onclick="return confirm(
                                    'Kembalikan halaman ini kepada editor?'
                                )"
                            >
                                Minta Revisi
                            </button>
                        </form>

                        <?php if (auth_can(
                            'website.pages.approve'
                        )) : ?>
                            <form
                                action="<?= base_url(
                                    '/website/pages/approve/'
                                    . $page['page_key']
                                ) ?>"
                                method="post"
                                class="public-cms-approve-form"
                                onsubmit="return confirm(
                                    'Setujui draft halaman ini?'
                                )"
                            >
                                <?= csrf_field() ?>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Setujui Halaman
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (
                    $status === 'approved'
                    && auth_can('website.pages.publish')
                ) : ?>
                    <div class="public-cms-publish-action">
                        <form
                            action="<?= base_url(
                                '/website/pages/publish/'
                                . $page['page_key']
                            ) ?>"
                            method="post"
                            onsubmit="return confirm(
                                'Publikasikan halaman yang sudah disetujui ini?'
                            )"
                        >
                            <?= csrf_field() ?>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Publikasikan ke Website
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

</div>

<?= $this->endSection() ?>
