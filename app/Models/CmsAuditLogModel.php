<?php

namespace App\Models;

use CodeIgniter\Model;

class CmsAuditLogModel extends Model
{
    protected $table = 'cms_audit_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'module',
        'event_type',
        'severity',
        'subject_type',
        'subject_id',
        'subject_key',
        'subject_label',
        'summary',
        'details',
        'metadata',
        'actor_type',
        'user_id',
        'actor_name',
        'actor_role',
        'source_ip_hash',
        'user_agent',
        'request_method',
        'request_path',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * @param array<string, string> $filters
     */
    public function applyFilters(array $filters): self
    {
        $keyword = trim((string) ($filters['q'] ?? ''));

        if ($keyword !== '') {
            $this->groupStart()
                ->like('summary', $keyword)
                ->orLike('event_type', $keyword)
                ->orLike('actor_name', $keyword)
                ->orLike('actor_role', $keyword)
                ->orLike('subject_label', $keyword)
                ->orLike('subject_key', $keyword)
                ->groupEnd();
        }

        foreach (['module', 'severity', 'actor_type'] as $field) {
            $value = trim((string) ($filters[$field] ?? ''));

            if ($value !== '') {
                $this->where($field, $value);
            }
        }

        $eventType = trim((string) ($filters['event_type'] ?? ''));

        if ($eventType !== '') {
            $this->where('event_type', $eventType);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));

        if ($dateFrom !== '') {
            $this->where('created_at >=', $dateFrom . ' 00:00:00');
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));

        if ($dateTo !== '') {
            $this->where('created_at <=', $dateTo . ' 23:59:59');
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function availableEventTypes(): array
    {
        $rows = $this
            ->select('event_type')
            ->distinct()
            ->orderBy('event_type', 'ASC')
            ->findAll();

        return array_values(array_filter(array_map(
            static fn (array $row): string => (string) ($row['event_type'] ?? ''),
            $rows
        )));
    }
}
