<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingModel extends Model
{
    protected $table         = 'meetings';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'title',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'agenda',
        'decisions',
        'notes',
        'status'
    ];

    protected $useTimestamps = true;
}