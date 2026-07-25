<?php

namespace App\Controllers;

use App\Models\PublicPageModel;
use App\Models\PublicPageSectionModel;
use App\Models\UserModel;
use Config\PublicCms;
use RuntimeException;

class PublicPageController extends BaseController
{
    protected PublicPageModel $pageModel;
    protected PublicPageSectionModel $sectionModel;
    protected UserModel $userModel;
    protected PublicCms $cmsConfig;

    /**
     * @var list<string>
     */
    private array $validWorkflowStatuses = [
        'draft',
        'in_review',
        'changes_requested',
        'approved',
        'published',
    ];

    public function __construct()
    {
        $this->pageModel = new PublicPageModel();
        $this->sectionModel =
            new PublicPageSectionModel();
        $this->userModel = new UserModel();
        $this->cmsConfig = new PublicCms();
    }

    public function index()
    {
        $ready = $this->cmsReady();
        $reviewReady = $ready
            && $this->reviewWorkflowReady();

        $pages = [];
        $workflowCounts = $this->emptyWorkflowCounts();

        if ($ready) {
            foreach (
                $this->cmsConfig->pages as
                $pageKey => $definition
            ) {
                $page = $this->pageModel
                    ->findByKey($pageKey);

                if (!$page) {
                    continue;
                }

                $page['section_count'] =
                    $this->sectionModel
                        ->where(
                            'public_page_id',
                            (int) $page['id']
                        )
                        ->countAllResults();

                $page['workflow_status'] =
                    $reviewReady
                        ? $this->normalizeWorkflowStatus(
                            (string) (
                                $page['workflow_status']
                                ?? ''
                            ),
                            $page
                        )
                        : $this->legacyWorkflowStatus(
                            $page
                        );

                $status = $page['workflow_status'];

                if (isset($workflowCounts[$status])) {
                    $workflowCounts[$status]++;
                }

                $pages[] = $page;
            }
        }

        return view('public_pages/index', [
            'title' => 'Kelola Halaman Publik',
            'ready' => $ready,
            'reviewReady' => $reviewReady,
            'pages' => $pages,
            'workflowCounts' => $workflowCounts,
            'workflowLabels' =>
                $this->workflowLabels(),
        ]);
    }

    public function reviewQueue()
    {
        $this->assertReviewWorkflowReady();

        $pages = $this->pageModel
            ->whereIn('workflow_status', [
                'in_review',
                'approved',
                'changes_requested',
            ])
            ->orderBy(
                "FIELD(
                    workflow_status,
                    'in_review',
                    'approved',
                    'changes_requested'
                )",
                '',
                false
            )
            ->orderBy('submitted_at', 'ASC')
            ->findAll();

        $userNames = $this->userNamesForPages(
            $pages
        );

        return view('public_pages/review', [
            'title' => 'Review Halaman Publik',
            'pages' => $pages,
            'userNames' => $userNames,
            'workflowLabels' =>
                $this->workflowLabels(),
        ]);
    }

    public function edit(string $pageKey)
    {
        $this->assertReady();

        $definition = $this->definition($pageKey);
        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            throw new RuntimeException(
                'Halaman CMS tidak ditemukan.'
            );
        }

        $page['workflow_status'] =
            $this->reviewWorkflowReady()
                ? $this->normalizeWorkflowStatus(
                    (string) (
                        $page['workflow_status']
                        ?? ''
                    ),
                    $page
                )
                : $this->legacyWorkflowStatus($page);

        $sections = $this->sectionModel
            ->where(
                'public_page_id',
                (int) $page['id']
            )
            ->orderBy('display_order', 'ASC')
            ->findAll();

        $sectionMap = [];

        foreach ($sections as $section) {
            $content = json_decode(
                (string) (
                    $section['draft_content'] ?? ''
                ),
                true
            );

            $section['draft_data'] = is_array($content)
                ? $content
                : [];

            $sectionMap[$section['section_key']] =
                $section;
        }

        return view('public_pages/edit', [
            'title' =>
                'Edit Halaman ' . $definition['name'],
            'pageKey' => $pageKey,
            'page' => $page,
            'definition' => $definition,
            'sections' => $sectionMap,
            'reviewReady' =>
                $this->reviewWorkflowReady(),
            'workflowLabels' =>
                $this->workflowLabels(),
        ]);
    }

    public function update(string $pageKey)
    {
        $this->assertReady();

        $definition = $this->definition($pageKey);
        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            return redirect()->to('/website/pages')
                ->with(
                    'error',
                    'Halaman CMS tidak ditemukan.'
                );
        }

        if (
            $this->reviewWorkflowReady()
            && in_array(
                $this->normalizeWorkflowStatus(
                    (string) (
                        $page['workflow_status']
                        ?? ''
                    ),
                    $page
                ),
                ['in_review', 'approved'],
                true
            )
        ) {
            return redirect()->back()->with(
                'error',
                'Draft sedang dikunci oleh proses review. Minta revisi atau selesaikan proses review terlebih dahulu.'
            );
        }

        $title = trim(
            (string) $this->request
                ->getPost('draft_title')
        );

        $metaDescription = trim(
            (string) $this->request
                ->getPost('draft_meta_description')
        );

        $revisionNote = trim(
            (string) $this->request
                ->getPost('revision_note')
        );

        $postedSections = $this->request
            ->getPost('sections');

        if (!is_array($postedSections)) {
            $postedSections = [];
        }

        $enabledSections = $this->request
            ->getPost('section_enabled');

        if (!is_array($enabledSections)) {
            $enabledSections = [];
        }

        $errors = [];

        if ($title === '' || mb_strlen($title) > 180) {
            $errors[] =
                'Judul SEO wajib diisi maksimal 180 karakter.';
        }

        if (
            $metaDescription === ''
            || mb_strlen($metaDescription) > 255
        ) {
            $errors[] =
                'Meta description wajib diisi maksimal 255 karakter.';
        }

        if (mb_strlen($revisionNote) > 255) {
            $errors[] =
                'Catatan revisi maksimal 255 karakter.';
        }

        $sectionPayloads = [];

        foreach (
            $definition['sections'] as
            $sectionKey => $sectionDefinition
        ) {
            $postedValues = $postedSections[$sectionKey]
                ?? [];

            if (!is_array($postedValues)) {
                $postedValues = [];
            }

            $cleanValues = [];

            foreach (
                $sectionDefinition['fields'] as
                $fieldKey => $fieldDefinition
            ) {
                $value = trim(
                    strip_tags(
                        (string) (
                            $postedValues[$fieldKey]
                            ?? ''
                        )
                    )
                );

                $label = $fieldDefinition['label'];
                $max = (int) (
                    $fieldDefinition['max'] ?? 1000
                );

                if (
                    !empty($fieldDefinition['required'])
                    && $value === ''
                ) {
                    $errors[] =
                        $sectionDefinition['name']
                        . ': '
                        . $label
                        . ' wajib diisi.';
                }

                if (mb_strlen($value) > $max) {
                    $errors[] =
                        $sectionDefinition['name']
                        . ': '
                        . $label
                        . ' maksimal '
                        . $max
                        . ' karakter.';
                }

                if (
                    ($fieldDefinition['type'] ?? '')
                        === 'url'
                    && $value !== ''
                    && !$this->validCmsUrl($value)
                ) {
                    $errors[] =
                        $sectionDefinition['name']
                        . ': '
                        . $label
                        . ' harus berupa URL internal atau URL http/https yang valid.';
                }

                $cleanValues[$fieldKey] = $value;
            }

            $encoded = json_encode(
                $cleanValues,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            if ($encoded === false) {
                $errors[] =
                    'Konten section '
                    . $sectionDefinition['name']
                    . ' gagal diproses.';
            }

            $toggleable = (bool) (
                $sectionDefinition['toggleable']
                ?? true
            );

            $sectionPayloads[$sectionKey] = [
                'draft_content' => $encoded ?: '{}',
                'draft_enabled' => $toggleable
                    ? (
                        isset(
                            $enabledSections[$sectionKey]
                        ) ? 1 : 0
                    )
                    : 1,
            ];
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $pageUpdate = [
                'draft_title' => $title,
                'draft_meta_description' =>
                    $metaDescription,
                'revision_note' =>
                    $revisionNote !== ''
                        ? $revisionNote
                        : null,
                'last_edited_by' =>
                    $this->currentUserId(),
                'has_unpublished_changes' => 1,
            ];

            if ($this->reviewWorkflowReady()) {
                $pageUpdate = array_merge(
                    $pageUpdate,
                    [
                        'workflow_status' => 'draft',
                        'submitted_by' => null,
                        'submitted_at' => null,
                        'reviewed_by' => null,
                        'reviewed_at' => null,
                        'approved_by' => null,
                        'approved_at' => null,
                    ]
                );
            }

            $this->pageModel->update(
                (int) $page['id'],
                $pageUpdate
            );

            $existingSections =
                $this->sectionModel
                    ->where(
                        'public_page_id',
                        (int) $page['id']
                    )
                    ->findAll();

            $sectionIds = [];

            foreach ($existingSections as $section) {
                $sectionIds[$section['section_key']] =
                    (int) $section['id'];
            }

            foreach (
                $sectionPayloads as
                $sectionKey => $payload
            ) {
                if (!isset($sectionIds[$sectionKey])) {
                    continue;
                }

                $payload['updated_by'] =
                    $this->currentUserId();

                $this->sectionModel->update(
                    $sectionIds[$sectionKey],
                    $payload
                );
            }

            if ($db->transCommit() === false) {
                throw new RuntimeException(
                    'Draft halaman belum dapat disimpan.'
                );
            }
        } catch (\Throwable $exception) {
            $db->transRollback();

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    $exception instanceof RuntimeException
                        ? $exception->getMessage()
                        : 'Draft halaman belum dapat disimpan.'
                );
        }

        return redirect()->to(
            '/website/pages/edit/' . $pageKey
        )->with(
            'success',
            'Draft halaman berhasil disimpan.'
        );
    }

    public function submitReview(string $pageKey)
    {
        $this->assertReviewWorkflowReady();

        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            return redirect()->to('/website/pages')
                ->with(
                    'error',
                    'Halaman CMS tidak ditemukan.'
                );
        }

        $status = $this->normalizeWorkflowStatus(
            (string) (
                $page['workflow_status'] ?? ''
            ),
            $page
        );

        if (
            !in_array(
                $status,
                [
                    'draft',
                    'changes_requested',
                    'published',
                ],
                true
            )
            || empty($page['has_unpublished_changes'])
        ) {
            return redirect()->back()->with(
                'error',
                'Halaman belum mempunyai draft yang siap dikirim untuk review.'
            );
        }

        $revisionNote = trim((string) (
            $page['revision_note'] ?? ''
        ));

        if ($revisionNote === '') {
            return redirect()->back()->with(
                'error',
                'Isi Catatan Revisi lalu simpan draft sebelum mengirim halaman untuk review.'
            );
        }

        try {
            $this->pageModel->update(
                (int) $page['id'],
                [
                    'workflow_status' => 'in_review',
                    'submitted_by' =>
                        $this->currentUserId(),
                    'submitted_at' =>
                        date('Y-m-d H:i:s'),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                ]
            );
        } catch (\Throwable $exception) {
            return redirect()->back()->with(
                'error',
                'Halaman belum dapat dikirim untuk review.'
            );
        }

        return redirect()->to(
            '/website/pages/edit/' . $pageKey
        )->with(
            'success',
            'Halaman berhasil dikirim untuk review.'
        );
    }

    public function requestChanges(string $pageKey)
    {
        $this->assertReviewWorkflowReady();

        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            return redirect()->to(
                '/website/pages/review'
            )->with(
                'error',
                'Halaman CMS tidak ditemukan.'
            );
        }

        if (
            $this->normalizeWorkflowStatus(
                (string) (
                    $page['workflow_status'] ?? ''
                ),
                $page
            ) !== 'in_review'
        ) {
            return redirect()->back()->with(
                'error',
                'Halaman tidak sedang menunggu review.'
            );
        }

        $reviewNote = trim(strip_tags(
            (string) $this->request
                ->getPost('review_note')
        ));

        if (
            $reviewNote === ''
            || mb_strlen($reviewNote) > 1000
        ) {
            return redirect()->back()->with(
                'error',
                'Catatan revisi reviewer wajib diisi maksimal 1.000 karakter.'
            );
        }

        try {
            $this->pageModel->update(
                (int) $page['id'],
                [
                    'workflow_status' =>
                        'changes_requested',
                    'reviewed_by' =>
                        $this->currentUserId(),
                    'reviewed_at' =>
                        date('Y-m-d H:i:s'),
                    'review_note' => $reviewNote,
                    'approved_by' => null,
                    'approved_at' => null,
                ]
            );
        } catch (\Throwable $exception) {
            return redirect()->back()->with(
                'error',
                'Permintaan revisi belum dapat disimpan.'
            );
        }

        return redirect()->to(
            '/website/pages/review'
        )->with(
            'success',
            'Halaman dikembalikan kepada editor untuk direvisi.'
        );
    }

    public function approve(string $pageKey)
    {
        $this->assertReviewWorkflowReady();

        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            return redirect()->to(
                '/website/pages/review'
            )->with(
                'error',
                'Halaman CMS tidak ditemukan.'
            );
        }

        if (
            $this->normalizeWorkflowStatus(
                (string) (
                    $page['workflow_status'] ?? ''
                ),
                $page
            ) !== 'in_review'
        ) {
            return redirect()->back()->with(
                'error',
                'Halaman tidak sedang menunggu review.'
            );
        }

        try {
            $now = date('Y-m-d H:i:s');
            $userId = $this->currentUserId();

            $this->pageModel->update(
                (int) $page['id'],
                [
                    'workflow_status' => 'approved',
                    'reviewed_by' => $userId,
                    'reviewed_at' => $now,
                    'review_note' => null,
                    'approved_by' => $userId,
                    'approved_at' => $now,
                ]
            );
        } catch (\Throwable $exception) {
            return redirect()->back()->with(
                'error',
                'Persetujuan halaman belum dapat disimpan.'
            );
        }

        return redirect()->to(
            '/website/pages/review'
        )->with(
            'success',
            'Halaman disetujui dan siap dipublikasikan.'
        );
    }

    public function publish(string $pageKey)
    {
        $this->assertReady();

        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            return redirect()->to('/website/pages')
                ->with(
                    'error',
                    'Halaman CMS tidak ditemukan.'
                );
        }

        if ($this->reviewWorkflowReady()) {
            $status = $this->normalizeWorkflowStatus(
                (string) (
                    $page['workflow_status'] ?? ''
                ),
                $page
            );

            if ($status !== 'approved') {
                return redirect()->back()->with(
                    'error',
                    'Halaman harus melalui review dan berstatus Disetujui sebelum dipublikasikan.'
                );
            }
        }

        if (empty($page['has_unpublished_changes'])) {
            return redirect()->back()->with(
                'error',
                'Tidak ada perubahan draft untuk dipublikasikan.'
            );
        }

        $sections = $this->sectionModel
            ->where(
                'public_page_id',
                (int) $page['id']
            )
            ->findAll();

        $db = db_connect();
        $db->transBegin();

        try {
            $pageUpdate = [
                'published_title' =>
                    $page['draft_title'],
                'published_meta_description' =>
                    $page[
                        'draft_meta_description'
                    ],
                'published_by' =>
                    $this->currentUserId(),
                'published_at' =>
                    date('Y-m-d H:i:s'),
                'has_unpublished_changes' => 0,
            ];

            if ($this->reviewWorkflowReady()) {
                $pageUpdate['workflow_status'] =
                    'published';
            }

            $this->pageModel->update(
                (int) $page['id'],
                $pageUpdate
            );

            foreach ($sections as $section) {
                $this->sectionModel->update(
                    (int) $section['id'],
                    [
                        'published_content' =>
                            $section['draft_content'],
                        'published_enabled' =>
                            (int) $section[
                                'draft_enabled'
                            ],
                        'updated_by' =>
                            $this->currentUserId(),
                    ]
                );
            }

            if ($db->transCommit() === false) {
                throw new RuntimeException(
                    'Halaman belum dapat dipublikasikan.'
                );
            }
        } catch (\Throwable $exception) {
            $db->transRollback();

            return redirect()->back()->with(
                'error',
                $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Halaman belum dapat dipublikasikan.'
            );
        }

        return redirect()->to(
            '/website/pages/edit/' . $pageKey
        )->with(
            'success',
            'Halaman publik berhasil diperbarui.'
        );
    }

    public function restore(string $pageKey)
    {
        $this->assertReady();

        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page || empty($page['published_at'])) {
            return redirect()->back()->with(
                'error',
                'Versi terpublikasi belum tersedia.'
            );
        }

        $sections = $this->sectionModel
            ->where(
                'public_page_id',
                (int) $page['id']
            )
            ->findAll();

        $db = db_connect();
        $db->transBegin();

        try {
            $pageUpdate = [
                'draft_title' =>
                    $page['published_title'],
                'draft_meta_description' =>
                    $page[
                        'published_meta_description'
                    ],
                'has_unpublished_changes' => 0,
                'revision_note' => null,
                'last_edited_by' =>
                    $this->currentUserId(),
            ];

            if ($this->reviewWorkflowReady()) {
                $pageUpdate = array_merge(
                    $pageUpdate,
                    [
                        'workflow_status' => 'published',
                        'submitted_by' => null,
                        'submitted_at' => null,
                        'reviewed_by' => null,
                        'reviewed_at' => null,
                        'review_note' => null,
                        'approved_by' => null,
                        'approved_at' => null,
                    ]
                );
            }

            $this->pageModel->update(
                (int) $page['id'],
                $pageUpdate
            );

            foreach ($sections as $section) {
                $this->sectionModel->update(
                    (int) $section['id'],
                    [
                        'draft_content' =>
                            $section[
                                'published_content'
                            ],
                        'draft_enabled' =>
                            (int) $section[
                                'published_enabled'
                            ],
                        'updated_by' =>
                            $this->currentUserId(),
                    ]
                );
            }

            if ($db->transCommit() === false) {
                throw new RuntimeException(
                    'Draft belum dapat dipulihkan.'
                );
            }
        } catch (\Throwable $exception) {
            $db->transRollback();

            return redirect()->back()->with(
                'error',
                $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Draft belum dapat dipulihkan.'
            );
        }

        return redirect()->to(
            '/website/pages/edit/' . $pageKey
        )->with(
            'success',
            'Draft dikembalikan ke versi terpublikasi.'
        );
    }

    public function preview(string $pageKey)
    {
        $this->assertReady();

        $definition = $this->definition($pageKey);

        return redirect()->to(
            $definition['route']
            . (
                str_contains(
                    $definition['route'],
                    '?'
                )
                    ? '&'
                    : '?'
            )
            . 'cms_preview=1'
        );
    }

    /**
     * @return array<string, string>
     */
    private function workflowLabels(): array
    {
        return [
            'draft' => 'Draft',
            'in_review' => 'Menunggu Review',
            'changes_requested' => 'Perlu Revisi',
            'approved' => 'Disetujui',
            'published' => 'Terpublikasi',
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyWorkflowCounts(): array
    {
        return [
            'draft' => 0,
            'in_review' => 0,
            'changes_requested' => 0,
            'approved' => 0,
            'published' => 0,
        ];
    }

    /**
     * @param list<array<string, mixed>> $pages
     * @return array<int, string>
     */
    private function userNamesForPages(
        array $pages
    ): array {
        $userIds = [];

        foreach ($pages as $page) {
            foreach ([
                'last_edited_by',
                'submitted_by',
                'reviewed_by',
                'approved_by',
                'published_by',
            ] as $field) {
                if (!empty($page[$field])) {
                    $userIds[] = (int) $page[$field];
                }
            }
        }

        $userIds = array_values(array_unique(
            array_filter($userIds)
        ));

        if ($userIds === []) {
            return [];
        }

        $users = $this->userModel
            ->select('id, name')
            ->whereIn('id', $userIds)
            ->findAll();

        $result = [];

        foreach ($users as $user) {
            $result[(int) $user['id']] =
                (string) $user['name'];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $page
     */
    private function normalizeWorkflowStatus(
        string $status,
        array $page
    ): string {
        if (
            in_array(
                $status,
                $this->validWorkflowStatuses,
                true
            )
        ) {
            return $status;
        }

        return $this->legacyWorkflowStatus($page);
    }

    /**
     * @param array<string, mixed> $page
     */
    private function legacyWorkflowStatus(
        array $page
    ): string {
        if (!empty($page['has_unpublished_changes'])) {
            return 'draft';
        }

        return !empty($page['published_at'])
            ? 'published'
            : 'draft';
    }

    private function definition(string $pageKey): array
    {
        $definition = $this->cmsConfig
            ->pages[$pageKey] ?? null;

        if (!is_array($definition)) {
            throw new RuntimeException(
                'Definisi halaman publik tidak ditemukan.'
            );
        }

        return $definition;
    }

    private function cmsReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('public_pages')
            && $db->tableExists(
                'public_page_sections'
            );
    }

    private function reviewWorkflowReady(): bool
    {
        if (!$this->cmsReady()) {
            return false;
        }

        $fields = db_connect()->getFieldNames(
            'public_pages'
        );

        return in_array(
            'workflow_status',
            $fields,
            true
        );
    }

    private function assertReady(): void
    {
        if (!$this->cmsReady()) {
            throw new RuntimeException(
                'Fondasi CMS publik belum tersedia. Jalankan php spark migrate.'
            );
        }
    }

    private function assertReviewWorkflowReady(): void
    {
        if (!$this->reviewWorkflowReady()) {
            throw new RuntimeException(
                'Workflow review belum tersedia. Jalankan php spark migrate.'
            );
        }
    }

    private function currentUserId(): ?int
    {
        $userId = session()->get('user_id');

        return $userId !== null
            ? (int) $userId
            : null;
    }

    private function validCmsUrl(string $value): bool
    {
        if (str_starts_with($value, '/')) {
            return !str_starts_with($value, '//');
        }

        if (!preg_match('#^https?://#i', $value)) {
            return false;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_URL
        ) !== false;
    }
}
