<?php

namespace App\Controllers;

use App\Models\PublicPageModel;
use App\Models\PublicPageSectionModel;
use Config\PublicCms;
use RuntimeException;

class PublicPageController extends BaseController
{
    protected PublicPageModel $pageModel;
    protected PublicPageSectionModel $sectionModel;
    protected PublicCms $cmsConfig;

    public function __construct()
    {
        $this->pageModel = new PublicPageModel();
        $this->sectionModel =
            new PublicPageSectionModel();
        $this->cmsConfig = new PublicCms();
    }

    public function index()
    {
        $ready = $this->cmsReady();
        $pages = [];

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

                $pages[] = $page;
            }
        }

        return view('public_pages/index', [
            'title' => 'Kelola Halaman Publik',
            'ready' => $ready,
            'pages' => $pages,
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
            $this->pageModel->update(
                (int) $page['id'],
                [
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
                ]
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

        $sections = $this->sectionModel
            ->where(
                'public_page_id',
                (int) $page['id']
            )
            ->findAll();

        $db = db_connect();
        $db->transBegin();

        try {
            $this->pageModel->update(
                (int) $page['id'],
                [
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
                ]
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
            $this->pageModel->update(
                (int) $page['id'],
                [
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
                ]
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

    private function assertReady(): void
    {
        if (!$this->cmsReady()) {
            throw new RuntimeException(
                'Fondasi CMS publik belum tersedia. Jalankan php spark migrate.'
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
