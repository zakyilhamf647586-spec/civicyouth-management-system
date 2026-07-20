<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link
    rel="stylesheet"
    href="<?= base_url('assets/css/admin-activity-workflow.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/admin-activity-workflow.css') ?>"
>

<?php
$publicationBadgeClasses = [
    'draft' => 'badge-secondary',
    'review' => 'badge-warning',
    'published' => 'badge-success',
    'scheduled' => 'badge-warning',
    'archived' => 'badge-secondary',
];
?>

<div class="activity-workflow-page">

<div class="page-header">
    <div>
        <h2>Kegiatan</h2>
        <p>
            Kelola data pelaksanaan, dokumentasi, dan workflow publikasi
            kegiatan GARDA 01.
        </p>
    </div>

    <?php if (auth_can('activities.create')) : ?>
        <a
            href="<?= base_url('/activities/create') ?>"
            class="btn btn-primary"
        >
            + Tambah Kegiatan
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

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (
            session()->getFlashdata('errors') as $error
        ) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="filter-card">
    <form action="<?= base_url('/activities') ?>" method="get">
        <div class="filter-grid activity-filter-grid">
            <div class="form-group">
                <label for="keyword">Cari Kegiatan</label>
                <input
                    type="text"
                    id="keyword"
                    name="keyword"
                    value="<?= esc($keyword ?? '') ?>"
                    placeholder="Nama, lokasi, ringkasan, deskripsi, atau hasil"
                >
            </div>

            <div class="form-group">
                <label for="program_id">Pilar Program</label>
                <select id="program_id" name="program_id">
                    <option value="">Semua Program</option>

                    <?php foreach ($programs as $program) : ?>
                        <option
                            value="<?= (int) $program['id'] ?>"
                            <?= (string) ($selectedProgram ?? '')
                                === (string) $program['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($program['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status Pelaksanaan</label>
                <select id="status" name="status">
                    <option value="">Semua Status</option>

                    <?php foreach (
                        $executionStatusLabels as $value => $label
                    ) : ?>
                        <option
                            value="<?= esc($value) ?>"
                            <?= ($status ?? '') === $value
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="publication_status">
                    Status Publikasi
                </label>
                <select
                    id="publication_status"
                    name="publication_status"
                >
                    <option value="">Semua Publikasi</option>

                    <?php foreach (
                        $publicationStatusLabels as $value => $label
                    ) : ?>
                        <option
                            value="<?= esc($value) ?>"
                            <?= ($publicationStatus ?? '') === $value
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date_from">Dari Tanggal</label>
                <input
                    type="date"
                    id="date_from"
                    name="date_from"
                    value="<?= esc($date_from ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="date_to">Sampai Tanggal</label>
                <input
                    type="date"
                    id="date_to"
                    name="date_to"
                    value="<?= esc($date_to ?? '') ?>"
                >
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    Terapkan
                </button>

                <a
                    href="<?= base_url('/activities') ?>"
                    class="btn btn-secondary"
                >
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="activity-table-scroll-hint">
        Geser tabel ke samping untuk melihat seluruh kolom dan tindakan.
    </div>

    <div class="table-responsive" tabindex="0" aria-label="Tabel kegiatan dapat digeser ke samping">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kegiatan</th>
                    <th>Program</th>
                    <th>Tanggal & Lokasi</th>
                    <th>Pelaksanaan</th>
                    <th>Publikasi</th>
                    <th>Unggulan</th>
                    <th width="260">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($activities)) : ?>
                    <?php
                    $currentPage = $pager->getCurrentPage(
                        'activities'
                    );

                    $perPage = $pager->getPerPage('activities');

                    $no = 1 + (
                        $perPage * ($currentPage - 1)
                    );
                    ?>

                    <?php foreach ($activities as $activity) : ?>
                        <?php
                        $executionStatus =
                            $activity['status'] ?? 'planned';

                        $publication =
                            $activity['publication_status']
                            ?? 'draft';

                        $executionBadgeClass = match (
                            $executionStatus
                        ) {
                            'completed' => 'badge-success',
                            'cancelled' => 'badge-danger',
                            default => 'badge-warning',
                        };
                        ?>

                        <tr>
                            <td><?= $no++ ?></td>

                            <td>
                                <strong>
                                    <?= esc($activity['title']) ?>
                                </strong>

                                <br>

                                <small>
                                    <?= esc(
                                        mb_strimwidth(
                                            $activity['summary']
                                                ?: (
                                                    $activity['description']
                                                    ?? '-'
                                                ),
                                            0,
                                            105,
                                            '…'
                                        )
                                    ) ?>
                                </small>

                                <?php if (!empty(
                                    $activity['review_notes']
                                )) : ?>
                                    <br>
                                    <small>
                                        Catatan: <?= esc(
                                            mb_strimwidth(
                                                $activity[
                                                    'review_notes'
                                                ],
                                                0,
                                                90,
                                                '…'
                                            )
                                        ) ?>
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty(
                                    $activity['program_name']
                                )) : ?>
                                    <strong>
                                        <?= esc(
                                            $activity['program_name']
                                        ) ?>
                                    </strong>
                                    <br>
                                    <small>
                                        <?= esc(
                                            $activity['program_label']
                                            ?? ''
                                        ) ?>
                                    </small>
                                <?php else : ?>
                                    <span class="badge badge-secondary">
                                        Belum Dikategorikan
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= esc(
                                    date(
                                        'd M Y',
                                        strtotime(
                                            $activity[
                                                'activity_date'
                                            ]
                                        )
                                    )
                                ) ?>
                                <br>
                                <small>
                                    <?= esc(
                                        $activity['location'] ?? '-'
                                    ) ?>
                                </small>
                            </td>

                            <td>
                                <span class="badge <?= esc(
                                    $executionBadgeClass
                                ) ?>">
                                    <?= esc(
                                        $executionStatusLabels[
                                            $executionStatus
                                        ] ?? '-'
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge <?= esc(
                                    $publicationBadgeClasses[
                                        $publication
                                    ] ?? 'badge-secondary'
                                ) ?>">
                                    <?= esc(
                                        $publicationStatusLabels[
                                            $publication
                                        ] ?? 'Draft'
                                    ) ?>
                                </span>

                                <?php if (
                                    $publication === 'scheduled'
                                    && !empty(
                                        $activity['scheduled_at']
                                    )
                                ) : ?>
                                    <br>
                                    <small>
                                        <?= esc(
                                            date(
                                                'd M Y H:i',
                                                strtotime(
                                                    $activity[
                                                        'scheduled_at'
                                                    ]
                                                )
                                            )
                                        ) ?> WIB
                                    </small>
                                <?php elseif (
                                    $publication === 'published'
                                    && !empty(
                                        $activity['published_at']
                                    )
                                ) : ?>
                                    <br>
                                    <small>
                                        Terbit <?= esc(
                                            date(
                                                'd M Y H:i',
                                                strtotime(
                                                    $activity[
                                                        'published_at'
                                                    ]
                                                )
                                            )
                                        ) ?> WIB
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= (int) (
                                    $activity['is_featured'] ?? 0
                                ) === 1
                                    ? 'Ya'
                                    : 'Tidak' ?>
                            </td>

                            <td>
                                <?php
                                $hasVisibleAction = auth_can_any([
                                    'activities.gallery.view',
                                    'activities.update',
                                    'activities.submit_review',
                                    'activities.publish',
                                    'activities.return_to_draft',
                                    'activities.archive',
                                    'activities.delete',
                                ]);
                                ?>

                                <?php if (auth_can(
                                    'activities.gallery.view'
                                )) : ?>
                                    <a
                                        href="<?= base_url(
                                            '/activities/gallery/'
                                            . $activity['id']
                                        ) ?>"
                                        class="btn btn-primary"
                                    >
                                        Galeri
                                    </a>
                                <?php endif; ?>

                                <?php if (auth_can(
                                    'activities.update'
                                )) : ?>
                                    <a
                                        href="<?= base_url(
                                            '/activities/edit/'
                                            . $activity['id']
                                        ) ?>"
                                        class="btn btn-warning"
                                    >
                                        Edit
                                    </a>
                                <?php endif; ?>

                                <?php if (
                                    auth_can(
                                        'activities.submit_review'
                                    )
                                    && in_array(
                                        $publication,
                                        ['draft', 'archived'],
                                        true
                                    )
                                ) : ?>
                                    <form
                                        action="<?= base_url(
                                            '/activities/submit-review/'
                                            . $activity['id']
                                        ) ?>"
                                        method="post"
                                        class="inline-action-form"
                                    >
                                        <?= csrf_field() ?>
                                        <button
                                            type="submit"
                                            class="btn btn-warning"
                                        >
                                            Review
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (
                                    auth_can('activities.publish')
                                    && in_array(
                                        $publication,
                                        [
                                            'draft',
                                            'review',
                                            'scheduled',
                                        ],
                                        true
                                    )
                                ) : ?>
                                    <form
                                        action="<?= base_url(
                                            '/activities/publish/'
                                            . $activity['id']
                                        ) ?>"
                                        method="post"
                                        class="inline-action-form"
                                        onsubmit="return confirm('Publikasikan kegiatan ini sekarang?')"
                                    >
                                        <?= csrf_field() ?>
                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >
                                            Terbitkan
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (
                                    auth_can(
                                        'activities.return_to_draft'
                                    )
                                    && in_array(
                                        $publication,
                                        [
                                            'review',
                                            'published',
                                            'scheduled',
                                            'archived',
                                        ],
                                        true
                                    )
                                ) : ?>
                                    <form
                                        action="<?= base_url(
                                            '/activities/draft/'
                                            . $activity['id']
                                        ) ?>"
                                        method="post"
                                        class="inline-action-form"
                                    >
                                        <?= csrf_field() ?>
                                        <button
                                            type="submit"
                                            class="btn btn-secondary"
                                        >
                                            Draft
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (
                                    auth_can('activities.archive')
                                    && in_array(
                                        $publication,
                                        ['published', 'scheduled'],
                                        true
                                    )
                                ) : ?>
                                    <form
                                        action="<?= base_url(
                                            '/activities/archive/'
                                            . $activity['id']
                                        ) ?>"
                                        method="post"
                                        class="inline-action-form"
                                        onsubmit="return confirm('Arsipkan kegiatan ini dari website publik?')"
                                    >
                                        <?= csrf_field() ?>
                                        <button
                                            type="submit"
                                            class="btn btn-secondary"
                                        >
                                            Arsipkan
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (auth_can(
                                    'activities.delete'
                                )) : ?>
                                    <form
                                        action="<?= base_url(
                                            '/activities/delete/'
                                            . $activity['id']
                                        ) ?>"
                                        method="post"
                                        class="inline-action-form"
                                        onsubmit="return confirm('Yakin ingin menghapus data kegiatan ini secara permanen?')"
                                    >
                                        <?= csrf_field() ?>
                                        <button
                                            type="submit"
                                            class="btn btn-danger"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (!$hasVisibleAction) : ?>
                                    <span class="badge badge-secondary">
                                        Akses baca
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="empty">
                            Data kegiatan tidak ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pager)) : ?>
        <div class="pagination-wrapper">
            <?= $pager
                ->only([
                    'keyword',
                    'status',
                    'publication_status',
                    'program_id',
                    'date_from',
                    'date_to',
                ])
                ->links('activities', 'default_full') ?>
        </div>
    <?php endif; ?>
</div>

</div>

<?= $this->endSection() ?>
