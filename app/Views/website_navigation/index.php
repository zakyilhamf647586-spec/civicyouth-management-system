<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('website_navigation/_assets') ?>

<div class="navigation-admin-page">

<div class="page-header navigation-page-header">
    <div>
        <span class="navigation-eyebrow">
            Website Structure
        </span>

        <h2>Navigasi Website</h2>

        <p>
            Atur urutan, label, tujuan tautan, dan visibilitas menu
            tanpa mengubah source code.
        </p>
    </div>

    <?php if (auth_can(
        'website.navigation.preview'
    )) : ?>
        <a
            href="<?= base_url(
                '/website/navigation/preview/header'
            ) ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-secondary"
        >
            Preview Navigasi ↗
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

<?php if (!$ready) : ?>
    <section class="navigation-not-ready">
        <strong>Navigation Manager belum aktif.</strong>

        <p>
            Jalankan migration sebelum mengelola menu website.
        </p>

        <code>php spark migrate</code>
    </section>
<?php else : ?>
    <section class="navigation-overview">
        <div>
            <span>Prinsip Pengelolaan</span>
            <h3>Draft dahulu, publikasikan setelah diperiksa</h3>
        </div>

        <ol>
            <li>
                <b>1</b>
                <span>Ubah susunan dan label</span>
            </li>
            <li>
                <b>2</b>
                <span>Simpan sebagai draft</span>
            </li>
            <li>
                <b>3</b>
                <span>Periksa melalui preview</span>
            </li>
            <li>
                <b>4</b>
                <span>Publikasikan ke website</span>
            </li>
        </ol>
    </section>

    <section class="navigation-menu-grid">
        <?php foreach ($menus as $menu) : ?>
            <?php
            $hasChanges = !empty(
                $menu['has_unpublished_changes']
            );
            ?>

            <article class="navigation-menu-card">
                <header>
                    <div class="navigation-menu-icon">
                        <?= $menu['menu_key'] === 'header'
                            ? 'H'
                            : 'F' ?>
                    </div>

                    <div>
                        <span>
                            <?= esc(
                                strtoupper($menu['menu_key'])
                            ) ?>
                        </span>

                        <h3><?= esc($menu['name']) ?></h3>
                    </div>

                    <span class="navigation-state <?= $hasChanges
                        ? 'is-draft'
                        : 'is-synced' ?>">
                        <?= $hasChanges
                            ? 'Ada Draft'
                            : 'Sinkron' ?>
                    </span>
                </header>

                <p><?= esc($menu['description']) ?></p>

                <dl>
                    <div>
                        <dt>Item Draft</dt>
                        <dd>
                            <?= (int) $menu['draft_count'] ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Item Publik</dt>
                        <dd>
                            <?= (int) $menu[
                                'published_count'
                            ] ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Terakhir Tayang</dt>
                        <dd>
                            <?= !empty(
                                $menu['published_at']
                            )
                                ? esc(date(
                                    'd M Y · H.i',
                                    strtotime(
                                        $menu[
                                            'published_at'
                                        ]
                                    )
                                ))
                                : 'Belum pernah' ?>
                        </dd>
                    </div>
                </dl>

                <footer>
                    <?php if (auth_can(
                        'website.navigation.update'
                    )) : ?>
                        <a
                            href="<?= base_url(
                                '/website/navigation/edit/'
                                . $menu['menu_key']
                            ) ?>"
                            class="btn btn-primary"
                        >
                            Kelola Menu
                        </a>
                    <?php endif; ?>

                    <?php if (auth_can(
                        'website.navigation.preview'
                    )) : ?>
                        <a
                            href="<?= base_url(
                                '/website/navigation/preview/'
                                . $menu['menu_key']
                            ) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-secondary"
                        >
                            Preview ↗
                        </a>
                    <?php endif; ?>
                </footer>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="navigation-safety-note">
        <strong>Perlindungan bawaan</strong>

        <p>
            Menu publik tetap memakai susunan lama sebagai fallback
            apabila migration belum tersedia atau database sedang
            bermasalah. Draft hanya terlihat oleh pengguna berizin.
        </p>
    </section>
<?php endif; ?>

</div>

<?= $this->endSection() ?>
