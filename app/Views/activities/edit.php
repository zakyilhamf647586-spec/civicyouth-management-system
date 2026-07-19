<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link
    rel="stylesheet"
    href="<?= base_url('assets/css/admin-activity-workflow.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/admin-activity-workflow.css') ?>"
>

<?php
$currentPublicationStatus =
    $activity['publication_status'] ?? 'draft';

$scheduledInputValue = '';

if (!empty($activity['scheduled_at'])) {
    $scheduledTimestamp = strtotime(
        $activity['scheduled_at']
    );

    if ($scheduledTimestamp !== false) {
        $scheduledInputValue = date(
            'Y-m-d\TH:i',
            $scheduledTimestamp
        );
    }
}

$publicationBadgeClass = match (
    $currentPublicationStatus
) {
    'published' => 'badge-success',
    'review' => 'badge-warning',
    'scheduled' => 'badge-warning',
    'archived' => 'badge-secondary',
    default => 'badge-secondary',
};
?>

<div class="activity-workflow-page">

<div class="page-header">
    <div>
        <h2>Edit Kegiatan</h2>
        <p>
            Perbarui data kegiatan dan kelola status publikasinya tanpa
            mencampurkan status pelaksanaan.
        </p>
    </div>

    <div>
        <?php if (
            \App\Models\ActivityModel::isVisibleToPublic(
                $activity
            )
        ) : ?>
            <a
                href="<?= base_url(
                    '/kegiatan/' . $activity['id']
                ) ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-primary"
            >
                Lihat di Website
            </a>
        <?php endif; ?>

        <a
            href="<?= base_url('/activities') ?>"
            class="btn btn-secondary"
        >
            Kembali
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (
            session()->getFlashdata('errors') as $error
        ) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<div class="filter-card">
    <strong>Status publikasi saat ini</strong>

    <p>
        <span class="badge <?= esc($publicationBadgeClass) ?>">
            <?= esc(
                $publicationStatusLabels[
                    $currentPublicationStatus
                ] ?? 'Draft'
            ) ?>
        </span>

        <?= esc(
            $publicationStatusDescriptions[
                $currentPublicationStatus
            ] ?? ''
        ) ?>
    </p>

    <?php if (!empty($activity['published_at'])) : ?>
        <small>
            Terakhir diterbitkan:
            <?= esc(
                date(
                    'd M Y H:i',
                    strtotime($activity['published_at'])
                )
            ) ?> WIB
        </small>
    <?php endif; ?>
</div>

<div class="form-card">
    <form
        action="<?= base_url(
            '/activities/update/' . $activity['id']
        ) ?>"
        method="post"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="program_id">Pilar Program GARDA 01</label>

            <select id="program_id" name="program_id">
                <option value="">Belum Dikategorikan</option>

                <?php foreach ($programs as $program) : ?>
                    <option
                        value="<?= (int) $program['id'] ?>"
                        <?= old(
                            'program_id',
                            $activity['program_id'] ?? ''
                        ) == $program['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($program['name']) ?>
                        — <?= esc($program['label'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <small>
                Pilar program wajib dipilih sebelum konten ditinjau,
                dijadwalkan, atau diterbitkan.
            </small>
        </div>

        <div class="form-group">
            <label for="title">Nama Kegiatan</label>
            <input
                type="text"
                id="title"
                name="title"
                value="<?= esc(
                    old('title', $activity['title'])
                ) ?>"
                maxlength="150"
                required
            >
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="activity_date">Tanggal Kegiatan</label>
                <input
                    type="date"
                    id="activity_date"
                    name="activity_date"
                    value="<?= esc(
                        old(
                            'activity_date',
                            $activity['activity_date']
                        )
                    ) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="location">Lokasi</label>
                <input
                    type="text"
                    id="location"
                    name="location"
                    value="<?= esc(
                        old(
                            'location',
                            $activity['location']
                        )
                    ) ?>"
                    maxlength="200"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="summary">Ringkasan Publik</label>
            <textarea
                id="summary"
                name="summary"
                rows="3"
                maxlength="220"
                placeholder="Ringkasan singkat 40–220 karakter untuk kartu kegiatan."
            ><?= esc(
                old(
                    'summary',
                    $activity['summary'] ?? ''
                )
            ) ?></textarea>
            <small>
                Minimal 40 karakter untuk status Review, Published, atau
                Scheduled.
            </small>
        </div>

        <div class="form-group">
            <label for="description">Deskripsi Kegiatan</label>
            <textarea
                id="description"
                name="description"
                rows="7"
                placeholder="Tuliskan konteks, tujuan, proses, dan pihak yang terlibat."
            ><?= esc(
                old(
                    'description',
                    $activity['description'] ?? ''
                )
            ) ?></textarea>
            <small>
                Minimal 50 karakter sebelum konten ditinjau atau diterbitkan.
            </small>
        </div>

        <div class="form-group">
            <label for="result">Hasil dan Dampak Kegiatan</label>
            <textarea
                id="result"
                name="result"
                rows="5"
                placeholder="Tuliskan hasil, manfaat, capaian, atau tindak lanjut."
            ><?= esc(
                old('result', $activity['result'] ?? '')
            ) ?></textarea>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="documentation_link">
                    Tautan Dokumentasi
                </label>
                <input
                    type="url"
                    id="documentation_link"
                    name="documentation_link"
                    value="<?= esc(
                        old(
                            'documentation_link',
                            $activity['documentation_link'] ?? ''
                        )
                    ) ?>"
                    placeholder="https://..."
                >
            </div>

            <div class="form-group">
                <label for="documentation_file">
                    Ganti Foto Dokumentasi Utama
                </label>
                <input
                    type="file"
                    id="documentation_file"
                    name="documentation_file"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <small>
                    Kosongkan jika tidak ingin mengganti foto. Maksimal 4 MB.
                </small>
            </div>
        </div>

        <?php if (!empty($activity['documentation_file'])) : ?>
            <div class="form-group">
                <label>Dokumentasi Saat Ini</label><br>
                <img
                    src="<?= base_url(
                        'uploads/activities/'
                        . $activity['documentation_file']
                    ) ?>"
                    alt="Dokumentasi <?= esc(
                        $activity['title']
                    ) ?>"
                    style="max-width:220px;border-radius:12px;border:1px solid #d9e2ec;"
                >
            </div>
        <?php endif; ?>

        <div class="grid-2">
            <div class="form-group">
                <label for="status">Status Pelaksanaan</label>
                <select id="status" name="status" required>
                    <?php foreach (
                        $executionStatusLabels as $value => $label
                    ) : ?>
                        <option
                            value="<?= esc($value) ?>"
                            <?= old(
                                'status',
                                $activity['status']
                            ) === $value
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="scheduled_at">Jadwal Publikasi</label>
                <input
                    type="datetime-local"
                    id="scheduled_at"
                    name="scheduled_at"
                    value="<?= esc(
                        old(
                            'scheduled_at',
                            $scheduledInputValue
                        )
                    ) ?>"
                >
                <small>
                    Wajib diisi ketika memilih Jadwalkan Publikasi.
                </small>
            </div>
        </div>

        <div class="form-group">
            <input type="hidden" name="is_featured" value="0">

            <label class="activity-feature-toggle">
                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    <?= old(
                        'is_featured',
                        (string) ($activity['is_featured'] ?? 0)
                    ) === '1'
                        ? 'checked'
                        : '' ?>
                >
                Jadikan kegiatan unggulan / Cerita Dampak
            </label>
        </div>

        <div class="form-group">
            <label for="review_notes">Catatan Internal / Reviewer</label>
            <textarea
                id="review_notes"
                name="review_notes"
                rows="4"
                maxlength="2000"
                placeholder="Catatan koreksi atau arahan. Tidak tampil pada website publik."
            ><?= esc(
                old(
                    'review_notes',
                    $activity['review_notes'] ?? ''
                )
            ) ?></textarea>
        </div>

        <div class="filter-card activity-workflow-action-panel">
            <strong>Kelola penyimpanan dan publikasi</strong>
            <p>
                Simpan Perubahan mempertahankan status publikasi saat ini.
                Gunakan tombol lain untuk memindahkan kegiatan ke tahap
                workflow yang berbeda.
            </p>

            <div class="activity-workflow-action-buttons">

            <button
                type="submit"
                name="workflow_action"
                value="save_changes"
                class="btn btn-primary"
            >
                Simpan Perubahan
            </button>

            <button
                type="submit"
                name="workflow_action"
                value="save_draft"
                class="btn btn-secondary"
            >
                Simpan sebagai Draft
            </button>

            <button
                type="submit"
                name="workflow_action"
                value="submit_review"
                class="btn btn-warning"
            >
                Kirim untuk Ditinjau
            </button>

            <button
                type="submit"
                name="workflow_action"
                value="publish_now"
                class="btn btn-primary"
            >
                Terbitkan Sekarang
            </button>

            <button
                type="submit"
                name="workflow_action"
                value="schedule"
                class="btn btn-secondary"
            >
                Jadwalkan Publikasi
            </button>

            <a
                href="<?= base_url('/activities') ?>"
                class="btn btn-secondary"
            >
                Batal
            </a>

            </div>
        </div>
    </form>
</div>

</div>

<?= $this->endSection() ?>
