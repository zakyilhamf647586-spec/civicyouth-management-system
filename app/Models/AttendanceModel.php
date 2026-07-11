<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table         = 'attendances';
    protected $primaryKey    = 'id';

    protected $allowedFields = [
        'meeting_id',
        'member_id',
        'attendance_status',
        'note'
    ];

    protected $useTimestamps = true;
}