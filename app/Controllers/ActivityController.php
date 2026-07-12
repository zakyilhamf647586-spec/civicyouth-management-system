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
        $keyword  = $this->request->getGet('keyword');
        $status   = $this->request->getGet('status');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo   = $this->request->getGet('date_to');

        $query = $this->activityModel;

        if (!empty($keyword)) {
            $query = $query->groupStart()
                ->like('title', $keyword)
                ->orLike('location', $keyword)
                ->orLike('description', $keyword)
                ->orLike('result', $keyword)
                ->groupEnd();
        }

        if (!empty($status)) {
            $query = $query->where('status', $status);
        }

        if (!empty($dateFrom)) {
            $query = $query->where('activity_date >=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query = $query->where('activity_date <=', $dateTo);
        }

        $data = [
            'title'      => 'Kegiatan',
            'activities' => $query
                ->orderBy('activity_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->paginate(10, 'activities'),
            'pager'      => $this->activityModel->pager,
            'keyword'    => $keyword,
            'status'     => $status,
            'date_from'  => $dateFrom,
            'date_to'    => $dateTo,
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