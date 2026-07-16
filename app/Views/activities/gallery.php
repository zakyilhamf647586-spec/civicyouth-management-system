<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <span class="gallery-admin-kicker">
            Galeri Dokumentasi
        </span>

        <h2><?= esc($activity['title']) ?></h2>

        <p>
            Upload, atur urutan, tambahkan caption,
            dan pilih foto utama kegiatan.
        </p>
    </div>

    <a
        href="<?= base_url('/activities') ?>"
        class="btn btn-secondary"
    >
        Kembali
    </a>
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

<section class="gallery-upload-card">

    <div>
        <h3>Tambahkan Foto</h3>

        <p>
            Pilih beberapa foto sekaligus. Foto pertama otomatis
            menjadi cover apabila kegiatan belum memiliki foto utama.
        </p>
    </div>

    <form
        action="<?= base_url(
            '/activities/gallery/'
            . $activity['id']
            . '/upload'
        ) ?>"
        method="post"
        enctype="multipart/form-data"
        class="gallery-upload-form"
    >
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="images">Foto Dokumentasi</label>

            <input
                type="file"
                id="images"
                name="images[]"
                accept=".jpg,.jpeg,.png,.webp"
                multiple
                required
            >

            <small>
                Maksimal 12 foto per upload, 4 MB per foto,
                dan 30 foto per kegiatan.
            </small>
        </div>

        <button type="submit" class="btn btn-primary">
            Upload Foto
        </button>
    </form>

</section>

<?php if (!empty($images)) : ?>

    <section class="gallery-admin-grid">

        <?php foreach ($images as $image) : ?>

            <article
                class="gallery-admin-card
                <?= (int) $image['is_cover'] === 1
                    ? 'is-cover'
                    : '' ?>"
            >

                <div class="gallery-admin-media">
                    <img
                        src="<?= base_url(
                            'uploads/activities/'
                            . $image['image_file']
                        ) ?>"
                        alt="<?= esc(
                            $image['caption']
                            ?: $activity['title']
                        ) ?>"
                    >

                    <?php if (
                        (int) $image['is_cover'] === 1
                    ) : ?>
                        <span class="gallery-cover-badge">
                            Foto Utama
                        </span>
                    <?php endif; ?>
                </div>

                <div class="gallery-admin-content">

                    <form
                        action="<?= base_url(
                            '/activities/gallery/'
                            . $activity['id']
                            . '/image/'
                            . $image['id']
                            . '/update'
                        ) ?>"
                        method="post"
                    >
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label>Caption</label>

                            <textarea
                                name="caption"
                                rows="3"
                                maxlength="255"
                                placeholder="Jelaskan isi foto ini"
                            ><?= esc(
                                $image['caption'] ?? ''
                            ) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Urutan Tampil</label>

                            <input
                                type="number"
                                name="display_order"
                                min="0"
                                value="<?= esc(
                                    $image['display_order']
                                ) ?>"
                            >
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Simpan Informasi
                        </button>
                    </form>

                    <div class="gallery-admin-actions">

                        <?php if (
                            (int) $image['is_cover'] !== 1
                        ) : ?>
                            <form
                                action="<?= base_url(
                                    '/activities/gallery/'
                                    . $activity['id']
                                    . '/image/'
                                    . $image['id']
                                    . '/cover'
                                ) ?>"
                                method="post"
                            >
                                <?= csrf_field() ?>

                                <button
                                    type="submit"
                                    class="btn btn-warning"
                                >
                                    Jadikan Cover
                                </button>
                            </form>
                        <?php endif; ?>

                        <form
                            action="<?= base_url(
                                '/activities/gallery/'
                                . $activity['id']
                                . '/image/'
                                . $image['id']
                                . '/delete'
                            ) ?>"
                            method="post"
                            onsubmit="return confirm(
                                'Hapus foto ini dari galeri?'
                            )"
                        >
                            <?= csrf_field() ?>

                            <button
                                type="submit"
                                class="btn btn-danger"
                            >
                                Hapus
                            </button>
                        </form>

                    </div>

                </div>

            </article>

        <?php endforeach; ?>

    </section>

<?php else : ?>

    <div class="gallery-admin-empty">
        <strong>Galeri masih kosong</strong>

        <p>
            Upload foto dokumentasi untuk mulai membangun
            galeri kegiatan ini.
        </p>
    </div>

<?php endif; ?>

<?= $this->endSection() ?>