<?php

namespace App\Libraries;

use App\Models\PublicPageExternalReviewModel;
use App\Models\PublicPageModel;
use App\Models\PublicPagePreviewAccessLogModel;
use App\Models\PublicPagePreviewTokenModel;
use App\Models\PublicPageRevisionModel;
use RuntimeException;

class PublicPageExternalReviewService
{
    protected PublicPageModel $pageModel;
    protected PublicPageRevisionModel $revisionModel;
    protected PublicPageRevisionService $revisionService;
    protected PublicPagePreviewTokenModel $tokenModel;
    protected PublicPageExternalReviewModel $reviewModel;
    protected PublicPagePreviewAccessLogModel $accessLogModel;

    public function __construct()
    {
        $this->pageModel = new PublicPageModel();
        $this->revisionModel =
            new PublicPageRevisionModel();
        $this->revisionService =
            new PublicPageRevisionService();
        $this->tokenModel =
            new PublicPagePreviewTokenModel();
        $this->reviewModel =
            new PublicPageExternalReviewModel();
        $this->accessLogModel =
            new PublicPagePreviewAccessLogModel();
    }

    public function ready(): bool
    {
        try {
            $db = db_connect();

            return $db->tableExists(
                'public_page_preview_tokens'
            )
                && $db->tableExists(
                    'public_page_external_reviews'
                )
                && $db->tableExists(
                    'public_page_preview_access_logs'
                )
                && $this->revisionService->ready();
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array{
     *     raw_token: string,
     *     token: array<string, mixed>,
     *     revision_id: int
     * }
     */
    public function createLink(
        array $page,
        array $options,
        ?int $actorId
    ): array {
        $this->assertReady();

        if (empty($page['has_unpublished_changes'])) {
            throw new RuntimeException(
                'Buat dan simpan perubahan draft terlebih dahulu sebelum membuat tautan review eksternal.'
            );
        }

        $label = trim(strip_tags(
            (string) ($options['label'] ?? '')
        ));

        if ($label === '' || mb_strlen($label) > 120) {
            throw new RuntimeException(
                'Label tautan wajib diisi maksimal 120 karakter.'
            );
        }

        $reviewerName = $this->cleanNullable(
            $options['reviewer_name'] ?? null,
            120
        );

        $reviewerEmail = $this->cleanNullable(
            $options['reviewer_email'] ?? null,
            150
        );

        if (
            $reviewerEmail !== null
            && filter_var(
                $reviewerEmail,
                FILTER_VALIDATE_EMAIL
            ) === false
        ) {
            throw new RuntimeException(
                'Email reviewer tidak valid.'
            );
        }

        $days = max(
            1,
            min(30, (int) (
                $options['expires_in_days'] ?? 7
            ))
        );

        $maxViews = max(
            5,
            min(500, (int) (
                $options['max_views'] ?? 100
            ))
        );

        $allowDecision = !empty(
            $options['allow_decision']
        );

        $db = db_connect();
        $db->transBegin();

        try {
            $revisionId = $this->revisionService
                ->capture(
                    (int) $page['id'],
                    'draft',
                    'external_preview',
                    $actorId,
                    'Snapshot untuk tautan eksternal: '
                        . $label
                );

            $rawToken = $this->generateRawToken();
            $tokenHash = hash('sha256', $rawToken);

            $tokenId = $this->tokenModel->insert([
                'public_page_id' =>
                    (int) $page['id'],
                'public_page_revision_id' =>
                    $revisionId,
                'token_hash' => $tokenHash,
                'label' => $label,
                'reviewer_name' => $reviewerName,
                'reviewer_email' => $reviewerEmail,
                'status' => 'active',
                'expires_at' => date(
                    'Y-m-d H:i:s',
                    strtotime('+' . $days . ' days')
                ),
                'max_views' => $maxViews,
                'view_count' => 0,
                'allow_decision' =>
                    $allowDecision ? 1 : 0,
                'created_by' => $actorId,
            ], true);

            if (!$tokenId) {
                throw new RuntimeException(
                    'Tautan review belum dapat dibuat.'
                );
            }

            $this->writeLog(
                (int) $tokenId,
                (int) $page['id'],
                'token_created',
                [
                    'revision_id' => $revisionId,
                    'expires_in_days' => $days,
                    'max_views' => $maxViews,
                    'allow_decision' =>
                        $allowDecision,
                ]
            );

            if ($db->transCommit() === false) {
                throw new RuntimeException(
                    'Tautan review belum dapat disimpan.'
                );
            }

            $token = $this->tokenModel->find(
                (int) $tokenId
            );

            return [
                'raw_token' => $rawToken,
                'token' => $token ?: [],
                'revision_id' => $revisionId,
            ];
        } catch (\Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function linksForPage(
        int $pageId
    ): array {
        $this->assertReady();

        $tokens = $this->tokenModel
            ->forPage($pageId);

        foreach ($tokens as &$token) {
            $token['derived_status'] =
                $this->derivedStatus($token);

            $revision = $this->revisionModel->find(
                (int) $token[
                    'public_page_revision_id'
                ]
            );

            $token['revision_version_number'] =
                (int) (
                    $revision['version_number'] ?? 0
                );

            $token['latest_review'] =
                $this->reviewModel
                    ->latestForToken(
                        (int) $token['id']
                    );

            $token['review_count'] =
                $this->reviewModel
                    ->where(
                        'preview_token_id',
                        (int) $token['id']
                    )
                    ->countAllResults();
        }

        unset($token);

        return $tokens;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resolve(
        string $rawToken,
        bool $countView = false,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ?array {
        if (!$this->ready()) {
            return null;
        }

        if (!$this->validRawToken($rawToken)) {
            return null;
        }

        $token = $this->tokenModel
            ->findByHash(hash('sha256', $rawToken));

        if (!$token) {
            return null;
        }

        $status = $this->derivedStatus($token);

        if (!in_array(
            $status,
            ['active', 'completed'],
            true
        )) {
            $this->writeLog(
                (int) $token['id'],
                (int) $token['public_page_id'],
                'access_denied_' . $status,
                [],
                $ipAddress,
                $userAgent
            );

            return null;
        }

        $page = $this->pageModel->find(
            (int) $token['public_page_id']
        );

        $revision = $this->revisionModel
            ->findForPage(
                (int) $token['public_page_id'],
                (int) $token[
                    'public_page_revision_id'
                ]
            );

        if (!$page || !$revision) {
            return null;
        }

        if ($countView) {
            $this->incrementView(
                $token,
                $ipAddress,
                $userAgent
            );

            $token['view_count'] =
                ((int) $token['view_count']) + 1;

            $token['last_accessed_at'] =
                date('Y-m-d H:i:s');
        }

        $latestReview = $this->reviewModel
            ->latestForToken((int) $token['id']);

        return [
            'token' => $token,
            'page' => $page,
            'revision' => $revision,
            'bundle' => $this->bundleFromRevision(
                $page,
                $revision
            ),
            'derived_status' => $status,
            'latest_review' => $latestReview,
            'can_submit' =>
                $status === 'active'
                && empty($latestReview),
        ];
    }

    /**
     * @param array<string, mixed> $resolved
     * @param array<string, mixed> $payload
     * @return int
     */
    public function submitFeedback(
        array $resolved,
        array $payload,
        ?string $ipAddress,
        ?string $userAgent
    ): int {
        $this->assertReady();

        $token = $resolved['token'] ?? null;
        $page = $resolved['page'] ?? null;

        if (!is_array($token) || !is_array($page)) {
            throw new RuntimeException(
                'Tautan review tidak valid.'
            );
        }

        if (empty($resolved['can_submit'])) {
            throw new RuntimeException(
                'Tanggapan untuk tautan ini sudah diselesaikan.'
            );
        }

        $decision = (string) (
            $payload['decision'] ?? 'comment'
        );

        $allowedDecisions = ['comment'];

        if (!empty($token['allow_decision'])) {
            $allowedDecisions[] = 'approved';
            $allowedDecisions[] =
                'changes_requested';
        }

        if (!in_array(
            $decision,
            $allowedDecisions,
            true
        )) {
            throw new RuntimeException(
                'Keputusan reviewer tidak valid.'
            );
        }

        $reviewerName = trim(strip_tags(
            (string) (
                $payload['reviewer_name'] ?? ''
            )
        ));

        $reviewerEmail = $this->cleanNullable(
            $payload['reviewer_email'] ?? null,
            150
        );

        $comment = trim(strip_tags(
            (string) (
                $payload['comment'] ?? ''
            )
        ));

        if (
            $reviewerName === ''
            || mb_strlen($reviewerName) > 120
        ) {
            throw new RuntimeException(
                'Nama reviewer wajib diisi maksimal 120 karakter.'
            );
        }

        if (
            $reviewerEmail !== null
            && filter_var(
                $reviewerEmail,
                FILTER_VALIDATE_EMAIL
            ) === false
        ) {
            throw new RuntimeException(
                'Email reviewer tidak valid.'
            );
        }

        if (
            mb_strlen($comment) < 10
            || mb_strlen($comment) > 3000
        ) {
            throw new RuntimeException(
                'Catatan reviewer wajib berisi 10–3.000 karakter.'
            );
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $reviewId = $this->reviewModel->insert([
                'preview_token_id' =>
                    (int) $token['id'],
                'public_page_id' =>
                    (int) $page['id'],
                'public_page_revision_id' =>
                    (int) $token[
                        'public_page_revision_id'
                    ],
                'decision' => $decision,
                'reviewer_name' => $reviewerName,
                'reviewer_email' => $reviewerEmail,
                'comment' => $comment,
                'source_ip_hash' =>
                    $this->hashIp($ipAddress),
                'user_agent' => $this->cleanUserAgent(
                    $userAgent
                ),
                'created_at' => date(
                    'Y-m-d H:i:s'
                ),
            ], true);

            if (!$reviewId) {
                throw new RuntimeException(
                    'Tanggapan reviewer belum dapat disimpan.'
                );
            }

            $this->tokenModel->update(
                (int) $token['id'],
                [
                    'status' => 'completed',
                    'completed_at' =>
                        date('Y-m-d H:i:s'),
                ]
            );

            $this->writeLog(
                (int) $token['id'],
                (int) $page['id'],
                'feedback_submitted',
                [
                    'review_id' => (int) $reviewId,
                    'decision' => $decision,
                ],
                $ipAddress,
                $userAgent
            );

            if ($db->transCommit() === false) {
                throw new RuntimeException(
                    'Tanggapan reviewer belum dapat disimpan.'
                );
            }

            return (int) $reviewId;
        } catch (\Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }

    public function revoke(
        array $token,
        ?int $actorId
    ): void {
        $this->assertReady();

        $status = $this->derivedStatus($token);

        if ($status === 'revoked') {
            return;
        }

        $this->tokenModel->update(
            (int) $token['id'],
            [
                'status' => 'revoked',
                'revoked_by' => $actorId,
                'revoked_at' =>
                    date('Y-m-d H:i:s'),
            ]
        );

        $this->writeLog(
            (int) $token['id'],
            (int) $token['public_page_id'],
            'token_revoked',
            []
        );
    }

    /**
     * @param list<int> $pageIds
     * @return array<int, array<string, mixed>>
     */
    public function latestReviewsForPages(
        array $pageIds
    ): array {
        if (!$this->ready()) {
            return [];
        }

        $pageIds = array_values(array_unique(
            array_filter(array_map(
                'intval',
                $pageIds
            ))
        ));

        if ($pageIds === []) {
            return [];
        }

        $rows = $this->reviewModel
            ->whereIn('public_page_id', $pageIds)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $latest = [];

        foreach ($rows as $row) {
            $pageId = (int) $row['public_page_id'];

            if (!isset($latest[$pageId])) {
                $latest[$pageId] = $row;
            }
        }

        return $latest;
    }

    /**
     * @param array<string, mixed> $token
     */
    public function derivedStatus(
        array $token
    ): string {
        if (
            ($token['status'] ?? '') === 'revoked'
            || !empty($token['revoked_at'])
        ) {
            return 'revoked';
        }

        $expiresAt = strtotime(
            (string) ($token['expires_at'] ?? '')
        );

        if (
            $expiresAt !== false
            && $expiresAt < time()
        ) {
            return 'expired';
        }

        $maxViews = (int) (
            $token['max_views'] ?? 0
        );

        if (
            $maxViews > 0
            && (int) ($token['view_count'] ?? 0)
                >= $maxViews
        ) {
            return 'exhausted';
        }

        if (
            ($token['status'] ?? '') === 'completed'
            || !empty($token['completed_at'])
        ) {
            return 'completed';
        }

        return 'active';
    }

    public function validRawToken(
        string $rawToken
    ): bool {
        return preg_match(
            '/^[A-Za-z0-9_-]{43}$/',
            $rawToken
        ) === 1;
    }

    /**
     * @param array<string, mixed> $page
     * @param array<string, mixed> $revision
     * @return array<string, mixed>
     */
    private function bundleFromRevision(
        array $page,
        array $revision
    ): array {
        $snapshot = $this->revisionService
            ->decode($revision);

        $pageSnapshot = is_array(
            $snapshot['page'] ?? null
        )
            ? $snapshot['page']
            : [];

        $sectionRows = is_array(
            $snapshot['sections'] ?? null
        )
            ? $snapshot['sections']
            : [];

        $sections = [];

        foreach ($sectionRows as $section) {
            if (
                !is_array($section)
                || empty($section['section_key'])
            ) {
                continue;
            }

            $sectionKey = (string) (
                $section['section_key']
            );

            $sections[$sectionKey] = [
                'key' => $sectionKey,
                'name' => (string) (
                    $section['section_name']
                    ?? $sectionKey
                ),
                'display_order' => (int) (
                    $section['display_order'] ?? 0
                ),
                'enabled' => !empty(
                    $section['enabled']
                ),
                'content' => is_array(
                    $section['content'] ?? null
                )
                    ? $section['content']
                    : [],
                'content_en' => is_array(
                    $section['content_en'] ?? null
                )
                    ? $section['content_en']
                    : [],
            ];
        }

        return [
            'id' => (int) $page['id'],
            'page_key' =>
                (string) $page['page_key'],
            'name' => (string) $page['name'],
            'route_path' =>
                (string) $page['route_path'],
            'title' =>
                $pageSnapshot['title'] ?? null,
            'title_en' =>
                $pageSnapshot['title_en'] ?? null,
            'meta_description' =>
                $pageSnapshot[
                    'meta_description'
                ] ?? null,
            'meta_description_en' =>
                $pageSnapshot[
                    'meta_description_en'
                ] ?? null,
            'has_unpublished_changes' => true,
            'published_at' =>
                $page['published_at'] ?? null,
            'updated_at' =>
                $revision['created_at'] ?? null,
            'revision_note' =>
                $revision['revision_note'] ?? null,
            'sections' => $sections,
            'mode' => 'external_revision',
            'revision_id' =>
                (int) $revision['id'],
            'version_number' =>
                (int) (
                    $revision['version_number'] ?? 0
                ),
        ];
    }

    /**
     * @param array<string, mixed> $token
     */
    private function incrementView(
        array $token,
        ?string $ipAddress,
        ?string $userAgent
    ): void {
        $now = date('Y-m-d H:i:s');

        $builder = db_connect()->table(
            'public_page_preview_tokens'
        );

        $builder
            ->set(
                'view_count',
                'view_count + 1',
                false
            )
            ->set('last_accessed_at', $now)
            ->set('updated_at', $now)
            ->where('id', (int) $token['id']);

        $maxViews = (int) (
            $token['max_views'] ?? 0
        );

        if ($maxViews > 0) {
            $builder->where(
                'view_count <',
                $maxViews
            );
        }

        $builder->update();

        $this->writeLog(
            (int) $token['id'],
            (int) $token['public_page_id'],
            'preview_viewed',
            [
                'revision_id' => (int) (
                    $token[
                        'public_page_revision_id'
                    ] ?? 0
                ),
            ],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function writeLog(
        ?int $tokenId,
        ?int $pageId,
        string $eventType,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        try {
            $encoded = $metadata === []
                ? null
                : json_encode(
                    $metadata,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );

            $this->accessLogModel->insert([
                'preview_token_id' => $tokenId,
                'public_page_id' => $pageId,
                'event_type' => mb_substr(
                    $eventType,
                    0,
                    40
                ),
                'source_ip_hash' =>
                    $this->hashIp($ipAddress),
                'user_agent' =>
                    $this->cleanUserAgent($userAgent),
                'metadata' =>
                    $encoded !== false
                        ? $encoded
                        : null,
                'created_at' =>
                    date('Y-m-d H:i:s'),
            ]);

            (new CmsAuditService())
                ->recordExternalReviewEvent(
                    $pageId,
                    $tokenId,
                    $eventType,
                    $metadata,
                    $ipAddress,
                    $userAgent
                );
        } catch (\Throwable $exception) {
            log_message(
                'warning',
                'External preview audit log fallback: '
                . $exception->getMessage()
            );
        }
    }

    private function generateRawToken(): string
    {
        return rtrim(strtr(
            base64_encode(random_bytes(32)),
            '+/',
            '-_'
        ), '=');
    }

    private function hashIp(
        ?string $ipAddress
    ): ?string {
        $ipAddress = trim((string) $ipAddress);

        if ($ipAddress === '') {
            return null;
        }

        return hash(
            'sha256',
            $ipAddress
            . '|'
            . (string) config('App')->baseURL
        );
    }

    private function cleanUserAgent(
        ?string $userAgent
    ): ?string {
        $userAgent = trim((string) $userAgent);

        if ($userAgent === '') {
            return null;
        }

        return mb_substr(
            strip_tags($userAgent),
            0,
            255
        );
    }

    private function cleanNullable(
        $value,
        int $maximum
    ): ?string {
        $value = trim(strip_tags(
            (string) $value
        ));

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maximum);
    }

    private function assertReady(): void
    {
        if (!$this->ready()) {
            throw new RuntimeException(
                'Fitur review eksternal belum tersedia. Jalankan php spark migrate.'
            );
        }
    }
}
