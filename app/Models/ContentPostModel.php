<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentPostModel extends Model
{
    protected $table = 'content_posts';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'content_code',
        'program_id',
        'activity_id',
        'channel',
        'publication_type',
        'canva_template_code',
        'category',
        'template_type',
        'event_title',
        'event_date',
        'event_time',
        'event_location',
        'activity_description',
        'cover_hook',
        'content_goal',
        'target_audience',
        'call_to_action',
        'canva_url',
        'instagram_url',
        'owner',
        'reviewer',
        'priority',
        'workflow_status',
        'scheduled_at',
        'published_at',
        'approval_notes',
        'approved_by',
        'approved_at',
        'archived_at',
        'title',
        'caption',
        'hashtags',
        'mentions',
        'alt_text',
        'ai_summary',
        'generated_image',
        'notes',
        'status',
        'created_by',
    ];

    protected $useTimestamps = true;
}
