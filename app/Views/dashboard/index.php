<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$userName =
    session()->get('name')
    ?? session()->get('full_name')
    ?? session()->get('user_name')
    ?? session()->get('email')
    ?? 'Pengurus GARDA 01';

$firstName = trim(
    explode(' ', (string) $userName)[0] ?? 'Pengurus'
);

$formatCurrency = static function ($value): string {
    return 'Rp'
        . number_format(
            (float) $value,
            0,
            ',',
            '.'
        );
};

$formatDate = static function (
    ?string $date,
    bool $includeTime = false
): string {
    if (empty($date)) {
        return 'Belum dijadwalkan';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return 'Belum dijadwalkan';
    }

    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $result =
        date('d', $timestamp)
        . ' '
        . $months[(int) date('n', $timestamp)]
        . ' '
        . date('Y', $timestamp);

    if (
        $includeTime
        && date('H:i', $timestamp) !== '00:00'
    ) {
        $result .= ' · '
            . date('H.i', $timestamp)
            . ' WIB';
    }

    return $result;
};

$formatRelativeTime = static function (
    ?string $date
) use ($formatDate): string {
    if (empty($date)) {
        return 'Baru saja';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return $formatDate($date);
    }

    $difference = time() - $timestamp;

    if ($difference >= 0 && $difference < 60) {
        return 'Baru saja';
    }

    if ($difference >= 60 && $difference < 3600) {
        return floor($difference / 60)
            . ' menit lalu';
    }

    if ($difference >= 3600 && $difference < 86400) {
        return floor($difference / 3600)
            . ' jam lalu';
    }

    if ($difference >= 86400 && $difference < 604800) {
        return floor($difference / 86400)
            . ' hari lalu';
    }

    return $formatDate($date, true);
};
?>

<div class="portal-dashboard">

    <header class="portal-dashboard-welcome">
        <div>
            <span class="portal-dashboard-kicker">
                GARDA 01 Portal
            </span>

            <h1>
                Selamat datang, <?= esc($firstName) ?>.
            </h1>

            <p>
                Berikut kondisi terbaru organisasi dan hal yang
                membutuhkan perhatian pengurus.
            </p>
        </div>

        <div class="portal-dashboard-header-actions">
            <a
                href="<?= base_url('/activities/create') ?>"
                class="btn btn-primary"
            >
                + Catat Kegiatan
            </a>

            <a
                href="<?= base_url('/content-studio') ?>"
                class="btn portal-dashboard-ai-button"
            >
                <svg aria-hidden="true">
                    <use href="#icon-ai"></use>
                </svg>

                Buat Konten AI
            </a>
        </div>
    </header>

    <!-- RINGKASAN UTAMA -->
    <section class="portal-dashboard-summary-grid">

        <a
            href="<?= base_url('/members') ?>"
            class="portal-dashboard-summary-card"
        >
            <span class="portal-dashboard-summary-icon">
                <svg aria-hidden="true">
                    <use href="#icon-members"></use>
                </svg>
            </span>

            <div>
                <small>Anggota Aktif</small>
                <strong><?= esc($activeMembers ?? 0) ?></strong>
                <p>Data anggota yang masih aktif.</p>
            </div>

            <span class="portal-dashboard-card-arrow">
                →
            </span>
        </a>

        <a
            href="<?= base_url('/cash') ?>"
            class="portal-dashboard-summary-card featured"
        >
            <span class="portal-dashboard-summary-icon">
                <svg aria-hidden="true">
                    <use href="#icon-cash"></use>
                </svg>
            </span>

            <div>
                <small>Saldo Kas</small>

                <strong>
                    <?= esc(
                        $formatCurrency(
                            $finance['balance'] ?? 0
                        )
                    ) ?>
                </strong>

                <p>Saldo terkini organisasi.</p>
            </div>

            <span class="portal-dashboard-card-arrow">
                →
            </span>
        </a>

        <a
            href="<?= base_url('/meetings') ?>"
            class="portal-dashboard-summary-card"
        >
            <span class="portal-dashboard-summary-icon">
                <svg aria-hidden="true">
                    <use href="#icon-meeting"></use>
                </svg>
            </span>

            <div>
                <small>Rapat Terdekat</small>

                <strong class="portal-dashboard-date-value">
                    <?= esc(
                        $formatDate(
                            $nextMeeting['date'] ?? null
                        )
                    ) ?>
                </strong>

                <p>
                    <?= esc(
                        $nextMeeting['title']
                        ?? 'Belum ada agenda'
                    ) ?>
                </p>
            </div>

            <span class="portal-dashboard-card-arrow">
                →
            </span>
        </a>

        <a
            href="<?= base_url('/activities') ?>"
            class="portal-dashboard-summary-card"
        >
            <span class="portal-dashboard-summary-icon">
                <svg aria-hidden="true">
                    <use href="#icon-activity"></use>
                </svg>
            </span>

            <div>
                <small>Kegiatan Selesai</small>

                <strong>
                    <?= esc(
                        $completedActivities ?? 0
                    ) ?>
                </strong>

                <p>Kegiatan yang telah dilaksanakan.</p>
            </div>

            <span class="portal-dashboard-card-arrow">
                →
            </span>
        </a>

        <a
            href="<?= base_url('/messages') ?>"
            class="portal-dashboard-summary-card
            <?= ($unreadMessages ?? 0) > 0
                ? 'requires-attention'
                : '' ?>"
        >
            <span class="portal-dashboard-summary-icon">
                <svg aria-hidden="true">
                    <use href="#icon-message"></use>
                </svg>
            </span>

            <div>
                <small>Pesan Belum Dibaca</small>

                <strong>
                    <?= esc($unreadMessages ?? 0) ?>
                </strong>

                <p>Pesan warga dan calon mitra.</p>
            </div>

            <span class="portal-dashboard-card-arrow">
                →
            </span>
        </a>

    </section>

    <!-- PERHATIAN + AKSI CEPAT -->
    <section class="portal-dashboard-primary-grid">

        <article class="portal-dashboard-panel">
            <div class="portal-dashboard-panel-header">
                <div>
                    <span class="portal-dashboard-section-label">
                        Prioritas
                    </span>

                    <h2>Perlu perhatian</h2>

                    <p>
                        Data dan agenda yang sebaiknya segera
                        diperiksa.
                    </p>
                </div>

                <span class="portal-dashboard-count-badge">
                    <?= count($attentionItems ?? []) ?>
                </span>
            </div>

            <div class="portal-dashboard-attention-list">

                <?php foreach (
                    ($attentionItems ?? []) as $item
                ) : ?>
                    <div
                        class="portal-dashboard-attention-item
                        tone-<?= esc(
                            $item['tone'] ?? 'info'
                        ) ?>"
                    >
                        <span
                            class="portal-dashboard-attention-dot"
                        ></span>

                        <div>
                            <strong>
                                <?= esc($item['title']) ?>
                            </strong>

                            <p>
                                <?= esc(
                                    $item['description']
                                ) ?>
                            </p>
                        </div>

                        <a
                            href="<?= base_url(
                                $item['url']
                            ) ?>"
                        >
                            <?= esc($item['action']) ?>
                        </a>
                    </div>
                <?php endforeach; ?>

            </div>
        </article>

        <aside class="portal-dashboard-panel">
            <div class="portal-dashboard-panel-header">
                <div>
                    <span class="portal-dashboard-section-label">
                        Jalan Pintas
                    </span>

                    <h2>Aksi cepat</h2>

                    <p>
                        Akses pekerjaan rutin tanpa membuka
                        banyak menu.
                    </p>
                </div>
            </div>

            <div class="portal-dashboard-quick-grid">

                <?php foreach (
                    ($quickActions ?? []) as $action
                ) : ?>
                    <a
                        href="<?= base_url(
                            $action['url']
                        ) ?>"
                        <?= !empty($action['newTab'])
                            ? 'target="_blank" rel="noopener noreferrer"'
                            : '' ?>
                    >
                        <span>
                            <svg aria-hidden="true">
                                <use
                                    href="#<?= esc(
                                        $action['icon']
                                    ) ?>"
                                ></use>
                            </svg>
                        </span>

                        <div>
                            <strong>
                                <?= esc($action['label']) ?>
                            </strong>

                            <small>
                                <?= esc(
                                    $action['description']
                                ) ?>
                            </small>
                        </div>
                    </a>
                <?php endforeach; ?>

            </div>
        </aside>

    </section>

    <!-- AGENDA DAN WEBSITE -->
    <section class="portal-dashboard-secondary-grid">

        <article class="portal-dashboard-panel">
            <div class="portal-dashboard-panel-header inline">
                <div>
                    <span class="portal-dashboard-section-label">
                        Kalender Organisasi
                    </span>

                    <h2>Agenda mendatang</h2>
                </div>

                <a href="<?= base_url('/meetings') ?>">
                    Semua Rapat →
                </a>
            </div>

            <?php if (!empty($upcomingMeetings)) : ?>

                <div class="portal-dashboard-agenda-list">

                    <?php foreach (
                        $upcomingMeetings as $meeting
                    ) : ?>
                        <a
                            href="<?= !empty($meeting['id'])
                                ? base_url(
                                    '/meetings/edit/'
                                    . $meeting['id']
                                )
                                : base_url('/meetings') ?>"
                            class="portal-dashboard-agenda-item"
                        >
                            <div class="portal-dashboard-agenda-date">
                                <strong>
                                    <?= !empty($meeting['date'])
                                        ? date(
                                            'd',
                                            strtotime(
                                                $meeting['date']
                                            )
                                        )
                                        : '--' ?>
                                </strong>

                                <span>
                                    <?= !empty($meeting['date'])
                                        ? strtoupper(
                                            date(
                                                'M',
                                                strtotime(
                                                    $meeting['date']
                                                )
                                            )
                                        )
                                        : '---' ?>
                                </span>
                            </div>

                            <div>
                                <strong>
                                    <?= esc(
                                        $meeting['title']
                                    ) ?>
                                </strong>

                                <p>
                                    <?= esc(
                                        $formatDate(
                                            $meeting['date']
                                                ?? null,
                                            true
                                        )
                                    ) ?>

                                    ·

                                    <?= esc(
                                        $meeting['location']
                                        ?? '-'
                                    ) ?>
                                </p>

                                <span>
                                    <?= esc(
                                        ucfirst(
                                            (string) (
                                                $meeting['status']
                                                ?? 'terjadwal'
                                            )
                                        )
                                    ) ?>
                                </span>
                            </div>

                            <b>→</b>
                        </a>
                    <?php endforeach; ?>

                </div>

            <?php else : ?>

                <div class="portal-dashboard-empty-state">
                    <svg aria-hidden="true">
                        <use href="#icon-meeting"></use>
                    </svg>

                    <strong>
                        Belum ada rapat mendatang
                    </strong>

                    <p>
                        Jadwalkan rapat agar agenda organisasi
                        tampil di sini.
                    </p>

                    <a
                        href="<?= base_url('/meetings/create') ?>"
                        class="btn btn-primary"
                    >
                        + Buat Rapat
                    </a>
                </div>

            <?php endif; ?>

        </article>

        <article class="portal-dashboard-panel website-panel">
            <div class="portal-dashboard-panel-header inline">
                <div>
                    <span class="portal-dashboard-section-label">
                        Website Publik
                    </span>

                    <h2>Kondisi konten</h2>
                </div>

                <a
                    href="<?= base_url('/') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Buka Website →
                </a>
            </div>

            <div class="portal-dashboard-website-stats">

                <a href="<?= base_url('/programs') ?>">
                    <span>Program Aktif</span>

                    <strong>
                        <?= esc(
                            $publishedPrograms ?? 0
                        ) ?>
                    </strong>

                    <small>
                        Tampil di website publik
                    </small>
                </a>

                <a
                    href="<?= base_url(
                        '/programs?status=draft'
                    ) ?>"
                >
                    <span>Program Draft</span>

                    <strong>
                        <?= esc($draftPrograms ?? 0) ?>
                    </strong>

                    <small>
                        Menunggu penyelesaian
                    </small>
                </a>

                <a href="<?= base_url('/activities') ?>">
                    <span>Tanpa Cover</span>

                    <strong>
                        <?= esc(
                            $missingActivityCovers ?? 0
                        ) ?>
                    </strong>

                    <small>
                        Dokumentasi belum lengkap
                    </small>
                </a>

                <a href="<?= base_url('/messages') ?>">
                    <span>Pesan Publik</span>

                    <strong>
                        <?= esc($unreadMessages ?? 0) ?>
                    </strong>

                    <small>
                        Belum ditindaklanjuti
                    </small>
                </a>

            </div>

            <div class="portal-dashboard-website-actions">
                <a
                    href="<?= base_url('/programs') ?>"
                    class="btn btn-primary"
                >
                    Kelola Program
                </a>

                <a
                    href="<?= base_url('/activities') ?>"
                    class="btn btn-secondary"
                >
                    Kelola Kegiatan
                </a>
            </div>
        </article>

    </section>

    <!-- KEUANGAN DAN AKTIVITAS -->
    <section class="portal-dashboard-bottom-grid">

        <article class="portal-dashboard-panel finance-panel">
            <div class="portal-dashboard-panel-header inline">
                <div>
                    <span class="portal-dashboard-section-label">
                        Keuangan
                    </span>

                    <h2>Ringkasan kas</h2>
                </div>

                <a href="<?= base_url('/cash') ?>">
                    Kelola Kas →
                </a>
            </div>

            <div class="portal-dashboard-finance-balance">
                <span>Saldo Saat Ini</span>

                <strong>
                    <?= esc(
                        $formatCurrency(
                            $finance['balance'] ?? 0
                        )
                    ) ?>
                </strong>

                <small>
                    Akumulasi seluruh pemasukan dan pengeluaran
                </small>
            </div>

            <div class="portal-dashboard-finance-grid">
                <div class="income">
                    <span>Pemasukan Bulan Ini</span>

                    <strong>
                        <?= esc(
                            $formatCurrency(
                                $finance[
                                    'monthlyIncome'
                                ] ?? 0
                            )
                        ) ?>
                    </strong>
                </div>

                <div class="expense">
                    <span>Pengeluaran Bulan Ini</span>

                    <strong>
                        <?= esc(
                            $formatCurrency(
                                $finance[
                                    'monthlyExpense'
                                ] ?? 0
                            )
                        ) ?>
                    </strong>
                </div>

                <div>
                    <span>Total Pemasukan</span>

                    <strong>
                        <?= esc(
                            $formatCurrency(
                                $finance[
                                    'totalIncome'
                                ] ?? 0
                            )
                        ) ?>
                    </strong>
                </div>

                <div>
                    <span>Total Pengeluaran</span>

                    <strong>
                        <?= esc(
                            $formatCurrency(
                                $finance[
                                    'totalExpense'
                                ] ?? 0
                            )
                        ) ?>
                    </strong>
                </div>
            </div>
        </article>

        <article class="portal-dashboard-panel">
            <div class="portal-dashboard-panel-header">
                <div>
                    <span class="portal-dashboard-section-label">
                        Riwayat Data
                    </span>

                    <h2>Aktivitas terbaru</h2>

                    <p>
                        Pembaruan data terakhir dari berbagai
                        modul portal.
                    </p>
                </div>
            </div>

            <?php if (!empty($recentActivities)) : ?>

                <div class="portal-dashboard-timeline">

                    <?php foreach (
                        $recentActivities as $activity
                    ) : ?>
                        <a
                            href="<?= base_url(
                                $activity['url']
                            ) ?>"
                            class="portal-dashboard-timeline-item"
                        >
                            <span>
                                <svg aria-hidden="true">
                                    <use
                                        href="#<?= esc(
                                            $activity['icon']
                                                ?? 'icon-dashboard'
                                        ) ?>"
                                    ></use>
                                </svg>
                            </span>

                            <div>
                                <strong>
                                    <?= esc(
                                        $activity['title']
                                    ) ?>
                                </strong>

                                <p>
                                    <?= esc(
                                        $activity[
                                            'description'
                                        ]
                                    ) ?>
                                </p>
                            </div>

                            <time>
                                <?= esc(
                                    $formatRelativeTime(
                                        $activity['date']
                                            ?? null
                                    )
                                ) ?>
                            </time>
                        </a>
                    <?php endforeach; ?>

                </div>

            <?php else : ?>

                <div class="portal-dashboard-empty-state compact">
                    <strong>
                        Belum ada aktivitas terbaru
                    </strong>

                    <p>
                        Aktivitas akan muncul setelah data portal
                        mulai diperbarui.
                    </p>
                </div>

            <?php endif; ?>
        </article>

    </section>

</div>

<?= $this->endSection() ?>