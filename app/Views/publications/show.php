<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page">

<?php
$status = $post['workflow_status'] ?: 'brief';
$typeLabel = $publicationTypes[$post['publication_type']] ?? ucfirst($post['publication_type'] ?: 'feed');
$priorityLabel = $priorities[$post['priority']] ?? ucfirst($post['priority'] ?: 'normal');
$categoryLabel = $categories[$post['category']] ?? ucfirst(str_replace('_', ' ', $post['category'] ?: 'umum'));

$formatDateTime = static function (?string $value): string {
    if (empty($value)) {
        return 'Belum ditentukan';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('d M Y · H.i', $timestamp) . ' WIB'
        : 'Belum ditentukan';
};
?>

<div class="publication-detail-header">
    <div>
        <div class="publication-detail-labels">
            <span class="publication-content-code">
                <?= esc($post['content_code'] ?: 'LEGACY-' . $post['id']) ?>
            </span>
            <span class="publication-status publication-status--<?= esc($status, 'attr') ?>">
                <?= esc($workflowStatuses[$status] ?? ucfirst($status)) ?>
            </span>
            <span class="publication-priority publication-priority--<?= esc($post['priority'] ?: 'normal', 'attr') ?>">
                <?= esc($priorityLabel) ?>
            </span>
        </div>

        <h1><?= esc($post['event_title'] ?: ($post['title'] ?: 'Publikasi Tanpa Judul')) ?></h1>
        <p><?= esc($post['cover_hook'] ?: 'Hook cover belum ditentukan.') ?></p>
    </div>

    <div class="publication-header-actions">
        <?php if (auth_can(
            'publications.audit.view'
        )) : ?>
            <a
                href="<?= base_url('/publications/audit?q='
                    . urlencode(
                        $post['content_code']
                        ?: $post['event_title']
                        ?: ''
                    )
                ) ?>"
                class="btn btn-secondary"
            >
                Audit Trail
            </a>
        <?php endif; ?>

        <?php if (auth_can(
            'publications.metrics.view'
        )) : ?>
            <a
                href="<?= base_url('/publications/analytics') ?>"
                class="btn btn-secondary"
            >
                Analitik
            </a>
        <?php endif; ?>

        <?php if (!empty($post['canva_url'])) : ?>
            <a
                href="<?= esc($post['canva_url'], 'attr') ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="btn publication-canva-button"
            >
                Buka Desain Canva ↗
            </a>
        <?php elseif (!empty($template['url'])) : ?>
            <a
                href="<?= esc($template['url'], 'attr') ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="btn publication-canva-button"
                data-canva-master-link
            >
                Buka &amp; Salin Master Canva ↗
            </a>
        <?php endif; ?>

        <?php if (auth_can('publications.update')) : ?>
            <a href="<?= base_url('/publications/edit/' . $post['id']) ?>" class="btn btn-primary">Edit Publikasi</a>
        <?php endif; ?>

        <a href="<?= base_url('/publications') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<section class="publication-workflow-card">
    <div class="publication-workflow-heading">
        <div>
            <span>Alur Produksi</span>
            <h2><?= esc($workflowStatuses[$status] ?? ucfirst($status)) ?></h2>
        </div>
        <p><?= esc($workflowDescriptions[$status] ?? '') ?></p>
    </div>

    <div class="publication-workflow-track">
        <?php foreach ($workflowStatuses as $key => $label) : ?>
            <div class="<?= $key === $status ? 'is-current' : '' ?>">
                <span></span>
                <small><?= esc($label) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="publication-detail-grid">
    <div class="publication-detail-main">
        <section class="publication-detail-card">
            <div class="publication-card-heading">
                <div>
                    <span>Creative Brief</span>
                    <h2>Arah konten</h2>
                </div>
                <span><?= esc($categoryLabel) ?></span>
            </div>

            <div class="publication-brief-highlight">
                <small>HOOK COVER</small>
                <strong><?= esc($post['cover_hook'] ?: 'Belum ditentukan') ?></strong>
                <p>Hook harus menarik, tetapi tetap mewakili isi carousel.</p>
            </div>

            <div class="publication-brief-grid">
                <article>
                    <small>TUJUAN KONTEN</small>
                    <p><?= nl2br(esc($post['content_goal'] ?: 'Belum ditentukan.')) ?></p>
                </article>
                <article>
                    <small>TARGET AUDIENS</small>
                    <p><?= esc($post['target_audience'] ?: 'Belum ditentukan.') ?></p>
                </article>
                <article>
                    <small>CALL TO ACTION</small>
                    <p><?= esc($post['call_to_action'] ?: 'Belum ditentukan.') ?></p>
                </article>
                <article>
                    <small>SUMBER PORTAL</small>
                    <p>
                        <?= esc($program['name'] ?? 'Tanpa pilar khusus') ?><br>
                        <?= esc($activity['title'] ?? 'Tidak terkait kegiatan tertentu') ?>
                    </p>
                </article>
            </div>
        </section>

        <section class="publication-detail-card">
            <div class="publication-card-heading">
                <div>
                    <span>Naskah</span>
                    <h2>Caption dan materi teks</h2>
                </div>
                <?php if (auth_can('content_studio.view')) : ?>
                    <a href="<?= base_url('/content-studio/show/' . $post['id']) ?>">Buka AI Studio →</a>
                <?php endif; ?>
            </div>

            <div class="publication-copy-block">
                <small>HEADLINE ISI</small>
                <strong><?= esc($post['title'] ?: 'Belum ditulis') ?></strong>
            </div>

            <div class="publication-copy-block">
                <small>CAPTION</small>
                <p><?= nl2br(esc($post['caption'] ?: 'Draft caption belum ditulis.')) ?></p>
            </div>

            <div class="publication-copy-columns">
                <div>
                    <small>HASHTAG</small>
                    <p><?= nl2br(esc($post['hashtags'] ?: '-')) ?></p>
                </div>
                <div>
                    <small>MENTION</small>
                    <p><?= nl2br(esc($post['mentions'] ?: '-')) ?></p>
                </div>
            </div>

            <div class="publication-copy-block subtle">
                <small>ALT TEXT</small>
                <p><?= nl2br(esc($post['alt_text'] ?: 'Alt text belum ditulis.')) ?></p>
            </div>
        </section>

        <section class="publication-detail-card">
            <div class="publication-card-heading">
                <div>
                    <span>Aset Produksi</span>
                    <h2>Foto dan bahan visual</h2>
                </div>
                <small><?= count($assets) ?> / 10 aset</small>
            </div>

            <?php if (!empty($assets)) : ?>
                <div class="publication-asset-grid">
                    <?php foreach ($assets as $asset) : ?>
                        <article>
                            <a
                                href="<?= base_url($asset['image_path']) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <img
                                    src="<?= base_url($asset['image_path']) ?>"
                                    alt="<?= esc($asset['original_name'] ?: 'Aset publikasi', 'attr') ?>"
                                >
                            </a>
                            <div>
                                <small><?= esc($asset['original_name'] ?: 'Aset publikasi') ?></small>
                                <?php if (auth_can('publications.assets')) : ?>
                                    <form
                                        method="post"
                                        action="<?= base_url('/publications/' . $post['id'] . '/assets/' . $asset['id'] . '/delete') ?>"
                                        onsubmit="return confirm('Hapus aset ini dari publikasi?')"
                                    >
                                        <?= csrf_field() ?>
                                        <button type="submit">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="publication-empty-state compact">
                    <strong>Belum ada aset foto</strong>
                    <p>Tambahkan dokumentasi sebagai bahan untuk Canva dan AI Studio.</p>
                </div>
            <?php endif; ?>

            <?php if (auth_can('publications.assets')) : ?>
                <form
                    method="post"
                    action="<?= base_url('/publications/' . $post['id'] . '/assets') ?>"
                enctype="multipart/form-data"
                class="publication-asset-upload"
            >
                <?= csrf_field() ?>
                <div>
                    <label for="content_images">Tambah aset</label>
                    <input
                        id="content_images"
                        type="file"
                        name="content_images[]"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple
                        required
                    >
                </div>
                    <button type="submit" class="btn btn-secondary">Upload Aset</button>
                </form>
            <?php endif; ?>
        </section>

        <section class="publication-detail-card publication-metrics-card">
            <div class="publication-card-heading">
                <div>
                    <span>Instagram Insights</span>
                    <h2>Performa publikasi</h2>
                </div>

                <?php if (!empty($latestMetric)) : ?>
                    <small>
                        Snapshot
                        <?= esc(
                            $formatDateTime(
                                $latestMetric['recorded_at']
                                ?? null
                            )
                        ) ?>
                    </small>
                <?php endif; ?>
            </div>

            <?php if (!$metricsReady) : ?>
                <div class="publication-notice">
                    <strong>Tabel metrik belum tersedia</strong>
                    <p>
                        Jalankan migration terbaru agar statistik
                        performa dapat dicatat.
                    </p>
                </div>
            <?php elseif (
                ($post['workflow_status'] ?? '') !== 'published'
            ) : ?>
                <div class="publication-notice">
                    <strong>Menunggu publikasi tayang</strong>
                    <p>
                        Statistik dapat dicatat setelah konten
                        berstatus Dipublikasikan.
                    </p>
                </div>
            <?php else : ?>
                <?php if (!empty($latestMetric)) : ?>
                    <div class="publication-metric-summary-grid">
                        <article>
                            <span>Reach</span>
                            <strong>
                                <?= number_format(
                                    $latestMetricSummary['reach']
                                    ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </strong>
                            <small>Akun unik terjangkau</small>
                        </article>

                        <article>
                            <span>Interaksi</span>
                            <strong>
                                <?= number_format(
                                    $latestMetricSummary[
                                        'interactions'
                                    ] ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </strong>
                            <small>
                                Like, komentar, bagikan, simpan
                            </small>
                        </article>

                        <article>
                            <span>Engagement Rate</span>
                            <strong>
                                <?= number_format(
                                    $latestMetricSummary[
                                        'engagement_rate'
                                    ] ?? 0,
                                    2,
                                    ',',
                                    '.'
                                ) ?>%
                            </strong>
                            <small>Interaksi ÷ reach</small>
                        </article>

                        <article>
                            <span>Simpan + Bagikan</span>
                            <strong>
                                <?= number_format(
                                    (
                                        $latestMetricSummary[
                                            'saves'
                                        ] ?? 0
                                    )
                                    + (
                                        $latestMetricSummary[
                                            'shares'
                                        ] ?? 0
                                    ),
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </strong>
                            <small>Sinyal nilai konten</small>
                        </article>
                    </div>
                <?php else : ?>
                    <div class="publication-empty-state compact">
                        <strong>Belum ada snapshot performa</strong>
                        <p>
                            Salin angka dari Instagram Insights,
                            lalu simpan sebagai baseline pertama.
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (auth_can(
                    'publications.metrics.manage'
                )) : ?>
                    <form
                        method="post"
                        action="<?= base_url(
                            '/publications/'
                            . $post['id']
                            . '/metrics'
                        ) ?>"
                        class="publication-metric-form"
                    >
                        <?= csrf_field() ?>

                        <div class="publication-metric-form__heading">
                            <div>
                                <span>Input Manual</span>
                                <strong>Catat snapshot terbaru</strong>
                            </div>

                            <small>
                                Ambil data dari Instagram Insights.
                            </small>
                        </div>

                        <div class="publication-metric-input-grid">
                            <div class="form-group wide">
                                <label for="recorded_at">
                                    Waktu Snapshot
                                </label>
                                <input
                                    type="datetime-local"
                                    id="recorded_at"
                                    name="recorded_at"
                                    value="<?= esc(
                                        old(
                                            'recorded_at',
                                            date('Y-m-d\TH:i')
                                        ),
                                        'attr'
                                    ) ?>"
                                    required
                                >
                            </div>

                            <?php
                            $metricInputs = [
                                'reach' => 'Reach',
                                'impressions' => 'Impressions',
                                'likes' => 'Likes',
                                'comments' => 'Comments',
                                'shares' => 'Shares',
                                'saves' => 'Saves',
                                'profile_visits' => 'Profile Visits',
                                'follows' => 'Follows',
                                'link_clicks' => 'Link Clicks',
                                'video_views' => 'Video Views',
                            ];
                            ?>

                            <?php foreach (
                                $metricInputs as $field => $label
                            ) : ?>
                                <div class="form-group">
                                    <label for="<?= esc(
                                        $field,
                                        'attr'
                                    ) ?>">
                                        <?= esc($label) ?>
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        id="<?= esc(
                                            $field,
                                            'attr'
                                        ) ?>"
                                        name="<?= esc(
                                            $field,
                                            'attr'
                                        ) ?>"
                                        value="<?= esc(
                                            old($field, ''),
                                            'attr'
                                        ) ?>"
                                        placeholder="0"
                                    >
                                </div>
                            <?php endforeach; ?>

                            <div class="form-group full">
                                <label for="metric_notes">
                                    Catatan Snapshot
                                </label>
                                <textarea
                                    id="metric_notes"
                                    name="metric_notes"
                                    rows="3"
                                    placeholder="Contoh: data 7 hari setelah tayang."
                                ><?= esc(
                                    old('metric_notes', '')
                                ) ?></textarea>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Simpan Snapshot
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (!empty($metricHistory)) : ?>
                    <div class="publication-metric-history">
                        <div class="publication-table-heading">
                            <div>
                                <span>Riwayat</span>
                                <h3>Snapshot performa</h3>
                            </div>

                            <small>
                                <?= count($metricHistory) ?> catatan
                            </small>
                        </div>

                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Reach</th>
                                        <th>Impressions</th>
                                        <th>Interaksi</th>
                                        <th>Simpan</th>
                                        <th>Bagikan</th>
                                        <th>Follows</th>
                                        <th>Pencatat</th>
                                        <?php if (auth_can(
                                            'publications.metrics.manage'
                                        )) : ?>
                                            <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach (
                                        $metricHistory as $metric
                                    ) : ?>
                                        <?php
                                        $interactionTotal =
                                            (int) (
                                                $metric['likes']
                                                ?? 0
                                            )
                                            + (int) (
                                                $metric['comments']
                                                ?? 0
                                            )
                                            + (int) (
                                                $metric['shares']
                                                ?? 0
                                            )
                                            + (int) (
                                                $metric['saves']
                                                ?? 0
                                            );
                                        ?>

                                        <tr>
                                            <td>
                                                <?= esc(
                                                    $formatDateTime(
                                                        $metric[
                                                            'recorded_at'
                                                        ] ?? null
                                                    )
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= number_format(
                                                    (int) (
                                                        $metric[
                                                            'reach'
                                                        ] ?? 0
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= number_format(
                                                    (int) (
                                                        $metric[
                                                            'impressions'
                                                        ] ?? 0
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= number_format(
                                                    $interactionTotal,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= number_format(
                                                    (int) (
                                                        $metric[
                                                            'saves'
                                                        ] ?? 0
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= number_format(
                                                    (int) (
                                                        $metric[
                                                            'shares'
                                                        ] ?? 0
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= number_format(
                                                    (int) (
                                                        $metric[
                                                            'follows'
                                                        ] ?? 0
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= esc(
                                                    $metric[
                                                        'recorded_by'
                                                    ] ?? '-'
                                                ) ?>
                                            </td>

                                            <?php if (auth_can(
                                                'publications.metrics.manage'
                                            )) : ?>
                                                <td>
                                                    <form
                                                        method="post"
                                                        action="<?= base_url(
                                                            '/publications/'
                                                            . $post['id']
                                                            . '/metrics/'
                                                            . $metric['id']
                                                            . '/delete'
                                                        ) ?>"
                                                        onsubmit="return confirm('Hapus snapshot performa ini?')"
                                                    >
                                                        <?= csrf_field() ?>

                                                        <button
                                                            type="submit"
                                                            class="btn btn-danger"
                                                        >
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>


        <?php if (auth_can(
            'publications.audit.view'
        )) : ?>
            <section class="publication-detail-card publication-audit-card">
                <div class="publication-card-heading">
                    <div>
                        <span>Audit Trail</span>
                        <h2>Riwayat perubahan publikasi</h2>
                    </div>

                    <a
                        href="<?= base_url(
                            '/publications/audit?q='
                            . urlencode(
                                $post['content_code']
                                ?: $post['event_title']
                                ?: ''
                            )
                        ) ?>"
                        class="btn btn-secondary"
                    >
                        Lihat Semua
                    </a>
                </div>

                <?php if (!$auditReady) : ?>
                    <div class="publication-notice">
                        <strong>
                            Tabel audit belum tersedia
                        </strong>

                        <p>
                            Jalankan migration terbaru agar perubahan
                            publikasi dapat direkam otomatis.
                        </p>
                    </div>
                <?php elseif (!empty($auditHistory)) : ?>
                    <div class="publication-audit-timeline">
                        <?php foreach (
                            $auditHistory as $log
                        ) : ?>
                            <?php
                            $changedFields = json_decode(
                                (string) (
                                    $log['changed_fields']
                                    ?? ''
                                ),
                                true
                            );

                            $changedLabels = [];

                            if (is_array($changedFields)) {
                                foreach (
                                    $changedFields as $change
                                ) {
                                    $changedLabels[] =
                                        $change['label']
                                        ?? 'Field';
                                }
                            }
                            ?>

                            <article
                                class="publication-audit-event publication-audit-event--<?= esc(
                                    $log['event_type'],
                                    'attr'
                                ) ?>"
                            >
                                <span
                                    class="publication-audit-event__dot"
                                    aria-hidden="true"
                                ></span>

                                <div>
                                    <header>
                                        <div>
                                            <strong>
                                                <?= esc(
                                                    $auditEventLabels[
                                                        $log[
                                                            'event_type'
                                                        ]
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
                                            </strong>

                                            <small>
                                                <?= esc(
                                                    $log[
                                                        'actor_name'
                                                    ] ?? 'Sistem'
                                                ) ?>
                                                ·
                                                <?= esc(
                                                    $log[
                                                        'actor_role'
                                                    ] ?? '-'
                                                ) ?>
                                            </small>
                                        </div>

                                        <time>
                                            <?= esc(
                                                $formatDateTime(
                                                    $log[
                                                        'created_at'
                                                    ] ?? null
                                                )
                                            ) ?>
                                        </time>
                                    </header>

                                    <p>
                                        <?= esc(
                                            $log['summary']
                                            ?? 'Aktivitas tercatat.'
                                        ) ?>
                                    </p>

                                    <?php if (
                                        !empty($log['from_status'])
                                        || !empty($log['to_status'])
                                    ) : ?>
                                        <div class="publication-audit-transition">
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
                                    <?php endif; ?>

                                    <?php if (!empty(
                                        $changedLabels
                                    )) : ?>
                                        <div class="publication-audit-chips">
                                            <?php foreach (
                                                array_slice(
                                                    $changedLabels,
                                                    0,
                                                    8
                                                ) as $label
                                            ) : ?>
                                                <span>
                                                    <?= esc($label) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="publication-empty-state compact">
                        <strong>
                            Belum ada aktivitas audit
                        </strong>

                        <p>
                            Aktivitas baru akan muncul setelah
                            migration dijalankan dan record ini
                            mengalami perubahan berikutnya.
                        </p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

    </div>

    <aside class="publication-detail-sidebar">
        <section class="publication-detail-card publication-production-card">
            <div class="publication-card-heading">
                <div>
                    <span>Produksi</span>
                    <h2>Canva dan format</h2>
                </div>
            </div>

            <dl class="publication-metadata-list">
                <div>
                    <dt>Format</dt>
                    <dd><?= esc($typeLabel) ?></dd>
                </div>
                <div>
                    <dt>Master</dt>
                    <dd><?= esc($post['canva_template_code'] ?: '-') ?></dd>
                </div>
                <div>
                    <dt>Canva Design ID</dt>
                    <dd><?= esc($template['design_id'] ?? '-') ?></dd>
                </div>
                <div>
                    <dt>Ukuran</dt>
                    <dd><?= esc($template['format'] ?? '-') ?></dd>
                </div>
                <div>
                    <dt>Jumlah Halaman</dt>
                    <dd><?= esc($template['pages'] ?? '-') ?></dd>
                </div>
            </dl>

            <?php if (!empty($template['url'])) : ?>
                <a
                    href="<?= esc($template['url'], 'attr') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="publication-master-link"
                    data-canva-master-link
                >
                    <span>MASTER CANVA</span>
                    <strong><?= esc($template['name']) ?></strong>
                    <small>Buka, buat salinan, lalu edit salinannya ↗</small>
                </a>
            <?php endif; ?>

            <?php if (!empty($post['canva_url'])) : ?>
                <a
                    href="<?= esc($post['canva_url'], 'attr') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn publication-canva-button full-width"
                >
                    Buka Desain Kerja Canva ↗
                </a>
            <?php else : ?>
                <div class="publication-notice">
                    <strong>Desain kerja belum ditautkan</strong>
                    <p>Duplikasikan master, lalu simpan tautannya melalui menu Edit.</p>
                </div>
            <?php endif; ?>
        </section>

        <section class="publication-detail-card">
            <div class="publication-card-heading">
                <div>
                    <span>Operasional</span>
                    <h2>PIC dan jadwal</h2>
                </div>
            </div>

            <dl class="publication-metadata-list">
                <div>
                    <dt>Penanggung Jawab</dt>
                    <dd><?= esc($post['owner'] ?: '-') ?></dd>
                </div>
                <div>
                    <dt>Reviewer</dt>
                    <dd><?= esc($post['reviewer'] ?: '-') ?></dd>
                </div>
                <div>
                    <dt>Rencana Tayang</dt>
                    <dd><?= esc($formatDateTime($post['scheduled_at'] ?? null)) ?></dd>
                </div>
                <div>
                    <dt>Disetujui Oleh</dt>
                    <dd><?= esc($post['approved_by'] ?: '-') ?></dd>
                </div>
                <div>
                    <dt>Waktu Tayang</dt>
                    <dd><?= esc($formatDateTime($post['published_at'] ?? null)) ?></dd>
                </div>
            </dl>

            <?php if (!empty($post['instagram_url'])) : ?>
                <a
                    href="<?= esc($post['instagram_url'], 'attr') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-secondary full-width"
                >
                    Buka Postingan Instagram ↗
                </a>
            <?php endif; ?>
        </section>

        <section class="publication-detail-card publication-status-card">
            <div class="publication-card-heading">
                <div>
                    <span>Approval</span>
                    <h2>Perbarui status</h2>
                </div>
            </div>

            <?php if (!empty($allowedTransitions)) : ?>
                <form method="post" action="<?= base_url('/publications/status/' . $post['id']) ?>">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="workflow_status">Status Berikutnya</label>
                        <select id="workflow_status" name="workflow_status" required>
                            <?php foreach ($allowedTransitions as $targetStatus) : ?>
                                <option value="<?= esc($targetStatus, 'attr') ?>">
                                    <?= esc($workflowStatuses[$targetStatus] ?? ucfirst($targetStatus)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="approval_notes">Catatan Review</label>
                        <textarea
                            id="approval_notes"
                            name="approval_notes"
                            rows="4"
                            placeholder="Keputusan, koreksi, atau alasan perubahan status."
                        ><?= esc($post['approval_notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary full-width">Perbarui Status</button>
                </form>
            <?php else : ?>
                <div class="publication-notice">
                    <strong>Alur telah selesai</strong>
                    <p>Tidak ada perpindahan status lanjutan untuk record ini.</p>
                </div>
            <?php endif; ?>
        </section>

        <?php if (!empty($post['notes'])) : ?>
            <section class="publication-detail-card">
                <div class="publication-card-heading">
                    <div>
                        <span>Catatan</span>
                        <h2>Produksi internal</h2>
                    </div>
                </div>
                <p><?= nl2br(esc($post['notes'])) ?></p>
            </section>
        <?php endif; ?>
    </aside>
</div>

</div>

<?= $this->endSection() ?>
