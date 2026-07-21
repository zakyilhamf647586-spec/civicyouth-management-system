<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use App\Models\ContentAssetModel;
use App\Models\ContentPostModel;
use App\Models\ContentPostMetricModel;
use App\Models\ContentPostAuditLogModel;
use App\Models\ProgramModel;
use Config\SocialMedia;

class SocialPublicationController extends BaseController
{
    protected ContentPostModel $postModel;
    protected ContentPostMetricModel $metricModel;
    protected ContentPostAuditLogModel $auditModel;
    protected ContentAssetModel $assetModel;
    protected ProgramModel $programModel;
    protected ActivityModel $activityModel;
    protected SocialMedia $socialMedia;
    protected ?string $publicationValidationError = null;

    public function __construct()
    {
        $this->postModel = new ContentPostModel();
        $this->metricModel = new ContentPostMetricModel();
        $this->auditModel = new ContentPostAuditLogModel();
        $this->assetModel = new ContentAssetModel();
        $this->programModel = new ProgramModel();
        $this->activityModel = new ActivityModel();
        $this->socialMedia = new SocialMedia();
    }

    public function index()
    {
        $status = trim((string) $this->request->getGet('status'));
        $type = trim((string) $this->request->getGet('type'));
        $programId = (int) $this->request->getGet('program_id');
        $search = trim((string) $this->request->getGet('q'));

        $model = new ContentPostModel();

        $model
            ->select(
                'content_posts.*, programs.name AS program_name, '
                . 'activities.title AS activity_title'
            )
            ->join(
                'programs',
                'programs.id = content_posts.program_id',
                'left'
            )
            ->join(
                'activities',
                'activities.id = content_posts.activity_id',
                'left'
            );

        if (isset($this->socialMedia->workflowStatuses[$status])) {
            $model->where('content_posts.workflow_status', $status);
        }

        if (isset($this->socialMedia->publicationTypes[$type])) {
            $model->where('content_posts.publication_type', $type);
        }

        if ($programId > 0) {
            $model->where('content_posts.program_id', $programId);
        }

        if ($search !== '') {
            $model
                ->groupStart()
                ->like('content_posts.content_code', $search)
                ->orLike('content_posts.event_title', $search)
                ->orLike('content_posts.cover_hook', $search)
                ->groupEnd();
        }

        $posts = $model
            ->orderBy('content_posts.updated_at', 'DESC')
            ->orderBy('content_posts.id', 'DESC')
            ->paginate(15, 'social_publications');

        $deadlineItems = $this->productionAttentionDataset();

        return view('publications/index', [
            'title' => 'Publikasi Sosial',
            'posts' => $posts,
            'pager' => $model->pager,
            'summary' => $this->publicationSummary(),
            'activityCandidates' => $this->publicationCandidates(),
            'deadlineSummary' =>
                $this->productionDeadlineSummary(
                    $deadlineItems
                ),
            'deadlineItems' => array_slice(
                array_values(array_filter(
                    $deadlineItems,
                    static fn (array $item): bool =>
                        in_array(
                            $item['urgency'],
                            [
                                'overdue',
                                'due_soon',
                                'unscheduled',
                            ],
                            true
                        )
                )),
                0,
                5
            ),
            'programs' => $this->programModel
                ->orderBy('display_order', 'ASC')
                ->findAll(),
            'templates' => $this->socialMedia->templates,
            'workflowStatuses' => $this->socialMedia->workflowStatuses,
            'publicationTypes' => $this->socialMedia->publicationTypes,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'program_id' => $programId,
                'q' => $search,
            ],
        ]);
    }

    public function create()
    {
        $activityId = (int) $this->request->getGet(
            'activity_id'
        );

        if ($activityId > 0) {
            return $this->createFromActivity($activityId);
        }

        return view('publications/create', array_merge(
            $this->formReferenceData(),
            [
                'title' => 'Buat Rencana Publikasi',
                'post' => null,
                'autoBrief' => null,
                'formAction' => '/publications/store',
                'submitLabel' => 'Simpan Brief Publikasi',
            ]
        ));
    }

    public function createFromActivity($id)
    {
        $activity = db_connect()
            ->table('activities')
            ->select(
                'activities.*, programs.name AS program_name'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            )
            ->where('activities.id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$activity) {
            return redirect()->to('/publications')
                ->with(
                    'error',
                    'Kegiatan sumber tidak ditemukan.'
                );
        }

        if (($activity['status'] ?? '') === 'cancelled') {
            return redirect()->to('/publications')
                ->with(
                    'error',
                    'Kegiatan yang dibatalkan tidak dapat '
                    . 'dijadikan brief publikasi otomatis.'
                );
        }

        $existingCount = $this->postModel
            ->where('activity_id', (int) $id)
            ->where('template_type', 'social_publication')
            ->countAllResults();

        return view('publications/create', array_merge(
            $this->formReferenceData(),
            [
                'title' => 'Brief Otomatis dari Kegiatan',
                'post' => $this->activityBriefData($activity),
                'autoBrief' => [
                    'activity' => $activity,
                    'existing_count' => $existingCount,
                ],
                'formAction' => '/publications/store',
                'submitLabel' => 'Simpan Brief Publikasi',
            ]
        ));
    }

    public function calendar()
    {
        $monthInput = trim(
            (string) $this->request->getGet('month')
        );

        $month = \DateTimeImmutable::createFromFormat(
            '!Y-m',
            $monthInput
        );

        $dateErrors = \DateTimeImmutable::getLastErrors();
        $hasDateErrors = is_array($dateErrors)
            && (
                ($dateErrors['warning_count'] ?? 0) > 0
                || ($dateErrors['error_count'] ?? 0) > 0
            );

        if (!$month || $hasDateErrors) {
            $month = new \DateTimeImmutable(
                'first day of this month'
            );
        }

        $monthStart = $month->modify(
            'first day of this month'
        );

        $monthEnd = $month->modify(
            'last day of this month'
        );

        $db = db_connect();

        $calendarDateExpression = "
            CASE
                WHEN content_posts.workflow_status = 'published'
                    AND content_posts.published_at IS NOT NULL
                THEN DATE(content_posts.published_at)
                WHEN content_posts.scheduled_at IS NOT NULL
                THEN DATE(content_posts.scheduled_at)
                WHEN content_posts.event_date IS NOT NULL
                THEN DATE(content_posts.event_date)
                ELSE DATE(content_posts.created_at)
            END
        ";

        $calendarDateAlias = trim(
            preg_replace(
                '/\s+/',
                ' ',
                $calendarDateExpression
            )
        );

        $startDate = $monthStart->format('Y-m-d');
        $endDate = $monthEnd->format('Y-m-d');

        $posts = $db
            ->table('content_posts')
            ->select(
                'content_posts.*, '
                . 'programs.name AS program_name, '
                . $calendarDateAlias
                . ' AS calendar_date',
                false
            )
            ->join(
                'programs',
                'programs.id = content_posts.program_id',
                'left'
            )
            ->where(
                $calendarDateAlias
                . ' >= '
                . $db->escape($startDate),
                null,
                false
            )
            ->where(
                $calendarDateAlias
                . ' <= '
                . $db->escape($endDate),
                null,
                false
            )
            ->orderBy('calendar_date', 'ASC')
            ->orderBy('content_posts.scheduled_at', 'ASC')
            ->orderBy('content_posts.id', 'ASC')
            ->get()
            ->getResultArray();

        $postsByDate = [];

        foreach ($posts as &$post) {
            $dateKey = (string) (
                $post['calendar_date'] ?? ''
            );

            if ($dateKey === '') {
                continue;
            }

            $post['calendar_time'] =
                $this->publicationCalendarTime($post);

            $postsByDate[$dateKey][] = $post;
        }

        unset($post);

        $calendarDays = [];
        $cursor = $monthStart;
        $today = date('Y-m-d');

        while ($cursor <= $monthEnd) {
            $dateKey = $cursor->format('Y-m-d');

            $calendarDays[] = [
                'date' => $dateKey,
                'day' => (int) $cursor->format('j'),
                'is_today' => $dateKey === $today,
                'posts' => $postsByDate[$dateKey] ?? [],
            ];

            $cursor = $cursor->modify('+1 day');
        }

        $unscheduled = $this->postModel
            ->select(
                'content_posts.*, '
                . 'programs.name AS program_name'
            )
            ->join(
                'programs',
                'programs.id = content_posts.program_id',
                'left'
            )
            ->where('content_posts.scheduled_at', null)
            ->whereNotIn(
                'content_posts.workflow_status',
                ['published', 'archived']
            )
            ->orderBy('content_posts.updated_at', 'DESC')
            ->limit(8)
            ->findAll();

        $calendarSummary = [
            'total' => count($posts),
            'production' => count(array_filter(
                $posts,
                static fn (array $post): bool => in_array(
                    $post['workflow_status'] ?? 'brief',
                    [
                        'brief',
                        'draft',
                        'design',
                        'review',
                        'revision',
                        'approved',
                    ],
                    true
                )
            )),
            'scheduled' => count(array_filter(
                $posts,
                static fn (array $post): bool =>
                    ($post['workflow_status'] ?? '')
                    === 'scheduled'
            )),
            'published' => count(array_filter(
                $posts,
                static fn (array $post): bool =>
                    ($post['workflow_status'] ?? '')
                    === 'published'
            )),
        ];

        return view('publications/calendar', [
            'title' => 'Kalender Konten',
            'monthValue' => $monthStart->format('Y-m'),
            'monthLabel' => $this->indonesianMonthLabel(
                $monthStart
            ),
            'previousMonth' => $monthStart
                ->modify('-1 month')
                ->format('Y-m'),
            'nextMonth' => $monthStart
                ->modify('+1 month')
                ->format('Y-m'),
            'leadingBlankDays' =>
                (int) $monthStart->format('N') - 1,
            'calendarDays' => $calendarDays,
            'calendarPosts' => $posts,
            'calendarSummary' => $calendarSummary,
            'unscheduledPosts' => $unscheduled,
            'workflowStatuses' =>
                $this->socialMedia->workflowStatuses,
            'publicationTypes' =>
                $this->socialMedia->publicationTypes,
        ]);
    }

    public function analytics()
    {
        $period = $this->resolveAnalyticsMonth(
            (string) $this->request->getGet('month')
        );

        $programId = (int) $this->request->getGet(
            'program_id'
        );

        $type = trim(
            (string) $this->request->getGet('type')
        );

        if (!isset(
            $this->socialMedia->publicationTypes[$type]
        )) {
            $type = '';
        }

        $rows = $this->analyticsDataset(
            $period['start'],
            $period['end'],
            $programId,
            $type
        );

        $topPosts = $rows;

        usort(
            $topPosts,
            static function (
                array $left,
                array $right
            ): int {
                $reachComparison =
                    ($right['reach'] ?? 0)
                    <=> ($left['reach'] ?? 0);

                return $reachComparison !== 0
                    ? $reachComparison
                    : (
                        ($right['interactions'] ?? 0)
                        <=> ($left['interactions'] ?? 0)
                    );
            }
        );

        return view('publications/analytics', [
            'title' => 'Analitik Instagram',
            'monthValue' => $period['start']->format('Y-m'),
            'monthLabel' => $this->indonesianMonthLabel(
                $period['start']
            ),
            'previousMonth' => $period['start']
                ->modify('-1 month')
                ->format('Y-m'),
            'nextMonth' => $period['start']
                ->modify('+1 month')
                ->format('Y-m'),
            'rows' => $rows,
            'topPosts' => array_slice($topPosts, 0, 10),
            'summary' => $this->analyticsSummary($rows),
            'formatPerformance' =>
                $this->formatPerformance($rows),
            'programPerformance' =>
                $this->programPerformance($rows),
            'programs' => $this->programModel
                ->orderBy('display_order', 'ASC')
                ->findAll(),
            'publicationTypes' =>
                $this->socialMedia->publicationTypes,
            'filters' => [
                'program_id' => $programId,
                'type' => $type,
            ],
            'metricsReady' => $this->metricsTableReady(),
            'auditHistory' => $auditHistory,
            'auditReady' => $this->auditTableReady(),
            'auditEventLabels' =>
                $this->auditEventLabels(),
        ]);
    }

    public function exportAnalytics()
    {
        $period = $this->resolveAnalyticsMonth(
            (string) $this->request->getGet('month')
        );

        $programId = (int) $this->request->getGet(
            'program_id'
        );

        $type = trim(
            (string) $this->request->getGet('type')
        );

        if (!isset(
            $this->socialMedia->publicationTypes[$type]
        )) {
            $type = '';
        }

        $rows = $this->analyticsDataset(
            $period['start'],
            $period['end'],
            $programId,
            $type
        );

        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            return redirect()->to('/publications/analytics')
                ->with(
                    'error',
                    'File analitik belum dapat dibuat.'
                );
        }

        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, [
            'Content ID',
            'Judul',
            'Pilar',
            'Format',
            'Tanggal Tayang',
            'Snapshot Terakhir',
            'Reach',
            'Impressions',
            'Likes',
            'Comments',
            'Shares',
            'Saves',
            'Interactions',
            'Engagement Rate (%)',
            'Profile Visits',
            'Follows',
            'Link Clicks',
            'Video Views',
            'Instagram URL',
        ]);

        foreach ($rows as $row) {
            fputcsv($stream, [
                $row['content_code'] ?? '',
                $row['display_title'] ?? '',
                $row['program_name'] ?? 'Umum',
                $this->socialMedia->publicationTypes[
                    $row['publication_type'] ?? ''
                ] ?? ($row['publication_type'] ?? ''),
                $row['published_at'] ?? '',
                $row['metric_recorded_at'] ?? '',
                $row['reach'] ?? 0,
                $row['impressions'] ?? 0,
                $row['likes'] ?? 0,
                $row['comments'] ?? 0,
                $row['shares'] ?? 0,
                $row['saves'] ?? 0,
                $row['interactions'] ?? 0,
                number_format(
                    (float) (
                        $row['engagement_rate'] ?? 0
                    ),
                    2,
                    '.',
                    ''
                ),
                $row['profile_visits'] ?? 0,
                $row['follows'] ?? 0,
                $row['link_clicks'] ?? 0,
                $row['video_views'] ?? 0,
                $row['instagram_url'] ?? '',
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        $filename = sprintf(
            'GARDA01-Instagram-Analytics-%s.csv',
            $period['start']->format('Y-m')
        );

        return $this->response
            ->setHeader(
                'Content-Type',
                'text/csv; charset=UTF-8'
            )
            ->setHeader(
                'Content-Disposition',
                'attachment; filename="' . $filename . '"'
            )
            ->setBody((string) $csv);
    }

    public function storeMetrics($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/publications')
                ->with(
                    'error',
                    'Publikasi tidak ditemukan.'
                );
        }

        if (
            ($post['workflow_status'] ?? '') !== 'published'
        ) {
            return redirect()->to(
                '/publications/' . $id
            )->with(
                'error',
                'Metrik hanya dapat dicatat setelah '
                . 'konten berstatus Dipublikasikan.'
            );
        }

        if (!$this->metricsTableReady()) {
            return redirect()->to(
                '/publications/' . $id
            )->with(
                'error',
                'Tabel metrik belum tersedia. '
                . 'Jalankan php spark migrate.'
            );
        }

        $rules = [
            'recorded_at' => [
                'label' => 'Waktu snapshot',
                'rules' => 'required',
            ],
        ];

        foreach ([
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
        ] as $field) {
            $rules[$field] = [
                'label' => ucfirst(
                    str_replace('_', ' ', $field)
                ),
                'rules' =>
                    'permit_empty|integer'
                    . '|greater_than_equal_to[0]',
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->to(
                '/publications/' . $id
            )->withInput()->with(
                'error',
                implode(
                    ' ',
                    $this->validator->getErrors()
                )
            );
        }

        $recordedAt = $this->normalizeDateTime(
            $this->request->getPost('recorded_at')
        );

        if ($recordedAt === null) {
            return redirect()->to(
                '/publications/' . $id
            )->withInput()->with(
                'error',
                'Waktu snapshot tidak valid.'
            );
        }

        $payload = [
            'content_post_id' => (int) $id,
            'recorded_at' => $recordedAt,
            'reach' => $this->metricInteger('reach'),
            'impressions' =>
                $this->metricInteger('impressions'),
            'likes' => $this->metricInteger('likes'),
            'comments' => $this->metricInteger('comments'),
            'shares' => $this->metricInteger('shares'),
            'saves' => $this->metricInteger('saves'),
            'profile_visits' =>
                $this->metricInteger('profile_visits'),
            'follows' => $this->metricInteger('follows'),
            'link_clicks' =>
                $this->metricInteger('link_clicks'),
            'video_views' =>
                $this->metricInteger('video_views'),
            'notes' => $this->nullableString('metric_notes'),
            'recorded_by' => $this->currentUser(),
        ];

        $metricTotal = array_sum([
            $payload['reach'],
            $payload['impressions'],
            $payload['likes'],
            $payload['comments'],
            $payload['shares'],
            $payload['saves'],
            $payload['profile_visits'],
            $payload['follows'],
            $payload['link_clicks'],
            $payload['video_views'],
        ]);

        if ($metricTotal < 1) {
            return redirect()->to(
                '/publications/' . $id
            )->withInput()->with(
                'error',
                'Isi minimal satu angka performa '
                . 'sebelum menyimpan snapshot.'
            );
        }

        $metricId = $this->metricModel->insert(
            $payload,
            true
        );

        if (!$metricId) {
            return redirect()->to(
                '/publications/' . $id
            )->with(
                'error',
                'Snapshot performa belum dapat disimpan.'
            );
        }

        $this->recordAudit(
            (int) $id,
            'metric_added',
            'Snapshot performa Instagram ditambahkan.',
            [
                'metadata' => [
                    'metric_id' => (int) $metricId,
                    'recorded_at' => $recordedAt,
                    'reach' => $payload['reach'],
                    'interactions' =>
                        $payload['likes']
                        + $payload['comments']
                        + $payload['shares']
                        + $payload['saves'],
                ],
            ]
        );

        return redirect()->to(
            '/publications/' . $id
        )->with(
            'success',
            'Snapshot performa Instagram berhasil dicatat.'
        );
    }

    public function deleteMetric($id, $metricId)
    {
        if (!$this->metricsTableReady()) {
            return redirect()->to(
                '/publications/' . $id
            )->with(
                'error',
                'Tabel metrik belum tersedia.'
            );
        }

        $metric = $this->metricModel->find($metricId);

        if (
            !$metric
            || (int) $metric['content_post_id'] !== (int) $id
        ) {
            return redirect()->to(
                '/publications/' . $id
            )->with(
                'error',
                'Snapshot performa tidak ditemukan.'
            );
        }

        $this->metricModel->delete($metricId);

        $this->recordAudit(
            (int) $id,
            'metric_deleted',
            'Snapshot performa Instagram dihapus.',
            [
                'metadata' => [
                    'metric_id' => (int) $metricId,
                    'recorded_at' =>
                        $metric['recorded_at'] ?? null,
                    'reach' => (int) (
                        $metric['reach'] ?? 0
                    ),
                ],
            ]
        );

        return redirect()->to(
            '/publications/' . $id
        )->with(
            'success',
            'Snapshot performa berhasil dihapus.'
        );
    }

    public function deadlines()
    {
        $urgency = trim(
            (string) $this->request->getGet('urgency')
        );

        $status = trim(
            (string) $this->request->getGet('status')
        );

        $priority = trim(
            (string) $this->request->getGet('priority')
        );

        $owner = trim(
            (string) $this->request->getGet('owner')
        );

        $items = $this->productionAttentionDataset();

        if (!in_array(
            $urgency,
            [
                'overdue',
                'due_soon',
                'unscheduled',
                'on_track',
            ],
            true
        )) {
            $urgency = '';
        }

        if (!isset(
            $this->socialMedia->workflowStatuses[$status]
        )) {
            $status = '';
        }

        if (!isset(
            $this->socialMedia->priorities[$priority]
        )) {
            $priority = '';
        }

        $items = array_values(array_filter(
            $items,
            static function (
                array $item
            ) use (
                $urgency,
                $status,
                $priority,
                $owner
            ): bool {
                if (
                    $urgency !== ''
                    && $item['urgency'] !== $urgency
                ) {
                    return false;
                }

                if (
                    $status !== ''
                    && $item['workflow_status'] !== $status
                ) {
                    return false;
                }

                if (
                    $priority !== ''
                    && $item['priority'] !== $priority
                ) {
                    return false;
                }

                if (
                    $owner !== ''
                    && stripos(
                        (string) ($item['owner'] ?? ''),
                        $owner
                    ) === false
                ) {
                    return false;
                }

                return true;
            }
        ));

        return view('publications/deadlines', [
            'title' => 'Deadline Produksi Publikasi',
            'items' => $items,
            'summary' =>
                $this->productionDeadlineSummary(
                    $this->productionAttentionDataset()
                ),
            'workflowStatuses' =>
                $this->socialMedia->workflowStatuses,
            'priorities' =>
                $this->socialMedia->priorities,
            'urgencyLabels' =>
                $this->deadlineUrgencyLabels(),
            'filters' => [
                'urgency' => $urgency,
                'status' => $status,
                'priority' => $priority,
                'owner' => $owner,
            ],
            'warningHours' =>
                $this->socialMedia->deadlineWarningHours,
        ]);
    }

    public function audit()
    {
        $eventType = trim(
            (string) $this->request->getGet('event_type')
        );

        $search = trim(
            (string) $this->request->getGet('q')
        );

        $actor = trim(
            (string) $this->request->getGet('actor')
        );

        $dateFrom = trim(
            (string) $this->request->getGet('date_from')
        );

        $dateTo = trim(
            (string) $this->request->getGet('date_to')
        );

        $logs = [];
        $pager = null;

        if ($this->auditTableReady()) {
            $model = new ContentPostAuditLogModel();

            $model
                ->select(
                    'content_post_audit_logs.*, '
                    . 'content_posts.content_code, '
                    . 'content_posts.event_title, '
                    . 'content_posts.title'
                )
                ->join(
                    'content_posts',
                    'content_posts.id = '
                    . 'content_post_audit_logs.content_post_id',
                    'left'
                );

            if (isset(
                $this->auditEventLabels()[$eventType]
            )) {
                $model->where(
                    'content_post_audit_logs.event_type',
                    $eventType
                );
            }

            if ($actor !== '') {
                $model
                    ->groupStart()
                    ->like(
                        'content_post_audit_logs.actor_name',
                        $actor
                    )
                    ->orLike(
                        'content_post_audit_logs.actor_role',
                        $actor
                    )
                    ->groupEnd();
            }

            if ($search !== '') {
                $model
                    ->groupStart()
                    ->like(
                        'content_post_audit_logs.summary',
                        $search
                    )
                    ->orLike(
                        'content_posts.content_code',
                        $search
                    )
                    ->orLike(
                        'content_posts.event_title',
                        $search
                    )
                    ->orLike(
                        'content_posts.title',
                        $search
                    )
                    ->groupEnd();
            }

            if ($this->isValidDate($dateFrom)) {
                $model->where(
                    'content_post_audit_logs.created_at >=',
                    $dateFrom . ' 00:00:00'
                );
            }

            if ($this->isValidDate($dateTo)) {
                $model->where(
                    'content_post_audit_logs.created_at <=',
                    $dateTo . ' 23:59:59'
                );
            }

            $logs = $model
                ->orderBy(
                    'content_post_audit_logs.created_at',
                    'DESC'
                )
                ->orderBy(
                    'content_post_audit_logs.id',
                    'DESC'
                )
                ->paginate(30, 'publication_audit');

            $pager = $model->pager;
        }

        return view('publications/audit', [
            'title' => 'Audit Trail Publikasi',
            'logs' => $logs,
            'pager' => $pager,
            'auditReady' => $this->auditTableReady(),
            'eventLabels' => $this->auditEventLabels(),
            'summary' => $this->auditSummary(),
            'filters' => [
                'event_type' => $eventType,
                'q' => $search,
                'actor' => $actor,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'workflowStatuses' =>
                $this->socialMedia->workflowStatuses,
        ]);
    }

    public function store()
    {
        if (!$this->validatePublicationRequest()) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    $this->publicationValidationError
                    ?? 'Mohon periksa kembali data brief publikasi.'
                );
        }

        $payload = $this->publicationPayload();
        $payload['status'] = 'draft';
        $payload['workflow_status'] = 'brief';
        $payload['created_by'] = $this->currentUser();

        $postId = $this->postModel->insert($payload, true);

        if (!$postId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Brief publikasi belum dapat disimpan.');
        }

        $this->postModel->update($postId, [
            'content_code' => $this->buildContentCode((int) $postId),
        ]);

        $this->recordAudit(
            (int) $postId,
            'created',
            'Brief publikasi dibuat.',
            [
                'to_status' => 'brief',
                'metadata' => [
                    'activity_id' =>
                        $payload['activity_id'] ?? null,
                    'program_id' =>
                        $payload['program_id'] ?? null,
                    'publication_type' =>
                        $payload['publication_type'] ?? null,
                ],
            ]
        );

        try {
            $savedAssets = $this->saveUploadedAssets(
                (int) $postId
            );
        } catch (\RuntimeException $e) {
            return redirect()->to('/publications/' . $postId)
                ->with('error', $e->getMessage());
        }

        if ($savedAssets > 0) {
            $this->recordAudit(
                (int) $postId,
                'assets_uploaded',
                $savedAssets
                . ' aset awal ditambahkan.',
                [
                    'metadata' => [
                        'asset_count' => $savedAssets,
                    ],
                ]
            );
        }

        return redirect()->to('/publications/' . $postId)
            ->with('success', 'Brief publikasi berhasil dibuat.');
    }

    public function show($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/publications')
                ->with('error', 'Publikasi tidak ditemukan.');
        }

        $program = !empty($post['program_id'])
            ? $this->programModel->find($post['program_id'])
            : null;

        $activity = !empty($post['activity_id'])
            ? $this->activityModel->find($post['activity_id'])
            : null;

        $templateCode = $post['canva_template_code'] ?? '';
        $currentStatus = $post['workflow_status'] ?: 'brief';

        $metricHistory = $this->metricsTableReady()
            ? $this->metricModel->historyForPost((int) $id)
            : [];

        $latestMetric = $metricHistory[0] ?? null;

        $auditHistory = $this->auditTableReady()
            ? $this->auditModel->historyForPost(
                (int) $id,
                30
            )
            : [];

        $allowedTransitions = array_values(array_filter(
            $this->socialMedia->transitions[$currentStatus] ?? [],
            fn (string $targetStatus): bool => $this->canTransitionTo(
                $currentStatus,
                $targetStatus
            )
        ));

        return view('publications/show', [
            'title' => $post['content_code'] ?: 'Detail Publikasi',
            'post' => $post,
            'program' => $program,
            'activity' => $activity,
            'assets' => $this->assetModel
                ->where('content_post_id', $id)
                ->orderBy('sort_order', 'ASC')
                ->findAll(),
            'template' => $this->socialMedia->templates[$templateCode] ?? null,
            'workflowStatuses' => $this->socialMedia->workflowStatuses,
            'workflowDescriptions' => $this->socialMedia->workflowDescriptions,
            'allowedTransitions' => $allowedTransitions,
            'publicationTypes' => $this->socialMedia->publicationTypes,
            'priorities' => $this->socialMedia->priorities,
            'categories' => $this->socialMedia->categories,
            'metricHistory' => $metricHistory,
            'latestMetric' => $latestMetric,
            'latestMetricSummary' =>
                $this->metricSnapshotSummary($latestMetric),
            'metricsReady' => $this->metricsTableReady(),
        ]);
    }

    public function edit($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/publications')
                ->with('error', 'Publikasi tidak ditemukan.');
        }

        return view('publications/edit', array_merge(
            $this->formReferenceData(),
            [
                'title' => 'Edit ' . ($post['content_code'] ?: 'Publikasi'),
                'post' => $post,
                'formAction' => '/publications/update/' . $id,
                'submitLabel' => 'Simpan Perubahan',
            ]
        ));
    }

    public function update($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/publications')
                ->with('error', 'Publikasi tidak ditemukan.');
        }

        if (!$this->validatePublicationRequest()) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    $this->publicationValidationError
                    ?? 'Mohon periksa kembali data publikasi.'
                );
        }

        $payload = $this->publicationPayload();
        $changedFields = $this->changedFields(
            $post,
            $payload
        );

        $this->postModel->update($id, $payload);

        try {
            $savedAssets = $this->saveUploadedAssets(
                (int) $id
            );
        } catch (\RuntimeException $e) {
            return redirect()->to('/publications/' . $id)
                ->with('error', $e->getMessage());
        }

        if (!empty($changedFields)) {
            $this->recordAudit(
                (int) $id,
                'updated',
                'Data publikasi diperbarui: '
                . $this->changedFieldSummary(
                    $changedFields
                )
                . '.',
                [
                    'changed_fields' => $changedFields,
                ]
            );
        }

        if ($savedAssets > 0) {
            $this->recordAudit(
                (int) $id,
                'assets_uploaded',
                $savedAssets . ' aset ditambahkan.',
                [
                    'metadata' => [
                        'asset_count' => $savedAssets,
                    ],
                ]
            );
        }

        return redirect()->to('/publications/' . $id)
            ->with('success', 'Data publikasi berhasil diperbarui.');
    }

    public function changeStatus($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/publications')
                ->with('error', 'Publikasi tidak ditemukan.');
        }

        $currentStatus = $post['workflow_status'] ?: 'brief';
        $targetStatus = trim((string) $this->request->getPost('workflow_status'));
        $allowed = $this->socialMedia->transitions[$currentStatus] ?? [];

        if (
            !isset($this->socialMedia->workflowStatuses[$targetStatus])
            || !in_array($targetStatus, $allowed, true)
        ) {
            return redirect()->back()
                ->with('error', 'Perpindahan status tersebut tidak diizinkan.');
        }

        $requiredPermission = $this->transitionPermission(
            $currentStatus,
            $targetStatus
        );

        if (!auth_can($requiredPermission)) {
            return redirect()->back()->with(
                'error',
                'Akses ditolak. Peran Anda tidak memiliki izin untuk '
                . authorization()->permissionLabel($requiredPermission)
                . '.'
            );
        }

        if ($targetStatus === 'scheduled' && empty($post['scheduled_at'])) {
            return redirect()->back()
                ->with('error', 'Isi jadwal tayang sebelum menjadwalkan konten.');
        }

        if ($targetStatus === 'published' && empty($post['instagram_url'])) {
            return redirect()->back()
                ->with('error', 'Catat tautan Instagram sebelum menandai konten sebagai tayang.');
        }

        $now = date('Y-m-d H:i:s');
        $update = [
            'workflow_status' => $targetStatus,
            'approval_notes' => trim(
                (string) $this->request->getPost('approval_notes')
            ) ?: ($post['approval_notes'] ?? null),
        ];

        if ($targetStatus === 'approved') {
            $update['approved_by'] = $this->currentUser();
            $update['approved_at'] = $now;
        } elseif (in_array(
            $targetStatus,
            ['brief', 'draft', 'design', 'review', 'revision'],
            true
        )) {
            $update['approved_by'] = null;
            $update['approved_at'] = null;
        }

        if ($targetStatus === 'published') {
            $update['published_at'] = $post['published_at'] ?: $now;
        }

        if ($targetStatus === 'archived') {
            $update['archived_at'] = $now;
        } elseif ($currentStatus === 'archived') {
            $update['archived_at'] = null;
        }

        $this->postModel->update($id, $update);

        $this->recordAudit(
            (int) $id,
            'status_changed',
            'Status publikasi berubah dari '
            . (
                $this->socialMedia->workflowStatuses[
                    $currentStatus
                ] ?? ucfirst($currentStatus)
            )
            . ' menjadi '
            . (
                $this->socialMedia->workflowStatuses[
                    $targetStatus
                ] ?? ucfirst($targetStatus)
            )
            . '.',
            [
                'from_status' => $currentStatus,
                'to_status' => $targetStatus,
                'metadata' => [
                    'approval_notes' =>
                        $update['approval_notes'] ?? null,
                ],
            ]
        );

        return redirect()->to('/publications/' . $id)
            ->with('success', 'Status publikasi berhasil diperbarui.');
    }

    public function uploadAssets($id)
    {
        if (!$this->postModel->find($id)) {
            return redirect()->to('/publications')
                ->with('error', 'Publikasi tidak ditemukan.');
        }

        try {
            $saved = $this->saveUploadedAssets((int) $id, true);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        if ($saved < 1) {
            return redirect()->back()
                ->with('error', 'Pilih minimal satu gambar yang valid.');
        }

        $this->recordAudit(
            (int) $id,
            'assets_uploaded',
            $saved . ' aset ditambahkan.',
            [
                'metadata' => [
                    'asset_count' => $saved,
                ],
            ]
        );

        return redirect()->to('/publications/' . $id)
            ->with('success', $saved . ' aset berhasil ditambahkan.');
    }

    public function deleteAsset($id, $assetId)
    {
        $asset = $this->assetModel
            ->where('id', $assetId)
            ->where('content_post_id', $id)
            ->first();

        if (!$asset) {
            return redirect()->back()->with('error', 'Aset tidak ditemukan.');
        }

        $path = FCPATH . ltrim($asset['image_path'], '/');

        if (is_file($path)) {
            unlink($path);
        }

        $this->assetModel->delete($assetId);

        $this->recordAudit(
            (int) $id,
            'asset_deleted',
            'Aset publikasi dihapus.',
            [
                'metadata' => [
                    'asset_id' => (int) $assetId,
                    'original_name' =>
                        $asset['original_name'] ?? null,
                    'image_path' =>
                        $asset['image_path'] ?? null,
                ],
            ]
        );

        return redirect()->to('/publications/' . $id)
            ->with('success', 'Aset berhasil dihapus.');
    }

    private function validatePublicationRequest(): bool
    {
        $rules = [
            'event_title' => 'required|min_length[3]|max_length[255]',
            'category' => 'required|max_length[50]',
            'publication_type' => 'required|max_length[30]',
            'canva_template_code' => 'required|max_length[40]',
            'priority' => 'required|max_length[20]',
            'owner' => 'required|max_length[100]',
            'cover_hook' => 'permit_empty|max_length[255]',
            'target_audience' => 'permit_empty|max_length[255]',
            'call_to_action' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return false;
        }

        $category = (string) $this->request->getPost('category');
        $publicationType = (string) $this->request->getPost('publication_type');
        $templateCode = (string) $this->request->getPost('canva_template_code');
        $priority = (string) $this->request->getPost('priority');
        $template = $this->socialMedia->templates[$templateCode] ?? null;

        if (
            !isset($this->socialMedia->categories[$category])
            || !isset($this->socialMedia->publicationTypes[$publicationType])
            || $template === null
            || !isset($this->socialMedia->priorities[$priority])
            || ($template['type'] ?? null) !== $publicationType
        ) {
            return false;
        }

        $programId = $this->nullableId('program_id');
        $activityId = $this->nullableId('activity_id');

        if ($programId !== null && !$this->programModel->find($programId)) {
            return false;
        }

        if ($activityId !== null && !$this->activityModel->find($activityId)) {
            return false;
        }

        $canvaUrl = trim(
            (string) $this->request->getPost('canva_url')
        );

        if ($canvaUrl !== '') {
            if (!$this->isAllowedPlatformUrl(
                $canvaUrl,
                ['canva.com']
            )) {
                $this->publicationValidationError =
                    'Tautan desain kerja harus menggunakan domain Canva.';

                return false;
            }

            $path = (string) parse_url($canvaUrl, PHP_URL_PATH);

            if (!str_contains($path, '/design/')) {
                $this->publicationValidationError =
                    'Tautan Canva harus berasal dari desain kerja hasil duplikasi, bukan tautan master.';

                return false;
            }
        }

        $instagramUrl = trim(
            (string) $this->request->getPost('instagram_url')
        );

        if (
            $instagramUrl !== ''
            && !$this->isAllowedPlatformUrl(
                $instagramUrl,
                ['instagram.com']
            )
        ) {
            $this->publicationValidationError =
                'Tautan tayang harus menggunakan domain Instagram.';

            return false;
        }

        return true;
    }

    private function publicationPayload(): array
    {
        return [
            'program_id' => $this->nullableId('program_id'),
            'activity_id' => $this->nullableId('activity_id'),
            'channel' => 'instagram',
            'publication_type' => trim(
                (string) $this->request->getPost('publication_type')
            ),
            'canva_template_code' => trim(
                (string) $this->request->getPost('canva_template_code')
            ),
            'category' => trim((string) $this->request->getPost('category')),
            'template_type' => 'social_publication',
            'event_title' => trim((string) $this->request->getPost('event_title')),
            'event_date' => $this->nullableString('event_date'),
            'event_time' => $this->nullableString('event_time'),
            'event_location' => $this->nullableString('event_location'),
            'activity_description' => $this->nullableString('activity_description'),
            'cover_hook' => $this->nullableString('cover_hook'),
            'content_goal' => $this->nullableString('content_goal'),
            'target_audience' => $this->nullableString('target_audience'),
            'call_to_action' => $this->nullableString('call_to_action'),
            'canva_url' => $this->nullableString('canva_url'),
            'instagram_url' => $this->nullableString('instagram_url'),
            'owner' => trim((string) $this->request->getPost('owner')),
            'reviewer' => $this->nullableString('reviewer'),
            'priority' => trim((string) $this->request->getPost('priority')),
            'scheduled_at' => $this->normalizeDateTime(
                $this->request->getPost('scheduled_at')
            ),
            'title' => $this->nullableString('title'),
            'caption' => $this->nullableString('caption'),
            'hashtags' => $this->nullableString('hashtags'),
            'mentions' => $this->nullableString('mentions'),
            'alt_text' => $this->nullableString('alt_text'),
            'notes' => $this->nullableString('notes'),
        ];
    }

    private function formReferenceData(): array
    {
        $activities = db_connect()
            ->table('activities')
            ->select(
                'activities.id, activities.title, activities.activity_date, '
                . 'programs.name AS program_name'
            )
            ->join('programs', 'programs.id = activities.program_id', 'left')
            ->orderBy('activities.activity_date', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        return [
            'programs' => $this->programModel
                ->orderBy('display_order', 'ASC')
                ->findAll(),
            'activities' => $activities,
            'templates' => $this->socialMedia->templates,
            'categories' => $this->socialMedia->categories,
            'publicationTypes' => $this->socialMedia->publicationTypes,
            'priorities' => $this->socialMedia->priorities,
        ];
    }

    private function publicationSummary(): array
    {
        $db = db_connect();

        return [
            'total' => $db->table('content_posts')->countAllResults(),
            'in_progress' => $db->table('content_posts')
                ->whereIn('workflow_status', [
                    'brief',
                    'draft',
                    'design',
                    'revision',
                ])
                ->countAllResults(),
            'review' => $db->table('content_posts')
                ->where('workflow_status', 'review')
                ->countAllResults(),
            'scheduled' => $db->table('content_posts')
                ->where('workflow_status', 'scheduled')
                ->countAllResults(),
            'published' => $db->table('content_posts')
                ->where('workflow_status', 'published')
                ->countAllResults(),
        ];
    }

    private function productionAttentionDataset(): array
    {
        $rows = db_connect()
            ->table('content_posts')
            ->select(
                'content_posts.*, '
                . 'programs.name AS program_name, '
                . 'COUNT(content_assets.id) AS asset_count',
                false
            )
            ->join(
                'programs',
                'programs.id = content_posts.program_id',
                'left'
            )
            ->join(
                'content_assets',
                'content_assets.content_post_id '
                . '= content_posts.id',
                'left'
            )
            ->where(
                'content_posts.template_type',
                'social_publication'
            )
            ->whereNotIn(
                'content_posts.workflow_status',
                ['published', 'archived']
            )
            ->groupBy('content_posts.id')
            ->orderBy('content_posts.scheduled_at', 'ASC')
            ->orderBy('content_posts.priority', 'DESC')
            ->orderBy('content_posts.updated_at', 'ASC')
            ->get()
            ->getResultArray();

        $now = new \DateTimeImmutable('now');

        foreach ($rows as &$row) {
            $row = array_merge(
                $row,
                $this->productionDeadlineState(
                    $row,
                    $now
                )
            );
        }

        unset($row);

        $urgencyRank = [
            'overdue' => 1,
            'due_soon' => 2,
            'unscheduled' => 3,
            'on_track' => 4,
        ];

        $priorityRank = [
            'urgent' => 1,
            'high' => 2,
            'normal' => 3,
            'low' => 4,
        ];

        usort(
            $rows,
            static function (
                array $left,
                array $right
            ) use (
                $urgencyRank,
                $priorityRank
            ): int {
                $urgencyComparison =
                    ($urgencyRank[
                        $left['urgency']
                    ] ?? 99)
                    <=>
                    ($urgencyRank[
                        $right['urgency']
                    ] ?? 99);

                if ($urgencyComparison !== 0) {
                    return $urgencyComparison;
                }

                $priorityComparison =
                    ($priorityRank[
                        $left['priority'] ?? 'normal'
                    ] ?? 99)
                    <=>
                    ($priorityRank[
                        $right['priority'] ?? 'normal'
                    ] ?? 99);

                if ($priorityComparison !== 0) {
                    return $priorityComparison;
                }

                $leftDue = $left['due_timestamp']
                    ?? PHP_INT_MAX;

                $rightDue = $right['due_timestamp']
                    ?? PHP_INT_MAX;

                return $leftDue <=> $rightDue;
            }
        );

        return $rows;
    }

    private function productionDeadlineState(
        array $post,
        \DateTimeImmutable $now
    ): array {
        $status = (string) (
            $post['workflow_status'] ?? 'brief'
        );

        $milestone = $this->socialMedia
            ->productionMilestones[$status] ?? [
                'label' => 'Lanjutkan produksi konten',
                'offset_hours' => -24,
            ];

        $scheduledAt = $this->dateTimeFromValue(
            $post['scheduled_at'] ?? null
        );

        $blockers = $this->productionBlockers(
            $post
        );

        if ($scheduledAt === null) {
            return [
                'urgency' => 'unscheduled',
                'urgency_label' =>
                    $this->deadlineUrgencyLabels()[
                        'unscheduled'
                    ],
                'next_milestone' =>
                    'Tetapkan rencana tayang',
                'due_at' => null,
                'due_timestamp' => null,
                'scheduled_at_object' => null,
                'hours_remaining' => null,
                'time_message' =>
                    'Belum dapat menghitung deadline.',
                'blockers' => $blockers,
            ];
        }

        $offsetHours = (int) (
            $milestone['offset_hours'] ?? 0
        );

        $modifier = ($offsetHours >= 0 ? '+' : '')
            . $offsetHours
            . ' hours';

        $dueAt = $scheduledAt->modify($modifier);

        $warningAt = $now->modify(
            '+'
            . max(
                1,
                $this->socialMedia->deadlineWarningHours
            )
            . ' hours'
        );

        if ($dueAt < $now) {
            $urgency = 'overdue';
        } elseif ($dueAt <= $warningAt) {
            $urgency = 'due_soon';
        } else {
            $urgency = 'on_track';
        }

        $secondsRemaining =
            $dueAt->getTimestamp()
            - $now->getTimestamp();

        $hoursRemaining = (int) floor(
            $secondsRemaining / 3600
        );

        return [
            'urgency' => $urgency,
            'urgency_label' =>
                $this->deadlineUrgencyLabels()[
                    $urgency
                ],
            'next_milestone' =>
                (string) ($milestone['label']
                ?? 'Lanjutkan produksi'),
            'due_at' => $dueAt,
            'due_timestamp' => $dueAt->getTimestamp(),
            'scheduled_at_object' => $scheduledAt,
            'hours_remaining' => $hoursRemaining,
            'time_message' =>
                $this->deadlineTimeMessage(
                    $hoursRemaining
                ),
            'blockers' => $blockers,
        ];
    }

    private function productionBlockers(
        array $post
    ): array {
        $status = (string) (
            $post['workflow_status'] ?? 'brief'
        );

        $blockers = [];

        if (empty(trim(
            (string) ($post['owner'] ?? '')
        ))) {
            $blockers[] = 'PIC belum ditentukan';
        }

        if (
            in_array(
                $status,
                [
                    'design',
                    'review',
                    'revision',
                    'approved',
                    'scheduled',
                ],
                true
            )
            && empty(trim(
                (string) ($post['canva_url'] ?? '')
            ))
        ) {
            $blockers[] =
                'Tautan desain kerja Canva belum tersedia';
        }

        if (
            in_array(
                $status,
                [
                    'review',
                    'revision',
                    'approved',
                    'scheduled',
                ],
                true
            )
            && empty(trim(
                (string) ($post['reviewer'] ?? '')
            ))
        ) {
            $blockers[] = 'Reviewer belum ditentukan';
        }

        if (
            in_array(
                $status,
                [
                    'review',
                    'revision',
                    'approved',
                    'scheduled',
                ],
                true
            )
            && (int) ($post['asset_count'] ?? 0) < 1
        ) {
            $blockers[] =
                'Aset dokumentasi belum tersedia';
        }

        if (
            in_array(
                $status,
                [
                    'review',
                    'revision',
                    'approved',
                    'scheduled',
                ],
                true
            )
            && empty(trim(
                (string) ($post['caption'] ?? '')
            ))
        ) {
            $blockers[] = 'Caption belum lengkap';
        }

        if (
            in_array(
                $status,
                ['approved', 'scheduled'],
                true
            )
            && empty($post['scheduled_at'])
        ) {
            $blockers[] =
                'Rencana tayang belum ditentukan';
        }

        return $blockers;
    }

    private function productionDeadlineSummary(
        array $items
    ): array {
        $summary = [
            'total' => count($items),
            'overdue' => 0,
            'due_soon' => 0,
            'unscheduled' => 0,
            'on_track' => 0,
            'blocked' => 0,
        ];

        foreach ($items as $item) {
            $urgency = $item['urgency']
                ?? 'on_track';

            if (isset($summary[$urgency])) {
                $summary[$urgency]++;
            }

            if (!empty($item['blockers'])) {
                $summary['blocked']++;
            }
        }

        return $summary;
    }

    private function deadlineUrgencyLabels(): array
    {
        return [
            'overdue' => 'Terlambat',
            'due_soon' => 'Segera Jatuh Tempo',
            'unscheduled' => 'Belum Dijadwalkan',
            'on_track' => 'Sesuai Jalur',
        ];
    }

    private function deadlineTimeMessage(
        int $hoursRemaining
    ): string {
        if ($hoursRemaining < 0) {
            $lateHours = abs($hoursRemaining);

            if ($lateHours < 24) {
                return 'Terlambat '
                    . max(1, $lateHours)
                    . ' jam';
            }

            return 'Terlambat '
                . max(
                    1,
                    (int) floor(
                        $lateHours / 24
                    )
                )
                . ' hari';
        }

        if ($hoursRemaining < 24) {
            return 'Sisa '
                . max(1, $hoursRemaining)
                . ' jam';
        }

        return 'Sisa '
            . max(
                1,
                (int) floor(
                    $hoursRemaining / 24
                )
            )
            . ' hari';
    }

    private function dateTimeFromValue(
        $value
    ): ?\DateTimeImmutable {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function recordAudit(
        int $postId,
        string $eventType,
        string $summary,
        array $options = []
    ): void {
        if (!$this->auditTableReady()) {
            return;
        }

        $payload = [
            'content_post_id' => $postId,
            'event_type' => $eventType,
            'summary' => mb_strimwidth(
                trim($summary),
                0,
                255,
                '…'
            ),
            'from_status' =>
                $options['from_status'] ?? null,
            'to_status' =>
                $options['to_status'] ?? null,
            'changed_fields' => $this->encodeAuditData(
                $options['changed_fields'] ?? null
            ),
            'metadata' => $this->encodeAuditData(
                $options['metadata'] ?? null
            ),
            'user_id' => session()->has('user_id')
                ? (int) session()->get('user_id')
                : null,
            'actor_name' => $this->currentUser(),
            'actor_role' => session()->get('role_name')
                ?: 'Tidak diketahui',
            'ip_address' =>
                $this->request->getIPAddress(),
            'user_agent' => mb_strimwidth(
                (string) $this->request
                    ->getUserAgent(),
                0,
                500,
                '…'
            ),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $this->auditModel->insert($payload);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'Publication audit log failed: '
                . $e->getMessage()
            );
        }
    }

    private function changedFields(
        array $before,
        array $after
    ): array {
        $changes = [];

        foreach ($after as $field => $newValue) {
            $oldValue = $before[$field] ?? null;

            if (
                $this->comparableValue($oldValue)
                === $this->comparableValue($newValue)
            ) {
                continue;
            }

            $changes[$field] = [
                'label' => $this->auditFieldLabels()[
                    $field
                ] ?? str_replace('_', ' ', $field),
                'before' => $oldValue,
                'after' => $newValue,
            ];
        }

        return $changes;
    }

    private function changedFieldSummary(
        array $changes
    ): string {
        $labels = array_map(
            static fn (array $change): string =>
                (string) ($change['label'] ?? 'field'),
            array_values($changes)
        );

        if (count($labels) > 5) {
            $remaining = count($labels) - 5;
            $labels = array_slice($labels, 0, 5);
            $labels[] = $remaining . ' field lainnya';
        }

        return implode(', ', $labels);
    }

    private function comparableValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ) ?: '';
        }

        return trim((string) $value);
    }

    private function encodeAuditData($value): ?string
    {
        if ($value === null || $value === []) {
            return null;
        }

        $json = json_encode(
            $value,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        return $json === false ? null : $json;
    }

    private function auditTableReady(): bool
    {
        return db_connect()->tableExists(
            'content_post_audit_logs'
        );
    }

    private function auditSummary(): array
    {
        if (!$this->auditTableReady()) {
            return [
                'total' => 0,
                'today' => 0,
                'status_changes' => 0,
                'updates' => 0,
            ];
        }

        $db = db_connect();

        return [
            'total' => $db
                ->table('content_post_audit_logs')
                ->countAllResults(),
            'today' => $db
                ->table('content_post_audit_logs')
                ->where(
                    'created_at >=',
                    date('Y-m-d') . ' 00:00:00'
                )
                ->countAllResults(),
            'status_changes' => $db
                ->table('content_post_audit_logs')
                ->where('event_type', 'status_changed')
                ->countAllResults(),
            'updates' => $db
                ->table('content_post_audit_logs')
                ->where('event_type', 'updated')
                ->countAllResults(),
        ];
    }

    private function auditEventLabels(): array
    {
        return [
            'created' => 'Brief Dibuat',
            'updated' => 'Data Diperbarui',
            'status_changed' => 'Status Berubah',
            'assets_uploaded' => 'Aset Ditambahkan',
            'asset_deleted' => 'Aset Dihapus',
            'metric_added' => 'Metrik Ditambahkan',
            'metric_deleted' => 'Metrik Dihapus',
        ];
    }

    private function auditFieldLabels(): array
    {
        return [
            'program_id' => 'Pilar',
            'activity_id' => 'Sumber kegiatan',
            'publication_type' => 'Format publikasi',
            'canva_template_code' => 'Master Canva',
            'category' => 'Kategori',
            'event_title' => 'Judul internal',
            'event_date' => 'Tanggal kegiatan',
            'event_time' => 'Waktu kegiatan',
            'event_location' => 'Lokasi',
            'activity_description' => 'Deskripsi kegiatan',
            'cover_hook' => 'Hook cover',
            'content_goal' => 'Tujuan konten',
            'target_audience' => 'Target audiens',
            'call_to_action' => 'Call to action',
            'canva_url' => 'Tautan desain Canva',
            'owner' => 'PIC',
            'reviewer' => 'Reviewer',
            'priority' => 'Prioritas',
            'title' => 'Judul konten',
            'caption' => 'Caption',
            'hashtags' => 'Hashtag',
            'mentions' => 'Mention',
            'alt_text' => 'Alt text',
            'notes' => 'Catatan produksi',
            'scheduled_at' => 'Jadwal tayang',
            'instagram_url' => 'Tautan Instagram',
            'approval_notes' => 'Catatan approval',
        ];
    }

    private function isValidDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value
        );

        $errors = \DateTimeImmutable::getLastErrors();

        return $date !== false
            && (
                $errors === false
                || (
                    ($errors['warning_count'] ?? 0) === 0
                    && ($errors['error_count'] ?? 0) === 0
                )
            );
    }

    private function resolveAnalyticsMonth(
        string $value
    ): array {
        $month = \DateTimeImmutable::createFromFormat(
            '!Y-m',
            trim($value)
        );

        $errors = \DateTimeImmutable::getLastErrors();
        $hasErrors = is_array($errors)
            && (
                ($errors['warning_count'] ?? 0) > 0
                || ($errors['error_count'] ?? 0) > 0
            );

        if (!$month || $hasErrors) {
            $month = new \DateTimeImmutable(
                'first day of this month'
            );
        }

        return [
            'start' => $month->modify(
                'first day of this month'
            ),
            'end' => $month->modify(
                'last day of this month'
            ),
        ];
    }

    private function analyticsDataset(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        int $programId = 0,
        string $type = ''
    ): array {
        $db = db_connect();

        $builder = $db
            ->table('content_posts')
            ->select(
                'content_posts.id, '
                . 'content_posts.content_code, '
                . 'content_posts.event_title, '
                . 'content_posts.title, '
                . 'content_posts.publication_type, '
                . 'content_posts.program_id, '
                . 'content_posts.published_at, '
                . 'content_posts.instagram_url, '
                . 'programs.name AS program_name'
            )
            ->join(
                'programs',
                'programs.id = content_posts.program_id',
                'left'
            );

        if ($this->metricsTableReady()) {
            $builder
                ->select(
                    'metric.id AS metric_id, '
                    . 'metric.recorded_at '
                    . 'AS metric_recorded_at, '
                    . 'metric.reach, '
                    . 'metric.impressions, '
                    . 'metric.likes, '
                    . 'metric.comments, '
                    . 'metric.shares, '
                    . 'metric.saves, '
                    . 'metric.profile_visits, '
                    . 'metric.follows, '
                    . 'metric.link_clicks, '
                    . 'metric.video_views'
                )
                ->join(
                    'content_post_metrics AS metric',
                    "metric.id = (
                        SELECT metric_inner.id
                        FROM content_post_metrics
                            AS metric_inner
                        WHERE metric_inner.content_post_id
                            = content_posts.id
                        ORDER BY
                            metric_inner.recorded_at DESC,
                            metric_inner.id DESC
                        LIMIT 1
                    )",
                    'left',
                    false
                );
        } else {
            $builder->select(
                'NULL AS metric_id, '
                . 'NULL AS metric_recorded_at, '
                . '0 AS reach, '
                . '0 AS impressions, '
                . '0 AS likes, '
                . '0 AS comments, '
                . '0 AS shares, '
                . '0 AS saves, '
                . '0 AS profile_visits, '
                . '0 AS follows, '
                . '0 AS link_clicks, '
                . '0 AS video_views',
                false
            );
        }

        $builder
            ->where(
                'content_posts.workflow_status',
                'published'
            )
            ->where(
                'content_posts.published_at >=',
                $start->format('Y-m-d') . ' 00:00:00'
            )
            ->where(
                'content_posts.published_at <=',
                $end->format('Y-m-d') . ' 23:59:59'
            );

        if ($programId > 0) {
            $builder->where(
                'content_posts.program_id',
                $programId
            );
        }

        if ($type !== '') {
            $builder->where(
                'content_posts.publication_type',
                $type
            );
        }

        $rows = $builder
            ->orderBy(
                'content_posts.published_at',
                'DESC'
            )
            ->orderBy('content_posts.id', 'DESC')
            ->get()
            ->getResultArray();

        $numericFields = [
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
        ];

        foreach ($rows as &$row) {
            foreach ($numericFields as $field) {
                $row[$field] = (int) (
                    $row[$field] ?? 0
                );
            }

            $row['display_title'] =
                $row['event_title']
                ?: $row['title']
                ?: 'Tanpa judul';

            $row['interactions'] =
                $row['likes']
                + $row['comments']
                + $row['shares']
                + $row['saves'];

            $row['engagement_rate'] =
                $row['reach'] > 0
                    ? (
                        $row['interactions']
                        / $row['reach']
                    ) * 100
                    : 0.0;
        }

        unset($row);

        return $rows;
    }

    private function analyticsSummary(array $rows): array
    {
        $summary = [
            'published' => count($rows),
            'tracked' => 0,
            'reach' => 0,
            'interactions' => 0,
            'engagement_rate' => 0.0,
            'coverage_rate' => 0.0,
        ];

        foreach ($rows as $row) {
            if (!empty($row['metric_id'])) {
                $summary['tracked']++;
            }

            $summary['reach'] +=
                (int) ($row['reach'] ?? 0);

            $summary['interactions'] +=
                (int) ($row['interactions'] ?? 0);
        }

        if ($summary['reach'] > 0) {
            $summary['engagement_rate'] =
                (
                    $summary['interactions']
                    / $summary['reach']
                ) * 100;
        }

        if ($summary['published'] > 0) {
            $summary['coverage_rate'] =
                (
                    $summary['tracked']
                    / $summary['published']
                ) * 100;
        }

        return $summary;
    }

    private function formatPerformance(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = $row['publication_type'] ?: 'other';

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'key' => $key,
                    'label' =>
                        $this->socialMedia->publicationTypes[
                            $key
                        ] ?? ucfirst($key),
                    'posts' => 0,
                    'tracked' => 0,
                    'reach' => 0,
                    'interactions' => 0,
                    'engagement_rate' => 0.0,
                ];
            }

            $groups[$key]['posts']++;

            if (!empty($row['metric_id'])) {
                $groups[$key]['tracked']++;
            }

            $groups[$key]['reach'] +=
                (int) ($row['reach'] ?? 0);

            $groups[$key]['interactions'] +=
                (int) ($row['interactions'] ?? 0);
        }

        foreach ($groups as &$group) {
            if ($group['reach'] > 0) {
                $group['engagement_rate'] =
                    (
                        $group['interactions']
                        / $group['reach']
                    ) * 100;
            }
        }

        unset($group);

        usort(
            $groups,
            static fn (
                array $left,
                array $right
            ): int =>
                $right['reach'] <=> $left['reach']
        );

        return array_values($groups);
    }

    private function programPerformance(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = $row['program_name'] ?: 'Umum';

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'label' => $key,
                    'posts' => 0,
                    'tracked' => 0,
                    'reach' => 0,
                    'interactions' => 0,
                    'engagement_rate' => 0.0,
                ];
            }

            $groups[$key]['posts']++;

            if (!empty($row['metric_id'])) {
                $groups[$key]['tracked']++;
            }

            $groups[$key]['reach'] +=
                (int) ($row['reach'] ?? 0);

            $groups[$key]['interactions'] +=
                (int) ($row['interactions'] ?? 0);
        }

        foreach ($groups as &$group) {
            if ($group['reach'] > 0) {
                $group['engagement_rate'] =
                    (
                        $group['interactions']
                        / $group['reach']
                    ) * 100;
            }
        }

        unset($group);

        usort(
            $groups,
            static fn (
                array $left,
                array $right
            ): int =>
                $right['reach'] <=> $left['reach']
        );

        return array_slice(
            array_values($groups),
            0,
            8
        );
    }

    private function metricSnapshotSummary(
        ?array $metric
    ): array {
        if (!$metric) {
            return [
                'reach' => 0,
                'interactions' => 0,
                'engagement_rate' => 0.0,
                'saves' => 0,
                'shares' => 0,
            ];
        }

        $reach = (int) ($metric['reach'] ?? 0);
        $interactions =
            (int) ($metric['likes'] ?? 0)
            + (int) ($metric['comments'] ?? 0)
            + (int) ($metric['shares'] ?? 0)
            + (int) ($metric['saves'] ?? 0);

        return [
            'reach' => $reach,
            'interactions' => $interactions,
            'engagement_rate' => $reach > 0
                ? ($interactions / $reach) * 100
                : 0.0,
            'saves' => (int) ($metric['saves'] ?? 0),
            'shares' => (int) ($metric['shares'] ?? 0),
        ];
    }

    private function metricsTableReady(): bool
    {
        return db_connect()->tableExists(
            'content_post_metrics'
        );
    }

    private function metricInteger(string $field): int
    {
        $value = $this->request->getPost($field);

        if ($value === null || $value === '') {
            return 0;
        }

        return max(0, (int) $value);
    }

    private function publicationCandidates(): array
    {
        return db_connect()
            ->table('activities')
            ->select(
                'activities.id, activities.program_id, '
                . 'activities.title, activities.activity_date, '
                . 'activities.location, activities.status, '
                . 'activities.summary, '
                . 'programs.name AS program_name'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            )
            ->join(
                'content_posts',
                "content_posts.activity_id = activities.id "
                . "AND content_posts.template_type = "
                . "'social_publication'",
                'left'
            )
            ->where('content_posts.id', null)
            ->whereIn(
                'activities.status',
                ['planned', 'completed']
            )
            ->orderBy(
                'activities.activity_date',
                'DESC'
            )
            ->orderBy('activities.id', 'DESC')
            ->limit(6)
            ->get()
            ->getResultArray();
    }

    private function activityBriefData(array $activity): array
    {
        $isCompleted =
            ($activity['status'] ?? '') === 'completed';

        $activityTitle = trim(
            (string) ($activity['title'] ?? '')
        );

        $programName = trim(
            (string) ($activity['program_name'] ?? '')
        );

        $summary = trim(
            (string) ($activity['summary'] ?? '')
        );

        if ($summary === '') {
            $summary = trim(
                (string) ($activity['description'] ?? '')
            );
        }

        if ($summary === '') {
            $summary = trim(
                (string) ($activity['result'] ?? '')
            );
        }

        if ($summary === '') {
            $summary =
                'Kegiatan GARDA 01 yang melibatkan pemuda '
                . 'dan warga RW 01 Randugarut.';
        }

        $summary = mb_strimwidth(
            preg_replace('/\s+/', ' ', $summary),
            0,
            700,
            '…'
        );

        $eventDate = trim(
            (string) ($activity['activity_date'] ?? '')
        );

        $eventLocation = trim(
            (string) ($activity['location'] ?? '')
        );

        $dateText = $eventDate !== ''
            ? $this->indonesianDate($eventDate)
            : 'Tanggal menyesuaikan agenda kegiatan';

        $locationText = $eventLocation !== ''
            ? $eventLocation
            : 'Wilayah RW 01 Randugarut';

        $eventTitle = $isCompleted
            ? 'Dokumentasi ' . $activityTitle
            : 'Informasi ' . $activityTitle;

        $coverHook = $isCompleted
            ? 'Gerak bersama yang memberi dampak.'
            : 'Catat waktunya, mari bergerak bersama.';

        $contentGoal = $isCompleted
            ? 'Mendokumentasikan pelaksanaan '
                . $activityTitle
                . ' serta menunjukkan partisipasi, '
                . 'kebersamaan, dan dampaknya bagi lingkungan.'
            : 'Menginformasikan agenda '
                . $activityTitle
                . ' secara jelas dan mendorong pemuda serta '
                . 'warga untuk berpartisipasi.';

        $callToAction = $isCompleted
            ? 'Geser untuk melihat rangkaian kegiatan '
                . 'dan dampaknya.'
            : 'Simpan jadwalnya dan ikut berpartisipasi.';

        $caption = implode("\n\n", [
            $activityTitle,
            $summary,
            'Tanggal: ' . $dateText,
            'Lokasi: ' . $locationText,
            'Guyub • Bergerak • Berdampak.',
        ]);

        return [
            'program_id' => $activity['program_id'] ?? null,
            'activity_id' => $activity['id'] ?? null,
            'publication_type' => 'carousel',
            'canva_template_code' => $isCompleted
                ? 'DOC-01A'
                : 'INFO-01F',
            'category' => $isCompleted
                ? 'dokumentasi_kegiatan'
                : 'pengumuman',
            'event_title' => $eventTitle,
            'event_date' => $eventDate ?: null,
            'event_time' => null,
            'event_location' => $eventLocation ?: null,
            'activity_description' => $summary,
            'cover_hook' => $coverHook,
            'content_goal' => $contentGoal,
            'target_audience' =>
                'Pemuda RW 01, warga Randugarut, '
                . 'mitra, dan calon anggota',
            'call_to_action' => $callToAction,
            'owner' => $this->currentUser(),
            'reviewer' => 'Ketua / Koordinator Program',
            'priority' => 'normal',
            'title' => $activityTitle,
            'caption' => $caption,
            'hashtags' =>
                '#GARDA01 #KarangTarunaRW01 '
                . '#Randugarut #GuyubBergerakBerdampak',
            'mentions' => null,
            'alt_text' =>
                'Dokumentasi atau informasi kegiatan '
                . $activityTitle
                . (
                    $programName !== ''
                        ? ' dalam program ' . $programName
                        : ''
                )
                . ' di ' . $locationText . '.',
            'notes' =>
                'Brief awal dibuat otomatis dari Data Kegiatan '
                . 'Portal. Periksa kembali fakta, caption, '
                . 'aset, dan jadwal sebelum dikirim untuk review.',
        ];
    }

    private function publicationCalendarTime(
        array $post
    ): ?string {
        $value = null;

        if (
            ($post['workflow_status'] ?? '') === 'published'
            && !empty($post['published_at'])
        ) {
            $value = $post['published_at'];
        } elseif (!empty($post['scheduled_at'])) {
            $value = $post['scheduled_at'];
        } elseif (!empty($post['event_date'])) {
            return null;
        } elseif (!empty($post['created_at'])) {
            $value = $post['created_at'];
        }

        if (empty($value)) {
            return null;
        }

        $timestamp = strtotime((string) $value);

        return $timestamp
            ? date('H.i', $timestamp) . ' WIB'
            : null;
    }

    private function indonesianMonthLabel(
        \DateTimeImmutable $date
    ): string {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[(int) $date->format('n')]
            . ' '
            . $date->format('Y');
    }

    private function indonesianDate(string $value): string
    {
        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return $value;
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return date('d', $timestamp)
            . ' '
            . $months[(int) date('n', $timestamp)]
            . ' '
            . date('Y', $timestamp);
    }

    private function saveUploadedAssets(int $postId, bool $required = false): int
    {
        $files = $this->request->getFiles();
        $uploads = $files['content_images'] ?? [];

        if (!is_array($uploads)) {
            $uploads = [$uploads];
        }

        $validFiles = [];

        foreach ($uploads as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            if (!in_array($file->getMimeType(), [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
            ], true)) {
                throw new \RuntimeException(
                    'Format aset harus JPG, JPEG, PNG, atau WEBP.'
                );
            }

            if ($file->getSizeByUnit('mb') > 6) {
                throw new \RuntimeException(
                    'Ukuran setiap aset maksimal 6MB.'
                );
            }

            $validFiles[] = $file;
        }

        if ($required && $validFiles === []) {
            throw new \RuntimeException('Pilih minimal satu gambar yang valid.');
        }

        $existingCount = $this->assetModel
            ->where('content_post_id', $postId)
            ->countAllResults();

        if ($existingCount + count($validFiles) > 10) {
            throw new \RuntimeException(
                'Maksimal 10 aset untuk satu publikasi.'
            );
        }

        if ($validFiles === []) {
            return 0;
        }

        $uploadDir = FCPATH . 'uploads/content_studio';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $sortOrder = $existingCount + 1;

        foreach ($validFiles as $file) {
            $fileName = $file->getRandomName();
            $file->move($uploadDir, $fileName);

            $this->assetModel->insert([
                'content_post_id' => $postId,
                'image_path' => 'uploads/content_studio/' . $fileName,
                'original_name' => $file->getClientName(),
                'sort_order' => $sortOrder++,
            ]);
        }

        return count($validFiles);
    }

    private function canTransitionTo(
        string $currentStatus,
        string $targetStatus
    ): bool {
        return auth_can(
            $this->transitionPermission(
                $currentStatus,
                $targetStatus
            )
        );
    }

    private function transitionPermission(
        string $currentStatus,
        string $targetStatus
    ): string {
        if (
            $currentStatus === 'archived'
            && $targetStatus === 'brief'
        ) {
            return 'publications.archive';
        }

        return match ($targetStatus) {
            'revision' => 'publications.review',
            'approved' => 'publications.approve',
            'scheduled',
            'published' => 'publications.publish',
            'archived' => 'publications.archive',
            default => 'publications.workflow',
        };
    }

    /**
     * @param list<string> $allowedDomains
     */
    private function isAllowedPlatformUrl(
        string $url,
        array $allowedDomains
    ): bool {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = mb_strtolower(
            (string) parse_url($url, PHP_URL_SCHEME)
        );

        $host = mb_strtolower(
            (string) parse_url($url, PHP_URL_HOST)
        );

        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        foreach ($allowedDomains as $domain) {
            if (
                $host === $domain
                || str_ends_with($host, '.' . $domain)
            ) {
                return true;
            }
        }

        return false;
    }

    private function nullableId(string $field): ?int
    {
        $value = (int) $this->request->getPost($field);

        return $value > 0 ? $value : null;
    }

    private function nullableString(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value !== '' ? $value : null;
    }

    private function normalizeDateTime($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp
            ? date('Y-m-d H:i:s', $timestamp)
            : null;
    }

    private function buildContentCode(int $id): string
    {
        return sprintf('PUB-%s-%04d', date('Ym'), $id);
    }

    private function currentUser(): string
    {
        return (string) (
            session()->get('name')
            ?? session()->get('full_name')
            ?? session()->get('user_name')
            ?? session()->get('email')
            ?? 'Pengurus GARDA 01'
        );
    }
}
