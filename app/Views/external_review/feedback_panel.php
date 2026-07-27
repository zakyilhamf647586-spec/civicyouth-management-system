<?php
$externalPreviewMeta = is_array(
    $externalPreviewMeta ?? null
)
    ? $externalPreviewMeta
    : [];

$latestExternalReview = is_array(
    $externalPreviewMeta['latest_review']
    ?? null
)
    ? $externalPreviewMeta['latest_review']
    : null;

$canSubmitExternalReview = !empty(
    $externalPreviewMeta['can_submit']
);
?>

<section
    id="external-review-panel"
    class="external-review-public-panel"
>
    <div class="external-review-public-panel__intro">
        <span>Review Eksternal GARDA 01</span>

        <h2>Berikan catatan atas halaman ini</h2>

        <p>
            Tanggapan Anda disimpan sebagai masukan eksternal.
            Keputusan ini tidak langsung memublikasikan website dan
            tetap diperiksa oleh pengurus berwenang.
        </p>
    </div>

    <?php if (session()->getFlashdata(
        'external_review_success'
    )) : ?>
        <div class="external-review-public-success">
            <?= esc(session()->getFlashdata(
                'external_review_success'
            )) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata(
        'external_review_error'
    )) : ?>
        <div class="external-review-public-error">
            <?= esc(session()->getFlashdata(
                'external_review_error'
            )) ?>
        </div>
    <?php endif; ?>

    <?php if ($latestExternalReview) : ?>
        <article class="external-review-public-completed">
            <span>Tanggapan Sudah Diterima</span>

            <h3>
                <?= match (
                    $latestExternalReview['decision']
                    ?? 'comment'
                ) {
                    'approved' =>
                        'Layak menurut reviewer eksternal',
                    'changes_requested' =>
                        'Reviewer meminta perbaikan',
                    default =>
                        'Komentar reviewer telah disimpan',
                } ?>
            </h3>

            <p>
                <?= esc(
                    $latestExternalReview['comment']
                    ?? ''
                ) ?>
            </p>

            <small>
                Oleh
                <?= esc(
                    $latestExternalReview[
                        'reviewer_name'
                    ] ?? 'Reviewer'
                ) ?>
            </small>
        </article>
    <?php elseif ($canSubmitExternalReview) : ?>
        <form
            action="<?= base_url(
                '/review/page/'
                . $externalPreviewToken
                . '/feedback'
            ) ?>"
            method="post"
            class="external-review-public-form"
        >
            <?= csrf_field() ?>

            <div class="external-review-public-grid">
                <div>
                    <label for="external_reviewer_name">
                        Nama Reviewer
                    </label>

                    <input
                        id="external_reviewer_name"
                        name="reviewer_name"
                        type="text"
                        maxlength="120"
                        value="<?= esc(old(
                            'reviewer_name',
                            $externalPreviewMeta[
                                'reviewer_name'
                            ] ?? ''
                        )) ?>"
                        required
                    >
                </div>

                <div>
                    <label for="external_reviewer_email">
                        Email
                        <small>(opsional)</small>
                    </label>

                    <input
                        id="external_reviewer_email"
                        name="reviewer_email"
                        type="email"
                        maxlength="150"
                        value="<?= esc(old(
                            'reviewer_email',
                            $externalPreviewMeta[
                                'reviewer_email'
                            ] ?? ''
                        )) ?>"
                    >
                </div>
            </div>

            <?php if (!empty(
                $externalPreviewMeta['allow_decision']
            )) : ?>
                <fieldset>
                    <legend>Penilaian</legend>

                    <label>
                        <input
                            type="radio"
                            name="decision"
                            value="approved"
                            <?= old(
                                'decision',
                                'comment'
                            ) === 'approved'
                                ? 'checked'
                                : '' ?>
                        >

                        <span>Layak menurut saya</span>
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="decision"
                            value="changes_requested"
                            <?= old('decision')
                                === 'changes_requested'
                                ? 'checked'
                                : '' ?>
                        >

                        <span>Memerlukan perbaikan</span>
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="decision"
                            value="comment"
                            <?= old(
                                'decision',
                                'comment'
                            ) === 'comment'
                                ? 'checked'
                                : '' ?>
                        >

                        <span>Komentar saja</span>
                    </label>
                </fieldset>
            <?php else : ?>
                <input
                    type="hidden"
                    name="decision"
                    value="comment"
                >
            <?php endif; ?>

            <div>
                <label for="external_review_comment">
                    Catatan
                </label>

                <textarea
                    id="external_review_comment"
                    name="comment"
                    rows="6"
                    minlength="10"
                    maxlength="3000"
                    placeholder="Jelaskan bagian yang sudah baik, perlu diperbaiki, atau perlu dikonfirmasi."
                    required
                ><?= esc(old('comment')) ?></textarea>
            </div>

            <button
                type="submit"
                class="external-review-public-submit"
                onclick="return confirm(
                    'Kirim tanggapan ini kepada pengurus GARDA 01?'
                )"
            >
                Kirim Tanggapan
            </button>
        </form>
    <?php else : ?>
        <div class="external-review-public-error">
            Tautan masih dapat digunakan untuk melihat snapshot,
            tetapi tidak menerima tanggapan tambahan.
        </div>
    <?php endif; ?>
</section>
