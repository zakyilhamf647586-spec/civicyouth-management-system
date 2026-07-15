<?php

namespace App\Models;

use CodeIgniter\Model;

class OrganizationalStructureModel extends Model
{
    protected $table         = 'organizational_structures';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'member_id',
        'position_name',
        'division',
        'rt_scope',
        'period',
        'description',
        'sort_order',
        'status',
        'photo',
        'short_bio',
    ];

    protected $useTimestamps = true;
}