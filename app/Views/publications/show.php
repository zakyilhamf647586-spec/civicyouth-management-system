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
