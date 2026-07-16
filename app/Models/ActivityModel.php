<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityModel extends Model
{
    protected $table         = 'activities';
    protected $primaryKey    = 'id';

    protected $allowedFields = [
        'program_id',
        'title',
        'activity_date',
        'location',
        'description',
        'result',
        'documentation_link',
        'documentation_file',
        'status'
    ];

    protected $useTimestamps = true;
}