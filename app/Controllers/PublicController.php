<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\ActivityModel;
use App\Models\MeetingModel;
use App\Models\CashTransactionModel;
use App\Models\OrganizationalStructureModel;
use App\Models\ProgramModel;
use App\Models\ActivityImageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class PublicController extends BaseController
{
    public function index()
    {
        $memberModel    = new MemberModel();
        $activityModel  = new ActivityModel();
        $structureModel = new OrganizationalStructureModel();
        $programModel   = new ProgramModel();

        /*
        * Statistik organisasi
        */
        $activeMembers = $memberModel
            ->where('membership_status', 'active')
            ->countAllResults();

        $connectedRt = $memberModel
            ->select('rt')
            ->where('membership_status', 'active')
            ->where('rt IS NOT NULL', null, false)
            ->where('rt !=', '')
            ->groupBy('rt')
            ->countAllResults();

        $activeOfficials = $structureModel->countPublicOfficials();

        $completedActivities = (new ActivityModel())
            ->applyPublicVisibility()
            ->where('status', 'completed')
            ->countAllResults();

        /*
        * Tujuh pilar program publik
        */
        $programs = $programModel->getPublishedPrograms();

        /*
        * Kegiatan unggulan:
        * mengambil kegiatan terbaru yang mempunyai dokumentasi.
        */
        $featuredActivity = (new ActivityModel())
            ->applyPublicVisibility()
            ->select(
                'activities.*, ' .
                'programs.name AS program_name, ' .
                'programs.slug AS program_slug, ' .
                'programs.label AS program_label'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            )
            ->where(
                'activities.documentation_file IS NOT NULL',
                null,
                false
            )
            ->where('activities.documentation_file !=', '')
            ->orderBy('activities.is_featured', 'DESC')
            ->orderBy('activities.activity_date', 'DESC')
            ->orderBy('activities.id', 'DESC')
            ->first();

        /*
        * Jika belum ada kegiatan yang memiliki foto,
        * gunakan kegiatan terbaru sebagai fallback.
        */
        if (!$featuredActivity) {
            $featuredActivity = (new ActivityModel())
                ->applyPublicVisibility()
                ->select(
                    'activities.*, ' .
                    'programs.name AS program_name, ' .
                    'programs.slug AS program_slug, ' .
                    'programs.label AS program_label'
                )
                ->join(
                    'programs',
                    'programs.id = activities.program_id',
                    'left'
                )
                ->orderBy('activities.activity_date', 'DESC')
                ->orderBy('activities.id', 'DESC')
                ->first();
        }

        /*
        * Cerita dampak:
        * prioritaskan kegiatan selesai yang memiliki hasil kegiatan.
        */
        $impactBuilder = (new ActivityModel())
            ->applyPublicVisibility()
            ->select(
                'activities.*, ' .
                'programs.name AS program_name, ' .
                'programs.slug AS program_slug, ' .
                'programs.label AS program_label'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            )
            ->where('activities.status', 'completed')
            ->where('activities.result IS NOT NULL', null, false)
            ->where('activities.result !=', '');

        if (!empty($featuredActivity['id'])) {
            $impactBuilder->where(
                'activities.id !=',
                $featuredActivity['id']
            );
        }

        $impactActivity = $impactBuilder
            ->orderBy('activities.is_featured', 'DESC')
            ->orderBy('activities.activity_date', 'DESC')
            ->orderBy('activities.id', 'DESC')
            ->first();

        /*
        * Bila belum ada field hasil yang terisi,
        * gunakan kegiatan selesai lainnya.
        */
        if (!$impactActivity) {
            $impactFallback = (new ActivityModel())
                ->applyPublicVisibility()
                ->select(
                    'activities.*, ' .
                    'programs.name AS program_name, ' .
                    'programs.slug AS program_slug, ' .
                    'programs.label AS program_label'
                )
                ->join(
                    'programs',
                    'programs.id = activities.program_id',
                    'left'
                )
                ->where('activities.status', 'completed');

            if (!empty($featuredActivity['id'])) {
                $impactFallback->where(
                    'activities.id !=',
                    $featuredActivity['id']
                );
            }

            $impactActivity = $impactFallback
                ->orderBy('activities.activity_date', 'DESC')
                ->orderBy('activities.id', 'DESC')
                ->first();
        }

        /*
        * Kandidat kegiatan terbaru untuk section Beranda.
        * Kegiatan hero dikecualikan agar konten tidak berulang.
        */
        $latestBuilder = (new ActivityModel())
            ->applyPublicVisibility()
            ->select(
                'activities.*, ' .
                'programs.name AS program_name, ' .
                'programs.slug AS program_slug, ' .
                'programs.label AS program_label'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            );

        if (!empty($featuredActivity['id'])) {
            $latestBuilder->where(
                'activities.id !=',
                $featuredActivity['id']
            );
        }

        $latestActivities = $latestBuilder
            ->orderBy('activities.activity_date', 'DESC')
            ->orderBy('activities.id', 'DESC')
            ->limit(6)
            ->findAll();

        $cmsState = $this->publicCmsPage('home');
        $cmsPage = $cmsState['page'];

        return view('public/home', [
            'title' =>
                $cmsPage['title']
                ?? 'GARDA 01 | Generasi Aktif Randugarut',

            'metaDescription' =>
                $cmsPage['meta_description']
                ?? 'Website resmi GARDA 01, Generasi Aktif Randugarut, Karang Taruna RW 01 Kelurahan Randugarut.',

            'activePage'          => 'home',
            'cmsPage'             => $cmsPage,
            'cmsPreview'          => $cmsState['preview'],
            'activeMembers'       => $activeMembers,
            'connectedRt'         => $connectedRt,
            'activeOfficials'     => $activeOfficials,
            'completedActivities' => $completedActivities,
            'programCount'        => count($programs),
            'programs'            => $programs,
            'featuredActivity'    => $featuredActivity,
            'impactActivity'      => $impactActivity,
            'latestActivities'    => $latestActivities,
        ]);
    }

    public function activities()
    {
        $activityModel = new ActivityModel();
        $programModel  = new ProgramModel();

        $selectedProgram = trim(
            (string) $this->request->getGet('program')
        );

        $activityModel
            ->applyPublicVisibility()
            ->select(
                'activities.*, ' .
                'programs.name AS program_name, ' .
                'programs.slug AS program_slug, ' .
                'programs.label AS program_label'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            )
            ->orderBy('activities.activity_date', 'DESC')
            ->orderBy('activities.id', 'DESC');

        if ($selectedProgram !== '') {
            $activityModel->where(
                'programs.slug',
                $selectedProgram
            );
        }

        return view('public/activities', [
            'title' => 'Kegiatan GARDA 01 | Randugarut',

            'metaDescription' =>
                'Dokumentasi kegiatan GARDA 01, Karang Taruna RW 01 Kelurahan Randugarut.',

            'activePage' => 'activities',

            'activities' => $activityModel->paginate(
                9,
                'public_activities'
            ),

            'pager' => $activityModel->pager,

            'programs' => $programModel
                ->where('status', 'published')
                ->orderBy('display_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll(),

            'selectedProgram' => $selectedProgram,
        ]);
    }

    public function activityDetail($id)
    {
        $activityModel = new ActivityModel();

        $activity = $activityModel
            ->applyPublicVisibility()
            ->select(
                'activities.*, ' .
                'programs.name AS program_name, ' .
                'programs.slug AS program_slug, ' .
                'programs.label AS program_label, ' .
                'programs.tagline AS program_tagline'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            )
            ->where('activities.id', $id)
            ->first();

        if (!$activity) {
            return redirect()->to('/kegiatan')
                ->with('error', 'Kegiatan tidak ditemukan.');
        }

        $imageModel = new ActivityImageModel();

        $galleryImages = $imageModel
            ->where('activity_id', $id)
            ->orderBy('is_cover', 'DESC')
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $relatedModel = new ActivityModel();

        $relatedModel
            ->applyPublicVisibility()
            ->select(
                'activities.*, ' .
                'programs.name AS program_name, ' .
                'programs.slug AS program_slug'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            )
            ->where('activities.id !=', $id);

        if (!empty($activity['program_id'])) {
            $relatedModel->where(
                'activities.program_id',
                $activity['program_id']
            );
        }

        $relatedActivities = $relatedModel
            ->orderBy('activities.activity_date', 'DESC')
            ->orderBy('activities.id', 'DESC')
            ->limit(3)
            ->findAll();

        return view('public/activity_detail', [
            'title' => $activity['title'] . ' | GARDA 01',

            'metaDescription' => !empty($activity['description'])
                ? mb_substr(strip_tags($activity['description']), 0, 155)
                : 'Dokumentasi kegiatan GARDA 01 Randugarut.',

            'activePage' => 'activity_detail',
            'activity' => $activity,
            'relatedActivities' => $relatedActivities,
            'galleryImages' => $galleryImages,
        ]);
    }

    public function officials()
    {
        $structureModel = new OrganizationalStructureModel();

        $officials = $structureModel->publicOfficials();

        return view('public/officials', [
            'title' => 'Pengurus GARDA 01 | Karang Taruna RW 01',
            'metaDescription' =>
                'Struktur dan profil pengurus GARDA 01, Karang Taruna RW 01 Kelurahan Randugarut.',
            'activePage' => 'officials',
            'officials' => $officials,
        ]);
    }

    public function profile()
    {
        $cmsState = $this->publicCmsPage('profile');
        $cmsPage = $cmsState['page'];

        return view('public/profile', [
            'title' => $cmsPage['title']
                ?? 'Profil GARDA 01 | Generasi Aktif Randugarut',
            'metaDescription' =>
                $cmsPage['meta_description']
                ?? 'Mengenal GARDA 01 — Generasi Aktif Randugarut, identitas Karang Taruna RW 01 Kelurahan Randugarut.',
            'activePage' => 'profile',
            'cmsPage' => $cmsPage,
            'cmsPreview' => $cmsState['preview'],
        ]);
    }

    public function programs()
    {
        $programModel = new ProgramModel();

        return view('public/programs', [
            'title' => 'Program GARDA 01 | Karang Taruna RW 01',
            'metaDescription' => 'Pilar program GARDA 01 dalam bidang sosial, lingkungan, olahraga, kreativitas, usaha, pendidikan, dan keagamaan.',
            'activePage' => 'programs',
            'programs' => $programModel->getPublishedPrograms(),
        ]);
    }

    public function programDetail(string $slug)
    {
        $programModel = new ProgramModel();

        $program = $programModel->findPublishedBySlug($slug);

        if (!$program) {
            throw PageNotFoundException::forPageNotFound(
                'Program GARDA 01 tidak ditemukan.'
            );
        }

        return view('public/program_detail', [
            'title' => $program['name'] . ' | GARDA 01',
            'metaDescription' => $program['short_description']
                ?? 'Program GARDA 01 Randugarut.',
            'activePage' => 'program_detail',
            'program' => $program,
        ]);
    }
}