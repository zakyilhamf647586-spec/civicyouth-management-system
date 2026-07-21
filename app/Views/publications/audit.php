<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page publication-audit-page">

<?php
$formatDateTime = static function (
    ?string $value
): string {
    if (empty($value)) {
        return '-';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('d M Y · H.i', $timestamp) . ' WIB'
        : '-';
};
?>

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">
            Governance &amp; Accountability
        </span>

        <h2>Audit Trail Publikasi</h2>

        <p>
            Lacak siapa yang membuat, mengubah, memindahkan status,
            mengelola aset, dan mencatat performa setiap publikasi.
        </p>
    </div>

    <div class="publication-header-actions">
        <a
            href="<?= base_url('/publications') ?>"
            class="btn btn-secondary"
        >
            Kembali ke Pipeline
        </a>

        <?php if (auth_can(
            'publications.metrics.view'
        )) : ?>
            <a
                href="<?= base_url('/publications/analytics') ?>"
                class="btn btn-secondary"
            >
                Analitik Instagram
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!$auditReady) : ?>
    <div class="publication-analytics-notice is-warning">
        <strong>Migration audit belum dijalankan.</strong>

        <p>
            Jalankan <code>php spark migrate</code> agar aktivitas
            publikasi mulai direkam. Riwayat sebelum migration
            tidak dapat dibuat mundur secara otomatis.
        </p>
    </div>
<?php endif; ?>

<section class="publication-audit-summary-grid">
    <article>
        <span>Total Aktivitas</span>
        <strong><?= number_format(
            (int) ($summary['total'] ?? 0),
            0,
            ',',
            '.'
        ) ?></strong>
        <small>Seluruh log publikasi.</small>
    </article>

    <article>
        <span>Aktivitas Hari Ini</span>
        <strong><?= number_format(
            (int) ($summary['today'] ?? 0),
            0,
            ',',
            '.'
        ) ?></strong>
        <small>Perubahan sejak pukul 00.00.</small>
    </article>

    <article>
        <span>Perubahan Status</span>
        <strong><?= number_format(
            (int) ($summary['status_changes'] ?? 0),
            0,
            ',',
            '.'
        ) ?></strong>
        <small>Transisi workflow tercatat.</small>
    </article>

    <article class="featured">
        <span>Pembaruan Konten</span>
        <strong><?= number_format(
            (int) ($summary['updates'] ?? 0),
            0,
            ',',
            '.'
        ) ?></strong>
        <small>Edit data publikasi.</small>
    </article>
</section>

<section class="publication-filter-card">
    <form
        method="get"
        action="<?= base_url('/publications/audit') ?>"
        class="publication-audit-filter"
    >
        <div>
            <label for="audit-q">
                Cari Publikasi atau Aktivitas
            </label>

            <input
                id="audit-q"
                type="text"
                name="q"
                value="<?= esc(
                    $filters['q'] ?? '',
                    'attr'
                ) ?>"
                placeholder="Content ID, judul, atau ringkasan"
            >
        </div>

        <div>
            <label for="audit-event">
                Jenis Aktivitas
            </label>

            <select
                id="audit-event"
                name="event_type"
            >
                <option value="">Semua Aktivitas</option>

                <?php foreach (
                    $eventLabels as $key => $label
                ) : ?>
                    <option
                        value="<?= esc($key, 'attr') ?>"
                        <?= (
                            $filters['event_type'] ?? ''
                        ) === $key
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="audit-actor">
                Pengguna / Peran
            </label>

            <input
                id="audit-actor"
                type="text"
                name="actor"
                value="<?= esc(
                    $filters['actor'] ?? '',
                    'attr'
                ) ?>"
                placeholder="Nama atau role"
            >
        </div>

        <div>
            <label for="audit-date-from">
                Dari Tanggal
            </label>

            <input
                id="audit-date-from"
                type="date"
                name="date_from"
                value="<?= esc(
                    $filters['date_from'] ?? '',
                    'attr'
                ) ?>"
            >
        </div>

        <div>
            <label for="audit-date-to">
                Sampai Tanggal
            </label>

            <input
                id="audit-date-to"
                type="date"
                name="date_to"
                value="<?= esc(
                    $filters['date_to'] ?? '',
                    'attr'
                ) ?>"
            >
        </div>

        <div class="publication-filter-actions">
            <button type="submit" class="btn btn-primary">
                Terapkan
            </button>

            <a
                href="<?= base_url('/publications/audit') ?>"
                class="btn btn-secondary"
            >
                Reset
            </a>
        </div>
    </form>
</section>

<section class="publication-table-card">
    <div class="publication-table-heading">
        <div>
            <span>Immutable History</span>
            <h3>Catatan aktivitas publikasi</h3>
        </div>

        <small>
            <?= count($logs ?? []) ?> log pada halaman ini
        </small>
    </div>

    <?php if (!empty($logs)) : ?>
        <div class="table-responsive">
            <table class="publication-audit-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Publikasi</th>
                        <th>Aktivitas</th>
                        <th>Ringkasan</th>
                        <th>Perubahan Status</th>
                        <th>IP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td>
                                <?= esc(
                                    $formatDateTime(
                                        $log['created_at']
                                        ?? null
                                    )
                                ) ?>
                            </td>

                            <td>
                                <strong>
                                    <?= esc(
                                        $log['actor_name']
                                        ?? 'Sistem'
                                    ) ?>
                                </strong>

                                <small>
                                    <?= esc(
                                        $log['actor_role']
                                        ?? '-'
                                    ) ?>
                                </small>
                            </td>

                            <td>
                                <strong>
                                    <?= esc(
                                        $log['content_code']
                                        ?: 'PUB-' . $log[
                                            'content_post_id'
                                        ]
                                    ) ?>
                                </strong>

                                <small>
                                    <?= esc(
                                        mb_strimwidth(
                                            $log['event_title']
                                            ?: $log['title']
                                            ?: 'Tanpa judul',
                                            0,
                                            70,
                                            '…'
                                        )
                                    ) ?>
                                </small>
                            </td>

                            <td>
                                <span
                                    class="publication-audit-badge publication-audit-badge--<?= esc(
                                        $log['event_type'],
                                        'attr'
                                    ) ?>"
                                >
                                    <?= esc(
                                        $eventLabels[
                                            $log['event_type']
                                        ]
                                        ?? ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $log[
                                                    'event_type'
                                                ]
                                            )
                                        )
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <?= esc(
                                    $log['summary']
                                    ?? 'Aktivitas tercatat.'
                                ) ?>
                            </td>

                            <td>
                                <?php if (
                                    !empty($log['from_status'])
                                    || !empty($log['to_status'])
                                ) : ?>
                                    <div
                                        class="publication-audit-transition"
                                    >
                                        <span>
                                            <?= esc(
                                                $workflowStatuses[
                                                    $log[
                                                        'from_status'
                                                    ]
                                                ]
                                                ?? (
                                                    $log[
                                                        'from_status'
                                                    ] ?: '-'
                                                )
                                            ) ?>
                                        </span>

                                        <b>→</b>

                                        <span>
                                            <?= esc(
                                                $workflowStatuses[
                                                    $log[
                                                        'to_status'
                                                    ]
                                                ]
                                                ?? (
                                                    $log[
                                                        'to_status'
                                                    ] ?: '-'
                                                )
                                            ) ?>
                                        </span>
                                    </div>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <td>
                                <small>
                                    <?= esc(
                                        $log['ip_address']
                                        ?? '-'
                                    ) ?>
                                </small>
                            </td>

                            <td>
                                <a
                                    href="<?= base_url(
                                        '/publications/'
                                        . $log[
                                            'content_post_id'
                                        ]
                                    ) ?>"
                                    class="btn btn-secondary"
                                >
                                    Buka
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="publication-empty-state">
            <strong>Belum ada audit trail</strong>

            <p>
                Aktivitas baru akan tampil setelah migration
                dijalankan dan modul publikasi digunakan kembali.
            </p>
        </div>
    <?php endif; ?>
</section>

<?php if ($pager !== null && !empty($logs)) : ?>
    <div class="pagination-wrapper">
        <?= $pager->links(
            'publication_audit',
            'default_full'
        ) ?>
    </div>
<?php endif; ?>

</div>

<?= $this->endSection() ?>
