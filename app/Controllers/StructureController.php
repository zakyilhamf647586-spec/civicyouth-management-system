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
            'position_name' => 'required|min_length[3]',
            'member_id'     => 'permit_empty|numeric',
            'sort_order'    => 'permit_empty|numeric',
            'status'        => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->structureModel->save([
            'member_id'      => $this->request->getPost('member_id') ?: null,
            'position_name'  => $this->request->getPost('position_name'),
            'division'       => $this->request->getPost('division'),
            'rt_scope'       => $this->request->getPost('rt_scope'),
            'period'         => $this->request->getPost('period'),
            'description'    => $this->request->getPost('description'),
            'sort_order'     => $this->request->getPost('sort_order') ?: 0,
            'status'         => $this->request->getPost('status'),
        ]);

        return redirect()->to('/structures')->with('success', 'Struktur pengurus berhasil ditambahkan.');
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
            return redirect()->to('/structures')->with('error', 'Data struktur tidak ditemukan.');
        }

        $rules = [
            'position_name' => 'required|min_length[3]',
            'member_id'     => 'permit_empty|numeric',
            'sort_order'    => 'permit_empty|numeric',
            'status'        => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->structureModel->update($id, [
            'member_id'      => $this->request->getPost('member_id') ?: null,
            'position_name'  => $this->request->getPost('position_name'),
            'division'       => $this->request->getPost('division'),
            'rt_scope'       => $this->request->getPost('rt_scope'),
            'period'         => $this->request->getPost('period'),
            'description'    => $this->request->getPost('description'),
            'sort_order'     => $this->request->getPost('sort_order') ?: 0,
            'status'         => $this->request->getPost('status'),
        ]);

        return redirect()->to('/structures')->with('success', 'Struktur pengurus berhasil diperbarui.');
    }

    public function delete($id)
    {
        $structure = $this->structureModel->find($id);

        if (!$structure) {
            return redirect()->to('/structures')->with('error', 'Data struktur tidak ditemukan.');
        }

        $this->structureModel->delete($id);

        return redirect()->to('/structures')->with('success', 'Struktur pengurus berhasil dihapus.');
    }
}