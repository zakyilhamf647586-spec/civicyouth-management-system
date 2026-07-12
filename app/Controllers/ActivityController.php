<?php

namespace App\Controllers;

use App\Models\ActivityModel;

class ActivityController extends BaseController
{
    protected $activityModel;

    public function __construct()
    {
        $this->activityModel = new ActivityModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Kegiatan',
            'activities' => $this->activityModel
                ->orderBy('activity_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->findAll()
        ];

        return view('activities/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Kegiatan'
        ];

        return view('activities/create', $data);
    }

    public function store()
    {
        $rules = [
            'title'         => 'required|min_length[3]',
            'activity_date' => 'required|valid_date',
            'status'        => 'required|in_list[planned,completed,cancelled]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->activityModel->save([
            'title'              => $this->request->getPost('title'),
            'activity_date'      => $this->request->getPost('activity_date'),
            'location'           => $this->request->getPost('location'),
            'description'        => $this->request->getPost('description'),
            'result'             => $this->request->getPost('result'),
            'documentation_link' => $this->request->getPost('documentation_link'),
            'status'             => $this->request->getPost('status'),
        ]);

        return redirect()->to('/activities')->with('success', 'Data kegiatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')->with('error', 'Data kegiatan tidak ditemukan.');
        }

        $data = [
            'title'    => 'Edit Kegiatan',
            'activity' => $activity
        ];

        return view('activities/edit', $data);
    }

    public function update($id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')->with('error', 'Data kegiatan tidak ditemukan.');
        }

        $rules = [
            'title'         => 'required|min_length[3]',
            'activity_date' => 'required|valid_date',
            'status'        => 'required|in_list[planned,completed,cancelled]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->activityModel->update($id, [
            'title'              => $this->request->getPost('title'),
            'activity_date'      => $this->request->getPost('activity_date'),
            'location'           => $this->request->getPost('location'),
            'description'        => $this->request->getPost('description'),
            'result'             => $this->request->getPost('result'),
            'documentation_link' => $this->request->getPost('documentation_link'),
            'status'             => $this->request->getPost('status'),
        ]);

        return redirect()->to('/activities')->with('success', 'Data kegiatan berhasil diperbarui.');
    }

    public function delete($id)
    {
        $activity = $this->activityModel->find($id);

        if (!$activity) {
            return redirect()->to('/activities')->with('error', 'Data kegiatan tidak ditemukan.');
        }

        $this->activityModel->delete($id);

        return redirect()->to('/activities')->with('success', 'Data kegiatan berhasil dihapus.');
    }
}