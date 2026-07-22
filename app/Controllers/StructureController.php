<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\OrganizationalStructureModel;
use App\Libraries\SecureUploadService;

class StructureController extends BaseController
{
    protected $structureModel;
    protected $memberModel;
    protected SecureUploadService $uploadService;

    public function __construct()
    {
        $this->structureModel = new OrganizationalStructureModel();
        $this->memberModel    = new MemberModel();
        $this->uploadService = new SecureUploadService();
    }

    public function index()
    {
        $structures = $this->structureModel
            ->select('organizational_structures.*, members.full_name')
            ->join('members', 'members.id = organizational_structures.member_id', 'left')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $data = [
            'title'      => 'Struktur Pengurus',
            'structures' => $structures
        ];

        return view('structures/index', $data);
    }

    public function create()
    {
        $data = [
            'title'   => 'Tambah Struktur Pengurus',
            'members' => $this->memberModel
                ->where('membership_status', 'active')
                ->orderBy('full_name', 'ASC')
                ->findAll()
        ];

        return view('structures/create', $data);
    }

    public function store()
    {
        $rules = [
            'position_name' => [
                'label' => 'Jabatan',
                'rules' => 'required|min_length[2]',
            ],
            'member_id' => [
                'label' => 'Nama Pengurus',
                'rules' => 'required|is_natural_no_zero',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $photoName = null;

        try {
            $photoName = $this->processOfficialPhoto();

            $inserted = $this->structureModel->insert([
                'member_id'       => $this->request->getPost('member_id'),
                'position_name'   => $this->request->getPost('position_name'),
                'division'        => $this->request->getPost('division'),
                'rt_scope'        => $this->request->getPost('rt_scope'),
                'period'          => $this->request->getPost('period'),
                'sort_order'      => $this->request->getPost('sort_order') ?: 0,
                'description'     => $this->request->getPost('description'),
                'photo'           => $photoName,
                'short_bio'       => $this->request->getPost('short_bio'),
            ], true);

            if ($inserted === false) {
                throw new \RuntimeException(
                    'Data struktur gagal disimpan.'
                );
            }

            return redirect()->to('/structures')
                ->with('success', 'Data struktur berhasil ditambahkan.');
        } catch (\Throwable $exception) {
            if ($photoName !== null) {
                $this->deleteOfficialPhoto($photoName);
            }

            $message = $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Data struktur belum dapat disimpan.';

            return redirect()->back()
                ->withInput()
                ->with('errors', [$message]);
        }
    }

    public function edit($id)
    {
        $structure = $this->structureModel->find($id);

        if (!$structure) {
            return redirect()->to('/structures')->with('error', 'Data struktur tidak ditemukan.');
        }

        $data = [
            'title'     => 'Edit Struktur Pengurus',
            'structure' => $structure,
            'members'   => $this->memberModel
                ->where('membership_status', 'active')
                ->orderBy('full_name', 'ASC')
                ->findAll()
        ];

        return view('structures/edit', $data);
    }

    public function update($id)
    {
        $structure = $this->structureModel->find($id);

        if (!$structure) {
            return redirect()->to('/structures')
                ->with('error', 'Data struktur tidak ditemukan.');
        }

        $rules = [
            'position_name' => [
                'label' => 'Jabatan',
                'rules' => 'required|min_length[2]',
            ],
            'member_id' => [
                'label' => 'Nama Pengurus',
                'rules' => 'required|is_natural_no_zero',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $oldPhoto = $structure['photo'] ?? null;
        $photoName = $oldPhoto;
        $hasNewPhoto = false;

        try {
            $photoName = $this->processOfficialPhoto($oldPhoto);
            $hasNewPhoto = $photoName !== $oldPhoto;

            $updated = $this->structureModel->update($id, [
                'member_id'       => $this->request->getPost('member_id'),
                'position_name'   => $this->request->getPost('position_name'),
                'division'        => $this->request->getPost('division'),
                'rt_scope'        => $this->request->getPost('rt_scope'),
                'period'          => $this->request->getPost('period'),
                'sort_order'      => $this->request->getPost('sort_order') ?: 0,
                'description'     => $this->request->getPost('description'),
                'photo'           => $photoName,
                'short_bio'       => $this->request->getPost('short_bio'),
            ]);

            if ($updated === false) {
                throw new \RuntimeException(
                    'Data struktur gagal diperbarui.'
                );
            }

            if ($hasNewPhoto && $oldPhoto !== null) {
                $this->deleteOfficialPhoto($oldPhoto);
            }

            return redirect()->to('/structures')
                ->with('success', 'Data struktur berhasil diperbarui.');
        } catch (\Throwable $exception) {
            if ($hasNewPhoto && $photoName !== null) {
                $this->deleteOfficialPhoto($photoName);
            }

            $message = $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Data struktur belum dapat diperbarui.';

            return redirect()->back()
                ->withInput()
                ->with('errors', [$message]);
        }
    }

    public function delete($id)
    {
        $structure = $this->structureModel->find($id);

        if (!$structure) {
            return redirect()->to('/structures')
                ->with('error', 'Data struktur tidak ditemukan.');
        }

        if ($this->structureModel->delete($id) === false) {
            return redirect()->to('/structures')
                ->with('error', 'Data struktur gagal dihapus.');
        }

        $this->deleteOfficialPhoto($structure['photo'] ?? null);

        return redirect()->to('/structures')
            ->with('success', 'Data struktur berhasil dihapus.');
    }

    private function processOfficialPhoto(?string $oldPhoto = null): ?string
    {
        $photo = $this->request->getFile('photo');

        if (!$photo || $photo->getError() === UPLOAD_ERR_NO_FILE) {
            return $oldPhoto;
        }

        $stored = $this->uploadService->storeImage(
            $photo,
            'uploads/officials',
            [
                'max_bytes' => 2 * 1024 * 1024,
                'max_pixels' => 20_000_000,
                'target_max_width' => 1200,
                'target_max_height' => 1200,
            ]
        );

        return $stored['file_name'];
    }

    private function deleteOfficialPhoto(?string $photoName): void
    {
        if (empty($photoName)) {
            return;
        }

        $this->uploadService->deleteManagedFile(
            'uploads/officials/' . basename($photoName),
            ['uploads/officials']
        );
    }
}
