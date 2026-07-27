<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicPagePreviewAccessLogModel extends Model
{
    protected $table =
        'public_page_preview_access_logs';

    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'preview_token_id',
        'public_page_id',
        'event_type',
        'source_ip_hash',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $useTimestamps = false;
}
