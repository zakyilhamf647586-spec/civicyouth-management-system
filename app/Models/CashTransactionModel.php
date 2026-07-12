<?php

namespace App\Models;

use CodeIgniter\Model;

class CashTransactionModel extends Model
{
    protected $table         = 'cash_transactions';
    protected $primaryKey    = 'id';

    protected $allowedFields = [
        'transaction_date',
        'transaction_type',
        'category',
        'amount',
        'description',
        'created_by'
    ];

    protected $useTimestamps = true;
}