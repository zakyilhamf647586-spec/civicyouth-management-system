<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <span class="website-settings-kicker">
            Website Publik
        </span>

        <h2>Pengaturan Website</h2>

        <p>
            Kelola identitas, kontak, media sosial, footer,
            logo, dan SEO tanpa mengubah source code.
        </p>
    </div>

    <a
        href="<?= base_url('/') ?>"
        target="_blank"
        rel="noopener noreferrer"
        class="btn btn-secondary"
    >
        Pratinjau Website
    </a>
</div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')) : ?>
    <div class="alert-error">
        <?php foreach (
            session()->getFlashdata('errors')
            as $error
        ) : ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form
    action="<?= base_url(
        '/settings/website/update'
    ) ?>"
    method="post"
    enctype="multipart/form-data"
    class="website-settings-form"
>
    <?= csrf_field() ?>

    <div class="website-settings-layout">

        <aside class="website-settings-navigation">
            <strong>Bagian Pengaturan</strong>

            <nav>
                <?php foreach ($groups as $groupKey => $group) : ?>
                    <a href="#setting-<?= esc($groupKey) ?>">
                        <?= esc($group['title']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="website-settings-info">
                <span>Informasi</span>

                <p>
                    Perubahan akan diterapkan ke website publik
                    setelah tombol simpan ditekan.
                </p>
            </div>
        </aside>

        <main class="website-settings-content">

            <?php foreach ($groups as $groupKey => $group) : ?>

                <section
                    class="website-settings-card"
                    id="setting-<?= esc($groupKey) ?>"
                >
                    <header>
                        <span>
                            <?= esc($group['title']) ?>
                        </span>

                        <h3>
                            <?= esc($group['title']) ?>
                        </h3>

                        <p>
                            <?= esc($group['description']) ?>
                        </p>
                    </header>

                    <div class="website-settings-fields">

                        <?php foreach (
                            $group['fields'] as $key => $field
                        ) : ?>
                            <?php
                            $value = old(
                                $key,
                                $settings[$key] ?? ''
                            );
                            ?>

                            <div
                                class="form-group
                                <?= $field['type'] === 'textarea'
                                    ? 'website-setting-wide'
                                    : '' ?>"
                            >
                                <label for="<?= esc($key) ?>">
                                    <?= esc($field['label']) ?>
                                </label>

                                <?php if (
                                    $field['type'] === 'textarea'
                                ) : ?>

                                    <textarea
                                        id="<?= esc($key) ?>"
                                        name="<?= esc($key) ?>"
                                        rows="4"
                                        maxlength="<?= esc(
                                            $field['max_length']
                                            ?? 2000
                                        ) ?>"
                                        <?= !empty($field['required'])
                                            ? 'required'
                                            : '' ?>
                                    ><?= esc($value) ?></textarea>

                                <?php elseif (
                                    $field['type'] === 'file'
                                ) : ?>

                                    <?php if (!empty($value)) : ?>
                                        <div
                                            class="website-setting-image-preview"
                                        >
                                            <img
                                                src="<?= preg_match(
                                                    '#^https?://#i',
                                                    $value
                                                )
                                                    ? esc($value)
                                                    : base_url($value) ?>"
                                                alt="<?= esc(
                                                    $field['label']
                                                ) ?>"
                                            >
                                        </div>
                                    <?php endif; ?>

                                    <input
                                        type="file"
                                        id="<?= esc($key) ?>"
                                        name="<?= esc($key) ?>"
                                        accept="<?= $key === 'site_favicon'
                                            ? '.jpg,.jpeg,.png,.webp,.ico'
                                            : '.jpg,.jpeg,.png,.webp' ?>"
                                    >

                                    <small>
                                        Kosongkan apabila tidak ingin
                                        mengganti file saat ini.
                                    </small>

                                <?php else : ?>

                                    <input
                                        type="<?= esc(
                                            $field['type']
                                        ) ?>"
                                        id="<?= esc($key) ?>"
                                        name="<?= esc($key) ?>"
                                        value="<?= esc($value) ?>"
                                        maxlength="<?= esc(
                                            $field['max_length']
                                            ?? 255
                                        ) ?>"
                                        <?= $field['type'] === 'url'
                                            ? 'placeholder="https://..."'
                                            : '' ?>
                                        <?= !empty($field['required'])
                                            ? 'required'
                                            : '' ?>
                                    >

                                <?php endif; ?>
                            </div>

                        <?php endforeach; ?>

                    </div>
                </section>

            <?php endforeach; ?>

        </main>

    </div>

    <div class="website-settings-savebar">
        <div>
            <strong>Simpan Pengaturan Website</strong>

            <span>
                Pastikan informasi telah diperiksa sebelum disimpan.
            </span>
        </div>

        <button
            type="submit"
            class="btn btn-primary"
        >
            Simpan Perubahan
        </button>
    </div>

</form>

<?= $this->endSection() ?>