<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\ActivityModel;
use App\Models\MeetingModel;
use App\Models\CashTransactionModel;
use App\Models\OrganizationalStructureModel;

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
            ->orderBy('id', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Struktur Pengurus Karang Taruna RW 01',
            'officials' => $officials,
        ];

        return view('public/officials', $data);
    }
}