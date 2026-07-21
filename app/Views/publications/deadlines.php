<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page publication-deadline-page">

<?php
$formatDateTime = static function (
    ?\DateTimeImmutable $value
): string {
    if ($value === null) {
        return '-';
    }

    return $value->format(
        'd M Y · H.i'
    ) . ' WIB';
};
?>

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">
            Production Control
        </span>

        <h2>Deadline Produksi</h2>

        <p>
            Pantau konten terlambat, pekerjaan yang segera jatuh
            tempo, hambatan produksi, dan brief yang belum memiliki
            target tayang.
        </p>
    </div>

    <div class="publication-header-actions">
        <a
            href="<?= base_url('/publications') ?>"
            class="btn btn-secondary"
        >
            Kembali ke Pipeline
        </a>

        <a
            href="<?= base_url('/publications/calendar') ?>"
            class="btn btn-secondary"
        >
            Kalender Konten
        </a>

        <?php if (auth_can(
            'publications.create'
        )) : ?>
            <a
                href="<?= base_url('/publications/create') ?>"
                class="btn btn-primary"
            >
                + Buat Publikasi
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="publication-deadline-notice">
    <strong>
        Sistem memakai jendela peringatan
        <?= (int) $warningHours ?> jam.
    </strong>

    <p>
        Deadline setiap tahap dihitung mundur dari
        <em>Rencana Tayang</em>. Konten tanpa tanggal tayang masuk
        kategori Belum Dijadwalkan dan belum dapat dinilai terlambat.
    </p>
</div>

<section class="publication-deadline-summary-grid">
    <a
        href="<?= base_url(
            '/publications/deadlines?urgency=overdue'
        ) ?>"
        class="is-overdue"
    >
        <span>Terlambat</span>
        <strong>
            <?= (int) ($summary['overdue'] ?? 0) ?>
        </strong>
        <small>Melewati deadline tahap saat ini.</small>
    </a>

    <a
        href="<?= base_url(
            '/publications/deadlines?urgency=due_soon'
        ) ?>"
        class="is-due-soon"
    >
        <span>Segera Jatuh Tempo</span>
        <strong>
            <?= (int) ($summary['due_soon'] ?? 0) ?>
        </strong>
        <small>Jatuh tempo dalam jendela peringatan.</small>
    </a>

    <a
        href="<?= base_url(
            '/publications/deadlines?urgency=unscheduled'
        ) ?>"
        class="is-unscheduled"
    >
        <span>Belum Dijadwalkan</span>
        <strong>
            <?= (int) ($summary['unscheduled'] ?? 0) ?>
        </strong>
        <small>Belum memiliki target publikasi.</small>
    </a>

    <a
        href="<?= base_url(
            '/publications/deadlines?urgency=on_track'
        ) ?>"
        class="is-on-track"
    >
        <span>Sesuai Jalur</span>
        <strong>
            <?= (int) ($summary['on_track'] ?? 0) ?>
        </strong>
        <small>Deadline produksi masih aman.</small>
    </a>

    <article class="is-blocked">
        <span>Memiliki Hambatan</span>
        <strong>
            <?= (int) ($summary['blocked'] ?? 0) ?>
        </strong>
        <small>PIC, Canva, aset, reviewer, atau caption.</small>
    </article>
</section>

<section class="publication-filter-card">
    <form
        method="get"
        action="<?= base_url('/publications/deadlines') ?>"
        class="publication-deadline-filter"
    >
        <div>
            <label for="deadline-urgency">
                Kondisi Deadline
            </label>

            <select
                id="deadline-urgency"
                name="urgency"
            >
                <option value="">Semua Kondisi</option>

                <?php foreach (
                    $urgencyLabels as $value => $label
                ) : ?>
                    <option
                        value="<?= esc($value, 'attr') ?>"
                        <?= (
                            $filters['urgency'] ?? ''
                        ) === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="deadline-status">
                Status Workflow
            </label>

            <select
                id="deadline-status"
                name="status"
            >
                <option value="">Semua Status</option>

                <?php foreach (
                    $workflowStatuses as $value => $label
                ) : ?>
                    <?php if (in_array(
                        $value,
                        ['published', 'archived'],
                        true
                    )) : ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <option
                        value="<?= esc($value, 'attr') ?>"
                        <?= (
                            $filters['status'] ?? ''
                        ) === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="deadline-priority">
                Prioritas
            </label>

            <select
                id="deadline-priority"
                name="priority"
            >
                <option value="">Semua Prioritas</option>

                <?php foreach (
                    $priorities as $value => $label
                ) : ?>
                    <option
                        value="<?= esc($value, 'attr') ?>"
                        <?= (
                            $filters['priority'] ?? ''
                        ) === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="deadline-owner">
                PIC
            </label>

            <input
                id="deadline-owner"
                type="text"
                name="owner"
                value="<?= esc(
                    $filters['owner'] ?? '',
                    'attr'
                ) ?>"
                placeholder="Cari nama PIC"
            >
        </div>

        <div class="publication-filter-actions">
            <button type="submit" class="btn btn-primary">
                Terapkan
            </button>

            <a
                href="<?= base_url(
                    '/publications/deadlines'
                ) ?>"
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
            <span>Production Queue</span>
            <h3>Daftar deadline aktif</h3>
        </div>

        <small>
            <?= count($items ?? []) ?> pekerjaan
        </small>
    </div>

    <?php if (!empty($items)) : ?>
        <div class="table-responsive">
            <table class="publication-deadline-table">
                <thead>
                    <tr>
                        <th>Publikasi</th>
                        <th>Status</th>
                        <th>Rencana Tayang</th>
                        <th>Target Berikutnya</th>
                        <th>Deadline Tahap</th>
                        <th>Kondisi</th>
                        <th>Hambatan</th>
                        <th>PIC</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td>
                                <strong>
                                    <?= esc(
                                        $item['event_title']
                                        ?: $item['title']
                                        ?: 'Tanpa judul'
                                    ) ?>
                                </strong>

                                <small>
                                    <?= esc(
                                        $item['content_code']
                                        ?: 'PUB-' . $item['id']
                                    ) ?>
                                    ·
                                    <?= esc(
                                        $item['program_name']
                                        ?: 'Umum'
                                    ) ?>
                                </small>
                            </td>

                            <td>
                                <span
                                    class="publication-status publication-status--<?= esc(
                                        $item[
                                            'workflow_status'
                                        ],
                                        'attr'
                                    ) ?>"
                                >
                                    <?= esc(
                                        $workflowStatuses[
                                            $item[
                                                'workflow_status'
                                            ]
                                        ]
                                        ?? ucfirst(
                                            $item[
                                                'workflow_status'
                                            ]
                                        )
                                    ) ?>
                                </span>

                                <small>
                                    Prioritas:
                                    <?= esc(
                                        $priorities[
                                            $item['priority']
                                        ]
                                        ?? ucfirst(
                                            $item['priority']
                                            ?: 'normal'
                                        )
                                    ) ?>
                                </small>
                            </td>

                            <td>
                                <?= esc(
                                    $formatDateTime(
                                        $item[
                                            'scheduled_at_object'
                                        ]
                                    )
                                ) ?>
                            </td>

                            <td>
                                <strong>
                                    <?= esc(
                                        $item[
                                            'next_milestone'
                                        ]
                                    ) ?>
                                </strong>
                            </td>

                            <td>
                                <strong>
                                    <?= esc(
                                        $formatDateTime(
                                            $item['due_at']
                                        )
                                    ) ?>
                                </strong>

                                <small>
                                    <?= esc(
                                        $item['time_message']
                                    ) ?>
                                </small>
                            </td>

                            <td>
                                <span
                                    class="publication-deadline-urgency publication-deadline-urgency--<?= esc(
                                        $item['urgency'],
                                        'attr'
                                    ) ?>"
                                >
                                    <?= esc(
                                        $item[
                                            'urgency_label'
                                        ]
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <?php if (!empty(
                                    $item['blockers']
                                )) : ?>
                                    <div
                                        class="publication-deadline-blockers"
                                    >
                                        <?php foreach (
                                            $item['blockers']
                                            as $blocker
                                        ) : ?>
                                            <span>
                                                <?= esc($blocker) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <span
                                        class="publication-deadline-clear"
                                    >
                                        Tidak ada hambatan
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= esc(
                                    $item['owner']
                                    ?: 'Belum ditentukan'
                                ) ?>
                            </td>

                            <td>
                                <a
                                    href="<?= base_url(
                                        '/publications/'
                                        . $item['id']
                                    ) ?>"
                                    class="btn btn-secondary"
                                >
                                    Buka
                                </a>

                                <?php if (auth_can(
                                    'publications.update'
                                )) : ?>
                                    <a
                                        href="<?= base_url(
                                            '/publications/edit/'
                                            . $item['id']
                                        ) ?>"
                                        class="btn btn-primary"
                                    >
                                        Perbaiki
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="publication-empty-state">
            <strong>
                Tidak ada pekerjaan yang cocok
            </strong>

            <p>
                Ubah filter atau periksa kembali pipeline publikasi.
            </p>
        </div>
    <?php endif; ?>
</section>

</div>

<?= $this->endSection() ?>
