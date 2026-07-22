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
            'label' => [
                'label' => 'Kategori program',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'tagline' => [
                'label' => 'Tagline',
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

            $inserted = $this->programModel->insert([
                'name'              => $name,
                'slug'              => $this->createUniqueSlug($name),
                'label'             => trim((string) $this->request->getPost('label')),
                'tagline'           => trim((string) $this->request->getPost('tagline')),
                'short_description' => trim((string) $this->request->getPost('short_description')),
                'description'       => trim((string) $this->request->getPost('description')),
                'focus_items'       => $this->encodeLineList(
                    (string) $this->request->getPost('focus_items')
                ),
                'campaign_items'    => $this->encodeLineList(
                    (string) $this->request->getPost('campaign_items')
                ),
                'cover_image'       => $coverName,
                'status'            => $this->request->getPost('status'),
                'display_order'     => (int) $this->request->getPost('display_order'),
                'created_by'        => session()->get('user_id') ?: null,
            ], true);

            if ($inserted === false) {
                throw new \RuntimeException(
                    'Program gagal disimpan.'
                );
            }

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
            'label' => [
                'label' => 'Kategori program',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'tagline' => [
                'label' => 'Tagline',
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

            $updated = $this->programModel->update($id, [
                'name'              => $name,
                'slug'              => $this->createUniqueSlug($name, $id),
                'label'             => trim((string) $this->request->getPost('label')),
                'tagline'           => trim((string) $this->request->getPost('tagline')),
                'short_description' => trim((string) $this->request->getPost('short_description')),
                'description'       => trim((string) $this->request->getPost('description')),
                'focus_items'       => $this->encodeLineList(
                    (string) $this->request->getPost('focus_items')
                ),
                'campaign_items'    => $this->encodeLineList(
                    (string) $this->request->getPost('campaign_items')
                ),
                'cover_image'       => $coverName,
                'status'            => $this->request->getPost('status'),
                'display_order'     => (int) $this->request->getPost('display_order'),
            ]);

            if ($updated === false) {
                throw new \RuntimeException(
                    'Program gagal diperbarui.'
                );
            }

            if ($hasNewCover && $oldCover !== null) {
                $this->deleteProgramCover($oldCover);
            }

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
        if (!$this->programModel->find($id)) {
            return redirect()->to('/programs')
                ->with('error', 'Program tidak ditemukan.');
        }

        $this->programModel->update($id, [
            'status' => 'published',
        ]);

        return redirect()->to('/programs')
            ->with('success', 'Program berhasil dipublikasikan.');
    }

    public function archive(int $id)
    {
        if (!$this->programModel->find($id)) {
            return redirect()->to('/programs')
                ->with('error', 'Program tidak ditemukan.');
        }

        $this->programModel->update($id, [
            'status' => 'archived',
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
