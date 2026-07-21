<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SocialMedia extends BaseConfig
{
    public array $templates = [
        'COVER-00' => [
            'design_id' => 'DAHP68TRWeU',
            'name' => 'Grid & Hook Cover Library',
            'category' => 'Cover Carousel',
            'format' => '1080 × 1350',
            'pages' => 10,
            'type' => 'carousel',
            'url' => 'https://www.canva.com/d/8UEYUDnw-t-qRQO',
            'description' => 'Cover pemantik untuk tujuh pilar, INFO, REPORT, dan RECRUIT.',
        ],
        'DOC-01A' => [
            'design_id' => 'DAHP4PUF4xc',
            'name' => 'Carousel Dokumentasi',
            'category' => 'Dokumentasi',
            'format' => '1080 × 1350',
            'pages' => 6,
            'type' => 'carousel',
            'url' => 'https://www.canva.com/d/pfSnkHay5RS_mzR',
            'description' => 'Master dokumentasi kegiatan lengkap.',
        ],
        'DOC-01B' => [
            'design_id' => 'DAHP4YFVXJM',
            'name' => 'Pilar Variant Library',
            'category' => 'Dokumentasi',
            'format' => '1080 × 1350',
            'pages' => 7,
            'type' => 'carousel',
            'url' => 'https://www.canva.com/d/Uj15QXkF6Z5rutK',
            'description' => 'Varian visual tujuh pilar GARDA 01.',
        ],
        'INFO-01F' => [
            'design_id' => 'DAHP4Uo6wy4',
            'name' => 'Agenda & Pengumuman Feed',
            'category' => 'Informasi',
            'format' => '1080 × 1350',
            'pages' => 6,
            'type' => 'carousel',
            'url' => 'https://www.canva.com/d/GQ19QsNxgWK4sop',
            'description' => 'Agenda, pengumuman, pendaftaran, dan jadwal.',
        ],
        'INFO-01S' => [
            'design_id' => 'DAHP4ae7sfE',
            'name' => 'Agenda & Pengumuman Story',
            'category' => 'Informasi',
            'format' => '1080 × 1920',
            'pages' => 6,
            'type' => 'story',
            'url' => 'https://www.canva.com/d/MEQVbzBITyIcTbJ',
            'description' => 'Versi native Instagram Story.',
        ],
        'GREET-01F' => [
            'design_id' => 'DAHP4bYd1ZI',
            'name' => 'Master Ucapan Feed',
            'category' => 'Ucapan',
            'format' => '1080 × 1350',
            'pages' => 6,
            'type' => 'feed',
            'url' => 'https://www.canva.com/d/G8aNrWiA4T4ifZa',
            'description' => 'Hari besar, prestasi, belasungkawa, dan apresiasi.',
        ],
        'GREET-01S' => [
            'design_id' => 'DAHP4Yq2frY',
            'name' => 'Master Ucapan Story',
            'category' => 'Ucapan',
            'format' => '1080 × 1920',
            'pages' => 6,
            'type' => 'story',
            'url' => 'https://www.canva.com/d/2LsbezbLTrMsigO',
            'description' => 'Ucapan dengan komposisi vertikal native.',
        ],
        'REELS-01' => [
            'design_id' => 'DAHP4cdPuKM',
            'name' => 'Vertical Content Toolkit',
            'category' => 'Reels',
            'format' => '1080 × 1920',
            'pages' => 6,
            'type' => 'reels',
            'url' => 'https://www.canva.com/d/3oaIOuYiBRAPsMp',
            'description' => 'Cover, hook, konteks, lower-third, dampak, dan CTA.',
        ],
        'STORY-01' => [
            'design_id' => 'DAHP52QVXWU',
            'name' => 'Interactive Story Toolkit',
            'category' => 'Story',
            'format' => '1080 × 1920',
            'pages' => 6,
            'type' => 'story',
            'url' => 'https://www.canva.com/d/IiZoyuDe4BrvhNq',
            'description' => 'Countdown, polling, Q&A, kuis, repost, dan link.',
        ],
        'REPORT-01' => [
            'design_id' => 'DAHP6NaMQy4',
            'name' => 'Laporan & Transparansi',
            'category' => 'Laporan',
            'format' => '1080 × 1350',
            'pages' => 6,
            'type' => 'carousel',
            'url' => 'https://www.canva.com/d/GRa3sDUMBrMgpjg',
            'description' => 'Ringkasan, angka utama, anggaran, evaluasi, dan tindak lanjut.',
        ],
        'RECRUIT-01F' => [
            'design_id' => 'DAHP6XKqOd8',
            'name' => 'Master Rekrutmen Feed',
            'category' => 'Rekrutmen',
            'format' => '1080 × 1350',
            'pages' => 6,
            'type' => 'carousel',
            'url' => 'https://www.canva.com/d/jyV2636ovl4wjFf',
            'description' => 'Cover, manfaat, posisi, kriteria, alur, dan CTA.',
        ],
        'RECRUIT-01S' => [
            'design_id' => 'DAHP6VMxq7w',
            'name' => 'Master Rekrutmen Story',
            'category' => 'Rekrutmen',
            'format' => '1080 × 1920',
            'pages' => 6,
            'type' => 'story',
            'url' => 'https://www.canva.com/d/RErlsTvVGwqTVsr',
            'description' => 'Kampanye rekrutmen dalam komposisi vertikal.',
        ],
    ];

    public array $workflowStatuses = [
        'brief' => 'Brief',
        'draft' => 'Draft Konten',
        'design' => 'Desain',
        'review' => 'Menunggu Review',
        'revision' => 'Perlu Revisi',
        'approved' => 'Disetujui',
        'scheduled' => 'Dijadwalkan',
        'published' => 'Dipublikasikan',
        'archived' => 'Diarsipkan',
    ];

    public array $workflowDescriptions = [
        'brief' => 'Tujuan, audiens, sumber, dan format konten sedang ditetapkan.',
        'draft' => 'Naskah, hook, caption, dan susunan halaman sedang disiapkan.',
        'design' => 'Tim media sedang mengerjakan desain pada Canva.',
        'review' => 'Konten menunggu pemeriksaan pengurus yang ditunjuk.',
        'revision' => 'Konten dikembalikan untuk perbaikan.',
        'approved' => 'Konten telah disetujui dan siap dijadwalkan.',
        'scheduled' => 'Konten siap tayang pada waktu yang ditentukan.',
        'published' => 'Konten telah tayang dan tautannya telah dicatat.',
        'archived' => 'Konten selesai dan disimpan sebagai arsip publikasi.',
    ];

    public array $transitions = [
        'brief' => ['draft', 'design', 'archived'],
        'draft' => ['brief', 'design', 'review', 'archived'],
        'design' => ['draft', 'review', 'archived'],
        'review' => ['revision', 'approved', 'archived'],
        'revision' => ['draft', 'design', 'review', 'archived'],
        'approved' => ['revision', 'scheduled', 'published', 'archived'],
        'scheduled' => ['approved', 'revision', 'published', 'archived'],
        'published' => ['archived'],
        'archived' => ['brief'],
    ];

    public array $categories = [
        'dokumentasi_kegiatan' => 'Dokumentasi Kegiatan',
        'pengumuman' => 'Informasi & Pengumuman',
        'hari_besar' => 'Ucapan Hari Besar',
        'laporan' => 'Laporan & Transparansi',
        'rekrutmen' => 'Rekrutmen',
        'edukasi' => 'Edukasi',
        'kampanye' => 'Kampanye Program',
        'apresiasi' => 'Apresiasi',
        'umum' => 'Umum',
    ];

    public array $publicationTypes = [
        'carousel' => 'Instagram Carousel',
        'feed' => 'Instagram Feed',
        'reels' => 'Instagram Reels',
        'story' => 'Instagram Story',
    ];

    public array $priorities = [
        'low' => 'Rendah',
        'normal' => 'Normal',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ];

    /**
     * Waktu peringatan sebelum deadline produksi.
     */
    public int $deadlineWarningHours = 48;

    /**
     * Jangka waktu data historis untuk rekomendasi jam tayang.
     */
    public int $recommendationLookbackDays = 180;

    /**
     * Jumlah minimal konten dengan metrik agar rekomendasi
     * dianggap cukup representatif.
     */
    public int $recommendationMinimumSamples = 3;

    /**
     * Baseline eksperimen internal.
     *
     * Digunakan hanya ketika data performa internal belum cukup.
     * Nilai ini bukan klaim "jam terbaik Instagram" secara umum.
     */
    public array $baselinePostingSlots = [
        [
            'weekday' => 2,
            'time' => '19:30',
            'label' => 'Selasa malam',
        ],
        [
            'weekday' => 4,
            'time' => '19:30',
            'label' => 'Kamis malam',
        ],
        [
            'weekday' => 7,
            'time' => '09:00',
            'label' => 'Minggu pagi',
        ],
    ];

    /**
     * Deadline setiap tahap dihitung mundur dari Rencana Tayang.
     *
     * offset_hours:
     * - nilai negatif berarti sebelum waktu tayang;
     * - nol berarti tepat pada waktu tayang.
     */
    public array $productionMilestones = [
        'brief' => [
            'label' => 'Selesaikan draft konten',
            'offset_hours' => -144,
        ],
        'draft' => [
            'label' => 'Mulai dan lengkapi desain',
            'offset_hours' => -96,
        ],
        'design' => [
            'label' => 'Kirim konten untuk review',
            'offset_hours' => -48,
        ],
        'review' => [
            'label' => 'Selesaikan keputusan review',
            'offset_hours' => -24,
        ],
        'revision' => [
            'label' => 'Selesaikan revisi',
            'offset_hours' => -18,
        ],
        'approved' => [
            'label' => 'Finalisasi jadwal dan aset',
            'offset_hours' => -12,
        ],
        'scheduled' => [
            'label' => 'Publikasikan konten',
            'offset_hours' => 0,
        ],
    ];
}
