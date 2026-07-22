<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page publication-simple-home">

<?php
$formatActivityDate = static function (
    ?string $value
): string {
    if (empty($value)) {
        return 'Tanggal belum ditentukan';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('d M Y', $timestamp)
        : 'Tanggal belum ditentukan';
};

$formatDateTime = static function (
    ?string $value
): string {
    if (empty($value)) {
        return 'Belum dijadwalkan';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('d M Y · H.i', $timestamp) . ' WIB'
        : 'Belum dijadwalkan';
};

$hasActiveFilters =
    !empty($filters['q'])
    || !empty($filters['status'])
    || !empty($filters['type'])
    || !empty($filters['program_id']);

$criticalDeadlineCount =
    (int) ($deadlineSummary['overdue'] ?? 0)
    + (int) ($deadlineSummary['due_soon'] ?? 0);

$statusDescriptions = $workflowDescriptions ?? [];
?>

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">
            Ruang Kerja Konten Instagram
        </span>

        <h2>Publikasi Sosial</h2>

        <p>
            Siapkan konten Instagram dari kegiatan GARDA 01,
            kerjakan desainnya di Canva, lalu catat hasil tayangnya.
        </p>
    </div>

    <div class="publication-header-actions">
        <a
            href="<?= base_url('/publications/guide') ?>"
            class="btn btn-secondary"
        >
            Panduan Singkat
        </a>

        <?php if (auth_can('publications.create')) : ?>
            <a
                href="<?= base_url('/publications/create') ?>"
                class="btn btn-primary"
            >
                + Buat Konten
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

<section class="publication-purpose-banner">
    <div class="publication-purpose-banner__icon">
        IG
    </div>

    <div>
        <span>Perlu dipahami</span>
        <h3>Modul ini adalah ruang kerja internal Instagram.</h3>
        <p>
            Menyimpan brief di sini tidak otomatis membuat berita
            pada website publik dan tidak otomatis mengunggah
            konten ke Instagram.
        </p>
    </div>

    <div class="publication-purpose-banner__paths">
        <article>
            <strong>Untuk website publik</strong>
            <span>
                Gunakan
                <a href="<?= base_url('/activities') ?>">
                    Data Kegiatan
                </a>
            </span>
        </article>

        <article>
            <strong>Untuk Instagram</strong>
            <span>
                Gunakan Publikasi Sosial, Canva, lalu posting
                secara manual.
            </span>
        </article>
    </div>
</section>

<section class="publication-simple-actions">
    <?php if (auth_can('publications.create')) : ?>
        <a
            href="<?= !empty($activityCandidates)
                ? '#publication-from-activity'
                : base_url('/activities') ?>"
        >
            <span>01</span>
            <div>
                <strong>Buat dari Kegiatan</strong>
                <small>
                    Pilih kegiatan Portal dan isi brief otomatis.
                </small>
            </div>
            <b>→</b>
        </a>
    <?php endif; ?>

    <a href="#publication-work-list">
        <span>02</span>
        <div>
            <strong>Lanjutkan Pekerjaan</strong>
            <small>
                Buka konten yang masih brief, desain, atau review.
            </small>
        </div>
        <b>→</b>
    </a>

    <a href="<?= base_url('/publications/calendar') ?>">
        <span>03</span>
        <div>
            <strong>Lihat Kalender</strong>
            <small>
                Periksa rencana produksi dan waktu tayang.
            </small>
        </div>
        <b>→</b>
    </a>

    <a href="<?= base_url(
        '/publications?status=published'
    ) ?>">
        <span>04</span>
        <div>
            <strong>Konten Sudah Tayang</strong>
            <small>
                Lihat arsip tautan Instagram yang sudah dicatat.
            </small>
        </div>
        <b>→</b>
    </a>
</section>

<section class="publication-simple-flow">
    <div>
        <span>Alur Utama</span>
        <h3>Cukup ikuti lima langkah ini</h3>
    </div>

    <ol>
        <li>
            <span>1</span>
            <div>
                <strong>Pilih kegiatan</strong>
                <small>
                    Ambil sumber dari Data Kegiatan atau buat manual.
                </small>
            </div>
        </li>

        <li>
            <span>2</span>
            <div>
                <strong>Siapkan naskah</strong>
                <small>
                    Tentukan hook, caption, format, dan foto.
                </small>
            </div>
        </li>

        <li>
            <span>3</span>
            <div>
                <strong>Kerjakan desain</strong>
                <small>
                    Salin master Canva, lalu simpan tautan kerja.
                </small>
            </div>
        </li>

        <li>
            <span>4</span>
            <div>
                <strong>Review & jadwalkan</strong>
                <small>
                    Periksa hasil sebelum menentukan waktu tayang.
                </small>
            </div>
        </li>

        <li>
            <span>5</span>
            <div>
                <strong>Posting & catat</strong>
                <small>
                    Posting manual ke Instagram dan simpan tautannya.
                </small>
            </div>
        </li>
    </ol>
</section>

<section class="publication-simple-summary">
    <a href="<?= base_url('/publications') ?>">
        <span>Semua Konten</span>
        <strong><?= (int) ($summary['total'] ?? 0) ?></strong>
    </a>

    <a href="<?= base_url(
        '/publications?status=review'
    ) ?>" class="is-review">
        <span>Menunggu Review</span>
        <strong><?= (int) ($summary['review'] ?? 0) ?></strong>
    </a>

    <a href="<?= base_url(
        '/publications?status=scheduled'
    ) ?>" class="is-scheduled">
        <span>Dijadwalkan</span>
        <strong><?= (int) ($summary['scheduled'] ?? 0) ?></strong>
    </a>

    <a href="<?= base_url(
        '/publications?status=published'
    ) ?>" class="is-published">
        <span>Sudah Tayang</span>
        <strong><?= (int) ($summary['published'] ?? 0) ?></strong>
    </a>
</section>

<?php if (
    auth_can('publications.deadlines.view')
    && $criticalDeadlineCount > 0
) : ?>
    <section class="publication-simple-alert">
        <div>
            <span>Perlu Perhatian</span>
            <strong>
                <?= $criticalDeadlineCount ?>
                pekerjaan mendekati atau melewati deadline.
            </strong>
            <small>
                Buka pusat deadline untuk melihat target berikutnya.
            </small>
        </div>

        <a
            href="<?= base_url('/publications/deadlines') ?>"
            class="btn btn-primary"
        >
            Periksa Deadline
        </a>
    </section>
<?php endif; ?>

<?php if (
    auth_can('publications.create')
    && !empty($activityCandidates)
) : ?>
    <section
        id="publication-from-activity"
        class="publication-activity-candidates publication-simple-section"
    >
        <div class="publication-activity-candidates__heading">
            <div>
                <span>Mulai Paling Mudah</span>
                <h3>Buat konten dari kegiatan yang sudah ada</h3>
                <p>
                    Sistem mengisi judul, tanggal, lokasi, program,
                    caption awal, dan pilihan master Canva.
                </p>
            </div>

            <a
                href="<?= base_url('/activities') ?>"
                class="btn btn-secondary"
            >
                Buka Data Kegiatan
            </a>
        </div>

        <div class="publication-activity-candidate-grid">
            <?php foreach (
                $activityCandidates as $activity
            ) : ?>
                <?php
                $isCompleted =
                    ($activity['status'] ?? '') === 'completed';
                ?>

                <article class="publication-activity-candidate">
                    <div class="publication-activity-candidate__meta">
                        <span class="<?= $isCompleted
                            ? 'completed'
                            : 'planned' ?>">
                            <?= $isCompleted
                                ? 'Sudah Selesai'
                                : 'Akan Dilaksanakan' ?>
                        </span>

                        <small>
                            <?= esc(
                                $formatActivityDate(
                                    $activity['activity_date']
                                    ?? null
                                )
                            ) ?>
                        </small>
                    </div>

                    <h4><?= esc($activity['title']) ?></h4>

                    <p>
                        <?= esc(
                            $activity['program_name']
                            ?: 'Tanpa pilar khusus'
                        ) ?>
                        ·
                        <?= esc(
                            $activity['location']
                            ?: 'Lokasi belum dicatat'
                        ) ?>
                    </p>

                    <?php if (!empty(
                        $activity['summary']
                    )) : ?>
                        <small>
                            <?= esc(
                                mb_strimwidth(
                                    $activity['summary'],
                                    0,
                                    120,
                                    '…'
                                )
                            ) ?>
                        </small>
                    <?php endif; ?>

                    <a
                        href="<?= base_url(
                            '/publications/create/activity/'
                            . $activity['id']
                        ) ?>"
                        class="btn btn-primary"
                    >
                        Buat Konten dari Kegiatan Ini
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section
    id="publication-work-list"
    class="publication-table-card publication-simple-work-list"
>
    <div class="publication-table-heading">
        <div>
            <span>Pekerjaan Konten</span>
            <h3>Lanjutkan pekerjaan yang sudah dibuat</h3>
        </div>

        <small>
            <?= count($posts ?? []) ?>
            record pada halaman ini
        </small>
    </div>

    <details
        class="publication-simple-filter"
        <?= $hasActiveFilters ? 'open' : '' ?>
    >
        <summary>
            <span>
                Cari atau saring konten
                <?= $hasActiveFilters
                    ? '— filter sedang aktif'
                    : '' ?>
            </span>
            <b>Atur Filter</b>
        </summary>

        <form
            method="get"
            action="<?= base_url('/publications') ?>"
        >
            <div class="publication-filter-search">
                <label for="publication-search">
                    Cari publikasi
                </label>

                <input
                    id="publication-search"
                    type="search"
                    name="q"
                    value="<?= esc(
                        $filters['q'] ?? '',
                        'attr'
                    ) ?>"
                    placeholder="Content ID, judul, atau hook"
                >
            </div>

            <div>
                <label for="publication-status">
                    Tahap
                </label>

                <select
                    id="publication-status"
                    name="status"
                >
                    <option value="">Semua tahap</option>

                    <?php foreach (
                        $workflowStatuses as $value => $label
                    ) : ?>
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
                <label for="publication-type">
                    Format
                </label>

                <select
                    id="publication-type"
                    name="type"
                >
                    <option value="">Semua format</option>

                    <?php foreach (
                        $publicationTypes as $value => $label
                    ) : ?>
                        <option
                            value="<?= esc($value, 'attr') ?>"
                            <?= (
                                $filters['type'] ?? ''
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
                <label for="publication-program">
                    Pilar
                </label>

                <select
                    id="publication-program"
                    name="program_id"
                >
                    <option value="">Semua pilar</option>

                    <?php foreach ($programs as $program) : ?>
                        <option
                            value="<?= esc(
                                $program['id'],
                                'attr'
                            ) ?>"
                            <?= (string) (
                                $filters['program_id'] ?? ''
                            ) === (string) $program['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($program['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                Terapkan
            </button>

            <a
                href="<?= base_url('/publications') ?>"
                class="btn btn-secondary"
            >
                Reset
            </a>
        </form>
    </details>

    <div class="table-responsive">
        <table class="publication-table publication-simple-table">
            <thead>
                <tr>
                    <th>Konten</th>
                    <th>Tahap Saat Ini</th>
                    <th>Sumber &amp; Format</th>
                    <th>Penanggung Jawab</th>
                    <th>Target &amp; Desain</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($posts)) : ?>
                    <?php foreach ($posts as $post) : ?>
                        <?php
                        $workflowStatus =
                            $post['workflow_status']
                            ?: 'brief';

                        $templateCode =
                            $post['canva_template_code']
                            ?? '';

                        $template =
                            $templates[$templateCode]
                            ?? null;

                        $canvaUrl =
                            $post['canva_url']
                            ?: ($template['url'] ?? null);
                        ?>

                        <tr>
                            <td data-label="Konten">
                                <strong class="publication-content-code">
                                    <?= esc(
                                        $post['content_code']
                                        ?: 'LEGACY-' . $post['id']
                                    ) ?>
                                </strong>

                                <a
                                    href="<?= base_url(
                                        '/publications/'
                                        . $post['id']
                                    ) ?>"
                                    class="publication-title-link"
                                >
                                    <?= esc(
                                        $post['event_title']
                                        ?: (
                                            $post['title']
                                            ?: 'Tanpa judul'
                                        )
                                    ) ?>
                                </a>

                                <small>
                                    <?= esc(
                                        $post['cover_hook']
                                        ?: 'Hook belum ditentukan'
                                    ) ?>
                                </small>
                            </td>

                            <td data-label="Tahap Saat Ini">
                                <span
                                    class="publication-status publication-status--<?= esc(
                                        $workflowStatus,
                                        'attr'
                                    ) ?>"
                                >
                                    <?= esc(
                                        $workflowStatuses[
                                            $workflowStatus
                                        ]
                                        ?? ucfirst(
                                            $workflowStatus
                                        )
                                    ) ?>
                                </span>

                                <small>
                                    <?= esc(
                                        $statusDescriptions[
                                            $workflowStatus
                                        ]
                                        ?? 'Lanjutkan sesuai workflow.'
                                    ) ?>
                                </small>
                            </td>

                            <td data-label="Sumber & Format">
                                <strong>
                                    <?= esc(
                                        $post['activity_title']
                                        ?: (
                                            $post['program_name']
                                            ?: 'Konten manual'
                                        )
                                    ) ?>
                                </strong>

                                <small>
                                    <?= esc(
                                        $publicationTypes[
                                            $post[
                                                'publication_type'
                                            ]
                                        ]
                                        ?? ucfirst(
                                            $post[
                                                'publication_type'
                                            ] ?: 'feed'
                                        )
                                    ) ?>
                                    ·
                                    <?= esc($templateCode ?: '-') ?>
                                </small>
                            </td>

                            <td data-label="Penanggung Jawab">
                                <strong>
                                    <?= esc(
                                        $post['owner']
                                        ?: 'Belum ditentukan'
                                    ) ?>
                                </strong>

                                <small>
                                    Reviewer:
                                    <?= esc(
                                        $post['reviewer']
                                        ?: 'belum ditentukan'
                                    ) ?>
                                </small>
                            </td>

                            <td data-label="Target & Desain">
                                <strong>
                                    <?= esc(
                                        $formatDateTime(
                                            $post['scheduled_at']
                                            ?? null
                                        )
                                    ) ?>
                                </strong>

                                <?php if ($canvaUrl) : ?>
                                    <a
                                        href="<?= esc(
                                            $canvaUrl,
                                            'attr'
                                        ) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="publication-external-link"
                                        <?= empty($post['canva_url'])
                                            ? 'data-canva-master-link'
                                            : '' ?>
                                    >
                                        <?= !empty($post['canva_url'])
                                            ? 'Buka desain kerja'
                                            : 'Buka master Canva' ?>
                                        ↗
                                    </a>
                                <?php else : ?>
                                    <small>Canva belum tersedia</small>
                                <?php endif; ?>
                            </td>

                            <td data-label="Aksi">
                                <div class="publication-row-actions">
                                    <a
                                        href="<?= base_url(
                                            '/publications/'
                                            . $post['id']
                                        ) ?>"
                                        class="btn btn-primary"
                                    >
                                        Lanjutkan
                                    </a>

                                    <?php if (auth_can(
                                        'publications.update'
                                    )) : ?>
                                        <a
                                            href="<?= base_url(
                                                '/publications/edit/'
                                                . $post['id']
                                            ) ?>"
                                            class="btn btn-secondary"
                                        >
                                            Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">
                            <div class="publication-empty-state">
                                <strong>
                                    Belum ada konten yang sesuai
                                </strong>

                                <p>
                                    Buat konten pertama atau ubah
                                    filter pencarian.
                                </p>

                                <?php if (auth_can(
                                    'publications.create'
                                )) : ?>
                                    <a
                                        href="<?= base_url(
                                            '/publications/create'
                                        ) ?>"
                                        class="btn btn-primary"
                                    >
                                        + Buat Konten
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <?= $pager->links(
            'social_publications',
            'default_full'
        ) ?>
    </div>
</section>

<details class="publication-advanced-tools">
    <summary>
        <div>
            <span>Fitur Lanjutan</span>
            <strong>
                Deadline, analitik, audit, dan alat pendukung
            </strong>
        </div>

        <b>Buka</b>
    </summary>

    <div>
        <?php if (auth_can(
            'publications.deadlines.view'
        )) : ?>
            <a href="<?= base_url(
                '/publications/deadlines'
            ) ?>">
                <strong>Deadline Produksi</strong>
                <small>
                    Periksa konten terlambat dan hambatan kerja.
                </small>
            </a>
        <?php endif; ?>

        <?php if (auth_can(
            'publications.metrics.view'
        )) : ?>
            <a href="<?= base_url(
                '/publications/analytics'
            ) ?>">
                <strong>Analitik Instagram</strong>
                <small>
                    Evaluasi reach, interaksi, dan performa format.
                </small>
            </a>
        <?php endif; ?>

        <?php if (auth_can(
            'publications.recommendations.view'
        )) : ?>
            <a href="<?= base_url(
                '/publications/recommendations'
            ) ?>">
                <strong>Rekomendasi Waktu Tayang</strong>
                <small>
                    Gunakan setelah data Insights mulai terkumpul.
                </small>
            </a>
        <?php endif; ?>

        <?php if (auth_can(
            'publications.audit.view'
        )) : ?>
            <a href="<?= base_url(
                '/publications/audit'
            ) ?>">
                <strong>Audit Trail</strong>
                <small>
                    Lacak perubahan dan pengguna yang melakukannya.
                </small>
            </a>
        <?php endif; ?>

        <?php if (auth_can(
            'content_studio.view'
        )) : ?>
            <a href="<?= base_url('/content-studio') ?>">
                <strong>AI Content Studio</strong>
                <small>
                    Bantu menyusun konsep dan naskah konten.
                </small>
            </a>
        <?php endif; ?>
    </div>
</details>

</div>

<?= $this->endSection() ?>
