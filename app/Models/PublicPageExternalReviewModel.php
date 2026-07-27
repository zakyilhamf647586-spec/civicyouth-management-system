<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicPageExternalReviewModel extends Model
{
    protected $table =
        'public_page_external_reviews';

    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'preview_token_id',
        'public_page_id',
        'public_page_revision_id',
        'decision',
        'reviewer_name',
        'reviewer_email',
        'comment',
        'source_ip_hash',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * @return list<array<string, mixed>>
     */
    public function forToken(
        int $tokenId
    ): array {
        return $this
            ->where('preview_token_id', $tokenId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function latestForToken(
        int $tokenId
    ): ?array {
        return $this
            ->where('preview_token_id', $tokenId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }
}
