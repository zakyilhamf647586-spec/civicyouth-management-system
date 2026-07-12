<?php

namespace App\Controllers;

use App\Models\MemberModel;

class MemberController extends BaseController
{
    protected $memberModel;

    public function __construct()
    {
        $this->memberModel = new MemberModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $rt      = $this->request->getGet('rt');
        $status  = $this->request->getGet('status');

        $query = $this->memberModel;

        if (!empty($keyword)) {
            $query = $query->groupStart()
                ->like('full_name', $keyword)
                ->orLike('phone', $keyword)
                ->orLike('position', $keyword)
                ->groupEnd();
        }

        if (!empty($rt)) {
            $query = $query->where('rt', $rt);
        }

        if (!empty($status)) {
            $query = $query->where('membership_status', $status);
        }

        $data = [
            'title'    => 'Data Anggota',
            'members'  => $query->orderBy('id', 'DESC')->paginate(10, 'members'),
            'pager'    => $this->memberModel->pager,
            'keyword'  => $keyword,
            'rt'       => $rt,
            'status'   => $status,
        ];

        return view('members/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Anggota'
        ];

        return view('members/create', $data);
    }

    public function store()
    {
        $rules = [
            'full_name' => 'required|min_length[3]',
            'rt'        => 'permit_empty|max_length[10]',
            'gender'    => 'permit_empty|in_list[male,female]',
            'phone'     => 'permit_empty|max_length[20]',
            'position'  => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->memberModel->save([
            'full_name'         => $this->request->getPost('full_name'),
            'rt'                => $this->request->getPost('rt'),
            'gender'            => $this->request->getPost('gender'),
            'birth_date'        => $this->request->getPost('birth_date') ?: null,
            'phone'             => $this->request->getPost('phone'),
            'address'           => $this->request->getPost('address'),
            'position'          => $this->request->getPost('position'),
            'membership_status' => $this->request->getPost('membership_status') ?: 'active',
        ]);

        return redirect()->to('/members')->with('success', 'Data anggota berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $member = $this->memberModel->find($id);

        if (!$member) {
            return redirect()->to('/members')->with('error', 'Data anggota tidak ditemukan.');
        }

        $data = [
            'title'  => 'Edit Anggota',
            'member' => $member
        ];

        return view('members/edit', $data);
    }

    public function update($id)
    {
        $member = $this->memberModel->find($id);

        if (!$member) {
            return redirect()->to('/members')->with('error', 'Data anggota tidak ditemukan.');
        }

        $rules = [
            'full_name' => 'required|min_length[3]',
            'rt'        => 'permit_empty|max_length[10]',
            'gender'    => 'permit_empty|in_list[male,female]',
            'phone'     => 'permit_empty|max_length[20]',
            'position'  => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->memberModel->update($id, [
            'full_name'         => $this->request->getPost('full_name'),
            'rt'                => $this->request->getPost('rt'),
            'gender'            => $this->request->getPost('gender'),
            'birth_date'        => $this->request->getPost('birth_date') ?: null,
            'phone'             => $this->request->getPost('phone'),
            'address'           => $this->request->getPost('address'),
            'position'          => $this->request->getPost('position'),
            'membership_status' => $this->request->getPost('membership_status') ?: 'active',
        ]);

        return redirect()->to('/members')->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function delete($id)
    {
        $member = $this->memberModel->find($id);

        if (!$member) {
            return redirect()->to('/members')->with('error', 'Data anggota tidak ditemukan.');
        }

        $this->memberModel->delete($id);

        return redirect()->to('/members')->with('success', 'Data anggota berhasil dihapus.');
    }
}