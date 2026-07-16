<?php
$isEdit = isset($program);
?>

<div class="program-form-grid">

    <div class="program-form-main">

        <div class="form-group">
            <label for="name">Nama Program</label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= esc(old('name', $program['name'] ?? '')) ?>"
                placeholder="Contoh: GARDA 01 Peduli"
                required
            >
        </div>

        <div class="form-group">
            <label for="label">Kategori / Ruang Lingkup</label>

            <input
                type="text"
                id="label"
                name="label"
                value="<?= esc(old('label', $program['label'] ?? '')) ?>"
                placeholder="Contoh: Sosial dan Kemanusiaan"
            >
        </div>

        <div class="form-group">
            <label for="tagline">Tagline</label>

            <input
                type="text"
                id="tagline"
                name="tagline"
                value="<?= esc(old('tagline', $program['tagline'] ?? '')) ?>"
                placeholder="Contoh: Peduli sesama, hadir untuk warga."
            >
        </div>

        <div class="form-group">
            <label for="short_description">Deskripsi Singkat</label>

            <textarea
                id="short_description"
                name="short_description"
                rows="4"
                placeholder="Ringkasan yang tampil pada kartu program."
            ><?= esc(old(
                'short_description',
                $program['short_description'] ?? ''
            )) ?></textarea>
        </div>

        <div class="form-group">
            <label for="description">Deskripsi Lengkap</label>

            <textarea
                id="description"
                name="description"
                rows="8"
                placeholder="Jelaskan tujuan, arah, dan ruang gerak program."
            ><?= esc(old(
                'description',
                $program['description'] ?? ''
            )) ?></textarea>
        </div>

        <div class="form-group">
            <label for="focus_items">Fokus Program</label>

            <textarea
                id="focus_items"
                name="focus_items"
                rows="6"
                placeholder="Tulis satu fokus pada setiap baris."
            ><?= esc(old(
                'focus_items',
                $program['focus_text'] ?? ''
            )) ?></textarea>

            <small>
                Contoh: Aksi sosial dan kemanusiaan. Gunakan satu baris untuk satu fokus.
            </small>
        </div>

        <div class="form-group">
            <label for="campaign_items">Program / Kampanye</label>

            <textarea
                id="campaign_items"
                name="campaign_items"
                rows="6"
                placeholder="Tulis satu program atau kampanye pada setiap baris."
            ><?= esc(old(
                'campaign_items',
                $program['campaign_text'] ?? ''
            )) ?></textarea>

            <small>
                Contoh: GARDA 01 Berbagi, Aksi Berbagi Ramadan.
            </small>
        </div>

    </div>

    <aside class="program-form-sidebar">

        <?php
        $selectedStatus = old(
            'status',
            $program['status'] ?? 'draft'
        );
        ?>

        <div class="form-group">
            <label for="status">Status Publikasi</label>

            <select id="status" name="status" required>
                <option
                    value="draft"
                    <?= $selectedStatus === 'draft' ? 'selected' : '' ?>
                >
                    Draft
                </option>

                <option
                    value="published"
                    <?= $selectedStatus === 'published' ? 'selected' : '' ?>
                >
                    Dipublikasikan
                </option>

                <option
                    value="archived"
                    <?= $selectedStatus === 'archived' ? 'selected' : '' ?>
                >
                    Diarsipkan
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="display_order">Urutan Tampil</label>

            <input
                type="number"
                id="display_order"
                name="display_order"
                min="0"
                value="<?= esc(old(
                    'display_order',
                    $program['display_order'] ?? 0
                )) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="cover_image">Cover Program</label>

            <input
                type="file"
                id="cover_image"
                name="cover_image"
                accept=".jpg,.jpeg,.png,.webp"
            >

            <small>
                Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
            </small>
        </div>

        <?php if (
            $isEdit &&
            !empty($program['cover_image'])
        ) : ?>
            <div class="program-current-cover">
                <span>Cover saat ini</span>

                <img
                    src="<?= base_url(
                        'uploads/programs/' . $program['cover_image']
                    ) ?>"
                    alt="Cover <?= esc($program['name']) ?>"
                >
            </div>
        <?php endif; ?>

        <div class="program-form-note">
            <strong>URL Program</strong>

            <p>
                Slug dibuat otomatis dari nama program dan dijaga agar tidak terjadi duplikasi.
            </p>

            <?php if (!empty($program['slug'])) : ?>
                <code>
                    /program/<?= esc($program['slug']) ?>
                </code>
            <?php endif; ?>
        </div>

    </aside>

</div>