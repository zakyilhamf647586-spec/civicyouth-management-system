<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table            = 'members';
    protected $primaryKey       = 'id';

    protected $allowedFields    = [
        'full_name',
        'rt',
        'gender',
        'birth_date',
        'phone',
        'address',
        'position',
        'membership_status'
    ];

    protected $useTimestamps    = true;
}