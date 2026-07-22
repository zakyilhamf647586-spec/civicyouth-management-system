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
                        'Identitas utama dan ajakan pertama pada halaman Beranda.',
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
                    ],
                ],
                'about' => [
                    'name' => 'Nilai Utama',
                    'description' =>
                        'Pengantar nilai organisasi pada Beranda.',
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
                        'collaboration_items' => [
                            'label' => 'Daftar Bidang Kolaborasi',
                            'type' => 'textarea',
                            'max' => 700,
                            'required' => true,
                            'help' => 'Satu bidang per baris.',
                            'default' =>
                                "Sosial dan kemanusiaan\nLingkungan dan kebersihan\nOlahraga dan kepemudaan\nPendidikan dan keterampilan\nUsaha produktif pemuda\nMedia dan kreativitas",
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
                        'Pembuka halaman Profil organisasi.',
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
                        'Pembuka halaman Kontak dan Kolaborasi.',
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
                    ],
                ],
                'form_intro' => [
                    'name' => 'Pengantar Form Pesan',
                    'description' =>
                        'Judul dan penjelasan di atas form kontak.',
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
                    ],
                ],
            ],
        ],
    ];
}
