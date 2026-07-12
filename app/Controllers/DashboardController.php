<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\CashTransactionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $memberModel = new MemberModel();
        $cashModel   = new CashTransactionModel();

        $totalIncome = $cashModel
            ->selectSum('amount')
            ->where('transaction_type', 'income')
            ->first();

        $totalExpense = $cashModel
            ->selectSum('amount')
            ->where('transaction_type', 'expense')
            ->first();

        $income  = $totalIncome['amount'] ?? 0;
        $expense = $totalExpense['amount'] ?? 0;
        $balance = $income - $expense;

        $data = [
            'title'          => 'Dashboard',
            'total_members'  => $memberModel->countAllResults(),
            'active_members' => $memberModel->where('membership_status', 'active')->countAllResults(),
            'cash_balance'   => $balance,
        ];

        return view('dashboard/index', $data);
    }
}