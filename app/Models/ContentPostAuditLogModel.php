<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentPostAuditLogModel extends Model
{
    protected $table = 'content_post_audit_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'content_post_id',
        'event_type',
        'summary',
        'from_status',
        'to_status',
        'changed_fields',
        'metadata',
        'user_id',
        'actor_name',
        'actor_role',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function historyForPost(
        int $postId,
        int $limit = 30
    ): array {
        return $this
            ->where('content_post_id', $postId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit);
    }
}
