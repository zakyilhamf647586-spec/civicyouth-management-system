<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<link
    rel="stylesheet"
    href="<?= base_url('assets/css/admin-activity-quality.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/admin-activity-quality.css') ?>"
>

<div class="activity-quality-page">
    <div class="page-header">
        <div>
            <span class="activity-quality-kicker">Quality Assurance</span>
            <h2>Audit Kualitas Kegiatan</h2>
            <p>
                Pemeriksaan non-destruktif untuk menemukan data kegiatan yang
                belum siap ditampilkan atau dijadikan Cerita Dampak.
            </p>
        </div>

        <a href="<?= base_url('/activities') ?>" class="btn btn-secondary">
            Kembali ke Kegiatan
        </a>
    </div>

    <div class="activity-quality-notice" role="note">
        <strong>Tidak ada data yang diubah otomatis.</strong>
        <span>
            Portal hanya menandai masalah. Isi faktual tetap harus diperiksa
            oleh pengurus yang mengetahui kegiatan tersebut.
        </span>
    </div>

    <section class="activity-quality-summary">
        <a href="<?= base_url('/activities/quality') ?>">
            <span>Total Kegiatan</span>
            <strong><?= (int) ($summary['total'] ?? 0) ?></strong>
        </a>
        <a href="<?= base_url('/activities/quality?readiness=incomplete') ?>" class="is-incomplete">
            <span>Belum Lengkap</span>
            <strong><?= (int) ($summary['incomplete'] ?? 0) ?></strong>
        </a>
        <a href="<?= base_url('/activities/quality?readiness=review') ?>" class="is-review">
            <span>Perlu Diperiksa</span>
            <strong><?= (int) ($summary['review'] ?? 0) ?></strong>
        </a>
        <a href="<?= base_url('/activities/quality?readiness=ready') ?>" class="is-ready">
            <span>Siap Dipublikasikan</span>
            <strong><?= (int) ($summary['ready'] ?? 0) ?></strong>
        </a>
        <article class="is-featured-risk">
            <span>Unggulan Belum Siap</span>
            <strong><?= (int) ($summary['featured_not_ready'] ?? 0) ?></strong>
        </article>
    </section>

    <section class="filter-card">
        <form method="get" action="<?= base_url('/activities/quality') ?>" class="activity-quality-filter">
            <div class="form-group">
                <label for="quality-keyword">Cari</label>
                <input
                    id="quality-keyword"
                    type="search"
                    name="keyword"
                    value="<?= esc($keyword ?? '', 'attr') ?>"
                    placeholder="Judul, lokasi, pilar, atau masalah"
                >
            </div>
            <div class="form-group">
                <label for="quality-readiness">Kesiapan</label>
                <select id="quality-readiness" name="readiness">
                    <option value="">Semua kondisi</option>
                    <option value="incomplete" <?= ($selectedReadiness ?? '') === 'incomplete' ? 'selected' : '' ?>>Belum Lengkap</option>
                    <option value="review" <?= ($selectedReadiness ?? '') === 'review' ? 'selected' : '' ?>>Perlu Diperiksa</option>
                    <option value="ready" <?= ($selectedReadiness ?? '') === 'ready' ? 'selected' : '' ?>>Siap Dipublikasikan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <a href="<?= base_url('/activities/quality') ?>" class="btn btn-secondary">Reset</a>
        </form>
    </section>

    <section class="activity-quality-list">
        <?php if (!empty($items)) : ?>
            <?php foreach ($items as $item) : ?>
                <article class="activity-quality-card activity-quality-card--<?= esc($item['readiness'], 'attr') ?>">
                    <header>
                        <div>
                            <span class="activity-quality-badge">
                                <?= esc($item['label']) ?>
                            </span>
                            <h3><?= esc($item['title'] ?: 'Tanpa judul') ?></h3>
                            <p>
                                <?= esc($item['program_name'] ?: 'Tanpa pilar') ?>
                                · <?= esc($item['location'] ?: 'Lokasi kosong') ?>
                                · <?= esc($item['activity_date'] ?: 'Tanggal kosong') ?>
                            </p>
                        </div>
                        <div class="activity-quality-actions">
                            <a href="<?= base_url('/activities/edit/' . $item['id']) ?>" class="btn btn-primary">Perbaiki</a>
                            <a href="<?= base_url('/activities/gallery/' . $item['id']) ?>" class="btn btn-secondary">Galeri</a>
                        </div>
                    </header>

                    <?php if (!empty($item['issues'])) : ?>
                        <ul>
                            <?php foreach ($item['issues'] as $issue) : ?>
                                <li class="is-<?= esc($issue['severity'], 'attr') ?>">
                                    <?= esc($issue['message']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <div class="activity-quality-clean">
                            Data inti, ringkasan, dokumentasi, dan status telah memenuhi pemeriksaan awal.
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="public-empty">Tidak ada kegiatan yang sesuai dengan filter.</div>
        <?php endif; ?>
    </section>
</div>

<?= $this->endSection() ?>
