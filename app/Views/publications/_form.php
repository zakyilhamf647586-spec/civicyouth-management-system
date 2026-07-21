<?php
$formValue = static function (
    string $field,
    $default = ''
) use ($post) {
    $oldValue = old($field);

    if ($oldValue !== null) {
        return $oldValue;
    }

    return $post[$field] ?? $default;
};

$scheduledValue = $formValue('scheduled_at');

if (!empty($scheduledValue)) {
    $scheduledTimestamp = strtotime((string) $scheduledValue);
    $scheduledValue = $scheduledTimestamp
        ? date('Y-m-d\TH:i', $scheduledTimestamp)
        : '';
}

$selectedTemplate = (string) $formValue(
    'canva_template_code',
    'COVER-00'
);
?>

<form
    action="<?= base_url($formAction) ?>"
    method="post"
    enctype="multipart/form-data"
    class="publication-form"
    data-publication-form
>
    <?= csrf_field() ?>

    <div class="publication-form-layout">
        <div class="publication-form-main">
            <section class="publication-section-card">
                <div class="publication-section-heading">
                    <span>01</span>
                    <div>
                        <h3>Sumber dan identitas konten</h3>
                        <p>Hubungkan publikasi dengan pilar dan kegiatan di Portal.</p>
                    </div>
                </div>

                <div class="publication-form-grid two-columns">
                    <div class="form-group">
                        <label for="program_id">Pilar / Program</label>
                        <select id="program_id" name="program_id">
                            <option value="">Tanpa pilar khusus</option>
                            <?php foreach ($programs as $program) : ?>
                                <option
                                    value="<?= esc($program['id'], 'attr') ?>"
                                    <?= (string) $formValue('program_id') === (string) $program['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($program['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="activity_id">Sumber Kegiatan Portal</label>
                        <select id="activity_id" name="activity_id">
                            <option value="">Tidak terkait kegiatan tertentu</option>
                            <?php foreach ($activities as $activity) : ?>
                                <option
                                    value="<?= esc($activity['id'], 'attr') ?>"
                                    <?= (string) $formValue('activity_id') === (string) $activity['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($activity['title']) ?>
                                    <?= !empty($activity['program_name'])
                                        ? ' · ' . esc($activity['program_name'])
                                        : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="event_title">Judul Internal Publikasi</label>
                    <input
                        id="event_title"
                        type="text"
                        name="event_title"
                        value="<?= esc($formValue('event_title'), 'attr') ?>"
                        placeholder="Contoh: Dokumentasi Fun Mini Soccer Juli 2026"
                        required
                    >
                    <small>Judul ini dipakai untuk pencarian dan arsip internal.</small>
                </div>

                <div class="publication-form-grid three-columns">
                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" required>
                            <?php foreach ($categories as $value => $label) : ?>
                                <option
                                    value="<?= esc($value, 'attr') ?>"
                                    <?= (string) $formValue('category', 'dokumentasi_kegiatan') === $value
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="event_date">Tanggal Kegiatan</label>
                        <input
                            id="event_date"
                            type="date"
                            name="event_date"
                            value="<?= esc($formValue('event_date'), 'attr') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="event_time">Waktu Kegiatan</label>
                        <input
                            id="event_time"
                            type="time"
                            name="event_time"
                            value="<?= esc($formValue('event_time'), 'attr') ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="event_location">Lokasi</label>
                    <input
                        id="event_location"
                        type="text"
                        name="event_location"
                        value="<?= esc($formValue('event_location'), 'attr') ?>"
                        placeholder="Contoh: Lapangan RW 01 Randugarut"
                    >
                </div>

                <div class="form-group">
                    <label for="activity_description">Ringkasan Sumber</label>
                    <textarea
                        id="activity_description"
                        name="activity_description"
                        rows="4"
                        placeholder="Fakta utama kegiatan yang menjadi sumber publikasi."
                    ><?= esc($formValue('activity_description')) ?></textarea>
                </div>
            </section>

            <section class="publication-section-card">
                <div class="publication-section-heading">
                    <span>02</span>
                    <div>
                        <h3>Strategi dan creative brief</h3>
                        <p>Tetapkan alasan konten dibuat sebelum masuk ke desain.</p>
                    </div>
                </div>

                <div class="publication-form-grid two-columns">
                    <div class="form-group">
                        <label for="publication_type">Format Publikasi</label>
                        <select id="publication_type" name="publication_type" required>
                            <?php foreach ($publicationTypes as $value => $label) : ?>
                                <option
                                    value="<?= esc($value, 'attr') ?>"
                                    <?= (string) $formValue('publication_type', 'carousel') === $value
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="canva_template_code">Master Canva</label>
                        <select
                            id="canva_template_code"
                            name="canva_template_code"
                            data-template-select
                            required
                        >
                            <?php foreach ($templates as $code => $template) : ?>
                                <option
                                    value="<?= esc($code, 'attr') ?>"
                                    <?= $selectedTemplate === $code ? 'selected' : '' ?>
                                >
                                    <?= esc($code) ?> — <?= esc($template['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="publication-template-preview" data-template-preview>
                    <div>
                        <span data-template-code><?= esc($selectedTemplate) ?></span>
                        <strong data-template-name>
                            <?= esc($templates[$selectedTemplate]['name'] ?? '-') ?>
                        </strong>
                        <small data-template-meta>
                            <?= esc($templates[$selectedTemplate]['format'] ?? '-') ?>
                        </small>
                    </div>

                    <a
                        href="<?= esc($templates[$selectedTemplate]['url'] ?? '#', 'attr') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-secondary"
                        data-template-link
                        data-canva-master-link
                    >
                        Buka &amp; Salin Master ↗
                    </a>
                </div>

                <div class="form-group">
                    <label for="cover_hook">Hook Cover / Headline</label>
                    <input
                        id="cover_hook"
                        type="text"
                        name="cover_hook"
                        value="<?= esc($formValue('cover_hook'), 'attr') ?>"
                        placeholder="Contoh: Bukan hanya tentang menang."
                    >
                    <small>Untuk carousel, gunakan satu gagasan yang jelas dan tidak clickbait.</small>
                </div>

                <div class="form-group">
                    <label for="content_goal">Tujuan Konten</label>
                    <textarea
                        id="content_goal"
                        name="content_goal"
                        rows="4"
                        placeholder="Apa yang harus dipahami atau dilakukan audiens setelah melihat konten?"
                    ><?= esc($formValue('content_goal')) ?></textarea>
                </div>

                <div class="publication-form-grid two-columns">
                    <div class="form-group">
                        <label for="target_audience">Target Audiens</label>
                        <input
                            id="target_audience"
                            type="text"
                            name="target_audience"
                            value="<?= esc($formValue('target_audience'), 'attr') ?>"
                            placeholder="Pemuda RW 01, warga, mitra, calon anggota"
                        >
                    </div>

                    <div class="form-group">
                        <label for="call_to_action">Call to Action</label>
                        <input
                            id="call_to_action"
                            type="text"
                            name="call_to_action"
                            value="<?= esc($formValue('call_to_action'), 'attr') ?>"
                            placeholder="Geser, daftar, hadir, simpan, atau bagikan"
                        >
                    </div>
                </div>
            </section>

            <section class="publication-section-card">
                <div class="publication-section-heading">
                    <span>03</span>
                    <div>
                        <h3>Naskah dan aset</h3>
                        <p>Simpan bahan produksi dalam satu record yang sama.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="title">Headline Isi</label>
                    <input
                        id="title"
                        type="text"
                        name="title"
                        value="<?= esc($formValue('title'), 'attr') ?>"
                        placeholder="Judul utama setelah halaman cover"
                    >
                </div>

                <div class="form-group">
                    <label for="caption">Draft Caption</label>
                    <textarea id="caption" name="caption" rows="8"><?= esc($formValue('caption')) ?></textarea>
                </div>

                <div class="publication-form-grid two-columns">
                    <div class="form-group">
                        <label for="hashtags">Hashtag</label>
                        <textarea id="hashtags" name="hashtags" rows="3"><?= esc($formValue('hashtags')) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="mentions">Mention / Tag</label>
                        <textarea id="mentions" name="mentions" rows="3"><?= esc($formValue('mentions')) ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label for="alt_text">Alt Text Aksesibilitas</label>
                    <textarea id="alt_text" name="alt_text" rows="3"><?= esc($formValue('alt_text')) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="content_images">Aset Foto</label>
                    <input
                        id="content_images"
                        type="file"
                        name="content_images[]"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple
                    >
                    <small>Opsional. Maksimal 10 aset per publikasi dan 6MB per gambar.</small>
                </div>
            </section>
        </div>

        <aside class="publication-form-sidebar">
            <section class="publication-section-card sticky-card">
                <div class="publication-section-heading compact">
                    <span>04</span>
                    <div>
                        <h3>Produksi dan penayangan</h3>
                        <p>Siapa mengerjakan, meninjau, dan kapan tayang.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="owner">Penanggung Jawab</label>
                    <input
                        id="owner"
                        type="text"
                        name="owner"
                        value="<?= esc($formValue(
                            'owner',
                            session()->get('name')
                            ?? session()->get('full_name')
                            ?? session()->get('user_name')
                            ?? session()->get('email')
                            ?? 'Tim Media GARDA 01'
                        ), 'attr') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="reviewer">Reviewer</label>
                    <input
                        id="reviewer"
                        type="text"
                        name="reviewer"
                        value="<?= esc($formValue('reviewer'), 'attr') ?>"
                        placeholder="Ketua / Koordinator Program"
                    >
                </div>

                <div class="form-group">
                    <label for="priority">Prioritas</label>
                    <select id="priority" name="priority" required>
                        <?php foreach ($priorities as $value => $label) : ?>
                            <option
                                value="<?= esc($value, 'attr') ?>"
                                <?= (string) $formValue('priority', 'normal') === $value
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="scheduled_at">Rencana Tayang</label>
                    <input
                        id="scheduled_at"
                        type="datetime-local"
                        name="scheduled_at"
                        value="<?= esc($scheduledValue, 'attr') ?>"
                    >
                    <small>
                        Isi sejak tahap brief agar sistem dapat
                        menghitung deadline draft, desain, review,
                        approval, dan waktu tayang.
                    </small>

                    <?php if (
                        auth_can(
                            'publications.recommendations.view'
                        )
                        && !empty($postingRecommendations)
                    ) : ?>
                        <div class="publication-schedule-suggestions">
                            <div>
                                <strong>
                                    Saran waktu tayang
                                </strong>

                                <span>
                                    <?= !empty(
                                        $postingRecommendationMeta[
                                            'has_enough_data'
                                        ]
                                    )
                                        ? 'Berdasarkan performa internal'
                                        : 'Baseline eksperimen sementara' ?>
                                </span>
                            </div>

                            <div>
                                <?php foreach (
                                    $postingRecommendations
                                    as $recommendation
                                ) : ?>
                                    <button
                                        type="button"
                                        data-recommended-datetime="<?= esc(
                                            $recommendation[
                                                'next_datetime_local'
                                            ],
                                            'attr'
                                        ) ?>"
                                    >
                                        <strong>
                                            <?= esc(
                                                $recommendation[
                                                    'weekday_label'
                                                ]
                                            ) ?>
                                            ·
                                            <?= esc(
                                                str_replace(
                                                    ':',
                                                    '.',
                                                    $recommendation[
                                                        'time'
                                                    ]
                                                )
                                            ) ?>
                                            WIB
                                        </strong>

                                        <small>
                                            <?= esc(
                                                $recommendation[
                                                    'evidence'
                                                ]
                                            ) ?>
                                        </small>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <a
                                href="<?= base_url(
                                    '/publications/recommendations'
                                ) ?>"
                            >
                                Lihat analisis lengkap →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="canva_url">Tautan Desain Kerja Canva</label>
                    <input
                        id="canva_url"
                        type="url"
                        name="canva_url"
                        value="<?= esc($formValue('canva_url'), 'attr') ?>"
                        placeholder="https://www.canva.com/design/..."
                    >
                    <small>Gunakan tautan desain hasil duplikasi. Jangan pernah menempelkan tautan master.</small>
                </div>

                <div class="form-group">
                    <label for="instagram_url">Tautan Instagram</label>
                    <input
                        id="instagram_url"
                        type="url"
                        name="instagram_url"
                        value="<?= esc($formValue('instagram_url'), 'attr') ?>"
                        placeholder="Diisi setelah konten tayang"
                    >
                </div>

                <div class="form-group">
                    <label for="notes">Catatan Produksi</label>
                    <textarea id="notes" name="notes" rows="5"><?= esc($formValue('notes')) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary publication-submit-button">
                    <?= esc($submitLabel) ?>
                </button>

                <a href="<?= base_url('/publications') ?>" class="btn btn-secondary publication-cancel-button">
                    Batal
                </a>
            </section>
        </aside>
    </div>

    <script type="application/json" data-template-catalog>
        <?= json_encode(
            $templates,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        ) ?>
    </script>
</form>
