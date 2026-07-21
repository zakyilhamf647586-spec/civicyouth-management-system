<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page publication-recommendation-page">

<?php
$data = $recommendationData;

$formatNumber = static function (
    int|float $value
): string {
    return number_format(
        $value,
        0,
        ',',
        '.'
    );
};

$formatDecimal = static function (
    int|float $value
): string {
    return number_format(
        $value,
        2,
        ',',
        '.'
    );
};
?>

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">
            Data-Driven Scheduling
        </span>

        <h2>Rekomendasi Waktu Tayang</h2>

        <p>
            Temukan hari dan jam yang paling menjanjikan berdasarkan
            histori publikasi serta snapshot Instagram Insights
            milik GARDA 01 sendiri.
        </p>
    </div>

    <div class="publication-header-actions">
        <a
            href="<?= base_url('/publications/analytics') ?>"
            class="btn btn-secondary"
        >
            Analitik Instagram
        </a>

        <a
            href="<?= base_url('/publications/calendar') ?>"
            class="btn btn-secondary"
        >
            Kalender Konten
        </a>

        <a
            href="<?= base_url('/publications') ?>"
            class="btn btn-primary"
        >
            Kembali ke Pipeline
        </a>
    </div>
</div>

<div class="publication-recommendation-notice <?= !empty(
    $data['has_enough_data']
) ? 'is-data' : 'is-baseline' ?>">
    <?php if (!empty(
        $data['has_enough_data']
    )) : ?>
        <strong>
            Rekomendasi sudah menggunakan data internal.
        </strong>

        <p>
            Sistem menilai reach rata-rata, engagement rate,
            saves + shares, serta jumlah sampel pada
            <?= (int) $data['lookback_days'] ?>
            hari terakhir.
        </p>
    <?php else : ?>
        <strong>
            Data internal masih terbatas.
        </strong>

        <p>
            Dibutuhkan minimal
            <?= (int) $data['minimum_samples'] ?>
            konten dengan snapshot performa. Sementara itu Portal
            menampilkan baseline eksperimen internal—bukan klaim
            jam terbaik Instagram secara umum.
        </p>
    <?php endif; ?>
</div>

<section class="publication-recommendation-toolbar">
    <form
        method="get"
        action="<?= base_url(
            '/publications/recommendations'
        ) ?>"
    >
        <div class="form-group">
            <label for="recommendation-type">
                Format Konten
            </label>

            <select
                id="recommendation-type"
                name="type"
            >
                <option value="">
                    Semua Format
                </option>

                <?php foreach (
                    $publicationTypes as $key => $label
                ) : ?>
                    <option
                        value="<?= esc($key, 'attr') ?>"
                        <?= $selectedType === $key
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Analisis
        </button>

        <a
            href="<?= base_url(
                '/publications/recommendations'
            ) ?>"
            class="btn btn-secondary"
        >
            Reset
        </a>
    </form>

    <div>
        <span>Periode Data</span>
        <strong>
            <?= esc(
                $data['date_start']->format('d M Y')
            ) ?>
            –
            <?= esc(
                $data['date_end']->format('d M Y')
            ) ?>
        </strong>
    </div>
</section>

<section class="publication-recommendation-summary-grid">
    <article>
        <span>Konten Tayang</span>
        <strong>
            <?= (int) $data['published_posts'] ?>
        </strong>
        <small>Dalam periode analisis.</small>
    </article>

    <article>
        <span>Konten Terukur</span>
        <strong>
            <?= (int) $data['tracked_posts'] ?>
        </strong>
        <small>Memiliki snapshot Insights.</small>
    </article>

    <article>
        <span>Slot Teruji</span>
        <strong>
            <?= (int) $data['tested_slots'] ?>
        </strong>
        <small>Kombinasi hari dan waktu.</small>
    </article>

    <article class="featured">
        <span>Kualitas Data</span>
        <strong>
            <?= !empty($data['has_enough_data'])
                ? 'Siap'
                : 'Awal' ?>
        </strong>
        <small>
            Minimum
            <?= (int) $data['minimum_samples'] ?>
            sampel terukur.
        </small>
    </article>
</section>

<?php if (!empty(
    $data['recommendations']
)) : ?>
    <section class="publication-recommendation-top">
        <div class="publication-table-heading">
            <div>
                <span>Top Recommendation</span>
                <h3>
                    Waktu dengan sinyal performa terbaik
                </h3>
            </div>

            <small>
                Skor relatif 0–100
            </small>
        </div>

        <div class="publication-recommendation-top-grid">
            <?php foreach (
                array_slice(
                    $data['recommendations'],
                    0,
                    3
                ) as $index => $item
            ) : ?>
                <article>
                    <span class="rank">
                        #<?= $index + 1 ?>
                    </span>

                    <div>
                        <span>
                            <?= esc(
                                $item['weekday_label']
                            ) ?>
                        </span>

                        <strong>
                            <?= esc(
                                str_replace(
                                    ':',
                                    '.',
                                    $item['time']
                                )
                            ) ?>
                            WIB
                        </strong>

                        <small>
                            <?= esc(
                                $item['period_label']
                            ) ?>
                            ·
                            <?= esc(
                                $item['slot_label']
                            ) ?>
                        </small>
                    </div>

                    <dl>
                        <div>
                            <dt>Skor</dt>
                            <dd>
                                <?= $formatDecimal(
                                    $item['score']
                                ) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Sampel</dt>
                            <dd>
                                <?= (int) $item['posts'] ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Avg Reach</dt>
                            <dd>
                                <?= $formatNumber(
                                    $item[
                                        'average_reach'
                                    ]
                                ) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>ER</dt>
                            <dd>
                                <?= $formatDecimal(
                                    $item[
                                        'engagement_rate'
                                    ]
                                ) ?>%
                            </dd>
                        </div>
                    </dl>

                    <span
                        class="publication-recommendation-confidence publication-recommendation-confidence--<?= esc(
                            $item['confidence'],
                            'attr'
                        ) ?>"
                    >
                        <?= esc(
                            $item['confidence_label']
                        ) ?>
                    </span>

                    <?php if (auth_can(
                        'publications.create'
                    )) : ?>
                        <a
                            href="<?= base_url(
                                '/publications/create'
                            ) ?>"
                            class="btn btn-primary"
                        >
                            Buat Publikasi
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php else : ?>
    <section class="publication-recommendation-baseline">
        <div class="publication-table-heading">
            <div>
                <span>Experiment Baseline</span>
                <h3>
                    Jadwal awal untuk membangun data
                </h3>
            </div>

            <small>
                Dapat disesuaikan di konfigurasi
            </small>
        </div>

        <div>
            <?php foreach (
                $data['baseline'] as $item
            ) : ?>
                <article>
                    <span>
                        <?= esc(
                            $item['weekday_label']
                        ) ?>
                    </span>

                    <strong>
                        <?= esc(
                            str_replace(
                                ':',
                                '.',
                                $item['time']
                            )
                        ) ?>
                        WIB
                    </strong>

                    <small>
                        <?= esc($item['label']) ?>
                    </small>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="publication-recommendation-method">
    <div>
        <span>Metodologi</span>
        <h3>Bagaimana skor dihitung?</h3>
    </div>

    <div class="publication-recommendation-formula">
        <article>
            <strong>50%</strong>
            <span>Reach rata-rata</span>
            <p>
                Mengukur seberapa luas konten menjangkau akun unik.
            </p>
        </article>

        <article>
            <strong>30%</strong>
            <span>Engagement Rate</span>
            <p>
                Like, komentar, bagikan, dan simpan dibanding reach.
            </p>
        </article>

        <article>
            <strong>20%</strong>
            <span>Saves + Shares</span>
            <p>
                Sinyal bahwa konten dianggap bernilai dan layak
                dibagikan.
            </p>
        </article>

        <article>
            <strong>×</strong>
            <span>Faktor Sampel</span>
            <p>
                Slot dengan terlalu sedikit konten diberi
                penalti keyakinan.
            </p>
        </article>
    </div>
</section>

<section class="publication-table-card">
    <div class="publication-table-heading">
        <div>
            <span>Evidence Table</span>
            <h3>
                Seluruh slot waktu yang sudah teruji
            </h3>
        </div>

        <small>
            <?= count($data['all_slots']) ?> slot
        </small>
    </div>

    <?php if (!empty(
        $data['all_slots']
    )) : ?>
        <div class="table-responsive">
            <table class="publication-recommendation-table">
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Hari</th>
                        <th>Rentang Waktu</th>
                        <th>Jam Rata-rata</th>
                        <th>Sampel</th>
                        <th>Avg Reach</th>
                        <th>Avg Interaksi</th>
                        <th>Avg Simpan + Bagikan</th>
                        <th>Engagement Rate</th>
                        <th>Skor</th>
                        <th>Keyakinan</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach (
                        $data['all_slots']
                        as $index => $item
                    ) : ?>
                        <tr>
                            <td>#<?= $index + 1 ?></td>

                            <td>
                                <strong>
                                    <?= esc(
                                        $item[
                                            'weekday_label'
                                        ]
                                    ) ?>
                                </strong>
                            </td>

                            <td>
                                <?= esc(
                                    $item['slot_label']
                                ) ?>
                            </td>

                            <td>
                                <?= esc(
                                    str_replace(
                                        ':',
                                        '.',
                                        $item['time']
                                    )
                                ) ?>
                                WIB
                            </td>

                            <td>
                                <?= (int) $item['posts'] ?>
                            </td>

                            <td>
                                <?= $formatNumber(
                                    $item[
                                        'average_reach'
                                    ]
                                ) ?>
                            </td>

                            <td>
                                <?= $formatDecimal(
                                    $item[
                                        'average_interactions'
                                    ]
                                ) ?>
                            </td>

                            <td>
                                <?= $formatDecimal(
                                    $item[
                                        'average_saves_shares'
                                    ]
                                ) ?>
                            </td>

                            <td>
                                <?= $formatDecimal(
                                    $item[
                                        'engagement_rate'
                                    ]
                                ) ?>%
                            </td>

                            <td>
                                <strong>
                                    <?= $formatDecimal(
                                        $item['score']
                                    ) ?>
                                </strong>
                            </td>

                            <td>
                                <span
                                    class="publication-recommendation-confidence publication-recommendation-confidence--<?= esc(
                                        $item[
                                            'confidence'
                                        ],
                                        'attr'
                                    ) ?>"
                                >
                                    <?= esc(
                                        $item[
                                            'confidence_label'
                                        ]
                                    ) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="publication-empty-state">
            <strong>
                Belum ada slot waktu yang dapat dianalisis
            </strong>

            <p>
                Publikasikan konten dan catat minimal satu snapshot
                Instagram Insights untuk membangun rekomendasi.
            </p>
        </div>
    <?php endif; ?>
</section>

</div>

<?= $this->endSection() ?>
