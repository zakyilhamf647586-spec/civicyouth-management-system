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
                aria-label="<?= esc(public_t(
                    'accessibility.gallery_open',
                    'Buka foto {number} di galeri',
                    ['number' => $index + 1]
                ), 'attr') ?>"
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
                    decoding="async"
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
    role="dialog"
    aria-modal="true"
    aria-labelledby="publicGalleryTitle"
    aria-describedby="publicGalleryCaption"
    inert
>
    <h2
        class="g01-visually-hidden"
        id="publicGalleryTitle"
    >
        <?= esc(public_t(
            'accessibility.gallery_dialog',
            'Galeri dokumentasi kegiatan'
        )) ?>
    </h2>

    <button
        type="button"
        class="public-gallery-close"
        id="publicGalleryClose"
        aria-label="<?= esc(public_t(
            'accessibility.gallery_close',
            'Tutup galeri'
        ), 'attr') ?>"
    >
        ×
    </button>

    <button
        type="button"
        class="public-gallery-nav public-gallery-prev"
        id="publicGalleryPrev"
        aria-label="<?= esc(public_t(
            'accessibility.gallery_previous',
            'Foto sebelumnya'
        ), 'attr') ?>"
    >
        ‹
    </button>

    <figure>
        <img
            src=""
            alt=""
            id="publicGalleryImage"
            decoding="async"
        >

        <figcaption id="publicGalleryCaption"></figcaption>
    </figure>

    <button
        type="button"
        class="public-gallery-nav public-gallery-next"
        id="publicGalleryNext"
        aria-label="<?= esc(public_t(
            'accessibility.gallery_next',
            'Foto berikutnya'
        ), 'attr') ?>"
    >
        ›
    </button>

    <div
        class="public-gallery-counter"
        id="publicGalleryCounter"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        data-label-template="<?= esc(public_t(
            'accessibility.gallery_position',
            'Foto {current} dari {total}'
        ), 'attr') ?>"
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
    let lastFocusedElement = null;

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

            const labelTemplate =
                counter.dataset.labelTemplate ||
                'Foto {current} dari {total}';

            counter.setAttribute(
                'aria-label',
                labelTemplate
                    .replace('{current}', activeIndex + 1)
                    .replace('{total}', items.length)
            );
        }
    };

    const openLightbox = function (index) {
        lastFocusedElement = document.activeElement;
        activeIndex = index;
        renderImage();

        lightbox.removeAttribute('inert');
        lightbox.classList.add('active');
        lightbox.setAttribute('aria-hidden', 'false');

        document.body.classList.add(
            'gallery-lightbox-open'
        );

        window.requestAnimationFrame(function () {
            closeButton?.focus({ preventScroll: true });
        });
    };

    const closeLightbox = function () {
        lightbox.classList.remove('active');
        lightbox.setAttribute('aria-hidden', 'true');
        lightbox.setAttribute('inert', '');

        document.body.classList.remove(
            'gallery-lightbox-open'
        );

        if (
            lastFocusedElement
            && document.contains(lastFocusedElement)
        ) {
            lastFocusedElement.focus({ preventScroll: true });
        }

        lastFocusedElement = null;
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
            event.preventDefault();
            closeLightbox();
            return;
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showPrevious();
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            showNext();
        }

        if (event.key === 'Tab') {
            const focusable = [
                closeButton,
                prevButton,
                nextButton,
            ].filter(function (element) {
                return element && !element.disabled;
            });

            if (!focusable.length) {
                event.preventDefault();
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (
                event.shiftKey
                && document.activeElement === first
            ) {
                event.preventDefault();
                last.focus();
            } else if (
                !event.shiftKey
                && document.activeElement === last
            ) {
                event.preventDefault();
                first.focus();
            }
        }
    });
});
</script>

<?php endif; ?>
