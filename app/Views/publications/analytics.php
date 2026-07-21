<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page publication-analytics-page">

<?php
$formatNumber = static fn (
    int|float $value
): string => number_format(
    $value,
    0,
    ',',
    '.'
);

$formatRate = static fn (
    int|float $value
): string => number_format(
    $value,
    2,
    ',',
    '.'
) . '%';

$maxFormatReach = 1;

foreach ($formatPerformance as $group) {
    $maxFormatReach = max(
        $maxFormatReach,
        (int) ($group['reach'] ?? 0)
    );
}
?>

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">
            Performance Intelligence
        </span>

        <h2>Analitik Instagram</h2>

        <p>
            Evaluasi konten yang tayang pada bulan terpilih
            memakai snapshot terbaru dari Instagram Insights.
        </p>
    </div>

    <div class="publication-header-actions">
        <?php if (auth_can(
            'publications.recommendations.view'
        )) : ?>
            <a
                href="<?= base_url(
                    '/publications/recommendations'
                ) ?>"
                class="btn btn-secondary"
            >
                Waktu Tayang
            </a>
        <?php endif; ?>

        <a
            href="<?= base_url('/publications/calendar') ?>"
            class="btn btn-secondary"
        >
            Kalender Konten
        </a>

        <a
            href="<?= base_url('/publications') ?>"
            class="btn btn-secondary"
        >
            Kembali ke Pipeline
        </a>

        <?php if (
            auth_can('publications.metrics.export')
            && !empty($rows)
        ) : ?>
            <a
                href="<?= base_url(
                    '/publications/analytics/export?'
                    . http_build_query([
                        'month' => $monthValue,
                        'program_id' =>
                            $filters['program_id'] ?? 0,
                        'type' =>
                            $filters['type'] ?? '',
                    ])
                ) ?>"
                class="btn btn-primary"
            >
                Export CSV
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="publication-analytics-notice <?= !$metricsReady
    ? 'is-warning'
    : '' ?>">
    <strong>
        <?= $metricsReady
            ? 'Data bersumber dari input manual.'
            : 'Migration metrik belum dijalankan.' ?>
    </strong>

    <p>
        <?php if ($metricsReady) : ?>
            Portal belum terhubung ke Instagram API.
            Salin angka dari Instagram Insights pada halaman
            detail publikasi. Engagement Rate dihitung dari
            like, komentar, bagikan, dan simpan dibagi reach.
        <?php else : ?>
            Jalankan <code>php spark migrate</code> agar
            statistik performa dapat dicatat.
        <?php endif; ?>
    </p>
</div>

<section class="publication-analytics-toolbar">
    <a
        href="<?= base_url(
            '/publications/analytics?'
            . http_build_query([
                'month' => $previousMonth,
                'program_id' =>
                    $filters['program_id'] ?? 0,
                'type' => $filters['type'] ?? '',
            ])
        ) ?>"
        class="btn btn-secondary"
    >
        ← Bulan Sebelumnya
    </a>

    <form
        method="get"
        action="<?= base_url('/publications/analytics') ?>"
    >
        <div class="form-group">
            <label for="analytics-month">Periode</label>
            <input
                type="month"
                id="analytics-month"
                name="month"
                value="<?= esc(
                    $monthValue,
                    'attr'
                ) ?>"
            >
        </div>

        <div class="form-group">
            <label for="analytics-program">Pilar</label>
            <select
                id="analytics-program"
                name="program_id"
            >
                <option value="0">Semua Pilar</option>

                <?php foreach ($programs as $program) : ?>
                    <option
                        value="<?= (int) $program['id'] ?>"
                        <?= (int) (
                            $filters['program_id'] ?? 0
                        ) === (int) $program['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($program['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="analytics-type">Format</label>
            <select
                id="analytics-type"
                name="type"
            >
                <option value="">Semua Format</option>

                <?php foreach (
                    $publicationTypes as $key => $label
                ) : ?>
                    <option
                        value="<?= esc($key, 'attr') ?>"
                        <?= (
                            $filters['type'] ?? ''
                        ) === $key
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Tampilkan
        </button>
    </form>

    <div class="publication-analytics-toolbar__period">
        <span>Performa Konten</span>
        <strong><?= esc($monthLabel) ?></strong>
    </div>

    <a
        href="<?= base_url(
            '/publications/analytics?'
            . http_build_query([
                'month' => $nextMonth,
                'program_id' =>
                    $filters['program_id'] ?? 0,
                'type' => $filters['type'] ?? '',
            ])
        ) ?>"
        class="btn btn-secondary"
    >
        Bulan Berikutnya →
    </a>
</section>

<section class="publication-analytics-summary-grid">
    <article>
        <span>Konten Tayang</span>
        <strong>
            <?= $formatNumber(
                $summary['published'] ?? 0
            ) ?>
        </strong>
        <small>Publikasi pada periode terpilih</small>
    </article>

    <article>
        <span>Sudah Dilacak</span>
        <strong>
            <?= $formatNumber(
                $summary['tracked'] ?? 0
            ) ?>
        </strong>
        <small>
            <?= $formatRate(
                $summary['coverage_rate'] ?? 0
            ) ?>
            cakupan data
        </small>
    </article>

    <article class="tone-reach">
        <span>Total Reach</span>
        <strong>
            <?= $formatNumber(
                $summary['reach'] ?? 0
            ) ?>
        </strong>
        <small>Akun unik terjangkau</small>
    </article>

    <article class="tone-interaction">
        <span>Total Interaksi</span>
        <strong>
            <?= $formatNumber(
                $summary['interactions'] ?? 0
            ) ?>
        </strong>
        <small>Like, komentar, bagikan, simpan</small>
    </article>

    <article class="tone-engagement">
        <span>Engagement Rate</span>
        <strong>
            <?= $formatRate(
                $summary['engagement_rate'] ?? 0
            ) ?>
        </strong>
        <small>Interaksi dibanding reach</small>
    </article>
</section>

<?php if (
    ($summary['published'] ?? 0) > 0
    && ($summary['tracked'] ?? 0)
        < ($summary['published'] ?? 0)
) : ?>
    <div class="publication-analytics-coverage">
        <strong>
            <?= (int) (
                ($summary['published'] ?? 0)
                - ($summary['tracked'] ?? 0)
            ) ?>
            konten belum memiliki snapshot performa.
        </strong>

        <p>
            Buka detail publikasi dan catat data Insights
            agar analisis periode semakin akurat.
        </p>
    </div>
<?php endif; ?>

<div class="publication-analytics-layout">
    <section class="publication-analytics-card">
        <div class="publication-table-heading">
            <div>
                <span>Top Content</span>
                <h3>Konten dengan reach tertinggi</h3>
            </div>

            <small>Snapshot terbaru per konten</small>
        </div>

        <?php if (!empty($topPosts)) : ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Konten</th>
                            <th>Format</th>
                            <th>Reach</th>
                            <th>Interaksi</th>
                            <th>Engagement</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach (
                            $topPosts as $index => $row
                        ) : ?>
                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td>
                                    <strong>
                                        <?= esc(
                                            $row['display_title']
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?= esc(
                                            $row['program_name']
                                            ?: 'Umum'
                                        ) ?>
                                        ·
                                        <?= esc(
                                            $row['content_code']
                                            ?: '-'
                                        ) ?>
                                    </small>
                                </td>

                                <td>
                                    <?= esc(
                                        $publicationTypes[
                                            $row[
                                                'publication_type'
                                            ]
                                        ]
                                        ?? ucfirst(
                                            $row[
                                                'publication_type'
                                            ] ?: 'konten'
                                        )
                                    ) ?>
                                </td>

                                <td>
                                    <?= $formatNumber(
                                        $row['reach'] ?? 0
                                    ) ?>
                                </td>

                                <td>
                                    <?= $formatNumber(
                                        $row[
                                            'interactions'
                                        ] ?? 0
                                    ) ?>
                                </td>

                                <td>
                                    <?= $formatRate(
                                        $row[
                                            'engagement_rate'
                                        ] ?? 0
                                    ) ?>
                                </td>

                                <td>
                                    <a
                                        href="<?= base_url(
                                            '/publications/'
                                            . $row['id']
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
                <strong>
                    Belum ada konten tayang pada periode ini
                </strong>

                <p>
                    Ubah bulan atau publikasikan konten
                    untuk mulai membangun analitik.
                </p>
            </div>
        <?php endif; ?>
    </section>

    <aside class="publication-analytics-card">
        <div class="publication-table-heading">
            <div>
                <span>Format Comparison</span>
                <h3>Performa berdasarkan format</h3>
            </div>
        </div>

        <?php if (!empty($formatPerformance)) : ?>
            <div class="publication-format-performance">
                <?php foreach (
                    $formatPerformance as $group
                ) : ?>
                    <?php
                    $barWidth = min(
                        100,
                        (
                            ($group['reach'] ?? 0)
                            / $maxFormatReach
                        ) * 100
                    );
                    ?>

                    <article>
                        <div>
                            <strong>
                                <?= esc($group['label']) ?>
                            </strong>

                            <span>
                                <?= (int) $group['tracked'] ?>
                                /
                                <?= (int) $group['posts'] ?>
                                terukur
                            </span>
                        </div>

                        <div class="publication-performance-bar">
                            <span
                                style="width: <?= number_format(
                                    $barWidth,
                                    2,
                                    '.',
                                    ''
                                ) ?>%"
                            ></span>
                        </div>

                        <dl>
                            <div>
                                <dt>Reach</dt>
                                <dd>
                                    <?= $formatNumber(
                                        $group['reach'] ?? 0
                                    ) ?>
                                </dd>
                            </div>

                            <div>
                                <dt>Interaksi</dt>
                                <dd>
                                    <?= $formatNumber(
                                        $group[
                                            'interactions'
                                        ] ?? 0
                                    ) ?>
                                </dd>
                            </div>

                            <div>
                                <dt>ER</dt>
                                <dd>
                                    <?= $formatRate(
                                        $group[
                                            'engagement_rate'
                                        ] ?? 0
                                    ) ?>
                                </dd>
                            </div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="publication-empty-state compact">
                <strong>Belum ada data format</strong>
                <p>
                    Data muncul setelah konten dipublikasikan.
                </p>
            </div>
        <?php endif; ?>
    </aside>
</div>

<section class="publication-analytics-card">
    <div class="publication-table-heading">
        <div>
            <span>Program Insight</span>
            <h3>Performa berdasarkan pilar GARDA 01</h3>
        </div>

        <small>Maksimal delapan pilar</small>
    </div>

    <?php if (!empty($programPerformance)) : ?>
        <div class="publication-program-performance-grid">
            <?php foreach (
                $programPerformance as $group
            ) : ?>
                <article>
                    <span><?= esc($group['label']) ?></span>

                    <strong>
                        <?= $formatNumber(
                            $group['reach'] ?? 0
                        ) ?>
                    </strong>

                    <p>Total reach</p>

                    <dl>
                        <div>
                            <dt>Konten</dt>
                            <dd>
                                <?= (int) $group['posts'] ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Terukur</dt>
                            <dd>
                                <?= (int) $group['tracked'] ?>
                            </dd>
                        </div>

                        <div>
                            <dt>ER</dt>
                            <dd>
                                <?= $formatRate(
                                    $group[
                                        'engagement_rate'
                                    ] ?? 0
                                ) ?>
                            </dd>
                        </div>
                    </dl>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="publication-empty-state compact">
            <strong>Belum ada data pilar</strong>
            <p>
                Hubungkan publikasi dengan Program GARDA 01
                agar analisis pilar terbentuk.
            </p>
        </div>
    <?php endif; ?>
</section>

</div>

<?= $this->endSection() ?>
