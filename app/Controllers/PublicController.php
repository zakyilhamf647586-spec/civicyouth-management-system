<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\ActivityModel;
use App\Models\MeetingModel;
use App\Models\CashTransactionModel;

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
}