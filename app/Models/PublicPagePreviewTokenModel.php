<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicPagePreviewTokenModel extends Model
{
    protected $table =
        'public_page_preview_tokens';

    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'public_page_id',
        'public_page_revision_id',
        'token_hash',
        'label',
        'reviewer_name',
        'reviewer_email',
        'status',
        'expires_at',
        'max_views',
        'view_count',
        'allow_decision',
        'last_accessed_at',
        'completed_at',
        'revoked_by',
        'revoked_at',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByHash(
        string $tokenHash
    ): ?array {
        return $this
            ->where('token_hash', $tokenHash)
            ->first();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forPage(
        int $pageId
    ): array {
        return $this
            ->where('public_page_id', $pageId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }
}
