<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$seoCssPath = FCPATH
    . 'assets/css/admin-seo-center.css';

$seoCssVersion = is_file($seoCssPath)
    ? (string) filemtime($seoCssPath)
    : '1';

$score = (int) ($audit['score'] ?? 0);

$scoreClass = $score >= 80
    ? 'is-good'
    : ($score >= 55 ? 'is-warning' : 'is-danger');
?>

<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/admin-seo-center.css'
    ) ?>?v=<?= esc($seoCssVersion, 'attr') ?>"
>

<div class="seo-admin-page">

<div class="page-header seo-page-header">
    <div>
        <span class="seo-eyebrow">
            Search Visibility
        </span>

        <h2>SEO & Sitemap</h2>

        <p>
            Periksa kesiapan metadata, struktur mesin pencari,
            sitemap, robots.txt, dan kelengkapan konten publik.
        </p>
    </div>

    <div class="seo-header-actions">
        <a
            href="<?= esc($sitemapUrl, 'attr') ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-secondary"
        >
            Buka Sitemap ↗
        </a>

        <a
            href="<?= base_url(
                '/settings/website'
            ) ?>"
            class="btn btn-primary"
        >
            Kelola SEO Default
        </a>
    </div>
</div>

<section class="seo-score-panel <?= esc(
    $scoreClass,
    'attr'
) ?>">
    <div class="seo-score-value">
        <span>Skor Kesiapan</span>
        <strong><?= $score ?></strong>
        <small>/ 100</small>
    </div>

    <div>
        <h3>
            <?= $score >= 80
                ? 'Fondasi SEO sudah kuat'
                : (
                    $score >= 55
                        ? 'Fondasi tersedia, masih perlu dilengkapi'
                        : 'Ada beberapa kebutuhan mendasar'
                ) ?>
        </h3>

        <p>
            Penilaian berasal dari metadata halaman, konten program,
            dokumentasi kegiatan, gambar sosial, dan Base URL.
        </p>
    </div>

    <div class="seo-score-status">
        <span>
            <?= (int) (
                $audit['issue_count'] ?? 0
            ) ?>
        </span>

        <small>catatan pemeriksaan</small>
    </div>
</section>

<section class="seo-stat-grid">
    <article>
        <span>Total URL Sitemap</span>
        <strong>
            <?= (int) (
                $audit['sitemap_total'] ?? 0
            ) ?>
        </strong>
        <small>Halaman yang dapat ditemukan mesin pencari.</small>
    </article>

    <article>
        <span>Halaman Utama</span>
        <strong>
            <?= (int) (
                $audit['source_counts']['static']
                ?? 0
            ) ?>
        </strong>
        <small>Beranda, Profil, Program, Kegiatan, dan lainnya.</small>
    </article>

    <article>
        <span>Program Publik</span>
        <strong>
            <?= (int) (
                $audit['source_counts']['program']
                ?? 0
            ) ?>
        </strong>
        <small>Program dengan status terbit.</small>
    </article>

    <article>
        <span>Kegiatan Publik</span>
        <strong>
            <?= (int) (
                $audit['source_counts']['activity']
                ?? 0
            ) ?>
        </strong>
        <small>Kegiatan yang lolos aturan visibilitas publik.</small>
    </article>
</section>

<section class="seo-endpoint-grid">
    <article>
        <span>XML Sitemap</span>
        <h3>/sitemap.xml</h3>

        <p>
            Dibuat otomatis dari halaman utama, program terbit,
            dan kegiatan publik.
        </p>

        <a
            href="<?= esc($sitemapUrl, 'attr') ?>"
            target="_blank"
            rel="noopener noreferrer"
        >
            Periksa sitemap
            <b aria-hidden="true">↗</b>
        </a>
    </article>

    <article>
        <span>Robots Control</span>
        <h3>/robots.txt</h3>

        <p>
            Mengizinkan website publik dan menutup route internal
            Portal dari crawler.
        </p>

        <a
            href="<?= esc($robotsUrl, 'attr') ?>"
            target="_blank"
            rel="noopener noreferrer"
        >
            Periksa robots.txt
            <b aria-hidden="true">↗</b>
        </a>
    </article>

    <article>
        <span>Structured Data</span>
        <h3>JSON-LD Otomatis</h3>

        <p>
            Organization, WebSite, WebPage, breadcrumb, program,
            dan dokumentasi kegiatan.
        </p>

        <a
            href="<?= base_url('/') ?>"
            target="_blank"
            rel="noopener noreferrer"
        >
            Buka website publik
            <b aria-hidden="true">↗</b>
        </a>
    </article>
</section>

<section class="seo-verification-panel">
    <div>
        <span>Search Engine Verification</span>
        <h3>Status kode verifikasi</h3>
    </div>

    <div>
        <article class="<?= !empty(
            $audit['verification']['google']
        ) ? 'is-ready' : 'is-empty' ?>">
            <strong>Google</strong>
            <span>
                <?= !empty(
                    $audit['verification']['google']
                )
                    ? 'Sudah diisi'
                    : 'Belum diisi' ?>
            </span>
        </article>

        <article class="<?= !empty(
            $audit['verification']['bing']
        ) ? 'is-ready' : 'is-empty' ?>">
            <strong>Bing</strong>
            <span>
                <?= !empty(
                    $audit['verification']['bing']
                )
                    ? 'Sudah diisi'
                    : 'Belum diisi' ?>
            </span>
        </article>
    </div>
</section>

<section class="seo-audit-card">
    <header>
        <div>
            <span>Quality Review</span>
            <h3>Catatan yang perlu diperiksa</h3>
        </div>

        <small>
            Base URL:
            <?= esc(
                $audit['base_url'] ?? '-'
            ) ?>
        </small>
    </header>

    <?php if (!empty($audit['issues'])) : ?>
        <ol>
            <?php foreach (
                $audit['issues'] as $issue
            ) : ?>
                <li><?= esc($issue) ?></li>
            <?php endforeach; ?>
        </ol>
    <?php else : ?>
        <div class="seo-empty-state">
            <strong>Tidak ada masalah mendasar</strong>

            <p>
                Tetap periksa tampilan hasil pencarian setelah
                website menggunakan domain production.
            </p>
        </div>
    <?php endif; ?>
</section>

<section class="seo-action-grid">
    <a href="<?= base_url('/website/pages') ?>">
        <strong>Metadata Halaman</strong>
        <span>
            Kelola judul dan deskripsi Beranda, Profil, dan Kontak.
        </span>
    </a>

    <a href="<?= base_url('/programs') ?>">
        <strong>Kualitas Program</strong>
        <span>
            Lengkapi deskripsi singkat dan cover setiap program.
        </span>
    </a>

    <a href="<?= base_url('/activities/quality') ?>">
        <strong>Kualitas Kegiatan</strong>
        <span>
            Periksa ringkasan, gambar, dan kesiapan publikasi.
        </span>
    </a>

    <a href="<?= base_url('/settings/website') ?>">
        <strong>SEO Default</strong>
        <span>
            Atur OG image, kode verifikasi, dan metadata global.
        </span>
    </a>
</section>

</div>

<?= $this->endSection() ?>
