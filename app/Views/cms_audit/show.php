<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$auditCssPath = FCPATH . 'assets/css/admin-cms-audit.css';
$auditCssVersion = is_file($auditCssPath)
    ? (string) filemtime($auditCssPath)
    : '1';
$module = (string) ($log['module'] ?? 'system');
$severity = (string) ($log['severity'] ?? 'info');
?>

<link
    rel="stylesheet"
    href="<?= base_url('assets/css/admin-cms-audit.css') ?>?v=<?= esc($auditCssVersion, 'attr') ?>"
>

<div class="cms-audit-page cms-audit-detail-page">

<div class="page-header cms-audit-header">
    <div>
        <span class="cms-audit-eyebrow">Audit Record #<?= (int) $log['id'] ?></span>
        <h2><?= esc($log['summary']) ?></h2>
        <p>Catatan ini bersifat read-only dan tidak dapat diubah melalui Portal.</p>
    </div>

    <div class="cms-audit-header-actions">
        <a href="<?= base_url('/website/audit') ?>" class="btn btn-secondary">
            Kembali ke Audit
        </a>
    </div>
</div>

<section class="cms-audit-detail-summary">
    <article>
        <span>Waktu</span>
        <strong>
            <?= !empty($log['created_at'])
                ? esc(date('d M Y · H.i.s', strtotime($log['created_at'])))
                : '-' ?>
        </strong>
    </article>
    <article>
        <span>Modul</span>
        <strong><?= esc($moduleLabels[$module] ?? $module) ?></strong>
    </article>
    <article>
        <span>Event</span>
        <strong><?= esc($log['event_type']) ?></strong>
    </article>
    <article>
        <span>Tingkat</span>
        <strong><?= esc($severityLabels[$severity] ?? $severity) ?></strong>
    </article>
</section>

<section class="cms-audit-detail-grid">
    <article>
        <header><span>Aktor</span><h3>Pelaku aktivitas</h3></header>
        <dl>
            <div><dt>Nama</dt><dd><?= esc($log['actor_name'] ?: 'Tidak diketahui') ?></dd></div>
            <div><dt>Role</dt><dd><?= esc($log['actor_role'] ?: '-') ?></dd></div>
            <div><dt>Tipe</dt><dd><?= esc(ucfirst($log['actor_type'] ?? 'system')) ?></dd></div>
            <div><dt>User ID</dt><dd><?= !empty($log['user_id']) ? (int) $log['user_id'] : '-' ?></dd></div>
        </dl>
    </article>

    <article>
        <header><span>Subjek</span><h3>Objek yang berubah</h3></header>
        <dl>
            <div><dt>Jenis</dt><dd><?= esc($log['subject_type'] ?: '-') ?></dd></div>
            <div><dt>Label</dt><dd><?= esc($log['subject_label'] ?: '-') ?></dd></div>
            <div><dt>Kunci</dt><dd><?= esc($log['subject_key'] ?: '-') ?></dd></div>
            <div><dt>ID</dt><dd><?= !empty($log['subject_id']) ? (int) $log['subject_id'] : '-' ?></dd></div>
        </dl>
    </article>

    <article>
        <header><span>Request</span><h3>Konteks permintaan</h3></header>
        <dl>
            <div><dt>Metode</dt><dd><?= esc($log['request_method'] ?: '-') ?></dd></div>
            <div><dt>Path</dt><dd><?= esc($log['request_path'] ?: '-') ?></dd></div>
            <div><dt>Hash IP</dt><dd class="audit-hash"><?= esc($log['source_ip_hash'] ?: 'Tidak tersedia') ?></dd></div>
            <div><dt>User Agent</dt><dd><?= esc($log['user_agent'] ?: '-') ?></dd></div>
        </dl>
    </article>
</section>

<?php if (!empty($log['details'])) : ?>
    <section class="cms-audit-detail-card">
        <header><span>Detail</span><h3>Keterangan tambahan</h3></header>
        <p><?= nl2br(esc($log['details'])) ?></p>
    </section>
<?php endif; ?>

<section class="cms-audit-detail-card">
    <header><span>Metadata Aman</span><h3>Data pendukung event</h3></header>
    <?php if ($metadata === []) : ?>
        <p>Tidak ada metadata tambahan.</p>
    <?php else : ?>
        <pre><?= esc(json_encode(
            $metadata,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        )) ?></pre>
    <?php endif; ?>
</section>

<section class="cms-audit-integrity-note">
    <strong>Catatan integritas</strong>
    <p>
        Audit log tidak menyediakan tombol edit atau hapus.
        Penyensoran metadata dilakukan sebelum data disimpan.
    </p>
</section>

</div>

<?= $this->endSection() ?>
