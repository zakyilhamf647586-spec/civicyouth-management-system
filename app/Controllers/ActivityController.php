<?php

namespace App\Controllers;

use App\Models\ActivityImageModel;
use App\Models\ActivityModel;
use App\Models\ProgramModel;
use App\Libraries\SecureUploadService;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

class ActivityController extends BaseController
{
    protected ActivityModel $activityModel;
    protected ProgramModel $programModel;
    protected SecureUploadService $uploadService;

    public function __construct()
    {
        $this->activityModel = new ActivityModel();
        $this->programModel  = new ProgramModel();
        $this->uploadService = new SecureUploadService();
    }

    public function index()
    {
        $keyword = trim(
            (string) $this->request->getGet('keyword')
        );

        $status = trim(
            (string) $this->request->getGet('status')
        );

        $publicationStatus = trim(
            (string) $this->request->getGet(
                'publication_status'
            )
        );

        $programId = trim(
            (string) $this->request->getGet('program_id')
        );

        $dateFrom = trim(
            (string) $this->request->getGet('date_from')
        );

        $dateTo = trim(
            (string) $this->request->getGet('date_to')
        );

        $builder = $this->activityModel
            ->select(
                'activities.*, ' .
                'programs.name AS program_name, ' .
                'programs.slug AS program_slug, ' .
                'programs.label AS program_label'
            )
            ->join(
                'programs',
                'programs.id = activities.program_id',
                'left'
            );

        if ($keyword !== '') {
            $builder
                ->groupStart()
                ->like('activities.title', $keyword)
                ->orLike('activities.location', $keyword)
                ->orLike('activities.summary', $keyword)
                ->orLike('activities.description', $keyword)
                ->orLike('activities.result', $keyword)
                ->groupEnd();
        }

        if (in_array(
            $status,
            ActivityModel::EXECUTION_STATUSES,
            true
        )) {
            $builder->where('activities.status', $status);
        }

        if (in_array(
            $publicationStatus,
            ActivityModel::PUBLICATION_STATUSES,
            true
        )) {
            $builder->where(
                'activities.publication_status',
                $publicationStatus
            );
        }

        if (
            $programId !== ''
            && ctype_digit($programId)
            && (int) $programId > 0
        ) {
            $builder->where(
                'activities.program_id',
                (int) $programId
            );
        }

        if ($this->isValidDate($dateFrom)) {
            $builder->where(
                'activities.activity_date >=',
                $dateFrom
            );
        }

        if ($this->isValidDate($dateTo)) {
            $builder->where(
                'activities.activity_date <=',
                $dateTo
            );
        }

        $activities = $builder
            ->orderBy('activities.activity_date', 'DESC')
            ->orderBy('activities.id', 'DESC')
            ->paginate(10, 'activities');

        $programs = $this->programModel
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('activities/index', [
            'title' => 'Data Kegiatan',
            'activities' => $activities,
            'pager' => $this->activityModel->pager,
            'programs' => $programs,
            'keyword' => $keyword,
            'status' => $status,
            'publicationStatus' => $publicationStatus,
            'selectedProgram' => $programId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'executionStatusLabels' =>
                ActivityModel::executionStatusLabels(),
            'publicationStatusLabels' =>
                ActivityModel::publicationStatusLabels(),
        ]);
    }

    public function qualityAudit()
    {
        $readiness = trim((string) $this->request->getGet('readiness'));
        $keyword = trim((string) $this->request->getGet('keyword'));

        if (!in_array($readiness, ['incomplete', 'review', 'ready'], true)) {
            $readiness = '';
        }

        $rows = db_connect()
            ->table('activities')
            ->select(
                'activities.*, programs.name AS program_name, '
                . 'COUNT(activity_images.id) AS gallery_count, '
                . "SUM(CASE WHEN activity_images.caption IS NOT NULL "
                . "AND TRIM(activity_images.caption) != '' THEN 1 ELSE 0 END) "
                . 'AS captioned_images',
                false
            )
            ->join('programs', 'programs.id = activities.program_id', 'left')
            ->join('activity_images', 'activity_images.activity_id = activities.id', 'left')
            ->groupBy('activities.id')
            ->orderBy('activities.activity_date', 'DESC')
            ->orderBy('activities.id', 'DESC')
            ->get()
            ->getResultArray();

        $items = [];
        $summary = [
            'total' => count($rows),
            'incomplete' => 0,
            'review' => 0,
            'ready' => 0,
            'featured_not_ready' => 0,
        ];

        foreach ($rows as $row) {
            $quality = $this->evaluateActivityQuality($row);
            $item = array_merge($row, $quality);
            $summary[$quality['readiness']]++;

            if ((int) ($row['is_featured'] ?? 0) === 1 && $quality['readiness'] !== 'ready') {
                $summary['featured_not_ready']++;
            }

            if ($readiness !== '' && $quality['readiness'] !== $readiness) {
                continue;
            }

            if ($keyword !== '') {
                $haystack = mb_strtolower(implode(' ', [
                    $row['title'] ?? '',
                    $row['location'] ?? '',
                    $row['program_name'] ?? '',
                    implode(' ', array_column($quality['issues'], 'message')),
                ]));

                if (!str_contains($haystack, mb_strtolower($keyword))) {
                    continue;
                }
            }

            $items[] = $item;
        }

        return view('activities/quality', [
            'title' => 'Audit Kualitas Kegiatan',
            'items' => $items,
            'summary' => $summary,
            'selectedReadiness' => $readiness,
            'keyword' => $keyword,
        ]);
    }

    public function create()
    {
        $programs = $this->programModel
            ->where('status', 'published')
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('activities/create', [
            'title' => 'Tambah Kegiatan',
            'programs' => $programs,
            'executionStatusLabels' =>
                ActivityModel::executionStatusLabels(),
            'publicationStatusLabels' =>
                ActivityModel::publicationStatusLabels(),
            'publicationStatusDescriptions' =>
                ActivityModel::publicationStatusDescriptions(),
        ]);
    }

    public function store()
    {
        if (!$this->validate($this->baseValidationRules())) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'errors',
                    $this->validator->getErrors()
                );
        }

        $action = $this->normalizeWorkflowAction(
            (string) $this->request->getPost(
                'workflow_action'
            ),
            false
        );

        if ($deniedResponse = $this->denyUnauthorizedWorkflowAction(
            $action,
            false
        )) {
            return $deniedResponse;
        }

        $newFile = null;

        try {
            $programId = $this->normalizeProgramId(
                $this->request->getPost('program_id')
            );

            $this->ensureProgramExists($programId);

            $baseData = $this->buildBaseData($programId);

            $workflowData = $this->buildWorkflowData(
                $action,
                null,
                $baseData
            );

            $newFile = $this->processNewDocumentationUpload();

            if ($newFile !== null) {
                $baseData['documentation_file'] = $newFile;
            }

            $insertId = $this->activityModel->insert(
                array_merge($baseData, $workflowData),
                true
            );

            if ($insertId === false) {
                $this->deleteDocumentationFile($newFile);

                throw new RuntimeException(
                    'Data kegiatan gagal disimpan.'
                );
            }

            return redirect()->to('/activities')
                ->with(
                    'success',
                    $this->successMessageForAction(
                        $action,
                        'ditambahkan'
                    )
                );
        } catch (RuntimeException $exception) {
            if ($newFile !== null) {
                $this->deleteDocumentationFile($newFile);
            }

            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    $exception->getMessage(),
                ]);
        } catch (Throwable $exception) {
            if ($newFile !== null) {
                $this->deleteDocumentationFile($newFile);
            }

            log_message(
                'error',
                'Gagal menyimpan kegiatan: {message}',
                ['message' => $exception->getMessage()]
            );

            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'Terjadi kendala saat menyimpan kegiatan.',
                ]);
        }
    }

    public function edit(int $id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with(
                    'error',
                    'Data kegiatan tidak ditemukan.'
                );
        }

        $programs = $this->programModel
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('activities/edit', [
            'title' => 'Edit Kegiatan',
            'activity' => $activity,
            'programs' => $programs,
            'executionStatusLabels' =>
                ActivityModel::executionStatusLabels(),
            'publicationStatusLabels' =>
                ActivityModel::publicationStatusLabels(),
            'publicationStatusDescriptions' =>
                ActivityModel::publicationStatusDescriptions(),
        ]);
    }

    public function update(int $id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with(
                    'error',
                    'Data kegiatan tidak ditemukan.'
                );
        }

        if (!$this->validate($this->baseValidationRules())) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'errors',
                    $this->validator->getErrors()
                );
        }

        $action = $this->normalizeWorkflowAction(
            (string) $this->request->getPost(
                'workflow_action'
            ),
            true
        );

        if ($deniedResponse = $this->denyUnauthorizedWorkflowAction(
            $action,
            true
        )) {
            return $deniedResponse;
        }

        $newFile = null;

        try {
            $programId = $this->normalizeProgramId(
                $this->request->getPost('program_id')
            );

            $this->ensureProgramExists($programId);

            $baseData = $this->buildBaseData($programId);

            $workflowData = $this->buildWorkflowData(
                $action,
                $activity,
                $baseData
            );

            $newFile = $this->processNewDocumentationUpload();

            if ($newFile !== null) {
                $baseData['documentation_file'] = $newFile;
            } else {
                $baseData['documentation_file'] =
                    $activity['documentation_file'] ?? null;
            }

            $updated = $this->activityModel->update(
                $id,
                array_merge($baseData, $workflowData)
            );

            if ($updated === false) {
                $this->deleteDocumentationFile($newFile);

                throw new RuntimeException(
                    'Data kegiatan gagal diperbarui.'
                );
            }

            if ($newFile !== null) {
                $this->deleteDocumentationFile(
                    $activity['documentation_file'] ?? null
                );
            }

            return redirect()->to('/activities')
                ->with(
                    'success',
                    $this->successMessageForAction(
                        $action,
                        'diperbarui'
                    )
                );
        } catch (RuntimeException $exception) {
            if ($newFile !== null) {
                $this->deleteDocumentationFile($newFile);
            }

            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    $exception->getMessage(),
                ]);
        } catch (Throwable $exception) {
            if ($newFile !== null) {
                $this->deleteDocumentationFile($newFile);
            }

            log_message(
                'error',
                'Gagal memperbarui kegiatan {id}: {message}',
                [
                    'id' => $id,
                    'message' => $exception->getMessage(),
                ]
            );

            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'Terjadi kendala saat memperbarui kegiatan.',
                ]);
        }
    }

    public function submitReview(int $id)
    {
        $activity = $this->findActivityOrRedirect($id);

        if ($activity instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $activity;
        }

        try {
            $this->assertReadyForPublication($activity);

            $this->activityModel->update($id, [
                'publication_status' => 'review',
                'is_public' => 0,
                'scheduled_at' => null,
                'published_at' => null,
            ]);

            return redirect()->to('/activities')
                ->with(
                    'success',
                    'Kegiatan berhasil dikirim untuk ditinjau.'
                );
        } catch (RuntimeException $exception) {
            return redirect()->to('/activities/edit/' . $id)
                ->with('errors', [
                    $exception->getMessage(),
                ]);
        }
    }

    public function publish(int $id)
    {
        $activity = $this->findActivityOrRedirect($id);

        if ($activity instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $activity;
        }

        try {
            $this->assertReadyForPublication($activity);

            $this->activityModel->update($id, [
                'publication_status' => 'published',
                'is_public' => 1,
                'scheduled_at' => null,
                'published_at' => date('Y-m-d H:i:s'),
                'review_notes' => null,
            ]);

            return redirect()->to('/activities')
                ->with(
                    'success',
                    'Kegiatan berhasil dipublikasikan.'
                );
        } catch (RuntimeException $exception) {
            return redirect()->to('/activities/edit/' . $id)
                ->with('errors', [
                    $exception->getMessage(),
                ]);
        }
    }

    public function draft(int $id)
    {
        $activity = $this->findActivityOrRedirect($id);

        if ($activity instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $activity;
        }

        $reviewNotes = trim(
            (string) $this->request->getPost('review_notes')
        );

        if (mb_strlen($reviewNotes) > 2000) {
            return redirect()->back()
                ->with(
                    'error',
                    'Catatan tinjauan maksimal 2000 karakter.'
                );
        }

        $this->activityModel->update($id, [
            'publication_status' => 'draft',
            'is_public' => 0,
            'scheduled_at' => null,
            'published_at' => null,
            'review_notes' => $reviewNotes !== ''
                ? $reviewNotes
                : ($activity['review_notes'] ?? null),
        ]);

        return redirect()->to('/activities')
            ->with(
                'success',
                'Kegiatan dikembalikan ke status draft.'
            );
    }

    public function archive(int $id)
    {
        $activity = $this->findActivityOrRedirect($id);

        if ($activity instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $activity;
        }

        $this->activityModel->update($id, [
            'publication_status' => 'archived',
            'is_public' => 0,
            'scheduled_at' => null,
        ]);

        return redirect()->to('/activities')
            ->with(
                'success',
                'Kegiatan berhasil diarsipkan.'
            );
    }

    public function delete(int $id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with(
                    'error',
                    'Data kegiatan tidak ditemukan.'
                );
        }

        $imageModel = new ActivityImageModel();

        $galleryImages = $imageModel
            ->where('activity_id', $id)
            ->findAll();

        $filesToDelete = [];

        if (!empty($activity['documentation_file'])) {
            $filesToDelete[] =
                $activity['documentation_file'];
        }

        foreach ($galleryImages as $image) {
            if (!empty($image['image_file'])) {
                $filesToDelete[] = $image['image_file'];
            }
        }

        $filesToDelete = array_values(
            array_unique($filesToDelete)
        );

        if (!$this->activityModel->delete($id)) {
            return redirect()->to('/activities')
                ->with(
                    'error',
                    'Data kegiatan gagal dihapus.'
                );
        }

        foreach ($filesToDelete as $fileName) {
            $this->deleteDocumentationFile($fileName);
        }

        return redirect()->to('/activities')
            ->with(
                'success',
                'Data kegiatan berhasil dihapus.'
            );
    }

    private function baseValidationRules(): array
    {
        return [
            'program_id' => [
                'label' => 'Pilar program',
                'rules' => 'permit_empty|is_natural_no_zero',
            ],
            'title' => [
                'label' => 'Nama kegiatan',
                'rules' =>
                    'required|min_length[3]|max_length[150]',
            ],
            'activity_date' => [
                'label' => 'Tanggal kegiatan',
                'rules' => 'required|valid_date[Y-m-d]',
            ],
            'location' => [
                'label' => 'Lokasi kegiatan',
                'rules' => 'required|max_length[200]',
            ],
            'summary' => [
                'label' => 'Ringkasan publik',
                'rules' => 'permit_empty|max_length[220]',
            ],
            'status' => [
                'label' => 'Status kegiatan',
                'rules' =>
                    'required|in_list[planned,completed,cancelled]',
            ],
            'documentation_link' => [
                'label' => 'Tautan dokumentasi',
                'rules' => 'permit_empty|valid_url_strict',
            ],
            'review_notes' => [
                'label' => 'Catatan tinjauan',
                'rules' => 'permit_empty|max_length[2000]',
            ],
        ];
    }

    private function buildBaseData(?int $programId): array
    {
        return [
            'program_id' => $programId,
            'title' => trim(
                (string) $this->request->getPost('title')
            ),
            'summary' => trim(
                (string) $this->request->getPost('summary')
            ),
            'description' => trim(
                (string) $this->request->getPost('description')
            ),
            'activity_date' =>
                $this->request->getPost('activity_date'),
            'location' => trim(
                (string) $this->request->getPost('location')
            ),
            'status' =>
                $this->request->getPost('status'),
            'result' => trim(
                (string) $this->request->getPost('result')
            ),
            'documentation_link' => trim(
                (string) $this->request->getPost(
                    'documentation_link'
                )
            ),
            'is_featured' =>
                $this->request->getPost('is_featured') === '1'
                    ? 1
                    : 0,
            'review_notes' => trim(
                (string) $this->request->getPost(
                    'review_notes'
                )
            ) ?: null,
        ];
    }

    private function buildWorkflowData(
        string $action,
        ?array $existingActivity,
        array $baseData
    ): array {
        if ($action === 'save_changes') {
            $currentStatus =
                $existingActivity['publication_status']
                ?? 'draft';

            if (in_array(
                $currentStatus,
                ['review', 'published', 'scheduled'],
                true
            )) {
                $candidate = array_merge(
                    $existingActivity,
                    $baseData
                );

                $this->assertReadyForPublication($candidate);
            }

            $scheduledAt =
                $existingActivity['scheduled_at'] ?? null;

            if ($currentStatus === 'scheduled') {
                $postedSchedule = trim(
                    (string) $this->request->getPost(
                        'scheduled_at'
                    )
                );

                if ($postedSchedule !== '') {
                    $scheduledAt =
                        $this->parseScheduledAt(
                            $postedSchedule
                        );
                }
            }

            return [
                'publication_status' => $currentStatus,
                'is_public' => (int) (
                    $existingActivity['is_public'] ?? 0
                ),
                'scheduled_at' => $scheduledAt,
                'published_at' =>
                    $existingActivity['published_at'] ?? null,
            ];
        }

        if ($action === 'save_draft') {
            return [
                'publication_status' => 'draft',
                'is_public' => 0,
                'scheduled_at' => null,
                'published_at' => null,
            ];
        }

        $candidate = array_merge(
            $existingActivity ?? [],
            $baseData
        );

        $this->assertReadyForPublication($candidate);

        if ($action === 'submit_review') {
            return [
                'publication_status' => 'review',
                'is_public' => 0,
                'scheduled_at' => null,
                'published_at' => null,
            ];
        }

        if ($action === 'publish_now') {
            return [
                'publication_status' => 'published',
                'is_public' => 1,
                'scheduled_at' => null,
                'published_at' => date('Y-m-d H:i:s'),
                'review_notes' => null,
            ];
        }

        if ($action === 'schedule') {
            $scheduledAt = $this->parseScheduledAt(
                trim(
                    (string) $this->request->getPost(
                        'scheduled_at'
                    )
                )
            );

            if (strtotime($scheduledAt) <= time()) {
                throw new RuntimeException(
                    'Jadwal publikasi harus berada di waktu mendatang.'
                );
            }

            return [
                'publication_status' => 'scheduled',
                'is_public' => 1,
                'scheduled_at' => $scheduledAt,
                'published_at' => null,
            ];
        }

        throw new RuntimeException(
            'Aksi publikasi tidak dikenali.'
        );
    }

    private function assertReadyForPublication(
        array $activity
    ): void {
        $errors = [];

        if (empty($activity['program_id'])) {
            $errors[] = 'Pilar program wajib dipilih.';
        }

        $summary = trim(
            (string) ($activity['summary'] ?? '')
        );

        if (mb_strlen($summary) < 40) {
            $errors[] =
                'Ringkasan publik minimal 40 karakter.';
        }

        $description = trim(
            (string) ($activity['description'] ?? '')
        );

        if (mb_strlen($description) < 50) {
            $errors[] =
                'Deskripsi kegiatan minimal 50 karakter sebelum ditinjau atau diterbitkan.';
        }

        if (empty($activity['activity_date'])) {
            $errors[] = 'Tanggal kegiatan wajib diisi.';
        }

        if (trim((string) ($activity['location'] ?? '')) === '') {
            $errors[] = 'Lokasi kegiatan wajib diisi.';
        }

        if ($errors !== []) {
            throw new RuntimeException(
                'Konten belum siap dipublikasikan: '
                . implode(' ', $errors)
            );
        }
    }

    private function denyUnauthorizedWorkflowAction(
        string $action,
        bool $isUpdate
    ) {
        $requiredPermission = match ($action) {
            'submit_review' => 'activities.submit_review',
            'publish_now',
            'schedule' => 'activities.publish',
            'save_draft' => $isUpdate
                ? 'activities.return_to_draft'
                : null,
            default => null,
        };

        if (
            $requiredPermission === null
            || auth_can($requiredPermission)
        ) {
            return null;
        }

        $permissionLabel = authorization()
            ->permissionLabel($requiredPermission);

        return redirect()->to('/activities')
            ->with(
                'error',
                'Akses ditolak. Peran Anda tidak memiliki izin untuk '
                . $permissionLabel
                . '.'
            );
    }

    private function normalizeWorkflowAction(
        string $action,
        bool $allowSaveChanges
    ): string {
        $allowed = [
            'save_draft',
            'submit_review',
            'publish_now',
            'schedule',
        ];

        if ($allowSaveChanges) {
            $allowed[] = 'save_changes';
        }

        if (!in_array($action, $allowed, true)) {
            return $allowSaveChanges
                ? 'save_changes'
                : 'save_draft';
        }

        return $action;
    }

    private function normalizeProgramId($programId): ?int
    {
        if (
            $programId === null
            || $programId === ''
            || !ctype_digit((string) $programId)
        ) {
            return null;
        }

        $programId = (int) $programId;

        return $programId > 0 ? $programId : null;
    }

    private function ensureProgramExists(?int $programId): void
    {
        if ($programId === null) {
            return;
        }

        if (!$this->programModel->find($programId)) {
            throw new RuntimeException(
                'Pilar program yang dipilih tidak ditemukan.'
            );
        }
    }

    private function parseScheduledAt(string $value): string
    {
        if ($value === '') {
            throw new RuntimeException(
                'Waktu publikasi terjadwal wajib diisi.'
            );
        }

        $date = DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i',
            $value
        );

        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date === false
            || (
                is_array($errors)
                && (
                    $errors['warning_count'] > 0
                    || $errors['error_count'] > 0
                )
            )
        ) {
            throw new RuntimeException(
                'Format waktu publikasi terjadwal tidak valid.'
            );
        }

        return $date->format('Y-m-d H:i:s');
    }

    private function processNewDocumentationUpload(): ?string
    {
        $file = $this->request->getFile('documentation_file');

        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $stored = $this->uploadService->storeImage(
            $file,
            'uploads/activities',
            [
                'max_bytes' => 4 * 1024 * 1024,
                'max_pixels' => 36_000_000,
                'target_max_width' => 2400,
                'target_max_height' => 1800,
            ]
        );

        return $stored['file_name'];
    }

    private function deleteDocumentationFile(
        ?string $fileName
    ): void {
        if (empty($fileName)) {
            return;
        }

        $this->uploadService->deleteManagedFile(
            'uploads/activities/' . basename($fileName),
            ['uploads/activities']
        );
    }

    /** @return array{readiness:string,label:string,issues:list<array{severity:string,message:string}>} */
    private function evaluateActivityQuality(array $activity): array
    {
        $issues = [];
        $critical = false;

        $title = trim((string) ($activity['title'] ?? ''));
        $summary = trim((string) ($activity['summary'] ?? ''));
        $description = trim((string) ($activity['description'] ?? ''));
        $result = trim((string) ($activity['result'] ?? ''));
        $location = trim((string) ($activity['location'] ?? ''));
        $executionStatus = (string) ($activity['status'] ?? '');
        $publicationStatus = (string) (
            $activity['publication_status'] ?? ''
        );
        $isPublic = (int) ($activity['is_public'] ?? 0);
        $galleryCount = (int) ($activity['gallery_count'] ?? 0);
        $captionedImages = (int) ($activity['captioned_images'] ?? 0);
        $genericTitles = ['kegiatan', 'agenda', 'rapat', 'dokumentasi', 'acara'];

        $add = static function (string $severity, string $message) use (&$issues): void {
            $issues[] = ['severity' => $severity, 'message' => $message];
        };

        if (mb_strlen($title) < 10 || in_array(mb_strtolower($title), $genericTitles, true)) {
            $add('critical', 'Judul terlalu umum atau terlalu pendek.');
            $critical = true;
        }

        if (empty($activity['program_id'])) {
            $add('critical', 'Pilar/program belum dipilih.');
            $critical = true;
        }

        if (!$this->isValidDate((string) ($activity['activity_date'] ?? ''))) {
            $add('critical', 'Tanggal kegiatan tidak valid.');
            $critical = true;
        }

        if ($location === '') {
            $add('critical', 'Lokasi kegiatan belum diisi.');
            $critical = true;
        }

        if (empty($activity['documentation_file']) && $galleryCount < 1) {
            $add('critical', 'Cover atau galeri dokumentasi belum tersedia.');
            $critical = true;
        }

        if (!in_array($executionStatus, ActivityModel::EXECUTION_STATUSES, true)) {
            $add('critical', 'Status pelaksanaan tidak valid.');
            $critical = true;
        }

        if (!in_array($publicationStatus, ActivityModel::PUBLICATION_STATUSES, true)) {
            $add('critical', 'Status publikasi tidak valid.');
            $critical = true;
        }

        if ($publicationStatus === 'published' && $isPublic !== 1) {
            $add('critical', 'Status Dipublikasikan tetapi visibilitas publik tidak aktif.');
            $critical = true;
        }

        if (
            in_array($publicationStatus, ['draft', 'review', 'archived'], true)
            && $isPublic === 1
        ) {
            $add('critical', 'Konten nonpublik masih memiliki penanda visibilitas publik aktif.');
            $critical = true;
        }

        if ($publicationStatus === 'scheduled') {
            $scheduledAt = trim((string) ($activity['scheduled_at'] ?? ''));
            $scheduledTimestamp = $scheduledAt !== ''
                ? strtotime($scheduledAt)
                : false;

            if ($scheduledTimestamp === false) {
                $add('critical', 'Status Dijadwalkan belum memiliki waktu tayang yang valid.');
                $critical = true;
            } elseif ($scheduledTimestamp <= time()) {
                $add('warning', 'Jadwal tayang sudah terlewati; periksa apakah status perlu diperbarui.');
            }

            if ($isPublic !== 1) {
                $add('critical', 'Konten terjadwal belum mengaktifkan visibilitas publik.');
                $critical = true;
            }
        }

        $summaryLength = mb_strlen($summary);

        if ($summaryLength < 120 || $summaryLength > 220) {
            $add('warning', 'Ringkasan idealnya 120–220 karakter. Saat ini ' . $summaryLength . ' karakter.');
        }

        if (mb_strlen($description) < 80) {
            $add('warning', 'Cerita/deskripsi kegiatan masih terlalu singkat.');
        }

        if ($executionStatus === 'completed' && mb_strlen($result) < 30) {
            $add('warning', 'Hasil atau dampak kegiatan selesai belum cukup jelas.');
        }

        if ($galleryCount > 0 && $captionedImages < $galleryCount) {
            $add('warning', ($galleryCount - $captionedImages) . ' foto galeri belum memiliki caption/teks alternatif.');
        }

        if (
            $publicationStatus === 'published'
            && empty($activity['published_at'])
        ) {
            $add('warning', 'Waktu publikasi belum tercatat.');
        }

        if ((int) ($activity['is_featured'] ?? 0) === 1) {
            if ($critical) {
                $add('critical', 'Kegiatan unggulan belum memenuhi data inti.');
            }

            if (!in_array($publicationStatus, ['published', 'scheduled'], true)) {
                $add('warning', 'Penanda unggulan aktif pada konten yang belum diterbitkan.');
            }
        }

        if ($critical) {
            return [
                'readiness' => 'incomplete',
                'label' => 'Belum Lengkap',
                'issues' => $issues,
            ];
        }

        if ($issues !== []) {
            return [
                'readiness' => 'review',
                'label' => 'Perlu Diperiksa',
                'issues' => $issues,
            ];
        }

        return [
            'readiness' => 'ready',
            'label' => 'Siap Dipublikasikan',
            'issues' => [],
        ];
    }

    private function findActivityOrRedirect(int $id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with(
                    'error',
                    'Data kegiatan tidak ditemukan.'
                );
        }

        return $activity;
    }

    private function successMessageForAction(
        string $action,
        string $fallbackVerb
    ): string {
        return match ($action) {
            'save_draft' =>
                'Kegiatan berhasil disimpan sebagai draft.',
            'submit_review' =>
                'Kegiatan berhasil dikirim untuk ditinjau.',
            'publish_now' =>
                'Kegiatan berhasil dipublikasikan.',
            'schedule' =>
                'Kegiatan berhasil dijadwalkan.',
            default =>
                'Data kegiatan berhasil ' . $fallbackVerb . '.',
        };
    }

    private function isValidDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $value
        );

        return $date !== false
            && $date->format('Y-m-d') === $value;
    }
}
