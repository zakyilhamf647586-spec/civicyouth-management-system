<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\OrganizationalStructureModel;

class StructureController extends BaseController
{
    protected $structureModel;
    protected $memberModel;

    public function __construct()
    {
        $this->structureModel = new OrganizationalStructureModel();
        $this->memberModel    = new MemberModel();
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

        try {
            $photoName = $this->processOfficialPhoto();

            $this->structureModel->insert([
                'member_id'       => $this->request->getPost('member_id'),
                'position_name'   => $this->request->getPost('position_name'),
                'division'        => $this->request->getPost('division'),
                'rt'              => $this->request->getPost('rt'),
                'period'          => $this->request->getPost('period'),
                'sort_order'      => $this->request->getPost('sort_order') ?: 0,
                'job_description' => $this->request->getPost('job_description'),
                'photo'           => $photoName,
                'short_bio'       => $this->request->getPost('short_bio'),
            ]);

            return redirect()->to('/structures')
                ->with('success', 'Data struktur berhasil ditambahkan.');
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [$e->getMessage()]);
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

        try {
            $photoName = $this->processOfficialPhoto($structure['photo'] ?? null);

            $this->structureModel->update($id, [
                'member_id'       => $this->request->getPost('member_id'),
                'position_name'   => $this->request->getPost('position_name'),
                'division'        => $this->request->getPost('division'),
                'rt'              => $this->request->getPost('rt'),
                'period'          => $this->request->getPost('period'),
                'sort_order'      => $this->request->getPost('sort_order') ?: 0,
                'job_description' => $this->request->getPost('job_description'),
                'photo'           => $photoName,
                'short_bio'       => $this->request->getPost('short_bio'),
            ]);

            return redirect()->to('/structures')
                ->with('success', 'Data struktur berhasil diperbarui.');
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [$e->getMessage()]);
        }
    }

    public function delete($id)
    {
        $structure = $this->structureModel->find($id);

        if (!$structure) {
            return redirect()->to('/structures')->with('error', 'Data struktur tidak ditemukan.');
        }

        if (!empty($structure['photo']) && file_exists(FCPATH . 'uploads/officials/' . $structure['photo'])) {
            unlink(FCPATH . 'uploads/officials/' . $structure['photo']);
        }

        $this->structureModel->delete($id);

        return redirect()->to('/structures')->with('success', 'Data struktur berhasil dihapus.');
    }

    private function processOfficialPhoto(?string $oldPhoto = null): ?string
    {
        $photo = $this->request->getFile('photo');

        if (!$photo || $photo->getError() === UPLOAD_ERR_NO_FILE) {
            return $oldPhoto;
        }

        if (!$photo->isValid()) {
            throw new \RuntimeException('Foto pengurus gagal diunggah.');
        }

        if ($photo->getSize() > (2 * 1024 * 1024)) {
            throw new \RuntimeException('Ukuran foto pengurus maksimal 2MB.');
        }

        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
        ];

        if (!in_array($photo->getMimeType(), $allowedMimes, true)) {
            throw new \RuntimeException(
                'Format foto harus JPG, JPEG, PNG, atau WEBP.'
            );
        }

        $uploadDirectory = FCPATH . 'uploads/officials';

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0775, true);
        }

        $photoName = $photo->getRandomName();
        $photo->move($uploadDirectory, $photoName);

        if (
            !empty($oldPhoto)
            && file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $oldPhoto)
        ) {
            unlink($uploadDirectory . DIRECTORY_SEPARATOR . $oldPhoto);
        }

        return $photoName;
    }
}