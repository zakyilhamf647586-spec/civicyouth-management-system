<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\ActivityModel;
use App\Models\MeetingModel;
use App\Models\CashTransactionModel;
use App\Models\OrganizationalStructureModel;
use App\Models\ProgramModel;
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