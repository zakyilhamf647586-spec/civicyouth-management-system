<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page publication-calendar-page">

<?php
$weekdays = [
    'Sen',
    'Sel',
    'Rab',
    'Kam',
    'Jum',
    'Sab',
    'Min',
];
?>

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">
            Content Planning
        </span>

        <h2>Kalender Konten</h2>

        <p>
            Lihat ritme produksi, jadwal tayang, dan publikasi
            Instagram dalam satu tampilan bulanan.
        </p>
    </div>

    <div class="publication-header-actions">
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

        <a
            href="<?= base_url('/publications') ?>"
            class="btn btn-secondary"
        >
            Kembali ke Pipeline
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

<section
    class="publication-summary-grid publication-calendar-summary"
>
    <article>
        <span>Konten Bulan Ini</span>
        <strong>
            <?= esc($calendarSummary['total'] ?? 0) ?>
        </strong>
        <small>Seluruh item pada kalender.</small>
    </article>

    <article>
        <span>Dalam Produksi</span>
        <strong>
            <?= esc($calendarSummary['production'] ?? 0) ?>
        </strong>
        <small>Brief hingga disetujui.</small>
    </article>

    <article class="tone-scheduled">
        <span>Dijadwalkan</span>
        <strong>
            <?= esc($calendarSummary['scheduled'] ?? 0) ?>
        </strong>
        <small>Siap tayang sesuai waktu.</small>
    </article>

    <article class="tone-published">
        <span>Sudah Tayang</span>
        <strong>
            <?= esc($calendarSummary['published'] ?? 0) ?>
        </strong>
        <small>Publikasi bulan berjalan.</small>
    </article>
</section>

<section class="publication-calendar-toolbar">
    <a
        href="<?= base_url(
            '/publications/calendar?month='
            . $previousMonth
        ) ?>"
        class="btn btn-secondary"
    >
        ← Bulan Sebelumnya
    </a>

    <form
        method="get"
        action="<?= base_url('/publications/calendar') ?>"
    >
        <label for="publication-calendar-month">
            Periode
        </label>

        <input
            id="publication-calendar-month"
            type="month"
            name="month"
            value="<?= esc($monthValue, 'attr') ?>"
        >

        <button type="submit" class="btn btn-primary">
            Tampilkan
        </button>
    </form>

    <div class="publication-calendar-toolbar__title">
        <span>Kalender Editorial</span>
        <strong><?= esc($monthLabel) ?></strong>
    </div>

    <a
        href="<?= base_url(
            '/publications/calendar?month='
            . $nextMonth
        ) ?>"
        class="btn btn-secondary"
    >
        Bulan Berikutnya →
    </a>
</section>

<div class="publication-calendar-grid-scroll">
    <section class="publication-calendar-grid">
        <?php foreach ($weekdays as $weekday) : ?>
            <div class="publication-calendar-weekday">
                <?= esc($weekday) ?>
            </div>
        <?php endforeach; ?>

        <?php for (
            $blank = 0;
            $blank < $leadingBlankDays;
            $blank++
        ) : ?>
            <div
                class="publication-calendar-day is-empty"
                aria-hidden="true"
            ></div>
        <?php endfor; ?>

        <?php foreach ($calendarDays as $day) : ?>
            <article
                class="publication-calendar-day <?= $day['is_today']
                    ? 'is-today'
                    : '' ?>"
            >
                <header>
                    <span><?= (int) $day['day'] ?></span>

                    <?php if ($day['is_today']) : ?>
                        <small>Hari ini</small>
                    <?php endif; ?>
                </header>

                <div class="publication-calendar-day__items">
                    <?php if (!empty($day['posts'])) : ?>
                        <?php foreach (
                            $day['posts'] as $post
                        ) : ?>
                            <?php
                            $status = $post['workflow_status']
                                ?: 'brief';
                            ?>

                            <a
                                href="<?= base_url(
                                    '/publications/'
                                    . $post['id']
                                ) ?>"
                                class="publication-calendar-item publication-calendar-item--<?= esc(
                                    $status,
                                    'attr'
                                ) ?>"
                            >
                                <span>
                                    <?= esc(
                                        $workflowStatuses[$status]
                                        ?? ucfirst($status)
                                    ) ?>
                                </span>

                                <strong>
                                    <?= esc(
                                        mb_strimwidth(
                                            $post['event_title']
                                            ?: $post['title']
                                            ?: 'Tanpa judul',
                                            0,
                                            48,
                                            '…'
                                        )
                                    ) ?>
                                </strong>

                                <small>
                                    <?= esc(
                                        $post['calendar_time']
                                        ?: (
                                            $publicationTypes[
                                                $post[
                                                    'publication_type'
                                                ]
                                            ]
                                            ?? 'Konten'
                                        )
                                    ) ?>
                                </small>
                            </a>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <span
                            class="publication-calendar-day__empty"
                        >
                            Belum ada konten
                        </span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</div>

<section class="publication-calendar-agenda">
    <div class="publication-table-heading">
        <div>
            <span>Agenda Bulanan</span>
            <h3><?= esc($monthLabel) ?></h3>
        </div>

        <small>
            <?= count($calendarPosts ?? []) ?> item
        </small>
    </div>

    <?php if (!empty($calendarPosts)) : ?>
        <div class="publication-calendar-agenda__list">
            <?php foreach (
                $calendarPosts as $post
            ) : ?>
                <?php
                $status = $post['workflow_status']
                    ?: 'brief';
                ?>

                <a
                    href="<?= base_url(
                        '/publications/' . $post['id']
                    ) ?>"
                >
                    <div>
                        <span>
                            <?= esc(
                                date(
                                    'd',
                                    strtotime(
                                        $post['calendar_date']
                                    )
                                )
                            ) ?>
                        </span>

                        <small>
                            <?= esc(
                                date(
                                    'M',
                                    strtotime(
                                        $post['calendar_date']
                                    )
                                )
                            ) ?>
                        </small>
                    </div>

                    <section>
                        <strong>
                            <?= esc(
                                $post['event_title']
                                ?: $post['title']
                                ?: 'Tanpa judul'
                            ) ?>
                        </strong>

                        <p>
                            <?= esc(
                                $post['program_name']
                                ?: 'Umum'
                            ) ?>
                            ·
                            <?= esc(
                                $publicationTypes[
                                    $post['publication_type']
                                ]
                                ?? 'Konten'
                            ) ?>
                        </p>
                    </section>

                    <span
                        class="publication-status publication-status--<?= esc(
                            $status,
                            'attr'
                        ) ?>"
                    >
                        <?= esc(
                            $workflowStatuses[$status]
                            ?? ucfirst($status)
                        ) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="publication-empty-state">
            <strong>Belum ada konten bulan ini</strong>
            <p>
                Buat brief baru atau jadwalkan publikasi yang
                sedang diproduksi.
            </p>
        </div>
    <?php endif; ?>
</section>

<section class="publication-unscheduled-card">
    <div class="publication-table-heading">
        <div>
            <span>Perlu Dijadwalkan</span>
            <h3>Konten tanpa rencana tayang</h3>
        </div>

        <small>
            Maksimal delapan pekerjaan terbaru
        </small>
    </div>

    <?php if (!empty($unscheduledPosts)) : ?>
        <div class="publication-unscheduled-grid">
            <?php foreach (
                $unscheduledPosts as $post
            ) : ?>
                <?php
                $status = $post['workflow_status']
                    ?: 'brief';
                ?>

                <article>
                    <div>
                        <span
                            class="publication-status publication-status--<?= esc(
                                $status,
                                'attr'
                            ) ?>"
                        >
                            <?= esc(
                                $workflowStatuses[$status]
                                ?? ucfirst($status)
                            ) ?>
                        </span>

                        <small>
                            <?= esc(
                                $publicationTypes[
                                    $post['publication_type']
                                ]
                                ?? 'Konten'
                            ) ?>
                        </small>
                    </div>

                    <h4>
                        <?= esc(
                            $post['event_title']
                            ?: $post['title']
                            ?: 'Tanpa judul'
                        ) ?>
                    </h4>

                    <p>
                        <?= esc(
                            $post['program_name']
                            ?: 'Tanpa pilar khusus'
                        ) ?>
                    </p>

                    <div>
                        <a
                            href="<?= base_url(
                                '/publications/'
                                . $post['id']
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
                                    . $post['id']
                                ) ?>"
                                class="btn btn-primary"
                            >
                                Atur Jadwal
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="publication-empty-state">
            <strong>Semua pekerjaan sudah terjadwal</strong>
            <p>
                Tidak ada publikasi aktif yang kehilangan
                rencana tayang.
            </p>
        </div>
    <?php endif; ?>
</section>

</div>

<?= $this->endSection() ?>
