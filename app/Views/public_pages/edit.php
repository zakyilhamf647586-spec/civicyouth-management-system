<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('public_pages/_assets') ?>

<?php
$oldSections = old('sections');
$oldEnabled = old('section_enabled');

if (!is_array($oldSections)) {
    $oldSections = [];
}

if (!is_array($oldEnabled)) {
    $oldEnabled = null;
}

$draftTitle = old(
    'draft_title',
    $page['draft_title'] ?? ''
);

$draftMetaDescription = old(
    'draft_meta_description',
    $page['draft_meta_description'] ?? ''
);

$revisionNote = old(
    'revision_note',
    $page['revision_note'] ?? ''
);

$hasPublishedVersion = !empty($page['published_at']);
$hasChanges = !empty($page['has_unpublished_changes']);
?>

<div class="public-cms-admin public-cms-editor">

<div class="page-header public-cms-page-header">
    <div>
        <span class="public-cms-eyebrow">
            Structured Public CMS
        </span>

        <h2>
            Kelola Halaman <?= esc($definition['name']) ?>
        </h2>

        <p>
            Perbarui konten sebagai draft, periksa preview,
            kemudian publikasikan setelah seluruh isi dinyatakan
            siap.
        </p>
    </div>

    <div class="public-cms-header-actions">
        <a
            href="<?= base_url('/website/pages') ?>"
            class="btn btn-secondary"
        >
            Kembali
        </a>

        <?php if (auth_can(
            'website.pages.preview'
        )) : ?>
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
        <?php endif; ?>

        <?php if (
            auth_can('website.pages.publish')
            && $hasChanges
        ) : ?>
            <form
                action="<?= base_url(
                    '/website/pages/publish/' . $pageKey
                ) ?>"
                method="post"
                onsubmit="return confirm(
                    'Publikasikan draft ini ke website publik?'
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

<section class="public-cms-editor-status">
    <div>
        <span>Halaman</span>
        <strong><?= esc($definition['route']) ?></strong>
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
        <span>Status Draft</span>
        <strong>
            <?= $hasChanges
                ? 'Ada perubahan'
                : 'Sinkron dengan publik' ?>
        </strong>
    </div>

    <div>
        <span>Terakhir Tayang</span>
        <strong>
            <?= $hasPublishedVersion
                ? esc(
                    date(
                        'd M Y · H.i',
                        strtotime($page['published_at'])
                    )
                )
                : '-' ?>
        </strong>
    </div>
</section>

<nav class="public-cms-section-index" aria-label="Navigasi section editor">
    <a href="#cms-section-seo">SEO</a>

    <?php foreach (
        $definition['sections'] as
        $navSectionKey => $navSectionDefinition
    ) : ?>
        <a href="#cms-section-<?= esc(
            $navSectionKey,
            'attr'
        ) ?>">
            <?= esc($navSectionDefinition['name']) ?>
        </a>
    <?php endforeach; ?>
</nav>

<form
    action="<?= base_url(
        '/website/pages/update/' . $pageKey
    ) ?>"
    method="post"
    class="public-cms-edit-form"
>
    <?= csrf_field() ?>

    <div class="public-cms-editor-layout">
        <div class="public-cms-editor-main">

            <section
                id="cms-section-seo"
                class="public-cms-section-card"
            >
                <div class="public-cms-section-card__heading">
                    <span>SEO & Metadata</span>

                    <div>
                        <h3>Identitas halaman pada mesin pencari</h3>

                        <p>
                            Judul dan deskripsi ini digunakan oleh
                            layout publik setelah versi draft
                            dipublikasikan.
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="draft_title">
                        Judul SEO
                    </label>

                    <input
                        id="draft_title"
                        type="text"
                        name="draft_title"
                        value="<?= esc(
                            $draftTitle,
                            'attr'
                        ) ?>"
                        maxlength="180"
                        required
                    >

                    <small>
                        Maksimal 180 karakter. Gunakan judul yang
                        jelas dan spesifik.
                    </small>
                </div>

                <div class="form-group">
                    <label for="draft_meta_description">
                        Meta Description
                    </label>

                    <textarea
                        id="draft_meta_description"
                        name="draft_meta_description"
                        rows="4"
                        maxlength="255"
                        required
                    ><?= esc($draftMetaDescription) ?></textarea>

                    <small>
                        Maksimal 255 karakter. Ringkas isi halaman
                        dalam satu atau dua kalimat.
                    </small>
                </div>
            </section>

            <?php
            $sectionNumber = 1;
            ?>

            <?php foreach (
                $definition['sections'] as
                $sectionKey => $sectionDefinition
            ) : ?>
                <?php
                $sectionRow = $sections[$sectionKey] ?? [];
                $draftData = $sectionRow['draft_data'] ?? [];

                $isEnabled = $oldEnabled !== null
                    ? isset($oldEnabled[$sectionKey])
                    : (bool) (
                        $sectionRow['draft_enabled'] ?? true
                    );

                $toggleable = (bool) (
                    $sectionDefinition['toggleable']
                    ?? true
                );
                ?>

                <section
                    id="cms-section-<?= esc(
                        $sectionKey,
                        'attr'
                    ) ?>"
                    class="public-cms-section-card"
                >
                    <div class="public-cms-section-card__heading">
                        <span>
                            Section
                            <?= str_pad(
                                (string) $sectionNumber,
                                2,
                                '0',
                                STR_PAD_LEFT
                            ) ?>
                        </span>

                        <div>
                            <h3>
                                <?= esc(
                                    $sectionDefinition['name']
                                ) ?>
                            </h3>

                            <p>
                                <?= esc(
                                    $sectionDefinition[
                                        'description'
                                    ] ?? ''
                                ) ?>
                            </p>
                        </div>

                        <?php if ($toggleable) : ?>
                            <label
                                class="public-cms-toggle"
                            >
                                <input
                                    type="checkbox"
                                    name="section_enabled[<?= esc(
                                        $sectionKey,
                                        'attr'
                                    ) ?>]"
                                    value="1"
                                    <?= $isEnabled
                                        ? 'checked'
                                        : '' ?>
                                >

                                <span>Tampilkan</span>
                            </label>
                        <?php else : ?>
                            <span
                                class="public-cms-required-badge"
                            >
                                Wajib tampil
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty(
                        $sectionDefinition['data_note']
                    )) : ?>
                        <div class="public-cms-dynamic-note">
                            <strong>Data dinamis</strong>
                            <span>
                                <?= esc(
                                    $sectionDefinition['data_note']
                                ) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="public-cms-fields-grid">
                        <?php foreach (
                            $sectionDefinition['fields'] as
                            $fieldKey => $fieldDefinition
                        ) : ?>
                            <?php
                            $oldSectionData =
                                $oldSections[$sectionKey]
                                ?? [];

                            $fieldValue = is_array(
                                $oldSectionData
                            ) && array_key_exists(
                                $fieldKey,
                                $oldSectionData
                            )
                                ? $oldSectionData[$fieldKey]
                                : (
                                    $draftData[$fieldKey]
                                    ?? (
                                        $fieldDefinition[
                                            'default'
                                        ] ?? ''
                                    )
                                );

                            $fieldType =
                                $fieldDefinition['type']
                                ?? 'text';

                            $isTextarea =
                                $fieldType === 'textarea';

                            $fieldClass =
                                $isTextarea
                                    ? 'is-wide'
                                    : '';
                            ?>

                            <div
                                class="form-group <?= $fieldClass ?>"
                            >
                                <label
                                    for="<?= esc(
                                        $sectionKey
                                        . '-'
                                        . $fieldKey,
                                        'attr'
                                    ) ?>"
                                >
                                    <?= esc(
                                        $fieldDefinition['label']
                                    ) ?>

                                    <?php if (!empty(
                                        $fieldDefinition[
                                            'required'
                                        ]
                                    )) : ?>
                                        <b>*</b>
                                    <?php endif; ?>
                                </label>

                                <?php if ($isTextarea) : ?>
                                    <textarea
                                        id="<?= esc(
                                            $sectionKey
                                            . '-'
                                            . $fieldKey,
                                            'attr'
                                        ) ?>"
                                        name="sections[<?= esc(
                                            $sectionKey,
                                            'attr'
                                        ) ?>][<?= esc(
                                            $fieldKey,
                                            'attr'
                                        ) ?>]"
                                        rows="5"
                                        maxlength="<?= (int) (
                                            $fieldDefinition[
                                                'max'
                                            ] ?? 1000
                                        ) ?>"
                                        <?= !empty(
                                            $fieldDefinition[
                                                'required'
                                            ]
                                        ) ? 'required' : '' ?>
                                    ><?= esc($fieldValue) ?></textarea>
                                <?php else : ?>
                                    <input
                                        id="<?= esc(
                                            $sectionKey
                                            . '-'
                                            . $fieldKey,
                                            'attr'
                                        ) ?>"
                                        type="<?= $fieldType === 'url'
                                            ? 'text'
                                            : 'text' ?>"
                                        name="sections[<?= esc(
                                            $sectionKey,
                                            'attr'
                                        ) ?>][<?= esc(
                                            $fieldKey,
                                            'attr'
                                        ) ?>]"
                                        value="<?= esc(
                                            $fieldValue,
                                            'attr'
                                        ) ?>"
                                        maxlength="<?= (int) (
                                            $fieldDefinition[
                                                'max'
                                            ] ?? 255
                                        ) ?>"
                                        <?= !empty(
                                            $fieldDefinition[
                                                'required'
                                            ]
                                        ) ? 'required' : '' ?>
                                    >
                                <?php endif; ?>

                                <?php if (!empty(
                                    $fieldDefinition['help']
                                )) : ?>
                                    <small>
                                        <?= esc(
                                            $fieldDefinition['help']
                                        ) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php $sectionNumber++; ?>
            <?php endforeach; ?>

        </div>

        <aside class="public-cms-editor-sidebar">
            <section class="public-cms-sidebar-card">
                <span class="public-cms-sidebar-card__eyebrow">
                    Draft Control
                </span>

                <h3>Simpan tanpa langsung tayang</h3>

                <p>
                    Tombol Simpan Draft hanya memperbarui versi
                    internal. Website publik tetap memakai versi
                    terakhir yang sudah diterbitkan.
                </p>

                <div class="form-group">
                    <label for="revision_note">
                        Catatan Revisi
                    </label>

                    <textarea
                        id="revision_note"
                        name="revision_note"
                        rows="4"
                        maxlength="255"
                        placeholder="Contoh: memperbarui hero dan CTA kolaborasi"
                    ><?= esc($revisionNote) ?></textarea>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary public-cms-save-button"
                >
                    Simpan Draft
                </button>
            </section>

            <section class="public-cms-sidebar-card is-info">
                <span class="public-cms-sidebar-card__eyebrow">
                    Preview
                </span>

                <h3>Periksa seperti pengunjung</h3>

                <p>
                    Preview hanya dapat dilihat pengguna Portal yang
                    login dan mempunyai izin preview.
                </p>

                <?php if (auth_can(
                    'website.pages.preview'
                )) : ?>
                    <a
                        href="<?= base_url(
                            '/website/pages/preview/'
                            . $pageKey
                        ) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-secondary"
                    >
                        Buka Preview Draft ↗
                    </a>
                <?php endif; ?>
            </section>

            <?php if (
                $hasPublishedVersion
                && $hasChanges
                && auth_can('website.pages.update')
            ) : ?>
                <section class="public-cms-sidebar-card is-warning">
                    <span
                        class="public-cms-sidebar-card__eyebrow"
                    >
                        Pemulihan
                    </span>

                    <h3>Batalkan seluruh perubahan draft</h3>

                    <p>
                        Draft akan dikembalikan persis ke versi yang
                        saat ini tampil pada website.
                    </p>

                    <button
                        type="submit"
                        form="public-cms-restore-form"
                        class="btn btn-secondary"
                    >
                        Pulihkan Versi Publik
                    </button>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</form>

<?php if (
    $hasPublishedVersion
    && $hasChanges
    && auth_can('website.pages.update')
) : ?>
    <form
        id="public-cms-restore-form"
        action="<?= base_url(
            '/website/pages/restore/' . $pageKey
        ) ?>"
        method="post"
        onsubmit="return confirm(
            'Batalkan seluruh perubahan draft dan pulihkan versi publik?'
        )"
    >
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

</div>

<?= $this->endSection() ?>
