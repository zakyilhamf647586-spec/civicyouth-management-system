<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicPageRevisionModel extends Model
{
    protected $table = 'public_page_revisions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'public_page_id',
        'version_number',
        'source_type',
        'snapshot_mode',
        'workflow_status',
        'revision_note',
        'source_revision_id',
        'snapshot_data',
        'created_by',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * @return list<array<string, mixed>>
     */
    public function historyForPage(
        int $pageId
    ): array {
        return $this
            ->where('public_page_id', $pageId)
            ->orderBy('version_number', 'DESC')
            ->findAll();
    }

    public function nextVersionNumber(
        int $pageId
    ): int {
        $result = $this
            ->selectMax(
                'version_number',
                'maximum_version'
            )
            ->where('public_page_id', $pageId)
            ->first();

        return ((int) (
            $result['maximum_version'] ?? 0
        )) + 1;
    }

    public function findForPage(
        int $pageId,
        int $revisionId
    ): ?array {
        return $this
            ->where('public_page_id', $pageId)
            ->where('id', $revisionId)
            ->first();
    }

    public function countForPage(
        int $pageId
    ): int {
        return $this
            ->where('public_page_id', $pageId)
            ->countAllResults();
    }
}
