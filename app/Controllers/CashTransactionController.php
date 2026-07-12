<?php

namespace App\Controllers;

use App\Models\CashTransactionModel;

class CashTransactionController extends BaseController
{
    protected $cashModel;

    public function __construct()
    {
        $this->cashModel = new CashTransactionModel();
    }

    public function index()
    {
        $keyword         = $this->request->getGet('keyword');
        $transactionType = $this->request->getGet('transaction_type');
        $dateFrom        = $this->request->getGet('date_from');
        $dateTo          = $this->request->getGet('date_to');

        $listModel = new CashTransactionModel();

        if (!empty($keyword)) {
            $listModel = $listModel->groupStart()
                ->like('category', $keyword)
                ->orLike('description', $keyword)
                ->orLike('created_by', $keyword)
                ->groupEnd();
        }

        if (!empty($transactionType)) {
            $listModel = $listModel->where('transaction_type', $transactionType);
        }

        if (!empty($dateFrom)) {
            $listModel = $listModel->where('transaction_date >=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $listModel = $listModel->where('transaction_date <=', $dateTo);
        }

        $incomeModel = new CashTransactionModel();
        $expenseModel = new CashTransactionModel();

        $totalIncome = $incomeModel
            ->selectSum('amount')
            ->where('transaction_type', 'income')
            ->first();

        $totalExpense = $expenseModel
            ->selectSum('amount')
            ->where('transaction_type', 'expense')
            ->first();

        $income  = $totalIncome['amount'] ?? 0;
        $expense = $totalExpense['amount'] ?? 0;
        $balance = $income - $expense;

        $data = [
            'title'            => 'Kas Organisasi',
            'transactions'     => $listModel
                ->orderBy('transaction_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->paginate(10, 'cash'),
            'pager'            => $listModel->pager,
            'total_income'     => $income,
            'total_expense'    => $expense,
            'balance'          => $balance,
            'keyword'          => $keyword,
            'transaction_type' => $transactionType,
            'date_from'        => $dateFrom,
            'date_to'          => $dateTo,
        ];

        return view('cash/index', $data);
    }
    
    public function create()
    {
        $data = [
            'title' => 'Tambah Transaksi Kas'
        ];

        return view('cash/create', $data);
    }

    public function store()
    {
        $rules = [
            'transaction_date' => 'required|valid_date',
            'transaction_type' => 'required|in_list[income,expense]',
            'amount'           => 'required|decimal|greater_than[0]',
            'category'         => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->cashModel->save([
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_type' => $this->request->getPost('transaction_type'),
            'category'         => $this->request->getPost('category'),
            'amount'           => $this->request->getPost('amount'),
            'description'      => $this->request->getPost('description'),
            'created_by'       => session()->get('name'),
        ]);

        return redirect()->to('/cash')->with('success', 'Transaksi kas berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $transaction = $this->cashModel->find($id);

        if (!$transaction) {
            return redirect()->to('/cash')->with('error', 'Transaksi kas tidak ditemukan.');
        }

        $data = [
            'title'       => 'Edit Transaksi Kas',
            'transaction' => $transaction
        ];

        return view('cash/edit', $data);
    }

    public function update($id)
    {
        $transaction = $this->cashModel->find($id);

        if (!$transaction) {
            return redirect()->to('/cash')->with('error', 'Transaksi kas tidak ditemukan.');
        }

        $rules = [
            'transaction_date' => 'required|valid_date',
            'transaction_type' => 'required|in_list[income,expense]',
            'amount'           => 'required|decimal|greater_than[0]',
            'category'         => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->cashModel->update($id, [
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_type' => $this->request->getPost('transaction_type'),
            'category'         => $this->request->getPost('category'),
            'amount'           => $this->request->getPost('amount'),
            'description'      => $this->request->getPost('description'),
            'created_by'       => session()->get('name'),
        ]);

        return redirect()->to('/cash')->with('success', 'Transaksi kas berhasil diperbarui.');
    }

    public function delete($id)
    {
        $transaction = $this->cashModel->find($id);

        if (!$transaction) {
            return redirect()->to('/cash')->with('error', 'Transaksi kas tidak ditemukan.');
        }

        $this->cashModel->delete($id);

        return redirect()->to('/cash')->with('success', 'Transaksi kas berhasil dihapus.');
    }
}