<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2>Detail Konten AI</h2>
        <p>Preview gambar, hasil caption, hashtag, dan teks yang bisa diedit sebelum dipakai.</p>
    </div>

    <div>
        <?php if (auth_can('publications.view')) : ?>
            <a href="<?= base_url('/publications/' . $post['id']) ?>" class="btn btn-primary">
                Buka Publikasi
            </a>
        <?php endif; ?>
        <a href="<?= base_url('/content-studio') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="content-studio-grid">
    <div class="panel-card">
        <h3>Gambar Konten</h3>

        <div class="content-image-grid">
            <?php foreach ($assets as $asset) : ?>
                <a href="<?= base_url($asset['image_path']) ?>" target="_blank">
                    <img src="<?= base_url($asset['image_path']) ?>" alt="Gambar Konten">
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($post['generated_image'])) : ?>
            <br>

            <div class="generated-preview">
                <h3>Preview Feed Visual 4:5</h3>

                <img src="<?= base_url($post['generated_image']) ?>" alt="Generated Feed">

                <br><br>

                <a href="<?= base_url($post['generated_image']) ?>" class="btn btn-primary" download>
                    Download PNG
                </a>

                <a href="<?= base_url($post['generated_image']) ?>" class="btn btn-secondary" target="_blank">
                    Buka Gambar
                </a>
            </div>
        <?php endif; ?>

        <br>
        <p>
            <strong>Kategori:</strong> <?= esc($post['category']) ?><br>
            <strong>Template:</strong> <?= esc($post['template_type']) ?><br>
            <strong>Status:</strong> <?= esc($post['status']) ?>
        </p>

        <br>
        <p>
            <strong>Judul Feed:</strong> <?= esc($post['event_title'] ?? '-') ?><br>
            <strong>Tanggal:</strong> <?= esc($post['event_date'] ?? '-') ?><br>
            <strong>Jam:</strong> <?= esc($post['event_time'] ?? '-') ?><br>
            <strong>Lokasi:</strong> <?= esc($post['event_location'] ?? '-') ?><br>
            <strong>Bentuk Kegiatan:</strong><br>
            <?= nl2br(esc($post['activity_description'] ?? '-')) ?>
        </p>

        <form action="<?= base_url('/content-studio/generate/' . $post['id']) ?>" method="post" style="display:inline-block;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary" onclick="return confirm('Generate konten menggunakan AI?')">
                Generate Teks AI
            </button>
        </form>

        <form action="<?= base_url('/content-studio/render-feed/' . $post['id']) ?>" method="post" style="display:inline-block;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-warning" onclick="return confirm('Buat visual feed 4:5 otomatis?')">
                Generate Feed 4:5
            </button>
        </form>
    </div>

    <div class="panel-card">
        <h3>Hasil Konten</h3>

        <form action="<?= base_url('/content-studio/update-text/' . $post['id']) ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Judul / Headline</label>
                <input type="text" name="title" value="<?= esc($post['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Caption Instagram</label>
                <textarea name="caption" rows="10"><?= esc($post['caption'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Hashtag</label>
                <textarea name="hashtags" rows="4"><?= esc($post['hashtags'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Mentions / Tags</label>
                <input type="text" name="mentions" value="<?= esc($post['mentions'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Alt Text</label>
                <textarea name="alt_text" rows="3"><?= esc($post['alt_text'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan Teks</button>
        </form>
    </div>
</div>

<br>

<?php if (!empty($post['ai_summary'])) : ?>
    <div class="panel-card">
        <h3>Ringkasan AI</h3>
        <p><?= nl2br(esc($post['ai_summary'])) ?></p>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
