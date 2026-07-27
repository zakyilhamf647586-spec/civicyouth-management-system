<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('public_pages/_assets') ?>

<?php
$sourceLabels = $sourceLabels ?? [];

$pageSnapshot = is_array(
    $snapshot['page'] ?? null
)
    ? $snapshot['page']
    : [];

$sectionSnapshots = is_array(
    $snapshot['sections'] ?? null
)
    ? $snapshot['sections']
    : [];

$createdBy = !empty($revision['created_by'])
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

<div class="public-cms-admin public-cms-revision-detail">

<div class="page-header public-cms-page-header">
    <div>
        <span class="public-cms-eyebrow">
            Historical Snapshot
        </span>

        <h2>
            Versi #<?= (int) (
                $revision['version_number'] ?? 0
            ) ?> — <?= esc($page['name']) ?>
        </h2>

        <p>
            Periksa isi snapshot dan perbedaannya terhadap draft
            halaman yang aktif sekarang.
        </p>
    </div>

    <div class="public-cms-header-actions">
        <a
            href="<?= base_url(
                '/website/pages/revisions/' . $pageKey
            ) ?>"
            class="btn btn-secondary"
        >
            Kembali ke Riwayat
        </a>

        <?php if (auth_can(
            'website.pages.revisions.restore'
        )) : ?>
            <form
                action="<?= base_url(
                    '/website/pages/revisions/'
                    . $pageKey
                    . '/'
                    . $revision['id']
                    . '/restore'
                ) ?>"
                method="post"
                onsubmit="return confirm(
                    'Pulihkan versi ini sebagai draft baru? Website publik tidak akan langsung berubah.'
                )"
            >
                <?= csrf_field() ?>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Pulihkan sebagai Draft
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<section class="public-cms-revision-detail-summary">
    <div>
        <span>Sumber Versi</span>
        <strong>
            <?= esc(
                $sourceLabels[$sourceType]
                ?? $sourceType
            ) ?>
        </strong>
    </div>

    <div>
        <span>Mode Snapshot</span>
        <strong>
            <?= $revision['snapshot_mode']
                === 'published'
                    ? 'Versi Publik'
                    : 'Versi Draft' ?>
        </strong>
    </div>

    <div>
        <span>Dibuat oleh</span>
        <strong><?= esc($createdBy) ?></strong>
    </div>

    <div>
        <span>Waktu</span>
        <strong>
            <?= !empty($revision['created_at'])
                ? esc(date(
                    'd M Y · H.i',
                    strtotime(
                        $revision['created_at']
                    )
                ))
                : '-' ?>
        </strong>
    </div>
</section>

<section class="public-cms-revision-comparison">
    <header>
        <div>
            <span>Perbandingan dengan Draft Saat Ini</span>
            <h3>
                <?= !empty($comparison['has_changes'])
                    ? 'Versi ini berbeda dari draft aktif'
                    : 'Versi ini sama dengan draft aktif' ?>
            </h3>
        </div>
    </header>

    <div>
        <article class="<?= !empty(
            $comparison['title_changed']
        ) ? 'is-changed' : 'is-same' ?>">
            <span>Judul SEO</span>
            <strong>
                <?= !empty(
                    $comparison['title_changed']
                ) ? 'Berbeda' : 'Sama' ?>
            </strong>
        </article>

        <article class="<?= !empty(
            $comparison['meta_changed']
        ) ? 'is-changed' : 'is-same' ?>">
            <span>Meta Description</span>
            <strong>
                <?= !empty(
                    $comparison['meta_changed']
                ) ? 'Berbeda' : 'Sama' ?>
            </strong>
        </article>

        <article>
            <span>Section Berubah</span>
            <strong>
                <?= (int) (
                    $comparison[
                        'changed_section_count'
                    ] ?? 0
                ) ?>
            </strong>
        </article>

        <article>
            <span>Field Berubah</span>
            <strong>
                <?= (int) (
                    $comparison[
                        'changed_field_count'
                    ] ?? 0
                ) ?>
            </strong>
        </article>
    </div>
</section>

<section class="public-cms-revision-metadata">
    <header>
        <span>Metadata pada Versi Ini</span>
        <h3>Identitas halaman</h3>
    </header>

    <dl>
        <div>
            <dt>Judul SEO</dt>
            <dd>
                <?= esc(
                    $pageSnapshot['title']
                    ?? 'Tidak tersedia'
                ) ?>
            </dd>
        </div>

        <div>
            <dt>Meta Description</dt>
            <dd>
                <?= esc(
                    $pageSnapshot[
                        'meta_description'
                    ] ?? 'Tidak tersedia'
                ) ?>
            </dd>
        </div>

        <div>
            <dt>Route</dt>
            <dd>
                <?= esc(
                    $pageSnapshot['route_path']
                    ?? $page['route_path']
                ) ?>
            </dd>
        </div>

        <div>
            <dt>Catatan Versi</dt>
            <dd>
                <?= esc(
                    $revision['revision_note']
                    ?: 'Tanpa catatan'
                ) ?>
            </dd>
        </div>
    </dl>
</section>

<?php if (!empty(
    $comparison['section_changes']
)) : ?>
    <section class="public-cms-revision-change-list">
        <header>
            <span>Section yang Berbeda</span>
            <h3>Ringkasan perubahan</h3>
        </header>

        <div>
            <?php foreach (
                $comparison['section_changes']
                as $change
            ) : ?>
                <article>
                    <strong>
                        <?= esc(
                            $change['section_name']
                            ?? $change['section_key']
                        ) ?>
                    </strong>

                    <?php if (!empty(
                        $change['enabled_changed']
                    )) : ?>
                        <span>
                            Status tampil/sembunyi berbeda
                        </span>
                    <?php endif; ?>

                    <?php if (!empty(
                        $change['changed_fields']
                    )) : ?>
                        <small>
                            Field:
                            <?= esc(implode(
                                ', ',
                                array_slice(
                                    $change[
                                        'changed_fields'
                                    ],
                                    0,
                                    8
                                )
                            )) ?>
                            <?= count(
                                $change['changed_fields']
                            ) > 8 ? '…' : '' ?>
                        </small>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="public-cms-revision-sections">
    <header>
        <span>Isi Snapshot</span>
        <h3>Section halaman pada versi ini</h3>
    </header>

    <div>
        <?php foreach (
            $sectionSnapshots as $section
        ) : ?>
            <?php
            $content = is_array(
                $section['content'] ?? null
            )
                ? $section['content']
                : [];
            ?>

            <article>
                <header>
                    <div>
                        <span>
                            <?= esc(
                                $section['section_key']
                                ?? ''
                            ) ?>
                        </span>

                        <h4>
                            <?= esc(
                                $section['section_name']
                                ?? 'Section'
                            ) ?>
                        </h4>
                    </div>

                    <span class="revision-section-state <?= !empty(
                        $section['enabled']
                    ) ? 'is-enabled' : 'is-disabled' ?>">
                        <?= !empty($section['enabled'])
                            ? 'Ditampilkan'
                            : 'Disembunyikan' ?>
                    </span>
                </header>

                <?php if ($content === []) : ?>
                    <p class="revision-empty-content">
                        Tidak ada konten tersimpan.
                    </p>
                <?php else : ?>
                    <dl>
                        <?php foreach (
                            $content as $field => $value
                        ) : ?>
                            <div>
                                <dt>
                                    <?= esc(
                                        ucwords(str_replace(
                                            '_',
                                            ' ',
                                            (string) $field
                                        ))
                                    ) ?>
                                </dt>

                                <dd>
                                    <?= nl2br(esc(
                                        is_scalar($value)
                                            ? (string) $value
                                            : json_encode(
                                                $value,
                                                JSON_UNESCAPED_UNICODE
                                            )
                                    )) ?>
                                </dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="public-cms-revision-safety">
    <strong>Versi lama tidak diterbitkan otomatis</strong>

    <p>
        Tombol pemulihan hanya membuat draft baru. Setelah itu,
        lakukan preview, kirim review, minta persetujuan, lalu
        publikasikan seperti biasa.
    </p>
</section>

</div>

<?= $this->endSection() ?>
