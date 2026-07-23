<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class PublicCms extends BaseConfig
{
    /**
     * CMS halaman publik terstruktur.
     *
     * Konten disimpan sebagai JSON per section. View publik tetap
     * mempunyai fallback sehingga website tidak kosong ketika tabel
     * CMS belum tersedia atau content belum dipublikasikan.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $pages = [
        'home' => [
            'name' => 'Beranda',
            'route' => '/',
            'default_title' =>
                'GARDA 01 | Generasi Aktif Randugarut',
            'default_meta_description' =>
                'Website resmi GARDA 01, Generasi Aktif Randugarut, Karang Taruna RW 01 Kelurahan Randugarut.',
            'sections' => [
                'hero' => [
                    'name' => 'Hero Beranda',
                    'description' =>
                        'Identitas utama, ajakan pertama, dan teks kartu kegiatan unggulan.',
                    'toggleable' => false,
                    'fields' => [
                        'eyebrow' => [
                            'label' => 'Label Atas',
                            'type' => 'text',
                            'max' => 140,
                            'required' => true,
                            'default' =>
                                'Karang Taruna RW 01 • Kelurahan Randugarut',
                        ],
                        'title' => [
                            'label' => 'Judul Utama',
                            'type' => 'text',
                            'max' => 90,
                            'required' => true,
                            'default' => 'GARDA 01',
                        ],
                        'subtitle' => [
                            'label' => 'Subjudul',
                            'type' => 'text',
                            'max' => 140,
                            'required' => true,
                            'default' => 'Generasi Aktif Randugarut',
                        ],
                        'manifesto' => [
                            'label' => 'Manifesto',
                            'type' => 'textarea',
                            'max' => 320,
                            'required' => true,
                            'help' =>
                                'Gunakan baris baru untuk memisahkan kalimat.',
                            'default' =>
                                "Guyub dalam kebersamaan.\nBergerak melalui karya.\nBerdampak bagi lingkungan.",
                        ],
                        'introduction' => [
                            'label' => 'Pengantar',
                            'type' => 'textarea',
                            'max' => 620,
                            'required' => true,
                            'default' =>
                                'Ruang tumbuh dan kolaborasi pemuda RW 01 dalam kegiatan sosial, lingkungan, olahraga, kreativitas, pendidikan, usaha, dan pemberdayaan masyarakat.',
                        ],
                        'primary_label' => [
                            'label' => 'Teks Tombol Utama',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Lihat Gerak Kami',
                        ],
                        'primary_url' => [
                            'label' => 'URL Tombol Utama',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'help' =>
                                'Boleh memakai URL internal seperti /kegiatan.',
                            'default' => '/kegiatan',
                        ],
                        'secondary_label' => [
                            'label' => 'Teks Tombol Kedua',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Kenali GARDA 01',
                        ],
                        'secondary_url' => [
                            'label' => 'URL Tombol Kedua',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'default' => '/profil',
                        ],
                        'watermark' => [
                            'label' => 'Watermark Hero',
                            'type' => 'text',
                            'max' => 18,
                            'required' => true,
                            'default' => 'G01',
                        ],
                        'featured_label' => [
                            'label' => 'Label Kegiatan Unggulan',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Gerak Terbaru',
                        ],
                        'featured_link_label' => [
                            'label' => 'Teks Tautan Kegiatan Unggulan',
                            'type' => 'text',
                            'max' => 70,
                            'required' => true,
                            'default' => 'Lihat dokumentasi',
                        ],
                        'featured_empty_title' => [
                            'label' => 'Judul Saat Kegiatan Belum Ada',
                            'type' => 'text',
                            'max' => 90,
                            'required' => true,
                            'default' => 'GARDA 01',
                        ],
                        'featured_empty_body' => [
                            'label' => 'Pesan Saat Kegiatan Belum Ada',
                            'type' => 'textarea',
                            'max' => 240,
                            'required' => true,
                            'default' =>
                                'Dokumentasi kegiatan akan segera ditampilkan.',
                        ],
                    ],
                ],
                'statistics' => [
                    'name' => 'Statistik Organisasi',
                    'description' =>
                        'Label dan penjelasan empat statistik pada Beranda.',
                    'data_note' =>
                        'Nilai angka dihitung otomatis dari data anggota, pengurus, kegiatan, dan program. Editor hanya mengubah label serta deskripsinya.',
                    'toggleable' => true,
                    'fields' => [
                        'rt_label' => [
                            'label' => 'Label Statistik RT',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'RT Terhubung',
                        ],
                        'rt_description' => [
                            'label' => 'Deskripsi Statistik RT',
                            'type' => 'textarea',
                            'max' => 220,
                            'required' => true,
                            'default' =>
                                'Wilayah pemuda yang terdata dan terlibat.',
                        ],
                        'officials_label' => [
                            'label' => 'Label Statistik Pengurus',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Pengurus Aktif',
                        ],
                        'officials_suffix' => [
                            'label' => 'Akhiran Angka Pengurus',
                            'type' => 'text',
                            'max' => 4,
                            'required' => false,
                            'help' => 'Contoh: +. Kosongkan bila tidak diperlukan.',
                            'default' => '+',
                        ],
                        'officials_description' => [
                            'label' => 'Deskripsi Statistik Pengurus',
                            'type' => 'textarea',
                            'max' => 220,
                            'required' => true,
                            'default' =>
                                'Penggerak organisasi dan program GARDA 01.',
                        ],
                        'activities_label' => [
                            'label' => 'Label Statistik Kegiatan',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Kegiatan Terlaksana',
                        ],
                        'activities_description' => [
                            'label' => 'Deskripsi Statistik Kegiatan',
                            'type' => 'textarea',
                            'max' => 220,
                            'required' => true,
                            'default' =>
                                'Gerakan yang telah selesai dan terdokumentasi.',
                        ],
                        'programs_label' => [
                            'label' => 'Label Statistik Program',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Pilar Gerakan',
                        ],
                        'programs_description' => [
                            'label' => 'Deskripsi Statistik Program',
                            'type' => 'textarea',
                            'max' => 220,
                            'required' => true,
                            'default' =>
                                'Ruang kontribusi pemuda dalam berbagai bidang.',
                        ],
                    ],
                ],
                'about' => [
                    'name' => 'Nilai Utama',
                    'description' =>
                        'Pengantar dan tiga nilai organisasi pada Beranda.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Semangat GARDA 01',
                        ],
                        'title' => [
                            'label' => 'Judul Section',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Bertumbuh melalui kebersamaan dan tindakan nyata',
                        ],
                        'body' => [
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'max' => 700,
                            'required' => true,
                            'default' =>
                                'GARDA 01 hadir bukan sekadar sebagai struktur organisasi, tetapi sebagai ruang bagi pemuda untuk saling menguatkan, menciptakan karya, dan memberi manfaat bagi lingkungan.',
                        ],
                        'value_one_title' => [
                            'label' => 'Nilai 01 — Judul',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Guyub',
                        ],
                        'value_one_body' => [
                            'label' => 'Nilai 01 — Deskripsi',
                            'type' => 'textarea',
                            'max' => 420,
                            'required' => true,
                            'default' =>
                                'Menyatukan pemuda, warga, dan berbagai unsur masyarakat melalui kebersamaan yang sehat, terbuka, dan saling menguatkan.',
                        ],
                        'value_two_title' => [
                            'label' => 'Nilai 02 — Judul',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Bergerak',
                        ],
                        'value_two_body' => [
                            'label' => 'Nilai 02 — Deskripsi',
                            'type' => 'textarea',
                            'max' => 420,
                            'required' => true,
                            'default' =>
                                'Mengubah gagasan menjadi kegiatan nyata melalui kolaborasi, tanggung jawab, dan keberanian mengambil peran.',
                        ],
                        'value_three_title' => [
                            'label' => 'Nilai 03 — Judul',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Berdampak',
                        ],
                        'value_three_body' => [
                            'label' => 'Nilai 03 — Deskripsi',
                            'type' => 'textarea',
                            'max' => 420,
                            'required' => true,
                            'default' =>
                                'Menghadirkan program yang relevan, terdokumentasi, dan memberikan manfaat yang dapat dirasakan lingkungan.',
                        ],
                    ],
                ],
                'programs' => [
                    'name' => 'Pilar Program',
                    'description' =>
                        'Pengantar, tautan, dan jumlah program yang tampil pada Beranda.',
                    'data_note' =>
                        'Kartu program diambil otomatis dari Program GARDA 01 yang berstatus terbit.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Pilar Gerakan',
                        ],
                        'title' => [
                            'label' => 'Judul Section',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' => 'Dari kepedulian menjadi aksi',
                        ],
                        'body' => [
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'max' => 620,
                            'required' => true,
                            'default' =>
                                'Setiap pilar GARDA 01 menjadi ruang bagi pemuda untuk berkontribusi sesuai minat, kemampuan, dan kebutuhan lingkungan.',
                        ],
                        'section_link_label' => [
                            'label' => 'Teks Tautan Semua Program',
                            'type' => 'text',
                            'max' => 70,
                            'required' => true,
                            'default' => 'Seluruh Program',
                        ],
                        'section_link_url' => [
                            'label' => 'URL Semua Program',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'default' => '/program',
                        ],
                        'card_link_label' => [
                            'label' => 'Teks Tautan pada Kartu',
                            'type' => 'text',
                            'max' => 70,
                            'required' => true,
                            'default' => 'Pelajari program',
                        ],
                        'item_limit' => [
                            'label' => 'Jumlah Program Ditampilkan',
                            'type' => 'text',
                            'max' => 2,
                            'required' => true,
                            'help' => 'Masukkan angka 1–12.',
                            'default' => '8',
                        ],
                        'empty_message' => [
                            'label' => 'Pesan Saat Program Kosong',
                            'type' => 'textarea',
                            'max' => 220,
                            'required' => true,
                            'default' =>
                                'Program GARDA 01 belum tersedia.',
                        ],
                    ],
                ],
                'impact' => [
                    'name' => 'Cerita Dampak',
                    'description' =>
                        'Teks pendukung pada kegiatan berdampak yang dipilih otomatis.',
                    'data_note' =>
                        'Kegiatan dipilih otomatis dari kegiatan selesai yang sudah terbit dan memiliki hasil kegiatan.',
                    'toggleable' => true,
                    'fields' => [
                        'eyebrow' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Cerita Dampak',
                        ],
                        'media_title' => [
                            'label' => 'Judul Media Fallback',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Cerita Dampak',
                        ],
                        'media_subtitle' => [
                            'label' => 'Subjudul Media Fallback',
                            'type' => 'text',
                            'max' => 140,
                            'required' => true,
                            'default' => 'Guyub • Bergerak • Berdampak',
                        ],
                        'fallback_summary' => [
                            'label' => 'Ringkasan Fallback',
                            'type' => 'textarea',
                            'max' => 620,
                            'required' => true,
                            'default' =>
                                'Gerakan bersama pemuda dan warga untuk menghadirkan manfaat nyata, memperkuat kepedulian, serta menjaga kebersamaan di lingkungan RW 01 Randugarut.',
                        ],
                        'button_label' => [
                            'label' => 'Teks Tombol',
                            'type' => 'text',
                            'max' => 70,
                            'required' => true,
                            'default' => 'Baca Cerita Kegiatan',
                        ],
                        'watermark' => [
                            'label' => 'Watermark',
                            'type' => 'text',
                            'max' => 18,
                            'required' => true,
                            'default' => '01',
                        ],
                    ],
                ],
                'latest' => [
                    'name' => 'Kegiatan Terbaru',
                    'description' =>
                        'Pengantar, tautan, dan jumlah kegiatan terbaru pada Beranda.',
                    'data_note' =>
                        'Daftar kegiatan diambil otomatis dari kegiatan publik terbaru dan tidak mengulang kegiatan hero.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Jejak Gerak GARDA 01',
                        ],
                        'title' => [
                            'label' => 'Judul Section',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' => 'Kegiatan terbaru',
                        ],
                        'body' => [
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'max' => 620,
                            'required' => true,
                            'default' =>
                                'Dokumentasi kegiatan, kolaborasi, dan kontribusi pemuda GARDA 01 bagi lingkungan.',
                        ],
                        'section_link_label' => [
                            'label' => 'Teks Tautan Semua Kegiatan',
                            'type' => 'text',
                            'max' => 70,
                            'required' => true,
                            'default' => 'Seluruh Kegiatan',
                        ],
                        'section_link_url' => [
                            'label' => 'URL Semua Kegiatan',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'default' => '/kegiatan',
                        ],
                        'card_link_label' => [
                            'label' => 'Teks Tautan pada Kartu',
                            'type' => 'text',
                            'max' => 70,
                            'required' => true,
                            'default' => 'Lihat Dokumentasi',
                        ],
                        'item_limit' => [
                            'label' => 'Jumlah Kegiatan Ditampilkan',
                            'type' => 'text',
                            'max' => 1,
                            'required' => true,
                            'help' => 'Masukkan angka 1–6.',
                            'default' => '3',
                        ],
                        'empty_message' => [
                            'label' => 'Pesan Saat Kegiatan Kosong',
                            'type' => 'textarea',
                            'max' => 240,
                            'required' => true,
                            'default' =>
                                'Dokumentasi kegiatan terbaru belum tersedia.',
                        ],
                    ],
                ],
                'collaboration' => [
                    'name' => 'Ajakan Kolaborasi',
                    'description' =>
                        'Ajakan kolaborasi pada bagian akhir Beranda.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Bergerak Bersama',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Mari hadirkan lebih banyak dampak untuk lingkungan',
                        ],
                        'body' => [
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'max' => 700,
                            'required' => true,
                            'default' =>
                                'GARDA 01 terbuka untuk berkolaborasi dengan warga, komunitas, UMKM, lembaga pendidikan, pemerintah, serta mitra sosial dalam kegiatan yang bermanfaat bagi masyarakat.',
                        ],
                        'primary_label' => [
                            'label' => 'Teks Tombol Utama',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Hubungi GARDA 01',
                        ],
                        'primary_url' => [
                            'label' => 'URL Tombol Utama',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'default' => '/kontak',
                        ],
                        'secondary_label' => [
                            'label' => 'Teks Tombol Kedua',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Lihat Program',
                        ],
                        'secondary_url' => [
                            'label' => 'URL Tombol Kedua',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'default' => '/program',
                        ],
                        'list_label' => [
                            'label' => 'Judul Daftar Kolaborasi',
                            'type' => 'text',
                            'max' => 120,
                            'required' => true,
                            'default' => 'Terbuka untuk kolaborasi:',
                        ],
                        'collaboration_items' => [
                            'label' => 'Daftar Bidang Kolaborasi',
                            'type' => 'textarea',
                            'max' => 700,
                            'required' => true,
                            'help' => 'Satu bidang per baris.',
                            'default' =>
                                "Sosial dan kemanusiaan\nLingkungan dan kebersihan\nOlahraga dan kepemudaan\nPendidikan dan keterampilan\nUsaha produktif pemuda\nMedia dan kreativitas",
                        ],
                        'watermark' => [
                            'label' => 'Watermark',
                            'type' => 'text',
                            'max' => 24,
                            'required' => true,
                            'default' => 'GARDA 01',
                        ],
                    ],
                ],
            ],
        ],

        'profile' => [
            'name' => 'Profil',
            'route' => '/profil',
            'default_title' =>
                'Profil GARDA 01 | Generasi Aktif Randugarut',
            'default_meta_description' =>
                'Mengenal GARDA 01 — Generasi Aktif Randugarut, identitas Karang Taruna RW 01 Kelurahan Randugarut.',
            'sections' => [
                'hero' => [
                    'name' => 'Hero Profil',
                    'description' =>
                        'Pembuka halaman Profil dan kartu identitas brand GARDA 01.',
                    'toggleable' => false,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Atas',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Tentang Organisasi',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'textarea',
                            'max' => 160,
                            'required' => true,
                            'help' =>
                                'Gunakan baris baru bila judul perlu dua baris.',
                            'default' =>
                                "Generasi Aktif\nRandugarut",
                        ],
                        'body' => [
                            'label' => 'Pengantar',
                            'type' => 'textarea',
                            'max' => 700,
                            'required' => true,
                            'default' =>
                                'GARDA 01 merupakan identitas publik Karang Taruna RW 01 Kelurahan Randugarut sebagai ruang kolaborasi, pengembangan pemuda, dan kontribusi nyata bagi lingkungan.',
                        ],
                        'primary_label' => [
                            'label' => 'Teks Tombol Utama',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Lihat Program',
                        ],
                        'primary_url' => [
                            'label' => 'URL Tombol Utama',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'default' => '/program',
                        ],
                        'secondary_label' => [
                            'label' => 'Teks Tombol Kedua',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Kenali Pengurus',
                        ],
                        'secondary_url' => [
                            'label' => 'URL Tombol Kedua',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'default' => '/pengurus',
                        ],
                        'brand_kicker' => [
                            'label' => 'Label Kartu Brand',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Master Brand',
                        ],
                        'brand_title' => [
                            'label' => 'Nama Brand',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'GARDA 01',
                        ],
                        'brand_subtitle' => [
                            'label' => 'Kepanjangan Brand',
                            'type' => 'text',
                            'max' => 160,
                            'required' => true,
                            'default' => 'Generasi Aktif Randugarut',
                        ],
                        'brand_slogan' => [
                            'label' => 'Slogan Brand',
                            'type' => 'text',
                            'max' => 160,
                            'required' => true,
                            'default' =>
                                'Guyub • Bergerak • Berdampak',
                        ],
                    ],
                ],
                'story' => [
                    'name' => 'Cerita Organisasi',
                    'description' =>
                        'Narasi utama tentang organisasi dan brand GARDA 01.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Siapa Kami',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Tumbuh bersama pemuda dan warga RW 01',
                        ],
                        'paragraph_1' => [
                            'label' => 'Paragraf Pertama',
                            'type' => 'textarea',
                            'max' => 900,
                            'required' => true,
                            'default' =>
                                'Karang Taruna RW 01 Kelurahan Randugarut merupakan organisasi sosial kepemudaan yang menjadi wadah partisipasi, pengembangan, dan kolaborasi pemuda di lingkungan RW 01.',
                        ],
                        'paragraph_2' => [
                            'label' => 'Paragraf Kedua',
                            'type' => 'textarea',
                            'max' => 900,
                            'required' => true,
                            'default' =>
                                'Melalui GARDA 01, organisasi membangun identitas yang lebih mudah dikenal sekaligus tetap mempertahankan nama resmi Karang Taruna RW 01 dalam administrasi, legalitas, dan tata kelola organisasi.',
                        ],
                        'paragraph_3' => [
                            'label' => 'Paragraf Tambahan',
                            'type' => 'textarea',
                            'max' => 900,
                            'required' => false,
                            'help' =>
                                'Opsional. Kosongkan apabila dua paragraf sudah cukup.',
                            'default' => '',
                        ],
                    ],
                ],
                'identity' => [
                    'name' => 'Identitas Resmi',
                    'description' =>
                        'Ringkasan identitas formal dan identitas publik organisasi.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Identitas Resmi',
                        ],
                        'title' => [
                            'label' => 'Judul Kartu',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Identitas yang kami bawa bersama',
                        ],
                        'organization_label' => [
                            'label' => 'Label Nama Organisasi',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Nama Organisasi',
                        ],
                        'organization_value' => [
                            'label' => 'Nama Resmi Organisasi',
                            'type' => 'text',
                            'max' => 200,
                            'required' => true,
                            'default' =>
                                'Karang Taruna RW 01 Kelurahan Randugarut',
                        ],
                        'public_label' => [
                            'label' => 'Label Identitas Publik',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Identitas Publik',
                        ],
                        'public_value' => [
                            'label' => 'Identitas Publik',
                            'type' => 'text',
                            'max' => 120,
                            'required' => true,
                            'default' => 'GARDA 01',
                        ],
                        'expansion_label' => [
                            'label' => 'Label Kepanjangan',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Kepanjangan',
                        ],
                        'expansion_value' => [
                            'label' => 'Kepanjangan Identitas',
                            'type' => 'text',
                            'max' => 160,
                            'required' => true,
                            'default' => 'Generasi Aktif Randugarut',
                        ],
                        'region_label' => [
                            'label' => 'Label Wilayah',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Wilayah',
                        ],
                        'region_value' => [
                            'label' => 'Wilayah Organisasi',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'RW 01 Kelurahan Randugarut',
                        ],
                        'slogan_label' => [
                            'label' => 'Label Slogan',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Slogan',
                        ],
                        'slogan_value' => [
                            'label' => 'Slogan Organisasi',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Guyub • Bergerak • Berdampak',
                        ],
                    ],
                ],
                'direction' => [
                    'name' => 'Visi dan Misi',
                    'description' =>
                        'Arah organisasi yang menjadi dasar gerakan GARDA 01.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Arah Organisasi',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' => 'Visi dan misi GARDA 01',
                        ],
                        'vision_label' => [
                            'label' => 'Label Visi',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Visi',
                        ],
                        'vision_text' => [
                            'label' => 'Isi Visi',
                            'type' => 'textarea',
                            'max' => 900,
                            'required' => true,
                            'default' =>
                                'Menjadi organisasi pemuda yang guyub, aktif, adaptif, dan menghadirkan dampak nyata bagi lingkungan.',
                        ],
                        'mission_label' => [
                            'label' => 'Label Misi',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Misi',
                        ],
                        'mission_items' => [
                            'label' => 'Daftar Misi',
                            'type' => 'textarea',
                            'max' => 2200,
                            'required' => true,
                            'help' =>
                                'Tulis satu butir misi pada setiap baris.',
                            'default' =>
                                "Membangun kebersamaan dan partisipasi pemuda di seluruh wilayah RW 01.\nMenyelenggarakan kegiatan sosial, lingkungan, olahraga, pendidikan, usaha, dan kreativitas.\nMengembangkan tata kelola organisasi yang tertib, transparan, dan terdokumentasi.\nMembangun kolaborasi dengan warga, komunitas, lembaga, pemerintah, dan mitra.",
                        ],
                    ],
                ],
                'values' => [
                    'name' => 'Nilai Organisasi',
                    'description' =>
                        'Tiga nilai budaya utama yang membentuk cara GARDA 01 bekerja.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Nilai Organisasi',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Budaya yang kami bangun bersama',
                        ],
                        'value_one_number' => [
                            'label' => 'Nomor Nilai Pertama',
                            'type' => 'text',
                            'max' => 12,
                            'required' => true,
                            'default' => '01',
                        ],
                        'value_one_title' => [
                            'label' => 'Judul Nilai Pertama',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Guyub',
                        ],
                        'value_one_body' => [
                            'label' => 'Deskripsi Nilai Pertama',
                            'type' => 'textarea',
                            'max' => 520,
                            'required' => true,
                            'default' =>
                                'Menjaga kebersamaan, rasa memiliki, dan hubungan yang sehat antarpemuda serta warga.',
                        ],
                        'value_two_number' => [
                            'label' => 'Nomor Nilai Kedua',
                            'type' => 'text',
                            'max' => 12,
                            'required' => true,
                            'default' => '02',
                        ],
                        'value_two_title' => [
                            'label' => 'Judul Nilai Kedua',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Bergerak',
                        ],
                        'value_two_body' => [
                            'label' => 'Deskripsi Nilai Kedua',
                            'type' => 'textarea',
                            'max' => 520,
                            'required' => true,
                            'default' =>
                                'Mengubah gagasan menjadi program, kegiatan, kolaborasi, dan karya yang nyata.',
                        ],
                        'value_three_number' => [
                            'label' => 'Nomor Nilai Ketiga',
                            'type' => 'text',
                            'max' => 12,
                            'required' => true,
                            'default' => '03',
                        ],
                        'value_three_title' => [
                            'label' => 'Judul Nilai Ketiga',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Berdampak',
                        ],
                        'value_three_body' => [
                            'label' => 'Deskripsi Nilai Ketiga',
                            'type' => 'textarea',
                            'max' => 520,
                            'required' => true,
                            'default' =>
                                'Memastikan setiap langkah memberi manfaat dan perubahan positif bagi lingkungan.',
                        ],
                    ],
                ],
                'cta' => [
                    'name' => 'Ajakan Jelajahi Program',
                    'description' =>
                        'CTA pada bagian akhir halaman Profil.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Ruang Kontribusi',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Kenali tujuh pilar gerakan GARDA 01',
                        ],
                        'body' => [
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'max' => 620,
                            'required' => true,
                            'default' =>
                                'Setiap pilar menjadi ruang bagi pemuda untuk belajar, berkolaborasi, dan menghasilkan kontribusi nyata.',
                        ],
                        'button_label' => [
                            'label' => 'Teks Tombol',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Jelajahi Program',
                        ],
                        'button_url' => [
                            'label' => 'URL Tombol',
                            'type' => 'url',
                            'max' => 255,
                            'required' => true,
                            'default' => '/program',
                        ],
                    ],
                ],
            ],
        ],

        'contact' => [
            'name' => 'Kontak',
            'route' => '/kontak',
            'default_title' =>
                'Kontak dan Kolaborasi | GARDA 01',
            'default_meta_description' =>
                'Hubungi GARDA 01 untuk kolaborasi kegiatan, program sosial, lingkungan, kepemudaan, usaha, media, dan pemberdayaan masyarakat.',
            'sections' => [
                'hero' => [
                    'name' => 'Hero Kontak',
                    'description' =>
                        'Pembuka halaman Kontak dan kartu kanal resmi organisasi.',
                    'toggleable' => false,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Atas',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Kontak dan Kolaborasi',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'textarea',
                            'max' => 160,
                            'required' => true,
                            'help' =>
                                'Gunakan baris baru bila judul perlu dua baris.',
                            'default' =>
                                "Bergerak bersama\nGARDA 01",
                        ],
                        'body' => [
                            'label' => 'Pengantar',
                            'type' => 'textarea',
                            'max' => 700,
                            'required' => true,
                            'default' =>
                                'Sampaikan gagasan, undangan, tawaran kolaborasi, kebutuhan sosial, maupun informasi kegiatan kepada Karang Taruna RW 01 Randugarut.',
                        ],
                        'values_line' => [
                            'label' => 'Baris Nilai',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'help' =>
                                'Gunakan pemisah titik tengah, misalnya Guyub • Bergerak • Berdampak.',
                            'default' =>
                                'Guyub • Bergerak • Berdampak',
                        ],
                        'identity_kicker' => [
                            'label' => 'Label Kartu Identitas',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Kanal Resmi Organisasi',
                        ],
                        'identity_title' => [
                            'label' => 'Nama pada Kartu Identitas',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'GARDA 01',
                        ],
                        'identity_subtitle' => [
                            'label' => 'Deskripsi Kartu Identitas',
                            'type' => 'textarea',
                            'max' => 260,
                            'required' => true,
                            'default' =>
                                "Generasi Aktif Randugarut\nKarang Taruna RW 01",
                        ],
                    ],
                ],
                'channels' => [
                    'name' => 'Kanal Kontak Resmi',
                    'description' =>
                        'Kartu email, WhatsApp, dan lokasi yang memakai data Pengaturan Website.',
                    'data_note' =>
                        'Alamat email, nomor WhatsApp, dan URL Maps diambil otomatis dari Pengaturan Website.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Kanal Resmi',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Pilih cara paling nyaman untuk terhubung',
                        ],
                        'body' => [
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'max' => 620,
                            'required' => true,
                            'default' =>
                                'Gunakan kanal resmi berikut untuk menyampaikan kebutuhan, undangan, atau peluang kolaborasi.',
                        ],
                        'email_label' => [
                            'label' => 'Label Email',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Email Resmi',
                        ],
                        'whatsapp_label' => [
                            'label' => 'Label WhatsApp',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'WhatsApp',
                        ],
                        'location_label' => [
                            'label' => 'Label Lokasi',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Lokasi Organisasi',
                        ],
                        'empty_value' => [
                            'label' => 'Teks Saat Kanal Belum Diisi',
                            'type' => 'text',
                            'max' => 120,
                            'required' => true,
                            'default' =>
                                'Belum dicantumkan oleh pengurus.',
                        ],
                    ],
                ],
                'form_intro' => [
                    'name' => 'Pengantar Form Pesan',
                    'description' =>
                        'Judul, penjelasan, dan microcopy form kontak.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Kirim Pesan',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Apa yang ingin Anda sampaikan?',
                        ],
                        'body' => [
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'max' => 620,
                            'required' => true,
                            'default' =>
                                'Isi informasi dengan lengkap agar tim GARDA 01 dapat menindaklanjuti pesan dengan tepat.',
                        ],
                        'submit_label' => [
                            'label' => 'Teks Tombol Kirim',
                            'type' => 'text',
                            'max' => 60,
                            'required' => true,
                            'default' => 'Kirim Pesan',
                        ],
                        'submit_note' => [
                            'label' => 'Catatan di Samping Tombol',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Pesan akan masuk ke Portal Pengurus GARDA 01.',
                        ],
                    ],
                ],
                'collaboration' => [
                    'name' => 'Ruang Kolaborasi',
                    'description' =>
                        'Daftar bidang kolaborasi yang terbuka bagi warga dan mitra.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Ruang Kolaborasi',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'Kami terbuka untuk bergerak bersama',
                        ],
                        'items' => [
                            'label' => 'Daftar Bidang Kolaborasi',
                            'type' => 'textarea',
                            'max' => 2200,
                            'required' => true,
                            'help' =>
                                'Tulis satu bidang kolaborasi pada setiap baris.',
                            'default' =>
                                "Program sosial dan kemanusiaan\nKegiatan lingkungan dan kebersihan\nOlahraga serta kepemudaan\nPendidikan dan keterampilan\nMedia, desain, dan dokumentasi\nUsaha produktif dan UMKM",
                        ],
                    ],
                ],
                'location' => [
                    'name' => 'Lokasi Organisasi',
                    'description' =>
                        'Kartu alamat dan tautan Google Maps pada halaman Kontak.',
                    'data_note' =>
                        'Alamat dan URL Maps diambil otomatis dari Pengaturan Website.',
                    'toggleable' => true,
                    'fields' => [
                        'kicker' => [
                            'label' => 'Label Section',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Wilayah Organisasi',
                        ],
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 180,
                            'required' => true,
                            'default' =>
                                'RW 01 Kelurahan Randugarut',
                        ],
                        'body' => [
                            'label' => 'Deskripsi',
                            'type' => 'textarea',
                            'max' => 520,
                            'required' => true,
                            'default' =>
                                'Basis gerakan pemuda GARDA 01 di wilayah Kelurahan Randugarut, Kecamatan Tugu, Kota Semarang.',
                        ],
                        'map_label' => [
                            'label' => 'Teks Tombol Maps',
                            'type' => 'text',
                            'max' => 80,
                            'required' => true,
                            'default' => 'Buka Google Maps',
                        ],
                    ],
                ],
                'notice' => [
                    'name' => 'Catatan Layanan',
                    'description' =>
                        'Catatan batas layanan organisasi pada sidebar Kontak.',
                    'toggleable' => true,
                    'fields' => [
                        'title' => [
                            'label' => 'Judul',
                            'type' => 'text',
                            'max' => 100,
                            'required' => true,
                            'default' => 'Catatan',
                        ],
                        'body' => [
                            'label' => 'Isi Catatan',
                            'type' => 'textarea',
                            'max' => 700,
                            'required' => true,
                            'default' =>
                                'Untuk kondisi darurat, layanan pemerintahan, keamanan, atau kesehatan, silakan menghubungi instansi resmi yang berwenang.',
                        ],
                    ],
                ],
            ],
        ],
    ];
}
