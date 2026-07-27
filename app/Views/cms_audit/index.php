<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$auditCssPath = FCPATH . 'assets/css/admin-cms-audit.css';
$auditCssVersion = is_file($auditCssPath)
    ? (string) filemtime($auditCssPath)
    : '1';

$moduleCounts = $statistics['module_counts'] ?? [];
$queryForExport = array_filter(
    $filters,
    static fn ($value): bool => trim((string) $value) !== ''
);
?>

<link
    rel="stylesheet"
    href="<?= base_url('assets/css/admin-cms-audit.css') ?>?v=<?= esc($auditCssVersion, 'attr') ?>"
>

<div class="cms-audit-page">

<div class="page-header cms-audit-header">
    <div>
        <span class="cms-audit-eyebrow">
            Governance & Accountability
        </span>

        <h2>Audit Aktivitas CMS</h2>

        <p>
            Satu pusat pemeriksaan untuk perubahan halaman,
            navigasi, program, kegiatan, publikasi sosial,
            review eksternal, dan penolakan akses.
        </p>
    </div>

    <div class="cms-audit-header-actions">
        <?php if (auth_can('website.audit.export')) : ?>
            <a
                href="<?= base_url('/website/audit/export') ?><?= $queryForExport !== []
                    ? '?' . http_build_query($queryForExport)
                    : '' ?>"
                class="btn btn-secondary"
            >
                Export CSV
            </a>
        <?php endif; ?>
    </div>
</div>

<section class="cms-audit-privacy">
    <div>
        <strong>Privasi diterapkan sejak pencatatan</strong>
        <p>
            Alamat IP disimpan sebagai hash. Password, token mentah,
            token hash, cookie, dan data autentikasi otomatis
            disensor dari metadata audit.
        </p>
    </div>
    <span>Ringkasan 30 hari</span>
</section>

<section class="cms-audit-stat-grid">
    <article>
        <span>Aktivitas 30 Hari</span>
        <strong><?= (int) ($statistics['total_30_days'] ?? 0) ?></strong>
        <small>Seluruh event CMS yang tercatat.</small>
    </article>

    <article>
        <span>Hari Ini</span>
        <strong><?= (int) ($statistics['today'] ?? 0) ?></strong>
        <small>Aktivitas sejak pukul 00.00.</small>
    </article>

    <article>
        <span>Event Keamanan</span>
        <strong><?= (int) ($statistics['security_30_days'] ?? 0) ?></strong>
        <small>Penolakan izin dan akses eksternal.</small>
    </article>

    <article>
        <span>Aktivitas Eksternal</span>
        <strong><?= (int) ($statistics['external_30_days'] ?? 0) ?></strong>
        <small>Preview dan tanggapan reviewer eksternal.</small>
    </article>

    <article>
        <span>Aktor Internal</span>
        <strong><?= (int) ($statistics['unique_internal_actors'] ?? 0) ?></strong>
        <small>Pengguna Portal unik dalam 30 hari.</small>
    </article>
</section>

<section class="cms-audit-module-strip">
    <?php foreach ($moduleLabels as $moduleKey => $moduleLabel) : ?>
        <a
            href="<?= base_url('/website/audit') ?>?module=<?= rawurlencode($moduleKey) ?>"
            class="<?= ($filters['module'] ?? '') === $moduleKey ? 'is-active' : '' ?>"
        >
            <span><?= esc($moduleLabel) ?></span>
            <strong><?= (int) ($moduleCounts[$moduleKey] ?? 0) ?></strong>
        </a>
    <?php endforeach; ?>
</section>

<form
    action="<?= base_url('/website/audit') ?>"
    method="get"
    class="cms-audit-filter-card"
>
    <div class="cms-audit-filter-grid">
        <div>
            <label for="audit_q">Cari aktivitas</label>
            <input
                id="audit_q"
                name="q"
                type="search"
                value="<?= esc($filters['q'] ?? '', 'attr') ?>"
                placeholder="Ringkasan, aktor, subjek, atau event"
            >
        </div>

        <div>
            <label for="audit_module">Modul</label>
            <select id="audit_module" name="module">
                <option value="">Semua modul</option>
                <?php foreach ($moduleLabels as $moduleKey => $moduleLabel) : ?>
                    <option
                        value="<?= esc($moduleKey, 'attr') ?>"
                        <?= ($filters['module'] ?? '') === $moduleKey ? 'selected' : '' ?>
                    ><?= esc($moduleLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="audit_event">Jenis event</label>
            <select id="audit_event" name="event_type">
                <option value="">Semua event</option>
                <?php foreach ($eventTypes as $eventType) : ?>
                    <option
                        value="<?= esc($eventType, 'attr') ?>"
                        <?= ($filters['event_type'] ?? '') === $eventType ? 'selected' : '' ?>
                    ><?= esc($eventType) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="audit_severity">Tingkat</label>
            <select id="audit_severity" name="severity">
                <option value="">Semua tingkat</option>
                <?php foreach ($severityLabels as $severityKey => $severityLabel) : ?>
                    <option
                        value="<?= esc($severityKey, 'attr') ?>"
                        <?= ($filters['severity'] ?? '') === $severityKey ? 'selected' : '' ?>
                    ><?= esc($severityLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="audit_actor_type">Tipe aktor</label>
            <select id="audit_actor_type" name="actor_type">
                <option value="">Semua aktor</option>
                <?php foreach ([
                    'internal' => 'Internal',
                    'external' => 'Eksternal',
                    'system' => 'Sistem',
                ] as $actorKey => $actorLabel) : ?>
                    <option
                        value="<?= esc($actorKey, 'attr') ?>"
                        <?= ($filters['actor_type'] ?? '') === $actorKey ? 'selected' : '' ?>
                    ><?= esc($actorLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="audit_date_from">Dari tanggal</label>
            <input
                id="audit_date_from"
                name="date_from"
                type="date"
                value="<?= esc($filters['date_from'] ?? '', 'attr') ?>"
            >
        </div>

        <div>
            <label for="audit_date_to">Sampai tanggal</label>
            <input
                id="audit_date_to"
                name="date_to"
                type="date"
                value="<?= esc($filters['date_to'] ?? '', 'attr') ?>"
            >
        </div>
    </div>

    <div class="cms-audit-filter-actions">
        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
        <a href="<?= base_url('/website/audit') ?>" class="btn btn-secondary">Reset</a>
    </div>
</form>

<section class="cms-audit-log-card">
    <header>
        <div>
            <span>Audit Timeline</span>
            <h3>Aktivitas terbaru</h3>
        </div>
        <small>Maksimal 30 event per halaman</small>
    </header>

    <?php if ($logs === []) : ?>
        <div class="cms-audit-empty">
            <strong>Tidak ada aktivitas yang sesuai</strong>
            <p>Ubah filter atau lakukan aktivitas CMS baru.</p>
        </div>
    <?php else : ?>
        <div class="cms-audit-list">
            <?php foreach ($logs as $log) : ?>
                <?php
                $module = (string) ($log['module'] ?? 'system');
                $severity = (string) ($log['severity'] ?? 'info');
                $subject = trim((string) (
                    $log['subject_label']
                    ?? $log['subject_key']
                    ?? ''
                ));
                $actor = trim((string) ($log['actor_name'] ?? ''));

                if ($actor === '') {
                    $actor = ($log['actor_type'] ?? '') === 'system'
                        ? 'GARDA 01 System'
                        : 'Tidak diketahui';
                }
                ?>

                <article class="cms-audit-item">
                    <div class="cms-audit-time">
                        <strong>
                            <?= !empty($log['created_at'])
                                ? esc(date('H.i', strtotime($log['created_at'])))
                                : '-' ?>
                        </strong>
                        <span>
                            <?= !empty($log['created_at'])
                                ? esc(date('d M Y', strtotime($log['created_at'])))
                                : '-' ?>
                        </span>
                    </div>

                    <div class="cms-audit-item-main">
                        <div class="cms-audit-item-tags">
                            <span class="audit-module">
                                <?= esc($moduleLabels[$module] ?? $module) ?>
                            </span>
                            <span class="audit-severity severity-<?= esc($severity, 'attr') ?>">
                                <?= esc($severityLabels[$severity] ?? $severity) ?>
                            </span>
                        </div>

                        <h4><?= esc($log['summary']) ?></h4>

                        <p>
                            <strong><?= esc($actor) ?></strong>
                            <?php if (!empty($log['actor_role'])) : ?>
                                · <?= esc($log['actor_role']) ?>
                            <?php endif; ?>
                            <?php if ($subject !== '') : ?>
                                · <?= esc($subject) ?>
                            <?php endif; ?>
                        </p>

                        <small>
                            <?= esc($log['event_type']) ?>
                            <?php if (!empty($log['request_path'])) : ?>
                                · <?= esc($log['request_method'] ?? '') ?>
                                <?= esc($log['request_path']) ?>
                            <?php endif; ?>
                        </small>
                    </div>

                    <a
                        href="<?= base_url('/website/audit/' . $log['id']) ?>"
                        class="btn btn-secondary"
                    >Detail</a>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="cms-audit-pagination">
            <?= $pager->links('cms_audit') ?>
        </div>
    <?php endif; ?>
</section>

</div>

<?= $this->endSection() ?>
