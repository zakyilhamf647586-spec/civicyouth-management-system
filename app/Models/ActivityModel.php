<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityModel extends Model
{
    protected $table         = 'activities';
    protected $primaryKey    = 'id';

    protected $allowedFields = [
        'title',
        'activity_date',
        'location',
        'description',
        'result',
        'documentation_link',
        'status'
    ];

    protected $useTimestamps = true;
}