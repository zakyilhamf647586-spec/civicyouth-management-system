<?php

namespace App\Libraries;

use App\Models\CmsAuditLogModel;
use App\Models\PublicPageModel;

class CmsAuditService
{
    protected CmsAuditLogModel $auditModel;

    /** @var array<string, string> */
    private array $moduleLabels = [
        'public_pages' => 'Halaman Publik',
        'navigation' => 'Navigasi Website',
        'settings' => 'Pengaturan Website',
        'programs' => 'Program GARDA 01',
        'activities' => 'Kegiatan',
        'social_publication' => 'Publikasi Sosial',
        'external_review' => 'Review Eksternal',
        'security' => 'Keamanan & Akses',
        'system' => 'Sistem',
    ];

    /** @var array<string, string> */
    private array $severityLabels = [
        'info' => 'Informasi',
        'notice' => 'Perubahan Penting',
        'warning' => 'Perlu Perhatian',
        'security' => 'Keamanan',
    ];

    public function __construct()
    {
        $this->auditModel = new CmsAuditLogModel();
    }

    public function ready(): bool
    {
        try {
            return db_connect()->tableExists('cms_audit_logs');
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * Fail-soft audit writer.
     *
     * @param array<string, mixed> $event
     */
    public function record(array $event): ?int
    {
        if (!$this->ready()) {
            return null;
        }

        try {
            $requestContext = $this->requestContext();
            $actor = $this->actorContext($event);
            $metadata = $this->sanitizeValue($event['metadata'] ?? []);

            $encodedMetadata = is_array($metadata) && $metadata !== []
                ? json_encode(
                    $metadata,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                )
                : null;

            $summary = $this->cleanString(
                $event['summary'] ?? 'Aktivitas CMS dicatat.',
                255
            ) ?? 'Aktivitas CMS dicatat.';

            $id = $this->auditModel->insert([
                'module' => $this->cleanKey($event['module'] ?? 'system', 50),
                'event_type' => $this->cleanKey(
                    $event['event_type'] ?? 'activity.recorded',
                    100,
                    true
                ),
                'severity' => $this->severity((string) ($event['severity'] ?? 'info')),
                'subject_type' => $this->cleanString($event['subject_type'] ?? null, 60),
                'subject_id' => !empty($event['subject_id'])
                    ? (int) $event['subject_id']
                    : null,
                'subject_key' => $this->cleanString($event['subject_key'] ?? null, 120),
                'subject_label' => $this->cleanString($event['subject_label'] ?? null, 180),
                'summary' => $summary,
                'details' => $this->cleanString($event['details'] ?? null, 5000),
                'metadata' => $encodedMetadata !== false ? $encodedMetadata : null,
                'actor_type' => $actor['actor_type'],
                'user_id' => $actor['user_id'],
                'actor_name' => $actor['actor_name'],
                'actor_role' => $actor['actor_role'],
                'source_ip_hash' => $this->hashIp(
                    $event['ip_address'] ?? $requestContext['ip_address']
                ),
                'user_agent' => $this->cleanString(
                    $event['user_agent'] ?? $requestContext['user_agent'],
                    255
                ),
                'request_method' => $this->cleanString(
                    $event['request_method'] ?? $requestContext['method'],
                    10
                ),
                'request_path' => $this->cleanString(
                    $event['request_path'] ?? $requestContext['path'],
                    255
                ),
                'created_at' => date('Y-m-d H:i:s'),
            ], true);

            return $id ? (int) $id : null;
        } catch (\Throwable $exception) {
            log_message(
                'warning',
                'Central CMS audit fallback: ' . $exception->getMessage()
            );

            return null;
        }
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function recordExternalReviewEvent(
        ?int $pageId,
        ?int $tokenId,
        string $eventType,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        if (in_array($eventType, ['token_created', 'token_revoked'], true)) {
            return;
        }

        $page = null;

        if ($pageId !== null && $pageId > 0) {
            try {
                $page = (new PublicPageModel())->find($pageId);
            } catch (\Throwable $exception) {
                $page = null;
            }
        }

        $isDenied = str_starts_with($eventType, 'access_denied_');

        $summary = match ($eventType) {
            'preview_viewed' => 'Preview eksternal dibuka.',
            'feedback_submitted' => 'Reviewer eksternal mengirim tanggapan.',
            default => $isDenied
                ? 'Akses ke preview eksternal ditolak.'
                : 'Aktivitas review eksternal dicatat.',
        };

        $this->record([
            'module' => $isDenied ? 'security' : 'external_review',
            'event_type' => 'external_review.' . $eventType,
            'severity' => $isDenied
                ? 'security'
                : ($eventType === 'feedback_submitted' ? 'notice' : 'info'),
            'subject_type' => 'public_page',
            'subject_id' => $pageId,
            'subject_key' => $page['page_key'] ?? null,
            'subject_label' => $page['name'] ?? (
                $pageId ? 'Halaman #' . $pageId : 'Preview Eksternal'
            ),
            'summary' => $summary,
            'metadata' => array_merge($metadata, [
                'preview_token_id' => $tokenId,
            ]),
            'actor_type' => 'external',
            'actor_name' => $eventType === 'feedback_submitted'
                ? 'Reviewer Eksternal'
                : 'Pengunjung Tautan Review',
            'actor_role' => 'External Reviewer',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /** @return array<string, mixed> */
    public function statistics(): array
    {
        if (!$this->ready()) {
            return [
                'total_30_days' => 0,
                'today' => 0,
                'security_30_days' => 0,
                'external_30_days' => 0,
                'unique_internal_actors' => 0,
                'module_counts' => [],
            ];
        }

        $db = db_connect();
        $start = date('Y-m-d H:i:s', strtotime('-30 days'));
        $today = date('Y-m-d 00:00:00');

        $total = $db->table('cms_audit_logs')
            ->where('created_at >=', $start)
            ->countAllResults();

        $todayCount = $db->table('cms_audit_logs')
            ->where('created_at >=', $today)
            ->countAllResults();

        $security = $db->table('cms_audit_logs')
            ->where('created_at >=', $start)
            ->where('severity', 'security')
            ->countAllResults();

        $external = $db->table('cms_audit_logs')
            ->where('created_at >=', $start)
            ->where('actor_type', 'external')
            ->countAllResults();

        $uniqueActorsRow = $db->table('cms_audit_logs')
            ->select('COUNT(DISTINCT user_id) AS total', false)
            ->where('created_at >=', $start)
            ->where('user_id IS NOT NULL', null, false)
            ->get()
            ->getRowArray();

        $moduleRows = $db->table('cms_audit_logs')
            ->select('module, COUNT(*) AS total', false)
            ->where('created_at >=', $start)
            ->groupBy('module')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        $moduleCounts = [];

        foreach ($moduleRows as $row) {
            $moduleCounts[(string) $row['module']] = (int) $row['total'];
        }

        return [
            'total_30_days' => $total,
            'today' => $todayCount,
            'security_30_days' => $security,
            'external_30_days' => $external,
            'unique_internal_actors' => (int) ($uniqueActorsRow['total'] ?? 0),
            'module_counts' => $moduleCounts,
        ];
    }

    /** @return array<string, string> */
    public function moduleLabels(): array
    {
        return $this->moduleLabels;
    }

    /** @return array<string, string> */
    public function severityLabels(): array
    {
        return $this->severityLabels;
    }

    /** @param array<string, mixed> $event */
    private function actorContext(array $event): array
    {
        $explicitType = trim((string) ($event['actor_type'] ?? ''));

        if ($explicitType !== '') {
            return [
                'actor_type' => in_array(
                    $explicitType,
                    ['internal', 'external', 'system'],
                    true
                ) ? $explicitType : 'system',
                'user_id' => !empty($event['user_id'])
                    ? (int) $event['user_id']
                    : null,
                'actor_name' => $this->cleanString($event['actor_name'] ?? null, 150),
                'actor_role' => $this->cleanString($event['actor_role'] ?? null, 100),
            ];
        }

        if ((bool) session()->get('isLoggedIn')) {
            return [
                'actor_type' => 'internal',
                'user_id' => session()->has('user_id')
                    ? (int) session()->get('user_id')
                    : null,
                'actor_name' => $this->cleanString(
                    session()->get('name')
                        ?? session()->get('email')
                        ?? 'Pengguna Portal',
                    150
                ),
                'actor_role' => $this->cleanString(
                    session()->get('role_name')
                        ?? session()->get('role')
                        ?? 'Tidak diketahui',
                    100
                ),
            ];
        }

        return [
            'actor_type' => 'system',
            'user_id' => null,
            'actor_name' => 'GARDA 01 System',
            'actor_role' => 'System',
        ];
    }

    /** @return array{ip_address:?string,user_agent:?string,method:?string,path:?string} */
    private function requestContext(): array
    {
        try {
            $request = service('request');

            return [
                'ip_address' => $request->getIPAddress(),
                'user_agent' => (string) $request->getUserAgent(),
                'method' => strtoupper((string) $request->getMethod()),
                'path' => '/' . ltrim((string) service('uri')->getPath(), '/'),
            ];
        } catch (\Throwable $exception) {
            return [
                'ip_address' => null,
                'user_agent' => null,
                'method' => null,
                'path' => null,
            ];
        }
    }

    private function severity(string $severity): string
    {
        return array_key_exists($severity, $this->severityLabels)
            ? $severity
            : 'info';
    }

    private function cleanKey($value, int $maximum, bool $allowDots = false): string
    {
        $pattern = $allowDots ? '/[^a-z0-9_.-]/' : '/[^a-z0-9_-]/';
        $clean = strtolower(trim((string) $value));
        $clean = preg_replace($pattern, '_', $clean) ?: 'unknown';

        return mb_substr($clean, 0, $maximum);
    }

    private function cleanString($value, int $maximum): ?string
    {
        $value = trim(strip_tags((string) $value));

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maximum);
    }

    private function hashIp($ipAddress): ?string
    {
        $ipAddress = trim((string) $ipAddress);

        if ($ipAddress === '') {
            return null;
        }

        return hash_hmac('sha256', $ipAddress, (string) config('App')->baseURL);
    }

    /** @return mixed */
    private function sanitizeValue($value, int $depth = 0, ?string $key = null)
    {
        if ($depth > 4) {
            return '[depth-limited]';
        }

        if ($key !== null && $this->sensitiveKey($key)) {
            return '[redacted]';
        }

        if (is_array($value)) {
            $result = [];
            $count = 0;

            foreach ($value as $itemKey => $item) {
                if ($count >= 100) {
                    $result['_truncated'] = true;
                    break;
                }

                $result[$itemKey] = $this->sanitizeValue(
                    $item,
                    $depth + 1,
                    (string) $itemKey
                );
                $count++;
            }

            return $result;
        }

        if (is_object($value)) {
            return '[object]';
        }

        if (is_bool($value) || is_numeric($value)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return mb_substr(strip_tags((string) $value), 0, 1000);
    }

    private function sensitiveKey(string $key): bool
    {
        return in_array(strtolower($key), [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'raw_token',
            'token_hash',
            'authorization',
            'cookie',
            'csrf',
            'csrf_token',
            'session_id',
            'api_key',
            'secret',
        ], true);
    }
}
