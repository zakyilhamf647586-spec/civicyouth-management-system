<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentPostModel extends Model
{
    protected $table = 'content_posts';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'category',
        'template_type',
        'event_title',
        'event_date',
        'event_time',
        'event_location',
        'activity_description',
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