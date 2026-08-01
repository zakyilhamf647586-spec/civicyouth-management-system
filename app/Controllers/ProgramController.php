<?php

namespace App\Controllers;

use App\Models\ProgramModel;
use App\Libraries\SecureUploadService;

class ProgramController extends BaseController
{
    protected ProgramModel $programModel;
    protected SecureUploadService $uploadService;

    public function __construct()
    {
        helper('text');

        $this->programModel = new ProgramModel();
        $this->uploadService = new SecureUploadService();
    }

    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $status  = trim((string) $this->request->getGet('status'));

        $model = $this->programModel
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($keyword !== '') {
            $model->groupStart()
                ->like('name', $keyword)
                ->orLike('label', $keyword)
                ->orLike('tagline', $keyword)
                ->groupEnd();
        }

        if (in_array($status, ['draft', 'published', 'archived'], true)) {
            $model->where('status', $status);
        }

        return view('programs/index', [
            'title'    => 'Program GARDA 01',
            'programs' => $model->findAll(),
            'keyword'  => $keyword,
            'status'   => $status,
        ]);
    }

    public function create()
    {
        return view('programs/create', [
            'title' => 'Tambah Program GARDA 01',
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => [
                'label' => 'Nama program',
                'rules' => 'required|min_length[3]|max_length[150]',
            ],
            'name_en' => [
                'label' => 'English program name',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'label' => [
                'label' => 'Kategori program',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'tagline' => [
                'label' => 'Tagline',
                'rules' => 'permit_empty|max_length[255]',
            ],
            'label_en' => [
                'label' => 'English program category',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'tagline_en' => [
                'label' => 'English tagline',
                'rules' => 'permit_empty|max_length[255]',
            ],
            'status' => [
                'label' => 'Status',
                'rules' => 'required|in_list[draft,published,archived]',
            ],
            'display_order' => [
                'label' => 'Urutan tampil',
                'rules' => 'required|integer|greater_than_equal_to[0]',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $coverName = null;

        try {
            $name      = trim((string) $this->request->getPost('name'));
            $coverName = $this->processCoverImage();

            $programData = [
                'name'              => $name,
                'name_en'           => trim((string) $this->request->getPost('name_en')),
                'slug'              => $this->createUniqueSlug($name),
                'label'             => trim((string) $this->request->getPost('label')),
                'label_en'          => trim((string) $this->request->getPost('label_en')),
                'tagline'           => trim((string) $this->request->getPost('tagline')),
                'tagline_en'        => trim((string) $this->request->getPost('tagline_en')),
                'short_description' => trim((string) $this->request->getPost('short_description')),
                'short_description_en' => trim((string) $this->request->getPost('short_description_en')),
                'description'       => trim((string) $this->request->getPost('description')),
                'description_en'    => trim((string) $this->request->getPost('description_en')),
                'focus_items'       => $this->encodeLineList(
                    (string) $this->request->getPost('focus_items')
                ),
                'focus_items_en'    => $this->encodeLineList(
                    (string) $this->request->getPost('focus_items_en')
                ),
                'campaign_items'    => $this->encodeLineList(
                    (string) $this->request->getPost('campaign_items')
                ),
                'campaign_items_en' => $this->encodeLineList(
                    (string) $this->request->getPost('campaign_items_en')
                ),
                'cover_image'       => $coverName,
                'status'            => $this->request->getPost('status'),
                'display_order'     => (int) $this->request->getPost('display_order'),
                'created_by'        => session()->get('user_id') ?: null,
            ];

            if ($programData['status'] === 'published') {
                $this->assertBilingualReady($programData);
            }

            $inserted = $this->programModel->insert(
                $programData,
                true
            );

            if ($inserted === false) {
                throw new \RuntimeException(
                    'Program gagal disimpan.'
                );
            }

            $createdProgram = $this->programModel->find((int) $inserted);

            $this->recordCmsAudit([
                'module' => 'programs',
                'event_type' => 'program.created',
                'severity' => 'notice',
                'subject_type' => 'program',
                'subject_id' => (int) $inserted,
                'subject_key' => $createdProgram['slug'] ?? null,
                'subject_label' => $name,
                'summary' => 'Program ' . $name . ' ditambahkan.',
                'metadata' => [
                    'status' => $this->request->getPost('status'),
                    'display_order' => (int) $this->request->getPost('display_order'),
                ],
            ]);

            return redirect()->to('/programs')
                ->with('success', 'Program GARDA 01 berhasil ditambahkan.');
        } catch (\Throwable $exception) {
            if ($coverName !== null) {
                $this->deleteProgramCover($coverName);
            }

            $message = $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Program belum dapat disimpan.';

            return redirect()->back()
                ->withInput()
                ->with('errors', [$message]);
        }
    }

    public function edit(int $id)
    {
        $program = $this->programModel->find($id);

        if (!$program) {
            return redirect()->to('/programs')
                ->with('error', 'Program tidak ditemukan.');
        }

        $prepared = $this->programModel->prepareProgram($program);

        $program['focus_text'] = implode(
            PHP_EOL,
            $prepared['focus'] ?? []
        );

        $program['campaign_text'] = implode(
            PHP_EOL,
            $prepared['campaigns'] ?? []
        );

        $program['focus_text_en'] = implode(
            PHP_EOL,
            $prepared['focus_en'] ?? []
        );

        $program['campaign_text_en'] = implode(
            PHP_EOL,
            $prepared['campaigns_en'] ?? []
        );

        return view('programs/edit', [
            'title'   => 'Edit Program GARDA 01',
            'program' => $program,
        ]);
    }

    public function update(int $id)
    {
        $program = $this->programModel->find($id);

        if (!$program) {
            return redirect()->to('/programs')
                ->with('error', 'Program tidak ditemukan.');
        }

        $rules = [
            'name' => [
                'label' => 'Nama program',
                'rules' => 'required|min_length[3]|max_length[150]',
            ],
            'name_en' => [
                'label' => 'English program name',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'label' => [
                'label' => 'Kategori program',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'tagline' => [
                'label' => 'Tagline',
                'rules' => 'permit_empty|max_length[255]',
            ],
            'label_en' => [
                'label' => 'English program category',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'tagline_en' => [
                'label' => 'English tagline',
                'rules' => 'permit_empty|max_length[255]',
            ],
            'status' => [
                'label' => 'Status',
                'rules' => 'required|in_list[draft,published,archived]',
            ],
            'display_order' => [
                'label' => 'Urutan tampil',
                'rules' => 'required|integer|greater_than_equal_to[0]',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $oldCover = $program['cover_image'] ?? null;
        $coverName = $oldCover;
        $hasNewCover = false;

        try {
            $name = trim((string) $this->request->getPost('name'));

            $coverName = $this->processCoverImage($oldCover);
            $hasNewCover = $coverName !== $oldCover;

            $programData = [
                'name'              => $name,
                'name_en'           => trim((string) $this->request->getPost('name_en')),
                'slug'              => $this->createUniqueSlug($name, $id),
                'label'             => trim((string) $this->request->getPost('label')),
                'label_en'          => trim((string) $this->request->getPost('label_en')),
                'tagline'           => trim((string) $this->request->getPost('tagline')),
                'tagline_en'        => trim((string) $this->request->getPost('tagline_en')),
                'short_description' => trim((string) $this->request->getPost('short_description')),
                'short_description_en' => trim((string) $this->request->getPost('short_description_en')),
                'description'       => trim((string) $this->request->getPost('description')),
                'description_en'    => trim((string) $this->request->getPost('description_en')),
                'focus_items'       => $this->encodeLineList(
                    (string) $this->request->getPost('focus_items')
                ),
                'focus_items_en'    => $this->encodeLineList(
                    (string) $this->request->getPost('focus_items_en')
                ),
                'campaign_items'    => $this->encodeLineList(
                    (string) $this->request->getPost('campaign_items')
                ),
                'campaign_items_en' => $this->encodeLineList(
                    (string) $this->request->getPost('campaign_items_en')
                ),
                'cover_image'       => $coverName,
                'status'            => $this->request->getPost('status'),
                'display_order'     => (int) $this->request->getPost('display_order'),
            ];

            if ($programData['status'] === 'published') {
                $this->assertBilingualReady($programData);
            }

            $updated = $this->programModel->update(
                $id,
                $programData
            );

            if ($updated === false) {
                throw new \RuntimeException(
                    'Program gagal diperbarui.'
                );
            }

            if ($hasNewCover && $oldCover !== null) {
                $this->deleteProgramCover($oldCover);
            }

            $updatedProgram = $this->programModel->find($id);

            $this->recordCmsAudit([
                'module' => 'programs',
                'event_type' => 'program.updated',
                'severity' => 'info',
                'subject_type' => 'program',
                'subject_id' => $id,
                'subject_key' => $updatedProgram['slug'] ?? null,
                'subject_label' => $name,
                'summary' => 'Program ' . $name . ' diperbarui.',
                'metadata' => [
                    'previous_status' => $program['status'] ?? null,
                    'new_status' => $this->request->getPost('status'),
                    'cover_changed' => $hasNewCover,
                ],
            ]);

            return redirect()->to('/programs')
                ->with('success', 'Program GARDA 01 berhasil diperbarui.');
        } catch (\Throwable $exception) {
            if ($hasNewCover && $coverName !== null) {
                $this->deleteProgramCover($coverName);
            }

            $message = $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Program belum dapat diperbarui.';

            return redirect()->back()
                ->withInput()
                ->with('errors', [$message]);
        }
    }

    public function publish(int $id)
    {
        $program = $this->programModel->find($id);

        if (!$program) {
            return redirect()->to('/programs')
                ->with('error', 'Program tidak ditemukan.');
        }

        try {
            $this->assertBilingualReady($program);
        } catch (\RuntimeException $exception) {
            return redirect()->to('/programs/edit/' . $id)
                ->with('errors', [$exception->getMessage()]);
        }

        $this->programModel->update($id, [
            'status' => 'published',
        ]);

        $this->recordCmsAudit([
            'module' => 'programs',
            'event_type' => 'program.published',
            'severity' => 'notice',
            'subject_type' => 'program',
            'subject_id' => $id,
            'subject_key' => $program['slug'] ?? null,
            'subject_label' => $program['name'] ?? 'Program',
            'summary' => 'Program ' . ($program['name'] ?? '#' . $id) . ' dipublikasikan.',
            'metadata' => [
                'previous_status' => $program['status'] ?? null,
                'new_status' => 'published',
            ],
        ]);

        return redirect()->to('/programs')
            ->with('success', 'Program berhasil dipublikasikan.');
    }

    public function archive(int $id)
    {
        $program = $this->programModel->find($id);

        if (!$program) {
            return redirect()->to('/programs')
                ->with('error', 'Program tidak ditemukan.');
        }

        $this->programModel->update($id, [
            'status' => 'archived',
        ]);

        $this->recordCmsAudit([
            'module' => 'programs',
            'event_type' => 'program.archived',
            'severity' => 'warning',
            'subject_type' => 'program',
            'subject_id' => $id,
            'subject_key' => $program['slug'] ?? null,
            'subject_label' => $program['name'] ?? 'Program',
            'summary' => 'Program ' . ($program['name'] ?? '#' . $id) . ' diarsipkan.',
            'metadata' => [
                'previous_status' => $program['status'] ?? null,
                'new_status' => 'archived',
            ],
        ]);

        return redirect()->to('/programs')
            ->with('success', 'Program berhasil diarsipkan.');
    }

    private function encodeLineList(string $value): string
    {
        $items = preg_split('/\r\n|\r|\n/', $value);

        $items = array_values(
            array_filter(
                array_map(
                    static fn ($item) => trim((string) $item),
                    $items ?: []
                ),
                static fn ($item) => $item !== ''
            )
        );

        return json_encode(
            $items,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param array<string, mixed> $program
     */
    private function assertBilingualReady(array $program): void
    {
        $pairs = [
            'name' => ['name_en', 'English program name'],
            'label' => ['label_en', 'English program category'],
            'tagline' => ['tagline_en', 'English tagline'],
            'short_description' => [
                'short_description_en',
                'English short description',
            ],
            'description' => [
                'description_en',
                'English full description',
            ],
            'focus_items' => [
                'focus_items_en',
                'English program focus',
            ],
            'campaign_items' => [
                'campaign_items_en',
                'English campaign list',
            ],
        ];
        $missing = [];

        foreach ($pairs as $source => [$english, $label]) {
            if (
                trim((string) ($program[$source] ?? '')) !== ''
                && trim((string) ($program[$english] ?? '')) === ''
            ) {
                $missing[] = $label;
            }
        }

        if ($missing !== []) {
            throw new \RuntimeException(
                'Program belum siap dipublikasikan. Lengkapi: '
                . implode(', ', $missing)
                . '.'
            );
        }
    }

    private function createUniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = url_title($name, '-', true);

        if ($baseSlug === '') {
            $baseSlug = 'program';
        }

        $slug    = $baseSlug;
        $counter = 2;
        $db      = db_connect();

        while (true) {
            $builder = $db->table('programs')
                ->where('slug', $slug);

            if ($ignoreId !== null) {
                $builder->where('id !=', $ignoreId);
            }

            if ($builder->countAllResults() === 0) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    private function processCoverImage(
        ?string $oldCover = null
    ): ?string {
        $cover = $this->request->getFile('cover_image');

        if (!$cover || $cover->getError() === UPLOAD_ERR_NO_FILE) {
            return $oldCover;
        }

        $stored = $this->uploadService->storeImage(
            $cover,
            'uploads/programs',
            [
                'max_bytes' => 2 * 1024 * 1024,
                'max_pixels' => 28_000_000,
                'target_max_width' => 2000,
                'target_max_height' => 1600,
            ]
        );

        return $stored['file_name'];
    }

    private function deleteProgramCover(?string $coverName): void
    {
        if (empty($coverName)) {
            return;
        }

        $this->uploadService->deleteManagedFile(
            'uploads/programs/' . basename($coverName),
            ['uploads/programs']
        );
    }
}
