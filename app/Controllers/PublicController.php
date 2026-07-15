<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\ActivityModel;
use App\Models\MeetingModel;
use App\Models\CashTransactionModel;
use App\Models\OrganizationalStructureModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class PublicController extends BaseController
{
    public function index()
    {
        $memberModel   = new MemberModel();
        $activityModel = new ActivityModel();
        $meetingModel  = new MeetingModel();

        $activeMembers = $memberModel
            ->where('membership_status', 'active')
            ->countAllResults();

        $totalActivities = $activityModel->countAllResults(false);
        $totalMeetings   = $meetingModel->countAllResults(false);

        $latestActivities = $activityModel
            ->orderBy('activity_date', 'DESC')
            ->limit(3)
            ->findAll();

        $data = [
            'title'             => 'Karang Taruna RW 01 Randugarut',
            'active_members'    => $activeMembers,
            'total_activities'  => $totalActivities,
            'total_meetings'    => $totalMeetings,
            'latest_activities' => $latestActivities,
        ];

        return view('public/home', $data);
    }

    public function activities()
    {
        $activityModel = new ActivityModel();

        $data = [
            'title' => 'Kegiatan Karang Taruna RW 01',
            'activities' => $activityModel
                ->orderBy('activity_date', 'DESC')
                ->paginate(9, 'public_activities'),
            'pager' => $activityModel->pager,
        ];

        return view('public/activities', $data);
    }

    public function activityDetail($id)
    {
        $activityModel = new ActivityModel();

        $activity = $activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/kegiatan')->with('error', 'Kegiatan tidak ditemukan.');
        }

        $data = [
            'title' => $activity['title'],
            'activity' => $activity,
        ];

        return view('public/activity_detail', $data);
    }

    public function officials()
    {
        $structureModel = new OrganizationalStructureModel();

        $officials = $structureModel
            ->select(
                'organizational_structures.*, ' .
                'members.full_name AS member_name'
            )
            ->join(
                'members',
                'members.id = organizational_structures.member_id',
                'left'
            )
            ->orderBy('organizational_structures.sort_order', 'ASC')
            ->orderBy('organizational_structures.id', 'ASC')
            ->findAll();

        return view('public/officials', [
            'title'     => 'Struktur Pengurus Karang Taruna RW 01',
            'officials' => $officials,
        ]);
    }

    public function profile()
    {
        return view('public/profile', [
            'title' => 'Profil GARDA 01 | Generasi Aktif Randugarut',
            'metaDescription' => 'Mengenal GARDA 01 — Generasi Aktif Randugarut, identitas Karang Taruna RW 01 Kelurahan Randugarut.',
            'activePage' => 'profile',
        ]);
    }

    public function programs()
    {
        return view('public/programs', [
            'title' => 'Program GARDA 01 | Karang Taruna RW 01',
            'metaDescription' => 'Pilar program GARDA 01 dalam bidang sosial, lingkungan, olahraga, kreativitas, usaha, pendidikan, dan keagamaan.',
            'activePage' => 'programs',
            'programs' => $this->getPublicPrograms(),
        ]);
    }

    public function programDetail(string $slug)
    {
        $programs = $this->getPublicPrograms();

        if (!isset($programs[$slug])) {
            throw PageNotFoundException::forPageNotFound(
                'Program GARDA 01 tidak ditemukan.'
            );
        }

        $program = $programs[$slug];

        return view('public/program_detail', [
            'title' => $program['name'] . ' | GARDA 01',
            'metaDescription' => $program['short_description'],
            'activePage' => 'program_detail',
            'program' => $program,
        ]);
    }

    private function getPublicPrograms(): array
    {
        return [
            'peduli' => [
                'slug' => 'peduli',
                'number' => '01',
                'name' => 'GARDA 01 Peduli',
                'label' => 'Sosial dan Kemanusiaan',
                'tagline' => 'Peduli sesama, hadir untuk warga.',
                'short_description' => 'Pilar sosial yang berfokus pada kepedulian, bantuan warga, kegiatan kemanusiaan, dan aksi berbagi.',
                'description' => 'GARDA 01 Peduli menjadi ruang gerak pemuda untuk menghadirkan dukungan nyata bagi warga dan lingkungan sosial. Program dijalankan melalui aksi berbagi, bantuan sosial, dukungan bagi warga yang membutuhkan, serta kolaborasi kemanusiaan.',
                'focus' => [
                    'Aksi sosial dan kemanusiaan',
                    'Bantuan dan dukungan bagi warga',
                    'Kampanye berbagi',
                    'Kolaborasi sosial masyarakat',
                ],
                'campaigns' => [
                    'GARDA 01 Berbagi',
                    'Aksi Berbagi Ramadan',
                    'Dukungan Sosial Warga',
                ],
            ],

            'hijau' => [
                'slug' => 'hijau',
                'number' => '02',
                'name' => 'GARDA 01 Hijau',
                'label' => 'Lingkungan',
                'tagline' => 'Lingkungan terawat, warga lebih sehat.',
                'short_description' => 'Pilar lingkungan untuk kebersihan, penghijauan, pengelolaan sampah, dan kepedulian terhadap ruang bersama.',
                'description' => 'GARDA 01 Hijau mendorong keterlibatan pemuda dalam menjaga kebersihan dan kualitas lingkungan RW 01 melalui kerja bakti, penghijauan, edukasi lingkungan, dan gerakan pengelolaan sampah.',
                'focus' => [
                    'Kerja bakti lingkungan',
                    'Penghijauan wilayah',
                    'Pengelolaan sampah',
                    'Edukasi lingkungan',
                ],
                'campaigns' => [
                    'GARDA 01 Bersih',
                    'Kerja Bakti RW 01',
                    'Gerakan Lingkungan Hijau',
                ],
            ],

            'sport' => [
                'slug' => 'sport',
                'number' => '03',
                'name' => 'GARDA 01 Sport',
                'label' => 'Olahraga dan Kepemudaan',
                'tagline' => 'Aktif bergerak, solid bersama.',
                'short_description' => 'Pilar olahraga dan aktivitas kepemudaan untuk membangun kesehatan, kebersamaan, dan sportivitas.',
                'description' => 'GARDA 01 Sport menjadi ruang aktivitas olahraga dan kebugaran pemuda melalui latihan bersama, turnamen, pertandingan persahabatan, dan dukungan terhadap kegiatan olahraga masyarakat.',
                'focus' => [
                    'Turnamen dan pertandingan',
                    'Latihan olahraga rutin',
                    'Aktivitas kebugaran pemuda',
                    'Kolaborasi komunitas olahraga',
                ],
                'campaigns' => [
                    'Fun Mini Soccer',
                    'Olahraga Bersama Pemuda',
                    'Turnamen RW 01',
                ],
            ],

            'creative' => [
                'slug' => 'creative',
                'number' => '04',
                'name' => 'GARDA 01 Creative',
                'label' => 'Media dan Kreativitas',
                'tagline' => 'Kreativitas pemuda untuk cerita yang bermakna.',
                'short_description' => 'Pilar media, desain, dokumentasi, seni, publikasi, dan pengembangan kreativitas pemuda.',
                'description' => 'GARDA 01 Creative mengembangkan kemampuan pemuda dalam desain, dokumentasi, publikasi, seni, fotografi, video, dan pengelolaan media sosial organisasi.',
                'focus' => [
                    'Desain dan publikasi',
                    'Dokumentasi foto dan video',
                    'Media sosial organisasi',
                    'Seni dan kreativitas pemuda',
                ],
                'campaigns' => [
                    'AI Content Studio',
                    'Dokumentasi GARDA 01',
                    'Media Kreatif Pemuda',
                ],
            ],

            'enterprise' => [
                'slug' => 'enterprise',
                'number' => '05',
                'name' => 'GARDA 01 Enterprise',
                'label' => 'Usaha dan Kemandirian',
                'tagline' => 'Produktif, mandiri, dan bertumbuh.',
                'short_description' => 'Pilar usaha pemuda, bazar, penjualan, dan penggalangan dana produktif untuk mendukung kemandirian organisasi.',
                'description' => 'GARDA 01 Enterprise menjadi ruang belajar dan pengembangan usaha produktif pemuda. Program ini mendorong pengalaman berjualan, pengelolaan modal, pemasaran, serta penguatan sumber dana organisasi.',
                'focus' => [
                    'Usaha produktif pemuda',
                    'Bazar dan penjualan',
                    'Pelatihan kewirausahaan',
                    'Penggalangan dana produktif',
                ],
                'campaigns' => [
                    'Es Teh GARDA',
                    'Stan Usaha Pemuda',
                    'Bazar GARDA 01',
                ],
            ],

            'belajar' => [
                'slug' => 'belajar',
                'number' => '06',
                'name' => 'GARDA 01 Belajar',
                'label' => 'Pendidikan dan Keterampilan',
                'tagline' => 'Belajar bersama, berkembang bersama.',
                'short_description' => 'Pilar pendidikan, pelatihan, literasi digital, administrasi, dan pengembangan keterampilan pemuda.',
                'description' => 'GARDA 01 Belajar mendukung pengembangan kapasitas pemuda melalui pelatihan, literasi digital, administrasi organisasi, pendidikan informal, dan kegiatan berbagi pengetahuan.',
                'focus' => [
                    'Pelatihan keterampilan',
                    'Literasi digital',
                    'Administrasi organisasi',
                    'Pendidikan dan mentoring',
                ],
                'campaigns' => [
                    'Pelatihan Administrasi Pemuda',
                    'Literasi Digital GARDA',
                    'Kelas Keterampilan Pemuda',
                ],
            ],

            'berkah' => [
                'slug' => 'berkah',
                'number' => '07',
                'name' => 'GARDA 01 Berkah',
                'label' => 'Keagamaan',
                'tagline' => 'Tumbuh dalam nilai, bergerak dalam kebaikan.',
                'short_description' => 'Pilar kegiatan keagamaan, spiritual, dan penguatan nilai kebersamaan masyarakat.',
                'description' => 'GARDA 01 Berkah mengembangkan kegiatan keagamaan dan spiritual yang mempererat kebersamaan pemuda dan warga melalui kajian, peringatan hari besar, kegiatan Ramadan, serta dukungan kegiatan keagamaan lingkungan.',
                'focus' => [
                    'Kegiatan keagamaan pemuda',
                    'Peringatan hari besar',
                    'Program Ramadan',
                    'Kolaborasi kegiatan spiritual',
                ],
                'campaigns' => [
                    'Ramadan Bersama GARDA',
                    'Peringatan Hari Besar Islam',
                    'Pemuda Berbagi Berkah',
                ],
            ],
        ];
    }
}