<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= $this->include('publications/_assets') ?>

<div class="publication-admin-page">

<?php
$formatActivityDate = static function (?string $value): string {
    if (empty($value)) {
        return 'Tanggal belum ditentukan';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('d M Y', $timestamp)
        : 'Tanggal belum ditentukan';
};

$formatDateTime = static function (?string $value): string {
    if (empty($value)) {
        return 'Belum dijadwalkan';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('d M Y · H.i', $timestamp) . ' WIB'
        : 'Belum dijadwalkan';
};
?>

<div class="page-header publication-page-header">
    <div>
        <span class="publication-eyebrow">Media Operations</span>
        <h2>Publikasi Sosial</h2>
        <p>Satu pusat kendali untuk brief, Canva, approval, jadwal, dan arsip Instagram.</p>
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
            href="<?= base_url('/publications/calendar') ?>"
            class="btn btn-secondary"
        >
            Kalender Konten
        </a>

        <?php if (auth_can('content_studio.view')) : ?>
            <a href="<?= base_url('/content-studio') ?>" class="btn btn-secondary">AI Content Studio</a>
        <?php endif; ?>

        <?php if (auth_can('publications.create')) : ?>
            <a href="<?= base_url('/publications/create') ?>" class="btn btn-primary">+ Buat Publikasi</a>
        <?php endif; ?>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<section class="publication-summary-grid">
    <article>
        <span>Total Publikasi</span>
        <strong><?= esc($summary['total'] ?? 0) ?></strong>
        <small>Seluruh record media</small>
    </article>
    <article>
        <span>Dalam Produksi</span>
        <strong><?= esc($summary['in_progress'] ?? 0) ?></strong>
        <small>Brief, draft, desain, revisi</small>
    </article>
    <article class="tone-review">
        <span>Menunggu Review</span>
        <strong><?= esc($summary['review'] ?? 0) ?></strong>
        <small>Memerlukan keputusan</small>
    </article>
    <article class="tone-scheduled">
        <span>Dijadwalkan</span>
        <strong><?= esc($summary['scheduled'] ?? 0) ?></strong>
        <small>Siap tayang</small>
    </article>
    <article class="tone-published">
        <span>Dipublikasikan</span>
        <strong><?= esc($summary['published'] ?? 0) ?></strong>
        <small>Tautan tayang tercatat</small>
    </article>
</section>


<?php if (
    auth_can('publications.create')
    && !empty($activityCandidates)
) : ?>
    <section class="publication-activity-candidates">
        <div class="publication-activity-candidates__heading">
            <div>
                <span>Brief Otomatis</span>
                <h3>Kegiatan yang siap diolah menjadi konten</h3>
                <p>
                    Data judul, tanggal, lokasi, program, ringkasan,
                    caption awal, dan master Canva akan diisi otomatis.
                </p>
            </div>

            <a
                href="<?= base_url('/activities') ?>"
                class="btn btn-secondary"
            >
                Lihat Data Kegiatan
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
                                ? 'Selesai'
                                : 'Direncanakan' ?>
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
                                    110,
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
                        Buat Brief Otomatis
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="publication-filter-card">
    <form method="get" action="<?= base_url('/publications') ?>">
        <div class="publication-filter-search">
            <label for="publication-search">Cari publikasi</label>
            <input
                id="publication-search"
                type="search"
                name="q"
                value="<?= esc($filters['q'] ?? '', 'attr') ?>"
                placeholder="Content ID, judul, atau hook"
            >
        </div>

        <div>
            <label for="publication-status">Status</label>
            <select id="publication-status" name="status">
                <option value="">Semua status</option>
                <?php foreach ($workflowStatuses as $value => $label) : ?>
                    <option
                        value="<?= esc($value, 'attr') ?>"
                        <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>
                    >
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="publication-type">Format</label>
            <select id="publication-type" name="type">
                <option value="">Semua format</option>
                <?php foreach ($publicationTypes as $value => $label) : ?>
                    <option
                        value="<?= esc($value, 'attr') ?>"
                        <?= ($filters['type'] ?? '') === $value ? 'selected' : '' ?>
                    >
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="publication-program">Pilar</label>
            <select id="publication-program" name="program_id">
                <option value="">Semua pilar</option>
                <?php foreach ($programs as $program) : ?>
                    <option
                        value="<?= esc($program['id'], 'attr') ?>"
                        <?= (string) ($filters['program_id'] ?? '') === (string) $program['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($program['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Terapkan</button>
        <a href="<?= base_url('/publications') ?>" class="btn btn-secondary">Reset</a>
    </form>
</section>

<section class="publication-table-card">
    <div class="publication-table-heading">
        <div>
            <span>Pipeline Publikasi</span>
            <h3>Daftar pekerjaan media</h3>
        </div>

        <small><?= count($posts ?? []) ?> record pada halaman ini</small>
    </div>

    <div class="table-responsive">
        <table class="publication-table">
            <thead>
                <tr>
                    <th>Content ID</th>
                    <th>Publikasi</th>
                    <th>Pilar & Format</th>
                    <th>Status</th>
                    <th>Jadwal</th>
                    <th>PIC</th>
                    <th>Canva</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($posts)) : ?>
                    <?php foreach ($posts as $post) : ?>
                        <?php
                        $workflowStatus = $post['workflow_status'] ?: 'brief';
                        $templateCode = $post['canva_template_code'] ?? '';
                        $template = $templates[$templateCode] ?? null;
                        $canvaUrl = $post['canva_url'] ?: ($template['url'] ?? null);
                        ?>
                        <tr>
                            <td data-label="Content ID">
                                <strong class="publication-content-code">
                                    <?= esc($post['content_code'] ?: 'LEGACY-' . $post['id']) ?>
                                </strong>
                                <small><?= esc($post['priority'] ?? 'normal') ?></small>
                            </td>
                            <td data-label="Publikasi">
                                <a
                                    href="<?= base_url('/publications/' . $post['id']) ?>"
                                    class="publication-title-link"
                                >
                                    <?= esc($post['event_title'] ?: ($post['title'] ?: 'Tanpa judul')) ?>
                                </a>
                                <small>
                                    <?= esc($post['cover_hook'] ?: 'Hook belum ditentukan') ?>
                                </small>
                            </td>
                            <td data-label="Pilar & Format">
                                <strong><?= esc($post['program_name'] ?: 'Umum') ?></strong>
                                <small>
                                    <?= esc($publicationTypes[$post['publication_type']] ?? ucfirst($post['publication_type'] ?: 'feed')) ?>
                                    · <?= esc($templateCode ?: '-') ?>
                                </small>
                            </td>
                            <td data-label="Status">
                                <span class="publication-status publication-status--<?= esc($workflowStatus, 'attr') ?>">
                                    <?= esc($workflowStatuses[$workflowStatus] ?? ucfirst($workflowStatus)) ?>
                                </span>
                            </td>
                            <td data-label="Jadwal">
                                <strong><?= esc($formatDateTime($post['scheduled_at'] ?? null)) ?></strong>
                                <small>
                                    <?= !empty($post['published_at'])
                                        ? 'Tayang ' . esc($formatDateTime($post['published_at']))
                                        : 'Belum tayang' ?>
                                </small>
                            </td>
                            <td data-label="PIC">
                                <strong><?= esc($post['owner'] ?: '-') ?></strong>
                                <small>Review: <?= esc($post['reviewer'] ?: '-') ?></small>
                            </td>
                            <td data-label="Canva">
                                <?php if ($canvaUrl) : ?>
                                    <a
                                        href="<?= esc($canvaUrl, 'attr') ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="publication-external-link"
                                        <?= empty($post['canva_url']) ? 'data-canva-master-link' : '' ?>
                                    >
                                        <?= !empty($post['canva_url']) ? 'Desain Kerja' : 'Master' ?> ↗
                                    </a>
                                <?php else : ?>
                                    <span class="publication-muted">Belum tersedia</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Aksi">
                                <div class="publication-row-actions">
                                    <a href="<?= base_url('/publications/' . $post['id']) ?>" class="btn btn-primary">Buka</a>

                                    <?php if (auth_can('publications.update')) : ?>
                                        <a href="<?= base_url('/publications/edit/' . $post['id']) ?>" class="btn btn-secondary">Edit</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8">
                            <div class="publication-empty-state">
                                <strong>Belum ada publikasi yang sesuai</strong>
                                <p>Buat brief pertama atau ubah filter pencarian.</p>
                                <?php if (auth_can('publications.create')) : ?>
                                    <a href="<?= base_url('/publications/create') ?>" class="btn btn-primary">+ Buat Publikasi</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <?= $pager->links('social_publications', 'default_full') ?>
    </div>
</section>

</div>

<?= $this->endSection() ?>
