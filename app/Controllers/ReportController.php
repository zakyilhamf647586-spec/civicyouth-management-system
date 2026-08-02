<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\MeetingModel;
use App\Models\CashTransactionModel;
use App\Models\ActivityModel;

class ReportController extends BaseController
{
    protected $memberModel;
    protected $meetingModel;
    protected $cashModel;
    protected $activityModel;

    public function __construct()
    {
        $this->memberModel   = new MemberModel();
        $this->meetingModel  = new MeetingModel();
        $this->cashModel     = new CashTransactionModel();
        $this->activityModel = new ActivityModel();
    }

    public function index()
    {
        $canViewMemberReports = auth_can('reports.members');
        $canViewCashReports = auth_can('reports.cash');
        $canViewMeetingReports = auth_can('reports.meetings');
        $canViewActivitySummary = auth_can('activities.view');

        $income = null;
        $expense = null;
        $balance = null;

        if ($canViewCashReports) {
            $totalIncome = $this->cashModel
                ->selectSum('amount')
                ->where('transaction_type', 'income')
                ->first();

            $totalExpense = $this->cashModel
                ->selectSum('amount')
                ->where('transaction_type', 'expense')
                ->first();

            $income = $totalIncome['amount'] ?? 0;
            $expense = $totalExpense['amount'] ?? 0;
            $balance = $income - $expense;
        }

        $data = [
            'title' => 'Laporan',
            'can_view_member_reports' => $canViewMemberReports,
            'can_view_cash_reports' => $canViewCashReports,
            'can_view_meeting_reports' => $canViewMeetingReports,
            'can_view_activity_summary' => $canViewActivitySummary,
            'total_members' => $canViewMemberReports
                ? $this->memberModel->countAllResults()
                : null,
            'active_members' => $canViewMemberReports
                ? $this->memberModel
                    ->where('membership_status', 'active')
                    ->countAllResults()
                : null,
            'total_meetings' => $canViewMeetingReports
                ? $this->meetingModel->countAllResults()
                : null,
            'total_activities' => $canViewActivitySummary
                ? $this->activityModel->countAllResults()
                : null,
            'total_income' => $income,
            'total_expense' => $expense,
            'balance' => $balance,
        ];

        return view('reports/index', $data);
    }

    public function members()
    {
        $data = [
            'title'   => 'Laporan Data Anggota',
            'members' => $this->memberModel
                ->orderBy('rt', 'ASC')
                ->orderBy('full_name', 'ASC')
                ->findAll()
        ];

        return view('reports/members', $data);
    }

    public function cash()
    {
        $transactions = $this->cashModel
            ->orderBy('transaction_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $totalIncome = $this->cashModel
            ->selectSum('amount')
            ->where('transaction_type', 'income')
            ->first();

        $totalExpense = $this->cashModel
            ->selectSum('amount')
            ->where('transaction_type', 'expense')
            ->first();

        $income  = $totalIncome['amount'] ?? 0;
        $expense = $totalExpense['amount'] ?? 0;
        $balance = $income - $expense;

        $data = [
            'title'        => 'Laporan Kas Organisasi',
            'transactions' => $transactions,
            'total_income' => $income,
            'total_expense'=> $expense,
            'balance'      => $balance,
        ];

        return view('reports/cash', $data);
    }

    public function meetings()
    {
        $data = [
            'title'    => 'Laporan Agenda Rapat',
            'meetings' => $this->meetingModel
                ->orderBy('meeting_date', 'DESC')
                ->findAll()
        ];

        return view('reports/meetings', $data);
    }
}