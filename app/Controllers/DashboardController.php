<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\MeetingModel;
use App\Models\ActivityModel;
use App\Models\CashTransactionModel;
use App\Models\AttendanceModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $memberModel     = new MemberModel();
        $meetingModel    = new MeetingModel();
        $activityModel   = new ActivityModel();
        $cashModel       = new CashTransactionModel();
        $attendanceModel = new AttendanceModel();

        $totalMembers = $memberModel->countAllResults(false);

        $activeMembers = $memberModel
            ->where('membership_status', 'active')
            ->countAllResults();

        $inactiveMembers = (new MemberModel())
            ->where('membership_status', 'inactive')
            ->countAllResults();

        $totalMeetings = $meetingModel->countAllResults(false);
        $totalActivities = $activityModel->countAllResults(false);

        $incomeRow = (new CashTransactionModel())
            ->selectSum('amount')
            ->where('transaction_type', 'income')
            ->first();

        $expenseRow = (new CashTransactionModel())
            ->selectSum('amount')
            ->where('transaction_type', 'expense')
            ->first();

        $totalIncome  = $incomeRow['amount'] ?? 0;
        $totalExpense = $expenseRow['amount'] ?? 0;
        $cashBalance  = $totalIncome - $totalExpense;

        $membersByRt = [
            'RT 01' => (new MemberModel())->where('rt', 'RT 01')->countAllResults(),
            'RT 02' => (new MemberModel())->where('rt', 'RT 02')->countAllResults(),
            'RT 03' => (new MemberModel())->where('rt', 'RT 03')->countAllResults(),
            'RT 04' => (new MemberModel())->where('rt', 'RT 04')->countAllResults(),
        ];

        $activitiesByStatus = [
            'planned'   => (new ActivityModel())->where('status', 'planned')->countAllResults(),
            'completed' => (new ActivityModel())->where('status', 'completed')->countAllResults(),
            'cancelled' => (new ActivityModel())->where('status', 'cancelled')->countAllResults(),
        ];

        $meetingsByStatus = [
            'scheduled' => (new MeetingModel())->where('status', 'scheduled')->countAllResults(),
            'completed' => (new MeetingModel())->where('status', 'completed')->countAllResults(),
            'cancelled' => (new MeetingModel())->where('status', 'cancelled')->countAllResults(),
        ];

        $attendanceSummary = [
            'present'    => (new AttendanceModel())->where('attendance_status', 'present')->countAllResults(),
            'permission' => (new AttendanceModel())->where('attendance_status', 'permission')->countAllResults(),
            'absent'     => (new AttendanceModel())->where('attendance_status', 'absent')->countAllResults(),
        ];

        $latestMeetings = (new MeetingModel())
            ->orderBy('meeting_date', 'DESC')
            ->limit(5)
            ->findAll();

        $latestActivities = (new ActivityModel())
            ->orderBy('activity_date', 'DESC')
            ->limit(5)
            ->findAll();

        $data = [
            'title'              => 'Dashboard',
            'total_members'      => $totalMembers,
            'active_members'     => $activeMembers,
            'inactive_members'   => $inactiveMembers,
            'total_meetings'     => $totalMeetings,
            'total_activities'   => $totalActivities,
            'total_income'       => $totalIncome,
            'total_expense'      => $totalExpense,
            'cash_balance'       => $cashBalance,
            'members_by_rt'      => $membersByRt,
            'activities_status'  => $activitiesByStatus,
            'meetings_status'    => $meetingsByStatus,
            'attendance_summary' => $attendanceSummary,
            'latest_meetings'    => $latestMeetings,
            'latest_activities'  => $latestActivities,
        ];

        return view('dashboard/index', $data);
    }
}