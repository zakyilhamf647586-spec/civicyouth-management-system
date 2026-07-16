<?php if (!empty($galleryImages)) : ?>

<section class="public-activity-gallery">

    <div class="public-section-header">
        <span class="public-kicker">
            Galeri Dokumentasi
        </span>

        <h2>Momen kegiatan</h2>

        <p>
            Dokumentasi visual dari pelaksanaan kegiatan
            GARDA 01.
        </p>
    </div>

    <div class="public-activity-gallery-grid">

        <?php foreach (
            $galleryImages as $index => $image
        ) : ?>

            <button
                type="button"
                class="public-gallery-item
                <?= $index === 0
                    ? 'public-gallery-item-featured'
                    : '' ?>"
                data-gallery-index="<?= $index ?>"
                data-gallery-src="<?= base_url(
                    'uploads/activities/'
                    . $image['image_file']
                ) ?>"
                data-gallery-caption="<?= esc(
                    $image['caption']
                    ?: ($activity['title'] ?? 'Dokumentasi GARDA 01'),
                    'attr'
                ) ?>"
            >
                <img
                    src="<?= base_url(
                        'uploads/activities/'
                        . $image['image_file']
                    ) ?>"
                    alt="<?= esc(
                        $image['caption']
                        ?: ($activity['title'] ?? 'Dokumentasi GARDA 01')
                    ) ?>"
                    loading="lazy"
                >

                <span class="public-gallery-overlay">
                    <b>Lihat Foto</b>

                    <?php if (!empty($image['caption'])) : ?>
                        <small>
                            <?= esc($image['caption']) ?>
                        </small>
                    <?php endif; ?>
                </span>
            </button>

        <?php endforeach; ?>

    </div>

</section>

<div
    class="public-gallery-lightbox"
    id="publicGalleryLightbox"
    aria-hidden="true"
>
    <button
        type="button"
        class="public-gallery-close"
        id="publicGalleryClose"
        aria-label="Tutup galeri"
    >
        ×
    </button>

    <button
        type="button"
        class="public-gallery-nav public-gallery-prev"
        id="publicGalleryPrev"
        aria-label="Foto sebelumnya"
    >
        ‹
    </button>

    <figure>
        <img
            src=""
            alt=""
            id="publicGalleryImage"
        >

        <figcaption id="publicGalleryCaption"></figcaption>
    </figure>

    <button
        type="button"
        class="public-gallery-nav public-gallery-next"
        id="publicGalleryNext"
        aria-label="Foto berikutnya"
    >
        ›
    </button>

    <div
        class="public-gallery-counter"
        id="publicGalleryCounter"
    ></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const items = Array.from(
        document.querySelectorAll('.public-gallery-item')
    );

    const lightbox = document.getElementById(
        'publicGalleryLightbox'
    );

    const image = document.getElementById(
        'publicGalleryImage'
    );

    const caption = document.getElementById(
        'publicGalleryCaption'
    );

    const counter = document.getElementById(
        'publicGalleryCounter'
    );

    const closeButton = document.getElementById(
        'publicGalleryClose'
    );

    const prevButton = document.getElementById(
        'publicGalleryPrev'
    );

    const nextButton = document.getElementById(
        'publicGalleryNext'
    );

    if (
        !items.length
        || !lightbox
        || !image
    ) {
        return;
    }

    let activeIndex = 0;

    const renderImage = function () {
        const item = items[activeIndex];

        image.src = item.dataset.gallerySrc;
        image.alt = item.dataset.galleryCaption || '';

        if (caption) {
            caption.textContent =
                item.dataset.galleryCaption || '';
        }

        if (counter) {
            counter.textContent =
                (activeIndex + 1)
                + ' / '
                + items.length;
        }
    };

    const openLightbox = function (index) {
        activeIndex = index;
        renderImage();

        lightbox.classList.add('active');
        lightbox.setAttribute('aria-hidden', 'false');

        document.body.classList.add(
            'gallery-lightbox-open'
        );
    };

    const closeLightbox = function () {
        lightbox.classList.remove('active');
        lightbox.setAttribute('aria-hidden', 'true');

        document.body.classList.remove(
            'gallery-lightbox-open'
        );
    };

    const showPrevious = function () {
        activeIndex =
            (activeIndex - 1 + items.length)
            % items.length;

        renderImage();
    };

    const showNext = function () {
        activeIndex =
            (activeIndex + 1)
            % items.length;

        renderImage();
    };

    items.forEach(function (item, index) {
        item.addEventListener('click', function () {
            openLightbox(index);
        });
    });

    closeButton?.addEventListener(
        'click',
        closeLightbox
    );

    prevButton?.addEventListener(
        'click',
        showPrevious
    );

    nextButton?.addEventListener(
        'click',
        showNext
    );

    lightbox.addEventListener('click', function (event) {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (!lightbox.classList.contains('active')) {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
        }

        if (event.key === 'ArrowLeft') {
            showPrevious();
        }

        if (event.key === 'ArrowRight') {
            showNext();
        }
    });
});
</script>

<?php endif; ?>