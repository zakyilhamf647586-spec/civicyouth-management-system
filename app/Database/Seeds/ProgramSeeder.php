<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run()
    {
        $table = $this->db->table('programs');

        if ($table->countAllResults() > 0) {
            return;
        }

        $programs = [
            [
                'name' => 'GARDA 01 Peduli',
                'slug' => 'peduli',
                'label' => 'Sosial dan Kemanusiaan',
                'tagline' => 'Peduli sesama, hadir untuk warga.',
                'short_description' => 'Pilar sosial yang berfokus pada kepedulian, bantuan warga, kegiatan kemanusiaan, dan aksi berbagi.',
                'description' => 'GARDA 01 Peduli menjadi ruang gerak pemuda untuk menghadirkan dukungan nyata bagi warga dan lingkungan sosial. Program dijalankan melalui aksi berbagi, bantuan sosial, dukungan bagi warga yang membutuhkan, serta kolaborasi kemanusiaan.',
                'focus_items' => json_encode([
                    'Aksi sosial dan kemanusiaan',
                    'Bantuan dan dukungan bagi warga',
                    'Kampanye berbagi',
                    'Kolaborasi sosial masyarakat',
                ], JSON_UNESCAPED_UNICODE),
                'campaign_items' => json_encode([
                    'GARDA 01 Berbagi',
                    'Aksi Berbagi Ramadan',
                    'Dukungan Sosial Warga',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'display_order' => 1,
            ],

            [
                'name' => 'GARDA 01 Hijau',
                'slug' => 'hijau',
                'label' => 'Lingkungan',
                'tagline' => 'Lingkungan terawat, warga lebih sehat.',
                'short_description' => 'Pilar lingkungan untuk kebersihan, penghijauan, pengelolaan sampah, dan kepedulian terhadap ruang bersama.',
                'description' => 'GARDA 01 Hijau mendorong keterlibatan pemuda dalam menjaga kebersihan dan kualitas lingkungan RW 01 melalui kerja bakti, penghijauan, edukasi lingkungan, dan gerakan pengelolaan sampah.',
                'focus_items' => json_encode([
                    'Kerja bakti lingkungan',
                    'Penghijauan wilayah',
                    'Pengelolaan sampah',
                    'Edukasi lingkungan',
                ], JSON_UNESCAPED_UNICODE),
                'campaign_items' => json_encode([
                    'GARDA 01 Bersih',
                    'Kerja Bakti RW 01',
                    'Gerakan Lingkungan Hijau',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'display_order' => 2,
            ],

            [
                'name' => 'GARDA 01 Sport',
                'slug' => 'sport',
                'label' => 'Olahraga dan Kepemudaan',
                'tagline' => 'Aktif bergerak, solid bersama.',
                'short_description' => 'Pilar olahraga dan aktivitas kepemudaan untuk membangun kesehatan, kebersamaan, dan sportivitas.',
                'description' => 'GARDA 01 Sport menjadi ruang aktivitas olahraga dan kebugaran pemuda melalui latihan bersama, turnamen, pertandingan persahabatan, dan dukungan terhadap kegiatan olahraga masyarakat.',
                'focus_items' => json_encode([
                    'Turnamen dan pertandingan',
                    'Latihan olahraga rutin',
                    'Aktivitas kebugaran pemuda',
                    'Kolaborasi komunitas olahraga',
                ], JSON_UNESCAPED_UNICODE),
                'campaign_items' => json_encode([
                    'Fun Mini Soccer',
                    'Olahraga Bersama Pemuda',
                    'Turnamen RW 01',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'display_order' => 3,
            ],

            [
                'name' => 'GARDA 01 Creative',
                'slug' => 'creative',
                'label' => 'Media dan Kreativitas',
                'tagline' => 'Kreativitas pemuda untuk cerita yang bermakna.',
                'short_description' => 'Pilar media, desain, dokumentasi, seni, publikasi, dan pengembangan kreativitas pemuda.',
                'description' => 'GARDA 01 Creative mengembangkan kemampuan pemuda dalam desain, dokumentasi, publikasi, seni, fotografi, video, dan pengelolaan media sosial organisasi.',
                'focus_items' => json_encode([
                    'Desain dan publikasi',
                    'Dokumentasi foto dan video',
                    'Media sosial organisasi',
                    'Seni dan kreativitas pemuda',
                ], JSON_UNESCAPED_UNICODE),
                'campaign_items' => json_encode([
                    'AI Content Studio',
                    'Dokumentasi GARDA 01',
                    'Media Kreatif Pemuda',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'display_order' => 4,
            ],

            [
                'name' => 'GARDA 01 Enterprise',
                'slug' => 'enterprise',
                'label' => 'Usaha dan Kemandirian',
                'tagline' => 'Produktif, mandiri, dan bertumbuh.',
                'short_description' => 'Pilar usaha pemuda, bazar, penjualan, dan penggalangan dana produktif untuk mendukung kemandirian organisasi.',
                'description' => 'GARDA 01 Enterprise menjadi ruang belajar dan pengembangan usaha produktif pemuda. Program ini mendorong pengalaman berjualan, pengelolaan modal, pemasaran, serta penguatan sumber dana organisasi.',
                'focus_items' => json_encode([
                    'Usaha produktif pemuda',
                    'Bazar dan penjualan',
                    'Pelatihan kewirausahaan',
                    'Penggalangan dana produktif',
                ], JSON_UNESCAPED_UNICODE),
                'campaign_items' => json_encode([
                    'Es Teh GARDA',
                    'Stan Usaha Pemuda',
                    'Bazar GARDA 01',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'display_order' => 5,
            ],

            [
                'name' => 'GARDA 01 Belajar',
                'slug' => 'belajar',
                'label' => 'Pendidikan dan Keterampilan',
                'tagline' => 'Belajar bersama, berkembang bersama.',
                'short_description' => 'Pilar pendidikan, pelatihan, literasi digital, administrasi, dan pengembangan keterampilan pemuda.',
                'description' => 'GARDA 01 Belajar mendukung pengembangan kapasitas pemuda melalui pelatihan, literasi digital, administrasi organisasi, pendidikan informal, dan kegiatan berbagi pengetahuan.',
                'focus_items' => json_encode([
                    'Pelatihan keterampilan',
                    'Literasi digital',
                    'Administrasi organisasi',
                    'Pendidikan dan mentoring',
                ], JSON_UNESCAPED_UNICODE),
                'campaign_items' => json_encode([
                    'Pelatihan Administrasi Pemuda',
                    'Literasi Digital GARDA',
                    'Kelas Keterampilan Pemuda',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'display_order' => 6,
            ],

            [
                'name' => 'GARDA 01 Berkah',
                'slug' => 'berkah',
                'label' => 'Keagamaan',
                'tagline' => 'Tumbuh dalam nilai, bergerak dalam kebaikan.',
                'short_description' => 'Pilar kegiatan keagamaan, spiritual, dan penguatan nilai kebersamaan masyarakat.',
                'description' => 'GARDA 01 Berkah mengembangkan kegiatan keagamaan dan spiritual yang mempererat kebersamaan pemuda dan warga melalui kajian, peringatan hari besar, kegiatan Ramadan, serta dukungan kegiatan keagamaan lingkungan.',
                'focus_items' => json_encode([
                    'Kegiatan keagamaan pemuda',
                    'Peringatan hari besar',
                    'Program Ramadan',
                    'Kolaborasi kegiatan spiritual',
                ], JSON_UNESCAPED_UNICODE),
                'campaign_items' => json_encode([
                    'Ramadan Bersama GARDA',
                    'Peringatan Hari Besar Islam',
                    'Pemuda Berbagi Berkah',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'display_order' => 7,
            ],
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($programs as &$program) {
            $program['created_at'] = $now;
            $program['updated_at'] = $now;
        }

        $table->insertBatch($programs);
    }
}