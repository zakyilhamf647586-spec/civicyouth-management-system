<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('public_pages/_assets') ?>

<div class="public-cms-admin public-cms-index">

<div class="page-header public-cms-page-header">
    <div>
        <span class="public-cms-eyebrow">
            Website Content Management
        </span>

        <h2>Kelola Halaman Publik</h2>

        <p>
            Kelola draft, preview, metadata SEO, dan konten utama
            Beranda, Profil, serta Kontak tanpa mengubah source code.
        </p>
    </div>

    <a
        href="<?= base_url('/') ?>"
        target="_blank"
        rel="noopener noreferrer"
        class="btn btn-secondary"
    >
        Lihat Website ↗
    </a>
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
                Website publik tetap memakai konten fallback
                sehingga tidak akan menjadi kosong.
            </p>
        </div>

        <code>php spark migrate</code>
    </section>
<?php else : ?>

    <section class="public-cms-intro-card">
        <div>
            <span>Alur Aman</span>
            <h3>Draft → Preview → Publish</h3>
            <p>
                Menyimpan perubahan tidak langsung mengubah website.
                Versi publik baru berubah setelah tombol
                Publikasikan ditekan.
            </p>
        </div>

        <ol>
            <li>
                <b>1</b>
                <span>Edit draft</span>
            </li>

            <li>
                <b>2</b>
                <span>Periksa preview</span>
            </li>

            <li>
                <b>3</b>
                <span>Publikasikan</span>
            </li>
        </ol>
    </section>

    <section class="public-cms-page-grid">
        <?php foreach ($pages as $page) : ?>
            <?php
            $isPublished = !empty($page['published_at']);
            $hasChanges = !empty(
                $page['has_unpublished_changes']
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
                        <?php if ($isPublished) : ?>
                            <span class="is-published">
                                Terpublikasi
                            </span>
                        <?php else : ?>
                            <span class="is-draft">
                                Draft
                            </span>
                        <?php endif; ?>

                        <?php if ($hasChanges) : ?>
                            <small>
                                Ada perubahan belum tayang
                            </small>
                        <?php else : ?>
                            <small>
                                Draft sama dengan versi publik
                            </small>
                        <?php endif; ?>
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
                                    ? esc(
                                        date(
                                            'd M Y · H.i',
                                            strtotime(
                                                $page['updated_at']
                                            )
                                        )
                                    )
                                    : '-' ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Terakhir Tayang</dt>
                            <dd>
                                <?= !empty($page['published_at'])
                                    ? esc(
                                        date(
                                            'd M Y · H.i',
                                            strtotime(
                                                $page['published_at']
                                            )
                                        )
                                    )
                                    : 'Belum pernah' ?>
                            </dd>
                        </div>
                    </dl>

                    <div class="public-cms-page-card__seo">
                        <span>Judul SEO</span>

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
                </footer>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="public-cms-scope-note">
        <strong>Scope Fase 2A</strong>

        <p>
            Fondasi ini mengelola konten terstruktur untuk Beranda,
            Profil, dan Kontak. Program, Kegiatan, Pengurus,
            navigasi, footer, serta Media Library tetap memakai
            modulnya masing-masing dan akan dikembangkan pada fase
            berikutnya.
        </p>
    </section>

<?php endif; ?>

</div>

<?= $this->endSection() ?>
