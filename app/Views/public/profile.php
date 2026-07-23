<?= $this->extend('layouts/public') ?>

<?= $this->section('head') ?>
<?php
$profileCmsCssPath = FCPATH
    . 'assets/css/public-cms-profile.css';

$profileCmsCssVersion = is_file($profileCmsCssPath)
    ? (string) filemtime($profileCmsCssPath)
    : '1';
?>
<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/public-cms-profile.css'
    ) ?>?v=<?= esc($profileCmsCssVersion, 'attr') ?>"
>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$cmsPage = $cmsPage ?? null;

$storyEnabled = public_cms_section_enabled(
    $cmsPage,
    'story',
    true
);

$identityEnabled = public_cms_section_enabled(
    $cmsPage,
    'identity',
    true
);

$missionItems = public_cms_lines(
    $cmsPage,
    'direction',
    'mission_items',
    "Membangun kebersamaan dan partisipasi pemuda di seluruh wilayah RW 01.\nMenyelenggarakan kegiatan sosial, lingkungan, olahraga, pendidikan, usaha, dan kreativitas.\nMengembangkan tata kelola organisasi yang tertib, transparan, dan terdokumentasi.\nMembangun kolaborasi dengan warga, komunitas, lembaga, pemerintah, dan mitra."
);
?>

<section class="public-editorial-hero">
    <div class="public-editorial-hero-copy">
        <span class="public-kicker">
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'kicker',
                'Tentang Organisasi'
            )) ?>
        </span>

        <h1>
            <?= nl2br(esc(public_cms_value(
                $cmsPage,
                'hero',
                'title',
                "Generasi Aktif\nRandugarut"
            ))) ?>
        </h1>

        <p>
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'body',
                'GARDA 01 merupakan identitas publik Karang Taruna RW 01 Kelurahan Randugarut sebagai ruang kolaborasi, pengembangan pemuda, dan kontribusi nyata bagi lingkungan.'
            )) ?>
        </p>

        <div class="public-editorial-actions">
            <a
                href="<?= esc(public_cms_url(
                    $cmsPage,
                    'hero',
                    'primary_url',
                    '/program'
                ), 'attr') ?>"
                class="btn btn-primary"
            >
                <?= esc(public_cms_value(
                    $cmsPage,
                    'hero',
                    'primary_label',
                    'Lihat Program'
                )) ?>
            </a>

            <a
                href="<?= esc(public_cms_url(
                    $cmsPage,
                    'hero',
                    'secondary_url',
                    '/pengurus'
                ), 'attr') ?>"
                class="btn btn-secondary"
            >
                <?= esc(public_cms_value(
                    $cmsPage,
                    'hero',
                    'secondary_label',
                    'Kenali Pengurus'
                )) ?>
            </a>
        </div>
    </div>

    <aside class="public-brand-lockup-card">
        <img
            src="<?= esc(site_asset_url(
                'site_logo',
                'assets/img/logo-rw01.png'
            ), 'attr') ?>"
            alt="Logo <?= esc(site_setting(
                'organization_name',
                'GARDA 01'
            )) ?>"
        >

        <span>
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'brand_kicker',
                'Master Brand'
            )) ?>
        </span>

        <strong>
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'brand_title',
                site_setting(
                    'organization_name',
                    'GARDA 01'
                )
            )) ?>
        </strong>

        <p>
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'brand_subtitle',
                site_setting(
                    'organization_full_name',
                    'Generasi Aktif Randugarut'
                )
            )) ?>
        </p>

        <div class="public-brand-slogan">
            <?= esc(public_cms_value(
                $cmsPage,
                'hero',
                'brand_slogan',
                site_setting(
                    'organization_tagline',
                    'Guyub • Bergerak • Berdampak'
                )
            )) ?>
        </div>
    </aside>
</section>

<?php if ($storyEnabled || $identityEnabled) : ?>
    <section class="public-content-section">
        <div class="profile-introduction-grid <?= (
            $storyEnabled xor $identityEnabled
        ) ? 'is-single' : '' ?>">

            <?php if ($storyEnabled) : ?>
                <article class="public-content-card profile-story-card">
                    <span class="public-kicker">
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'story',
                            'kicker',
                            'Siapa Kami'
                        )) ?>
                    </span>

                    <h2>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'story',
                            'title',
                            'Tumbuh bersama pemuda dan warga RW 01'
                        )) ?>
                    </h2>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'story',
                            'paragraph_1',
                            'Karang Taruna RW 01 Kelurahan Randugarut merupakan organisasi sosial kepemudaan yang menjadi wadah partisipasi, pengembangan, dan kolaborasi pemuda di lingkungan RW 01.'
                        )) ?>
                    </p>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'story',
                            'paragraph_2',
                            'Melalui GARDA 01, organisasi membangun identitas yang lebih mudah dikenal sekaligus tetap mempertahankan nama resmi Karang Taruna RW 01 dalam administrasi, legalitas, dan tata kelola organisasi.'
                        )) ?>
                    </p>

                    <?php
                    $thirdParagraph = public_cms_value(
                        $cmsPage,
                        'story',
                        'paragraph_3',
                        ''
                    );
                    ?>

                    <?php if ($thirdParagraph !== '') : ?>
                        <p><?= esc($thirdParagraph) ?></p>
                    <?php endif; ?>
                </article>
            <?php endif; ?>

            <?php if ($identityEnabled) : ?>
                <aside class="profile-identity-card">
                    <span class="public-kicker">
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'identity',
                            'kicker',
                            'Identitas Resmi'
                        )) ?>
                    </span>

                    <h2>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'identity',
                            'title',
                            'Identitas yang kami bawa bersama'
                        )) ?>
                    </h2>

                    <dl>
                        <div>
                            <dt>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'organization_label',
                                    'Nama Organisasi'
                                )) ?>
                            </dt>

                            <dd>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'organization_value',
                                    site_setting(
                                        'organization_legal_name',
                                        'Karang Taruna RW 01 Kelurahan Randugarut'
                                    )
                                )) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'public_label',
                                    'Identitas Publik'
                                )) ?>
                            </dt>

                            <dd>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'public_value',
                                    site_setting(
                                        'organization_name',
                                        'GARDA 01'
                                    )
                                )) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'expansion_label',
                                    'Kepanjangan'
                                )) ?>
                            </dt>

                            <dd>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'expansion_value',
                                    site_setting(
                                        'organization_full_name',
                                        'Generasi Aktif Randugarut'
                                    )
                                )) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'region_label',
                                    'Wilayah'
                                )) ?>
                            </dt>

                            <dd>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'region_value',
                                    'RW 01 Kelurahan Randugarut'
                                )) ?>
                            </dd>
                        </div>

                        <div>
                            <dt>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'slogan_label',
                                    'Slogan'
                                )) ?>
                            </dt>

                            <dd>
                                <?= esc(public_cms_value(
                                    $cmsPage,
                                    'identity',
                                    'slogan_value',
                                    site_setting(
                                        'organization_tagline',
                                        'Guyub • Bergerak • Berdampak'
                                    )
                                )) ?>
                            </dd>
                        </div>
                    </dl>
                </aside>
            <?php endif; ?>

        </div>
    </section>
<?php endif; ?>

<?php if (public_cms_section_enabled(
    $cmsPage,
    'direction',
    true
)) : ?>
    <section class="public-content-section">
        <div class="public-section-header">
            <span class="public-kicker">
                <?= esc(public_cms_value(
                    $cmsPage,
                    'direction',
                    'kicker',
                    'Arah Organisasi'
                )) ?>
            </span>

            <h2>
                <?= esc(public_cms_value(
                    $cmsPage,
                    'direction',
                    'title',
                    'Visi dan misi GARDA 01'
                )) ?>
            </h2>
        </div>

        <div class="profile-direction-grid">
            <article class="profile-vision-card">
                <span>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'direction',
                        'vision_label',
                        'Visi'
                    )) ?>
                </span>

                <h3>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'direction',
                        'vision_text',
                        'Menjadi organisasi pemuda yang guyub, aktif, adaptif, dan menghadirkan dampak nyata bagi lingkungan.'
                    )) ?>
                </h3>
            </article>

            <article class="profile-mission-card">
                <span>
                    <?= esc(public_cms_value(
                        $cmsPage,
                        'direction',
                        'mission_label',
                        'Misi'
                    )) ?>
                </span>

                <ol>
                    <?php foreach ($missionItems as $mission) : ?>
                        <li><?= esc($mission) ?></li>
                    <?php endforeach; ?>
                </ol>
            </article>
        </div>
    </section>
<?php endif; ?>

<?php if (public_cms_section_enabled(
    $cmsPage,
    'values',
    true
)) : ?>
    <section class="public-content-section">
        <div class="public-section-header">
            <span class="public-kicker">
                <?= esc(public_cms_value(
                    $cmsPage,
                    'values',
                    'kicker',
                    'Nilai Organisasi'
                )) ?>
            </span>

            <h2>
                <?= esc(public_cms_value(
                    $cmsPage,
                    'values',
                    'title',
                    'Budaya yang kami bangun bersama'
                )) ?>
            </h2>
        </div>

        <div class="profile-value-grid">
            <?php foreach ([
                'one' => ['01', 'Guyub', 'Menjaga kebersamaan, rasa memiliki, dan hubungan yang sehat antarpemuda serta warga.'],
                'two' => ['02', 'Bergerak', 'Mengubah gagasan menjadi program, kegiatan, kolaborasi, dan karya yang nyata.'],
                'three' => ['03', 'Berdampak', 'Memastikan setiap langkah memberi manfaat dan perubahan positif bagi lingkungan.'],
            ] as $key => $fallback) : ?>
                <article>
                    <span>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'values',
                            'value_' . $key . '_number',
                            $fallback[0]
                        )) ?>
                    </span>

                    <h3>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'values',
                            'value_' . $key . '_title',
                            $fallback[1]
                        )) ?>
                    </h3>

                    <p>
                        <?= esc(public_cms_value(
                            $cmsPage,
                            'values',
                            'value_' . $key . '_body',
                            $fallback[2]
                        )) ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if (public_cms_section_enabled(
    $cmsPage,
    'cta',
    true
)) : ?>
    <section class="public-page-cta">
        <div>
            <span class="public-kicker">
                <?= esc(public_cms_value(
                    $cmsPage,
                    'cta',
                    'kicker',
                    'Ruang Kontribusi'
                )) ?>
            </span>

            <h2>
                <?= esc(public_cms_value(
                    $cmsPage,
                    'cta',
                    'title',
                    'Kenali tujuh pilar gerakan GARDA 01'
                )) ?>
            </h2>

            <p>
                <?= esc(public_cms_value(
                    $cmsPage,
                    'cta',
                    'body',
                    'Setiap pilar menjadi ruang bagi pemuda untuk belajar, berkolaborasi, dan menghasilkan kontribusi nyata.'
                )) ?>
            </p>
        </div>

        <a
            href="<?= esc(public_cms_url(
                $cmsPage,
                'cta',
                'button_url',
                '/program'
            ), 'attr') ?>"
            class="btn btn-primary"
        >
            <?= esc(public_cms_value(
                $cmsPage,
                'cta',
                'button_label',
                'Jelajahi Program'
            )) ?>
        </a>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>
