<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use App\Models\ProgramModel;

class ActivityController extends BaseController
{
    protected ActivityModel $activityModel;
    protected ProgramModel $programModel;

    public function __construct()
    {
        $this->activityModel = new ActivityModel();
        $this->programModel  = new ProgramModel();
    }

        public function index()
    {
        $keyword   = trim((string) $this->request->getGet('keyword'));
        $status    = trim((string) $this->request->getGet('status'));
        $programId = trim((string) $this->request->getGet('program_id'));

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
                ->orLike('activities.description', $keyword)
                ->groupEnd();
        }

        if (
            in_array(
                $status,
                ['planned', 'completed', 'cancelled'],
                true
            )
        ) {
            $builder->where('activities.status', $status);
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

        $activities = $builder
            ->orderBy('activities.activity_date', 'DESC')
            ->orderBy('activities.id', 'DESC')
            ->paginate(10, 'activities');

        $programs = $this->programModel
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('activities/index', [
            'title'           => 'Data Kegiatan',
            'activities'      => $activities,
            'pager'           => $this->activityModel->pager,
            'programs'        => $programs,
            'keyword'         => $keyword,
            'status'          => $status,
            'selectedProgram' => $programId,
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
            'title'    => 'Tambah Kegiatan',
            'programs' => $programs,
        ]);
    }

    /**
     * Menyimpan kegiatan baru.
     */
    public function store()
    {
        $rules = [
            'program_id' => [
                'label' => 'Pilar program',
                'rules' => 'permit_empty|is_natural_no_zero',
            ],
            'title' => [
                'label' => 'Nama kegiatan',
                'rules' => 'required|min_length[3]|max_length[255]',
            ],
            'activity_date' => [
                'label' => 'Tanggal kegiatan',
                'rules' => 'required|valid_date[Y-m-d]',
            ],
            'location' => [
                'label' => 'Lokasi kegiatan',
                'rules' => 'required|max_length[255]',
            ],
            'status' => [
                'label' => 'Status kegiatan',
                'rules' => 'required|in_list[planned,completed,cancelled]',
            ],
            'documentation_link' => [
                'label' => 'Tautan dokumentasi',
                'rules' => 'permit_empty|valid_url_strict',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $documentationFile = $this->processDocumentationFile();

            $this->activityModel->insert([
                'program_id' => $this->normalizeProgramId(
                    $this->request->getPost('program_id')
                ),
                'title' => trim(
                    (string) $this->request->getPost('title')
                ),
                'description' => trim(
                    (string) $this->request->getPost('description')
                ),
                'activity_date' => $this->request->getPost(
                    'activity_date'
                ),
                'location' => trim(
                    (string) $this->request->getPost('location')
                ),
                'status' => $this->request->getPost('status'),
                'result' => trim(
                    (string) $this->request->getPost('result')
                ),
                'documentation_file' => $documentationFile,
                'documentation_link' => trim(
                    (string) $this->request->getPost(
                        'documentation_link'
                    )
                ),
            ]);

            return redirect()->to('/activities')
                ->with(
                    'success',
                    'Data kegiatan berhasil ditambahkan.'
                );
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [$e->getMessage()]);
        }
    }

    /**
     * Menampilkan form edit kegiatan.
     */
    public function edit(int $id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with('error', 'Data kegiatan tidak ditemukan.');
        }

        /*
         * Semua program tetap ditampilkan pada form edit.
         * Ini menjaga data lama apabila program pernah diarsipkan.
         */
        $programs = $this->programModel
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('activities/edit', [
            'title'    => 'Edit Kegiatan',
            'activity' => $activity,
            'programs' => $programs,
        ]);
    }

    /**
     * Memperbarui data kegiatan.
     */
    public function update(int $id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with('error', 'Data kegiatan tidak ditemukan.');
        }

        $rules = [
            'program_id' => [
                'label' => 'Pilar program',
                'rules' => 'permit_empty|is_natural_no_zero',
            ],
            'title' => [
                'label' => 'Nama kegiatan',
                'rules' => 'required|min_length[3]|max_length[255]',
            ],
            'activity_date' => [
                'label' => 'Tanggal kegiatan',
                'rules' => 'required|valid_date[Y-m-d]',
            ],
            'location' => [
                'label' => 'Lokasi kegiatan',
                'rules' => 'required|max_length[255]',
            ],
            'status' => [
                'label' => 'Status kegiatan',
                'rules' => 'required|in_list[planned,completed,cancelled]',
            ],
            'documentation_link' => [
                'label' => 'Tautan dokumentasi',
                'rules' => 'permit_empty|valid_url_strict',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $documentationFile = $this->processDocumentationFile(
                $activity['documentation_file'] ?? null
            );

            $this->activityModel->update($id, [
                'program_id' => $this->normalizeProgramId(
                    $this->request->getPost('program_id')
                ),
                'title' => trim(
                    (string) $this->request->getPost('title')
                ),
                'description' => trim(
                    (string) $this->request->getPost('description')
                ),
                'activity_date' => $this->request->getPost(
                    'activity_date'
                ),
                'location' => trim(
                    (string) $this->request->getPost('location')
                ),
                'status' => $this->request->getPost('status'),
                'result' => trim(
                    (string) $this->request->getPost('result')
                ),
                'documentation_file' => $documentationFile,
                'documentation_link' => trim(
                    (string) $this->request->getPost(
                        'documentation_link'
                    )
                ),
            ]);

            return redirect()->to('/activities')
                ->with(
                    'success',
                    'Data kegiatan berhasil diperbarui.'
                );
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [$e->getMessage()]);
        }
    }

    /**
     * Menghapus kegiatan dan file dokumentasinya.
     */
    public function delete(int $id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with('error', 'Data kegiatan tidak ditemukan.');
        }

        $oldFile = $activity['documentation_file'] ?? null;

        if (!empty($oldFile)) {
            $filePath = FCPATH
                . 'uploads/activities/'
                . $oldFile;

            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        $this->activityModel->delete($id);

        return redirect()->to('/activities')
            ->with('success', 'Data kegiatan berhasil dihapus.');
    }

    /**
     * Mengubah nilai program kosong menjadi null.
     */
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

    /**
     * Memproses upload dokumentasi kegiatan.
     */
    private function processDocumentationFile(
        ?string $oldFile = null
    ): ?string {
        $file = $this->request->getFile('documentation_file');

        if (
            !$file
            || $file->getError() === UPLOAD_ERR_NO_FILE
        ) {
            return $oldFile;
        }

        if (!$file->isValid()) {
            throw new \RuntimeException(
                'File dokumentasi gagal diunggah.'
            );
        }

        if ($file->getSize() > (4 * 1024 * 1024)) {
            throw new \RuntimeException(
                'Ukuran dokumentasi maksimal 4 MB.'
            );
        }

        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
        ];

        if (
            !in_array(
                $file->getMimeType(),
                $allowedMimes,
                true
            )
        ) {
            throw new \RuntimeException(
                'Dokumentasi harus berupa JPG, JPEG, PNG, atau WEBP.'
            );
        }

        $directory = FCPATH . 'uploads/activities';

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $newFileName = $file->getRandomName();

        $file->move(
            $directory,
            $newFileName
        );

        if (!empty($oldFile)) {
            $oldFilePath = $directory
                . DIRECTORY_SEPARATOR
                . $oldFile;

            if (is_file($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        return $newFileName;
    }
}