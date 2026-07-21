<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentPostMetricModel extends Model
{
    protected $table = 'content_post_metrics';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'content_post_id',
        'recorded_at',
        'reach',
        'impressions',
        'likes',
        'comments',
        'shares',
        'saves',
        'profile_visits',
        'follows',
        'link_clicks',
        'video_views',
        'notes',
        'recorded_by',
    ];

    protected $useTimestamps = true;

    public function historyForPost(
        int $postId,
        int $limit = 24
    ): array {
        return $this
            ->where('content_post_id', $postId)
            ->orderBy('recorded_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit);
    }
}
