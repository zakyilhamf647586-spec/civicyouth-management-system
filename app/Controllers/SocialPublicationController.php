<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use App\Models\ContentAssetModel;
use App\Models\ContentPostModel;
use App\Models\ProgramModel;
use Config\SocialMedia;

class SocialPublicationController extends BaseController
{
    protected ContentPostModel $postModel;
    protected ContentAssetModel $assetModel;
    protected ProgramModel $programModel;
    protected ActivityModel $activityModel;
    protected SocialMedia $socialMedia;
    protected ?string $publicationValidationError = null;

    public function __construct()
    {
        $this->postModel = new ContentPostModel();
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

        return view('publications/index', [
            'title' => 'Publikasi Sosial',
            'posts' => $posts,
            'pager' => $model->pager,
            'summary' => $this->publicationSummary(),
            'activityCandidates' => $this->publicationCandidates(),
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

        try {
            $this->saveUploadedAssets((int) $postId);
        } catch (\RuntimeException $e) {
            return redirect()->to('/publications/' . $postId)
                ->with('error', $e->getMessage());
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

        $this->postModel->update($id, $this->publicationPayload());

        try {
            $this->saveUploadedAssets((int) $id);
        } catch (\RuntimeException $e) {
            return redirect()->to('/publications/' . $id)
                ->with('error', $e->getMessage());
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
