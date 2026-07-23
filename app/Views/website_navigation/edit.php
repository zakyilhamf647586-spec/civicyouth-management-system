<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('website_navigation/_assets') ?>

<?php
$oldItems = old('items');

if (is_array($oldItems)) {
    $items = array_values($oldItems);
}

$hasChanges = !empty(
    $menu['has_unpublished_changes']
);

$hasPublishedVersion = !empty(
    $menu['published_at']
);
?>

<div class="navigation-admin-page navigation-editor">

<div class="page-header navigation-page-header">
    <div>
        <span class="navigation-eyebrow">
            Navigation Manager
        </span>

        <h2><?= esc($definition['name']) ?></h2>

        <p><?= esc($definition['description']) ?></p>
    </div>

    <div class="navigation-header-actions">
        <a
            href="<?= base_url(
                '/website/navigation'
            ) ?>"
            class="btn btn-secondary"
        >
            Kembali
        </a>

        <?php if (auth_can(
            'website.navigation.preview'
        )) : ?>
            <a
                href="<?= base_url(
                    '/website/navigation/preview/'
                    . $menuKey
                ) ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-secondary"
            >
                Preview Draft ↗
            </a>
        <?php endif; ?>

        <?php if (
            auth_can('website.navigation.publish')
            && $hasChanges
        ) : ?>
            <form
                action="<?= base_url(
                    '/website/navigation/publish/'
                    . $menuKey
                ) ?>"
                method="post"
                onsubmit="return confirm(
                    'Publikasikan susunan navigasi ini?'
                )"
            >
                <?= csrf_field() ?>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Publikasikan
                </button>
            </form>
        <?php endif; ?>
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

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (
            session()->getFlashdata('errors') as $error
        ) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<section class="navigation-editor-status">
    <div>
        <span>Menu</span>
        <strong><?= esc($definition['name']) ?></strong>
    </div>

    <div>
        <span>Status Draft</span>
        <strong>
            <?= $hasChanges
                ? 'Ada perubahan'
                : 'Sinkron dengan publik' ?>
        </strong>
    </div>

    <div>
        <span>Versi Publik</span>
        <strong>
            <?= $hasPublishedVersion
                ? 'Tersedia'
                : 'Belum tersedia' ?>
        </strong>
    </div>

    <div>
        <span>Batas Item</span>
        <strong><?= (int) $maximumItems ?></strong>
    </div>
</section>

<form
    action="<?= base_url(
        '/website/navigation/update/' . $menuKey
    ) ?>"
    method="post"
    data-navigation-editor
    data-maximum-items="<?= (int) $maximumItems ?>"
>
    <?= csrf_field() ?>

    <section class="navigation-editor-card">
        <header>
            <div>
                <span>Susunan Draft</span>
                <h3>Item navigasi</h3>
                <p>
                    Gunakan tombol naik dan turun untuk mengatur
                    urutan. Item nonaktif tetap tersimpan tetapi
                    tidak tampil di website.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-secondary"
                data-navigation-add
            >
                + Tambah Item
            </button>
        </header>

        <div
            class="navigation-item-list"
            data-navigation-list
        >
            <?php foreach (
                $items as $index => $item
            ) : ?>
                <article
                    class="navigation-item-row"
                    data-navigation-row
                >
                    <div class="navigation-item-order">
                        <button
                            type="button"
                            data-navigation-up
                            aria-label="Naikkan item"
                            title="Naikkan"
                        >
                            ↑
                        </button>

                        <span data-navigation-number>
                            <?= $index + 1 ?>
                        </span>

                        <button
                            type="button"
                            data-navigation-down
                            aria-label="Turunkan item"
                            title="Turunkan"
                        >
                            ↓
                        </button>
                    </div>

                    <div class="navigation-item-fields">
                        <input
                            type="hidden"
                            name="items[<?= $index ?>][item_key]"
                            value="<?= esc(
                                $item['item_key'] ?? '',
                                'attr'
                            ) ?>"
                            data-item-key
                        >

                        <div class="form-group">
                            <label>Label Menu</label>

                            <input
                                type="text"
                                name="items[<?= $index ?>][label]"
                                value="<?= esc(
                                    $item['label'] ?? '',
                                    'attr'
                                ) ?>"
                                maxlength="80"
                                placeholder="Contoh: Kegiatan"
                                required
                                data-item-label
                            >
                        </div>

                        <div class="form-group navigation-url-field">
                            <label>URL Tujuan</label>

                            <input
                                type="text"
                                name="items[<?= $index ?>][url]"
                                value="<?= esc(
                                    $item['url'] ?? '',
                                    'attr'
                                ) ?>"
                                placeholder="/kegiatan atau https://..."
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>Halaman Aktif</label>

                            <input
                                type="text"
                                name="items[<?= $index ?>][active_pages]"
                                value="<?= esc(
                                    implode(
                                        ', ',
                                        is_array(
                                            $item[
                                                'active_pages'
                                            ] ?? null
                                        )
                                            ? $item[
                                                'active_pages'
                                            ]
                                            : []
                                    ),
                                    'attr'
                                ) ?>"
                                placeholder="activities, activity_detail"
                            >

                            <small>
                                Opsional. Pisahkan dengan koma.
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Target</label>

                            <select
                                name="items[<?= $index ?>][target]"
                            >
                                <option
                                    value="self"
                                    <?= (
                                        $item['target'] ?? 'self'
                                    ) === 'self'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Tab yang sama
                                </option>

                                <option
                                    value="blank"
                                    <?= (
                                        $item['target'] ?? 'self'
                                    ) === 'blank'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Tab baru
                                </option>
                            </select>
                        </div>

                        <?php if ($menuKey === 'header') : ?>
                            <div class="form-group">
                                <label>Gaya</label>

                                <select
                                    name="items[<?= $index ?>][style]"
                                >
                                    <option
                                        value="default"
                                        <?= (
                                            $item['style']
                                            ?? 'default'
                                        ) === 'default'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Menu biasa
                                    </option>

                                    <option
                                        value="portal"
                                        <?= (
                                            $item['style']
                                            ?? 'default'
                                        ) === 'portal'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Tombol Portal
                                    </option>
                                </select>
                            </div>
                        <?php else : ?>
                            <input
                                type="hidden"
                                name="items[<?= $index ?>][style]"
                                value="default"
                            >
                        <?php endif; ?>

                        <label class="navigation-enabled-toggle">
                            <input
                                type="checkbox"
                                name="items[<?= $index ?>][enabled]"
                                value="1"
                                <?= !array_key_exists(
                                    'enabled',
                                    $item
                                ) || !empty(
                                    $item['enabled']
                                )
                                    ? 'checked'
                                    : '' ?>
                            >

                            <span>Aktif</span>
                        </label>
                    </div>

                    <button
                        type="button"
                        class="navigation-remove-button"
                        data-navigation-remove
                    >
                        Hapus
                    </button>
                </article>
            <?php endforeach; ?>
        </div>

        <div
            class="navigation-empty-state"
            data-navigation-empty
            <?= $items !== [] ? 'hidden' : '' ?>
        >
            <strong>Belum ada item navigasi</strong>

            <p>
                Tambahkan minimal satu item aktif sebelum menyimpan.
            </p>
        </div>
    </section>

    <section class="navigation-editor-footer">
        <div class="form-group">
            <label for="revision_note">
                Catatan Revisi
            </label>

            <input
                id="revision_note"
                type="text"
                name="revision_note"
                maxlength="255"
                value="<?= esc(
                    old(
                        'revision_note',
                        $menu['revision_note'] ?? ''
                    ),
                    'attr'
                ) ?>"
                placeholder="Contoh: Menambahkan tautan Kontak"
            >
        </div>

        <div class="navigation-save-actions">
            <?php if (
                $hasPublishedVersion
                && $hasChanges
            ) : ?>
                <button
                    type="submit"
                    form="navigation-restore-form"
                    class="btn btn-secondary"
                >
                    Pulihkan Versi Publik
                </button>
            <?php endif; ?>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Simpan Draft
            </button>
        </div>
    </section>
</form>

<?php if (
    $hasPublishedVersion
    && $hasChanges
) : ?>
    <form
        id="navigation-restore-form"
        action="<?= base_url(
            '/website/navigation/restore/' . $menuKey
        ) ?>"
        method="post"
        onsubmit="return confirm(
            'Pulihkan draft ke versi navigasi yang sedang tayang?'
        )"
    >
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

<template data-navigation-template>
    <article
        class="navigation-item-row"
        data-navigation-row
    >
        <div class="navigation-item-order">
            <button
                type="button"
                data-navigation-up
                aria-label="Naikkan item"
                title="Naikkan"
            >
                ↑
            </button>

            <span data-navigation-number>1</span>

            <button
                type="button"
                data-navigation-down
                aria-label="Turunkan item"
                title="Turunkan"
            >
                ↓
            </button>
        </div>

        <div class="navigation-item-fields">
            <input
                type="hidden"
                name="items[__INDEX__][item_key]"
                value=""
                data-item-key
            >

            <div class="form-group">
                <label>Label Menu</label>
                <input
                    type="text"
                    name="items[__INDEX__][label]"
                    maxlength="80"
                    placeholder="Contoh: Mitra"
                    required
                    data-item-label
                >
            </div>

            <div class="form-group navigation-url-field">
                <label>URL Tujuan</label>
                <input
                    type="text"
                    name="items[__INDEX__][url]"
                    placeholder="/halaman atau https://..."
                    required
                >
            </div>

            <div class="form-group">
                <label>Halaman Aktif</label>
                <input
                    type="text"
                    name="items[__INDEX__][active_pages]"
                    placeholder="contact"
                >
                <small>Opsional. Pisahkan dengan koma.</small>
            </div>

            <div class="form-group">
                <label>Target</label>
                <select
                    name="items[__INDEX__][target]"
                >
                    <option value="self">
                        Tab yang sama
                    </option>
                    <option value="blank">
                        Tab baru
                    </option>
                </select>
            </div>

            <?php if ($menuKey === 'header') : ?>
                <div class="form-group">
                    <label>Gaya</label>
                    <select
                        name="items[__INDEX__][style]"
                    >
                        <option value="default">
                            Menu biasa
                        </option>
                        <option value="portal">
                            Tombol Portal
                        </option>
                    </select>
                </div>
            <?php else : ?>
                <input
                    type="hidden"
                    name="items[__INDEX__][style]"
                    value="default"
                >
            <?php endif; ?>

            <label class="navigation-enabled-toggle">
                <input
                    type="checkbox"
                    name="items[__INDEX__][enabled]"
                    value="1"
                    checked
                >
                <span>Aktif</span>
            </label>
        </div>

        <button
            type="button"
            class="navigation-remove-button"
            data-navigation-remove
        >
            Hapus
        </button>
    </article>
</template>

</div>

<?= $this->endSection() ?>
