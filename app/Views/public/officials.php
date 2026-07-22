<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<?php
$officialName = static function (array $official): string {
    return trim(
        $official['member_name']
        ?? $official['full_name']
        ?? $official['name']
        ?? '-'
    );
};

$advisors  = [];
$chairman  = null;
$vice      = [];
$core      = [];
$divisions = [];
$activePeriod = 'Belum ditetapkan';

foreach ($officials as $official) {
    if (
        $activePeriod === 'Belum ditetapkan'
        && !empty($official['period'])
    ) {
        $activePeriod = trim((string) $official['period']);
    }
    $position = mb_strtolower(
        trim($official['position_name'] ?? '')
    );

    if (
        str_contains($position, 'pelindung')
        || str_contains($position, 'pembina')
        || str_contains($position, 'penasihat')
    ) {
        $advisors[] = $official;
        continue;
    }

    if (
        str_contains($position, 'ketua')
        && !str_contains($position, 'wakil')
        && $chairman === null
    ) {
        $chairman = $official;
        continue;
    }

    if (str_contains($position, 'wakil')) {
        $vice[] = $official;
        continue;
    }

    if (
        str_contains($position, 'sekretaris')
        || str_contains($position, 'bendahara')
    ) {
        $core[] = $official;
        continue;
    }

    $divisions[] = $official;
}
?>

<div class="officials-public-page">
        <section class="officials-hero">
            <div class="officials-hero-copy">
                <span class="public-kicker">Struktur Organisasi</span>

                <h1>
                    Pengurus Karang Taruna<br>
                    RW 01 Randugarut
                </h1>

                <p>
                    Mengenal para pengurus yang menjalankan fungsi koordinasi,
                    administrasi, keuangan, kegiatan, dan pelayanan kepemudaan
                    di lingkungan RW 01 Kelurahan Randugarut.
                </p>

                <div class="officials-hero-meta">
                    <div>
                        <strong><?= count($officials) ?></strong>
                        <span>Pengurus tercatat</span>
                    </div>

                    <div>
                        <strong>
                            <?= esc($activePeriod) ?>
                        </strong>
                        <span>Periode kepengurusan</span>
                    </div>
                </div>
            </div>

            <div class="officials-hero-emblem">
                <img
                    src="<?= esc(site_asset_url('site_logo', 'assets/img/logo-rw01.png'), 'attr') ?>"
                    alt="Logo <?= esc(site_setting('organization_name', 'GARDA 01')) ?>"
                >

                <strong>Karang Taruna RW 01</strong>
                <span>Aktif · Solid · Bertanggung Jawab</span>
            </div>
        </section>

        <section class="officials-structure-section" id="struktur">
            <div class="officials-section-heading">
                <div>
                    <span class="public-kicker">Diagram Organisasi</span>

                    <h2>Struktur Kepengurusan</h2>

                    <p>
                        Susunan organisasi ditampilkan berdasarkan urutan
                        dan jabatan yang tercatat pada sistem internal.
                    </p>
                </div>
            </div>

            <?php if (!empty($officials)) : ?>
                <div class="officials-chart">

                    <?php if (!empty($advisors)) : ?>
                        <div class="officials-chart-level officials-chart-advisors">
                            <?php foreach ($advisors as $official) : ?>
                                <article class="officials-chart-node officials-chart-node-soft">
                                    <span>
                                        <?= esc($official['position_name'] ?? '-') ?>
                                    </span>

                                    <strong>
                                        <?= esc($officialName($official)) ?>
                                    </strong>

                                    <small>
                                        <?= esc(
                                            $official['division']
                                            ?? 'Karang Taruna RW 01'
                                        ) ?>
                                    </small>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <div class="officials-chart-line"></div>
                    <?php endif; ?>

                    <?php if ($chairman) : ?>
                        <div class="officials-chart-level">
                            <article class="officials-chart-node officials-chart-node-primary">
                                <span>
                                    <?= esc($chairman['position_name'] ?? 'Ketua') ?>
                                </span>

                                <strong>
                                    <?= esc($officialName($chairman)) ?>
                                </strong>

                                <small>
                                    <?= esc(
                                        $chairman['division']
                                        ?? 'Pimpinan Organisasi'
                                    ) ?>
                                </small>
                            </article>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($vice)) : ?>
                        <div class="officials-chart-line"></div>

                        <div class="officials-chart-level">
                            <?php foreach ($vice as $official) : ?>
                                <article class="officials-chart-node officials-chart-node-secondary">
                                    <span>
                                        <?= esc($official['position_name'] ?? '-') ?>
                                    </span>

                                    <strong>
                                        <?= esc($officialName($official)) ?>
                                    </strong>

                                    <small>
                                        <?= esc(
                                            $official['division']
                                            ?? 'Pimpinan Organisasi'
                                        ) ?>
                                    </small>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($core)) : ?>
                        <div class="officials-chart-line"></div>

                        <div class="officials-chart-level officials-chart-grid">
                            <?php foreach ($core as $official) : ?>
                                <article class="officials-chart-node">
                                    <span>
                                        <?= esc($official['position_name'] ?? '-') ?>
                                    </span>

                                    <strong>
                                        <?= esc($officialName($official)) ?>
                                    </strong>

                                    <small>
                                        <?= esc(
                                            $official['division']
                                            ?? 'Pengurus Inti'
                                        ) ?>
                                    </small>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($divisions)) : ?>
                        <div class="officials-chart-line"></div>

                        <div class="officials-chart-level officials-chart-grid">
                            <?php foreach ($divisions as $official) : ?>
                                <article class="officials-chart-node">
                                    <span>
                                        <?= esc($official['position_name'] ?? '-') ?>
                                    </span>

                                    <strong>
                                        <?= esc($officialName($official)) ?>
                                    </strong>

                                    <small>
                                        <?= esc(
                                            $official['division']
                                            ?? 'Bidang/Seksi'
                                        ) ?>
                                    </small>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php else : ?>
                <div class="public-empty">
                    Data struktur pengurus belum tersedia.
                </div>
            <?php endif; ?>
        </section>

        <section class="officials-profile-section" id="profil-pengurus">
            <div class="officials-section-heading officials-section-heading-row">
                <div>
                    <span class="public-kicker">Profil Pengurus</span>

                    <h2>Orang-orang di balik organisasi</h2>

                    <p>
                        Geser ke samping untuk melihat profil pengurus lainnya.
                    </p>
                </div>

                <div class="officials-carousel-status">
                    <strong id="officialCurrent">1</strong>
                    <span>/ <?= max(count($officials), 1) ?></span>
                </div>
            </div>

            <?php if (!empty($officials)) : ?>
                <div class="officials-carousel-shell">

                    <button
                        type="button"
                        class="officials-carousel-arrow officials-carousel-prev"
                        id="officialPrev"
                        aria-label="Lihat pengurus sebelumnya"
                    >
                        ‹
                    </button>

                    <div
                        class="officials-carousel-viewport"
                        id="officialsCarousel"
                        tabindex="0"
                        aria-label="Daftar profil pengurus"
                    >
                        <?php foreach ($officials as $index => $official) : ?>
                            <?php
                            $name = $officialName($official);
                            $initial = mb_strtoupper(
                                mb_substr($name, 0, 1)
                            );
                            ?>

                            <article
                                class="official-profile-card"
                                data-official-index="<?= $index ?>"
                            >
                                <div class="official-profile-media">

                                    <?php if (!empty($official['photo'])) : ?>
                                        <img
                                            src="<?= base_url(
                                                'uploads/officials/'
                                                . $official['photo']
                                            ) ?>"
                                            alt="Foto <?= esc($name) ?>"
                                            class="official-profile-photo"
                                            loading="lazy"
                                        >
                                    <?php else : ?>
                                        <div class="official-profile-fallback">
                                            <?= esc($initial) ?>
                                        </div>
                                    <?php endif; ?>

                                    <span class="official-profile-role">
                                        <?= esc(
                                            $official['position_name']
                                            ?? 'Pengurus'
                                        ) ?>
                                    </span>
                                </div>

                                <div class="official-profile-content">
                                    <span class="official-profile-division">
                                        <?= esc(
                                            $official['division']
                                            ?? 'Karang Taruna RW 01'
                                        ) ?>
                                    </span>

                                    <h3><?= esc($name) ?></h3>

                                    <div class="official-profile-details">
                                        <?php if (!empty($official['rt_scope'])) : ?>
                                            <span>
                                                <?= esc($official['rt_scope']) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($official['period'])) : ?>
                                            <span>
                                                Periode <?= esc($official['period']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="official-profile-bio">
                                        <?= esc(
                                            !empty($official['short_bio'])
                                                ? $official['short_bio']
                                                : (
                                                    !empty($official['description'])
                                                        ? $official['description']
                                                        : 'Pengurus Karang Taruna RW 01 Kelurahan Randugarut.'
                                                )
                                        ) ?>
                                    </p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <button
                        type="button"
                        class="officials-carousel-arrow officials-carousel-next"
                        id="officialNext"
                        aria-label="Lihat pengurus berikutnya"
                    >
                        ›
                    </button>

                    <div class="officials-carousel-fade officials-carousel-fade-left"></div>
                    <div class="officials-carousel-fade officials-carousel-fade-right"></div>
                </div>

                <div class="officials-carousel-dots" id="officialDots">
                    <?php foreach ($officials as $index => $official) : ?>
                        <button
                            type="button"
                            class="<?= $index === 0 ? 'active' : '' ?>"
                            data-dot-index="<?= $index ?>"
                            aria-label="Tampilkan pengurus ke-<?= $index + 1 ?>"
                        ></button>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="public-empty">
                    Profil pengurus belum tersedia.
                </div>
            <?php endif; ?>
        </section>

        <section class="officials-public-cta">
            <div class="officials-public-cta-icon">
                <img
                    src="<?= esc(site_asset_url('site_logo', 'assets/img/logo-rw01.png'), 'attr') ?>"
                    alt=""
                >
            </div>

            <div>
                <span class="public-kicker">Terhubung dengan Pengurus</span>

                <h2>Sampaikan undangan, gagasan, atau peluang kolaborasi</h2>

                <p>
                    Hubungi GARDA 01 untuk menyampaikan informasi kegiatan,
                    aspirasi pemuda, atau kesempatan kolaborasi bagi lingkungan.
                </p>
            </div>

            <a href="<?= base_url('/kontak') ?>" class="btn btn-primary">
                Hubungi Kami
            </a>
        </section>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.getElementById('officialsCarousel');
    const prevButton = document.getElementById('officialPrev');
    const nextButton = document.getElementById('officialNext');
    const currentLabel = document.getElementById('officialCurrent');
    const dots = Array.from(
        document.querySelectorAll('#officialDots button')
    );

    if (!carousel) {
        return;
    }

    const cards = Array.from(
        carousel.querySelectorAll('.official-profile-card')
    );

    if (!cards.length) {
        return;
    }

    const getStep = function () {
        const card = cards[0];
        const style = window.getComputedStyle(carousel);
        const gap = parseFloat(style.columnGap || style.gap || 0);

        return card.getBoundingClientRect().width + gap;
    };

    const getActiveIndex = function () {
        const viewportCenter =
            carousel.scrollLeft + (carousel.clientWidth / 2);

        let closestIndex = 0;
        let closestDistance = Number.POSITIVE_INFINITY;

        cards.forEach(function (card, index) {
            const cardCenter =
                card.offsetLeft + (card.offsetWidth / 2);

            const distance = Math.abs(
                cardCenter - viewportCenter
            );

            if (distance < closestDistance) {
                closestDistance = distance;
                closestIndex = index;
            }
        });

        return closestIndex;
    };

    const updateCarouselState = function () {
        const activeIndex = getActiveIndex();
        const maxScroll =
            carousel.scrollWidth - carousel.clientWidth;

        if (currentLabel) {
            currentLabel.textContent = activeIndex + 1;
        }

        dots.forEach(function (dot, index) {
            dot.classList.toggle(
                'active',
                index === activeIndex
            );
        });

        if (prevButton) {
            prevButton.disabled = carousel.scrollLeft <= 4;
        }

        if (nextButton) {
            nextButton.disabled =
                carousel.scrollLeft >= maxScroll - 4;
        }
    };

    const scrollToCard = function (index) {
        const target = cards[index];

        if (!target) {
            return;
        }

        carousel.scrollTo({
            left:
                target.offsetLeft
                - ((carousel.clientWidth - target.offsetWidth) / 2),
            behavior: 'smooth'
        });
    };

    prevButton?.addEventListener('click', function () {
        carousel.scrollBy({
            left: -getStep(),
            behavior: 'smooth'
        });
    });

    nextButton?.addEventListener('click', function () {
        carousel.scrollBy({
            left: getStep(),
            behavior: 'smooth'
        });
    });

    dots.forEach(function (dot, index) {
        dot.addEventListener('click', function () {
            scrollToCard(index);
        });
    });

    carousel.addEventListener(
        'scroll',
        updateCarouselState,
        { passive: true }
    );

    carousel.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            carousel.scrollBy({
                left: -getStep(),
                behavior: 'smooth'
            });
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            carousel.scrollBy({
                left: getStep(),
                behavior: 'smooth'
            });
        }
    });

    window.addEventListener('resize', updateCarouselState);

    updateCarouselState();
});
</script>
<?= $this->endSection() ?>
