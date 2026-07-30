<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemHealthSnapshotModel extends Model
{
    protected $table = 'system_health_snapshots';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps = false;

    protected $allowedFields = [
        'overall_status',
        'score',
        'source',
        'checks_total',
        'checks_pass',
        'checks_warning',
        'checks_critical',
        'database_latency_ms',
        'disk_free_bytes',
        'memory_usage_bytes',
        'backup_age_hours',
        'payload_json',
        'created_at',
    ];
}
