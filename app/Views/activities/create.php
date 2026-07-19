<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Tambah Kegiatan</h2>
        <p>
            Catat kegiatan, siapkan konten publik, lalu simpan sebagai
            draft, kirim untuk ditinjau, atau terbitkan.
        </p>
    </div>

    <a
        href="<?= base_url('/activities') ?>"
        class="btn btn-secondary"
    >
        Kembali
    </a>
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

<div class="filter-card">
    <strong>Alur publikasi kegiatan</strong>
    <p>
        Draft belum tampil publik. Status Review menandakan konten siap
        diperiksa. Konten Published tampil langsung, sedangkan Scheduled
        akan tampil otomatis pada waktu yang ditentukan.
    </p>
</div>

<div class="form-card">
    <form
        action="<?= base_url('/activities/store') ?>"
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
                        <?= old('program_id') == $program['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($program['name']) ?>
                        — <?= esc($program['label'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <small>
                Pilar program wajib dipilih sebelum konten dikirim untuk
                ditinjau, dijadwalkan, atau diterbitkan.
            </small>
        </div>

        <div class="form-group">
            <label for="title">Nama Kegiatan</label>
            <input
                type="text"
                id="title"
                name="title"
                value="<?= esc(old('title')) ?>"
                maxlength="150"
                placeholder="Contoh: Kerja Bakti Lingkungan RW 01"
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
                        old('activity_date', date('Y-m-d'))
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
                    value="<?= esc(old('location')) ?>"
                    maxlength="200"
                    placeholder="Contoh: Lingkungan RW 01 Randugarut"
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
                placeholder="Ringkasan singkat 40–220 karakter untuk kartu kegiatan dan hasil pencarian."
            ><?= esc(old('summary')) ?></textarea>
            <small>
                Minimal 40 karakter untuk Review, Published, atau Scheduled.
            </small>
        </div>

        <div class="form-group">
            <label for="description">Deskripsi Kegiatan</label>
            <textarea
                id="description"
                name="description"
                rows="7"
                placeholder="Tuliskan latar belakang, tujuan, proses, pihak yang terlibat, serta konteks kegiatan."
            ><?= esc(old('description')) ?></textarea>
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
                placeholder="Tuliskan hasil, manfaat, capaian, atau tindak lanjut kegiatan."
            ><?= esc(old('result')) ?></textarea>
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
                    value="<?= esc(old('documentation_link')) ?>"
                    placeholder="https://..."
                >
            </div>

            <div class="form-group">
                <label for="documentation_file">
                    Foto Dokumentasi Utama
                </label>
                <input
                    type="file"
                    id="documentation_file"
                    name="documentation_file"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <small>JPG, PNG, atau WEBP. Maksimal 4 MB.</small>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="status">Status Pelaksanaan</label>
                <select id="status" name="status" required>
                    <?php foreach (
                        $executionStatusLabels as $value => $label
                    ) : ?>
                        <option
                            value="<?= esc($value) ?>"
                            <?= old('status', 'planned') === $value
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>
                    Status ini menjelaskan pelaksanaan kegiatan, bukan status
                    publikasinya.
                </small>
            </div>

            <div class="form-group">
                <label for="scheduled_at">Jadwal Publikasi</label>
                <input
                    type="datetime-local"
                    id="scheduled_at"
                    name="scheduled_at"
                    value="<?= esc(old('scheduled_at')) ?>"
                >
                <small>
                    Wajib diisi hanya ketika memilih tombol Jadwalkan.
                </small>
            </div>
        </div>

        <div class="form-group">
            <input type="hidden" name="is_featured" value="0">

            <label>
                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    <?= old('is_featured') === '1'
                        ? 'checked'
                        : '' ?>
                >
                Jadikan kegiatan unggulan / Cerita Dampak
            </label>

            <small>
                Gunakan hanya untuk kegiatan dengan dokumentasi dan narasi
                yang paling kuat.
            </small>
        </div>

        <div class="form-group">
            <label for="review_notes">Catatan Internal</label>
            <textarea
                id="review_notes"
                name="review_notes"
                rows="3"
                maxlength="2000"
                placeholder="Catatan untuk editor atau reviewer. Tidak ditampilkan pada website publik."
            ><?= esc(old('review_notes')) ?></textarea>
        </div>

        <div class="filter-card">
            <strong>Pilih tindakan penyimpanan</strong>
            <p>
                Simpan Draft untuk melanjutkan nanti. Kirim Review untuk
                pemeriksaan. Terbitkan Sekarang untuk langsung tampil.
                Jadwalkan memakai waktu publikasi di atas.
            </p>

            <button
                type="submit"
                name="workflow_action"
                value="save_draft"
                class="btn btn-secondary"
            >
                Simpan Draft
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
    </form>
</div>

<?= $this->endSection() ?>
