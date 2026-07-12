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
            'title'            => 'Laporan',
            'total_members'    => $this->memberModel->countAllResults(),
            'active_members'   => $this->memberModel->where('membership_status', 'active')->countAllResults(),
            'total_meetings'   => $this->meetingModel->countAllResults(),
            'total_activities' => $this->activityModel->countAllResults(),
            'total_income'     => $income,
            'total_expense'    => $expense,
            'balance'          => $balance,
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