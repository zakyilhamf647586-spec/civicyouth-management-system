<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemHealthIncidentModel extends Model
{
    protected $table = 'system_health_incidents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps = false;

    protected $allowedFields = [
        'component',
        'status',
        'severity',
        'title',
        'message',
        'occurrence_count',
        'first_seen_at',
        'last_seen_at',
        'resolved_at',
        'acknowledged_at',
        'acknowledged_by',
        'last_snapshot_id',
        'metadata_json',
        'created_at',
        'updated_at',
    ];
}
