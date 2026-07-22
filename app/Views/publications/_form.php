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
    $scheduledTimestamp = strtotime(
        (string) $scheduledValue
    );

    $scheduledValue = $scheduledTimestamp
        ? date('Y-m-d\TH:i', $scheduledTimestamp)
        : '';
}

$selectedTemplate = (string) $formValue(
    'canva_template_code',
    'COVER-00'
);
?>

<section class="publication-form-purpose">
    <strong>
        Hasil form ini adalah rencana konten Instagram.
    </strong>

    <span>
        Konten tidak otomatis tampil di website publik dan tidak
        otomatis terunggah ke Instagram.
    </span>
</section>

<nav class="publication-form-steps" aria-label="Tahapan form">
    <span><b>1</b>Sumber</span>
    <span><b>2</b>Konten</span>
    <span><b>3</b>Canva</span>
    <span><b>4</b>Jadwal</span>
</nav>

<form
    action="<?= base_url($formAction) ?>"
    method="post"
    enctype="multipart/form-data"
    class="publication-form publication-simple-form"
    data-publication-form
>
    <?= csrf_field() ?>

    <div class="publication-form-layout">
        <div class="publication-form-main">
            <section class="publication-section-card">
                <div class="publication-section-heading">
                    <span>01</span>

                    <div>
                        <h3>Pilih sumber konten</h3>

                        <p>
                            Hubungkan dengan kegiatan Portal atau
                            isi informasi dasarnya secara manual.
                        </p>
                    </div>
                </div>

                <div class="publication-form-grid two-columns">
                    <div class="form-group">
                        <label for="activity_id">
                            Sumber Kegiatan
                        </label>

                        <select
                            id="activity_id"
                            name="activity_id"
                        >
                            <option value="">
                                Konten manual / tidak terkait kegiatan
                            </option>

                            <?php foreach (
                                $activities as $activity
                            ) : ?>
                                <option
                                    value="<?= esc(
                                        $activity['id'],
                                        'attr'
                                    ) ?>"
                                    <?= (string) $formValue(
                                        'activity_id'
                                    ) === (string) $activity['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($activity['title']) ?>
                                    <?= !empty(
                                        $activity['program_name']
                                    )
                                        ? ' · '
                                            . esc(
                                                $activity[
                                                    'program_name'
                                                ]
                                            )
                                        : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <small>
                            Data Kegiatan tetap menjadi sumber
                            berita untuk website publik.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="program_id">
                            Pilar / Program
                        </label>

                        <select
                            id="program_id"
                            name="program_id"
                        >
                            <option value="">
                                Tanpa pilar khusus
                            </option>

                            <?php foreach (
                                $programs as $program
                            ) : ?>
                                <option
                                    value="<?= esc(
                                        $program['id'],
                                        'attr'
                                    ) ?>"
                                    <?= (string) $formValue(
                                        'program_id'
                                    ) === (string) $program['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($program['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="event_title">
                        Judul Konten Internal
                    </label>

                    <input
                        id="event_title"
                        type="text"
                        name="event_title"
                        value="<?= esc(
                            $formValue('event_title'),
                            'attr'
                        ) ?>"
                        placeholder="Contoh: Dokumentasi Fun Mini Soccer"
                        required
                    >

                    <small>
                        Dipakai untuk pencarian dan arsip Portal,
                        bukan otomatis menjadi judul website.
                    </small>
                </div>

                <div class="publication-form-grid three-columns">
                    <div class="form-group">
                        <label for="category">Kategori</label>

                        <select
                            id="category"
                            name="category"
                            required
                        >
                            <?php foreach (
                                $categories as $value => $label
                            ) : ?>
                                <option
                                    value="<?= esc(
                                        $value,
                                        'attr'
                                    ) ?>"
                                    <?= (string) $formValue(
                                        'category',
                                        'dokumentasi_kegiatan'
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
                        <label for="event_date">
                            Tanggal Kegiatan
                        </label>

                        <input
                            id="event_date"
                            type="date"
                            name="event_date"
                            value="<?= esc(
                                $formValue('event_date'),
                                'attr'
                            ) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="event_time">
                            Waktu Kegiatan
                        </label>

                        <input
                            id="event_time"
                            type="time"
                            name="event_time"
                            value="<?= esc(
                                $formValue('event_time'),
                                'attr'
                            ) ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="event_location">Lokasi</label>

                    <input
                        id="event_location"
                        type="text"
                        name="event_location"
                        value="<?= esc(
                            $formValue('event_location'),
                            'attr'
                        ) ?>"
                        placeholder="Contoh: Lapangan RW 01 Randugarut"
                    >
                </div>

                <details class="publication-form-details">
                    <summary>
                        Ringkasan sumber
                        <span>Opsional</span>
                    </summary>

                    <div class="form-group">
                        <label for="activity_description">
                            Fakta utama kegiatan
                        </label>

                        <textarea
                            id="activity_description"
                            name="activity_description"
                            rows="4"
                            placeholder="Tuliskan informasi penting yang menjadi dasar konten."
                        ><?= esc(
                            $formValue('activity_description')
                        ) ?></textarea>
                    </div>
                </details>
            </section>

            <section class="publication-section-card">
                <div class="publication-section-heading">
                    <span>02</span>

                    <div>
                        <h3>Siapkan konten dan desain</h3>

                        <p>
                            Pilih format, master Canva, hook,
                            caption, dan foto yang akan dipakai.
                        </p>
                    </div>
                </div>

                <div class="publication-form-grid two-columns">
                    <div class="form-group">
                        <label for="publication_type">
                            Format Instagram
                        </label>

                        <select
                            id="publication_type"
                            name="publication_type"
                            required
                        >
                            <?php foreach (
                                $publicationTypes as $value => $label
                            ) : ?>
                                <option
                                    value="<?= esc(
                                        $value,
                                        'attr'
                                    ) ?>"
                                    <?= (string) $formValue(
                                        'publication_type',
                                        'carousel'
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
                        <label for="canva_template_code">
                            Master Canva
                        </label>

                        <select
                            id="canva_template_code"
                            name="canva_template_code"
                            data-template-select
                            required
                        >
                            <?php foreach (
                                $templates as $code => $template
                            ) : ?>
                                <option
                                    value="<?= esc(
                                        $code,
                                        'attr'
                                    ) ?>"
                                    <?= $selectedTemplate === $code
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($code) ?>
                                    —
                                    <?= esc($template['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div
                    class="publication-template-preview"
                    data-template-preview
                >
                    <div>
                        <span data-template-code>
                            <?= esc($selectedTemplate) ?>
                        </span>

                        <strong data-template-name>
                            <?= esc(
                                $templates[
                                    $selectedTemplate
                                ]['name'] ?? '-'
                            ) ?>
                        </strong>

                        <small data-template-meta>
                            <?= esc(
                                $templates[
                                    $selectedTemplate
                                ]['format'] ?? '-'
                            ) ?>
                        </small>
                    </div>

                    <a
                        href="<?= esc(
                            $templates[
                                $selectedTemplate
                            ]['url'] ?? '#',
                            'attr'
                        ) ?>"
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
                    <label for="cover_hook">
                        Hook Cover / Headline
                    </label>

                    <input
                        id="cover_hook"
                        type="text"
                        name="cover_hook"
                        value="<?= esc(
                            $formValue('cover_hook'),
                            'attr'
                        ) ?>"
                        placeholder="Contoh: Lebih dari sekadar kegiatan."
                    >
                </div>

                <div class="form-group">
                    <label for="title">
                        Headline Isi
                    </label>

                    <input
                        id="title"
                        type="text"
                        name="title"
                        value="<?= esc(
                            $formValue('title'),
                            'attr'
                        ) ?>"
                        placeholder="Judul utama setelah cover"
                    >
                </div>

                <div class="form-group">
                    <label for="caption">
                        Draft Caption
                    </label>

                    <textarea
                        id="caption"
                        name="caption"
                        rows="8"
                        placeholder="Tulis caption Instagram di sini."
                    ><?= esc($formValue('caption')) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="hashtags">Hashtag</label>

                    <textarea
                        id="hashtags"
                        name="hashtags"
                        rows="3"
                        placeholder="#GARDA01 #Randugarut"
                    ><?= esc($formValue('hashtags')) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="content_images">
                        Foto / Aset Dokumentasi
                    </label>

                    <input
                        id="content_images"
                        type="file"
                        name="content_images[]"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple
                    >

                    <small>
                        Opsional. Maksimal 10 gambar dan 6 MB
                        per gambar.
                    </small>
                </div>

                <details class="publication-form-details">
                    <summary>
                        Strategi konten lanjutan
                        <span>Opsional</span>
                    </summary>

                    <div class="form-group">
                        <label for="content_goal">
                            Tujuan Konten
                        </label>

                        <textarea
                            id="content_goal"
                            name="content_goal"
                            rows="4"
                            placeholder="Apa yang harus dipahami atau dilakukan audiens?"
                        ><?= esc(
                            $formValue('content_goal')
                        ) ?></textarea>
                    </div>

                    <div class="publication-form-grid two-columns">
                        <div class="form-group">
                            <label for="target_audience">
                                Target Audiens
                            </label>

                            <input
                                id="target_audience"
                                type="text"
                                name="target_audience"
                                value="<?= esc(
                                    $formValue(
                                        'target_audience'
                                    ),
                                    'attr'
                                ) ?>"
                                placeholder="Pemuda, warga, mitra"
                            >
                        </div>

                        <div class="form-group">
                            <label for="call_to_action">
                                Call to Action
                            </label>

                            <input
                                id="call_to_action"
                                type="text"
                                name="call_to_action"
                                value="<?= esc(
                                    $formValue(
                                        'call_to_action'
                                    ),
                                    'attr'
                                ) ?>"
                                placeholder="Simpan, bagikan, hadir"
                            >
                        </div>
                    </div>

                    <div class="publication-form-grid two-columns">
                        <div class="form-group">
                            <label for="mentions">
                                Mention / Tag
                            </label>

                            <textarea
                                id="mentions"
                                name="mentions"
                                rows="3"
                            ><?= esc(
                                $formValue('mentions')
                            ) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="alt_text">
                                Alt Text Aksesibilitas
                            </label>

                            <textarea
                                id="alt_text"
                                name="alt_text"
                                rows="3"
                            ><?= esc(
                                $formValue('alt_text')
                            ) ?></textarea>
                        </div>
                    </div>
                </details>
            </section>
        </div>

        <aside class="publication-form-sidebar">
            <section class="publication-section-card sticky-card">
                <div class="publication-section-heading compact">
                    <span>03</span>

                    <div>
                        <h3>Atur pekerjaan dan tayang</h3>

                        <p>
                            Tentukan PIC, reviewer, Canva kerja,
                            dan target publikasi.
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="owner">
                        Penanggung Jawab / PIC
                    </label>

                    <input
                        id="owner"
                        type="text"
                        name="owner"
                        value="<?= esc(
                            $formValue(
                                'owner',
                                session()->get('name')
                                ?? session()->get('full_name')
                                ?? session()->get('user_name')
                                ?? session()->get('email')
                                ?? 'Tim Media GARDA 01'
                            ),
                            'attr'
                        ) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="reviewer">Reviewer</label>

                    <input
                        id="reviewer"
                        type="text"
                        name="reviewer"
                        value="<?= esc(
                            $formValue('reviewer'),
                            'attr'
                        ) ?>"
                        placeholder="Ketua / Koordinator Program"
                    >
                </div>

                <div class="form-group">
                    <label for="priority">Prioritas</label>

                    <select
                        id="priority"
                        name="priority"
                        required
                    >
                        <?php foreach (
                            $priorities as $value => $label
                        ) : ?>
                            <option
                                value="<?= esc(
                                    $value,
                                    'attr'
                                ) ?>"
                                <?= (string) $formValue(
                                    'priority',
                                    'normal'
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
                    <label for="scheduled_at">
                        Target Tayang
                    </label>

                    <input
                        id="scheduled_at"
                        type="datetime-local"
                        name="scheduled_at"
                        value="<?= esc(
                            $scheduledValue,
                            'attr'
                        ) ?>"
                    >

                    <small>
                        Boleh diisi sejak awal sebagai target kerja.
                    </small>

                    <?php if (
                        auth_can(
                            'publications.recommendations.view'
                        )
                        && !empty($postingRecommendations)
                    ) : ?>
                        <details class="publication-schedule-help">
                            <summary>
                                Lihat saran waktu tayang
                            </summary>

                            <div class="publication-schedule-suggestions">
                                <div>
                                    <strong>
                                        Pilih salah satu saran
                                    </strong>

                                    <span>
                                        <?= !empty(
                                            $postingRecommendationMeta[
                                                'has_enough_data'
                                            ]
                                        )
                                            ? 'Berdasarkan data internal'
                                            : 'Baseline sementara' ?>
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
                            </div>
                        </details>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="canva_url">
                        Tautan Desain Kerja Canva
                    </label>

                    <input
                        id="canva_url"
                        type="url"
                        name="canva_url"
                        value="<?= esc(
                            $formValue('canva_url'),
                            'attr'
                        ) ?>"
                        placeholder="https://www.canva.com/design/..."
                    >

                    <small>
                        Tempel tautan hasil duplikasi master,
                        bukan tautan master asli.
                    </small>
                </div>

                <div class="form-group">
                    <label for="instagram_url">
                        Tautan Instagram
                    </label>

                    <input
                        id="instagram_url"
                        type="url"
                        name="instagram_url"
                        value="<?= esc(
                            $formValue('instagram_url'),
                            'attr'
                        ) ?>"
                        placeholder="Diisi setelah posting manual"
                    >

                    <small>
                        Diisi setelah konten benar-benar tayang.
                    </small>
                </div>

                <details class="publication-form-details">
                    <summary>
                        Catatan internal
                        <span>Opsional</span>
                    </summary>

                    <div class="form-group">
                        <label for="notes">
                            Catatan Produksi
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="5"
                        ><?= esc($formValue('notes')) ?></textarea>
                    </div>
                </details>

                <div class="publication-form-checklist">
                    <strong>Sebelum menyimpan</strong>
                    <span>✓ Judul konten sudah jelas</span>
                    <span>✓ Format dan master Canva sudah dipilih</span>
                    <span>✓ PIC sudah ditentukan</span>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary publication-submit-button"
                >
                    <?= esc($submitLabel) ?>
                </button>

                <a
                    href="<?= base_url('/publications') ?>"
                    class="btn btn-secondary publication-cancel-button"
                >
                    Batal
                </a>
            </section>
        </aside>
    </div>

    <script
        type="application/json"
        data-template-catalog
    >
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
