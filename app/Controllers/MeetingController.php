<?php

namespace App\Controllers;

use App\Models\MeetingModel;

class MeetingController extends BaseController
{
    protected $meetingModel;

    public function __construct()
    {
        $this->meetingModel = new MeetingModel();
    }

    public function index()
    {
        $data = [
            'title'    => 'Agenda Rapat',
            'meetings' => $this->meetingModel
                ->orderBy('meeting_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->findAll()
        ];

        return view('meetings/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Agenda Rapat'
        ];

        return view('meetings/create', $data);
    }

    public function store()
    {
        $rules = [
            'title'        => 'required|min_length[3]',
            'meeting_date' => 'required|valid_date',
            'status'       => 'required|in_list[scheduled,completed,cancelled]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->meetingModel->save([
            'title'        => $this->request->getPost('title'),
            'meeting_date' => $this->request->getPost('meeting_date'),
            'start_time'   => $this->request->getPost('start_time') ?: null,
            'end_time'     => $this->request->getPost('end_time') ?: null,
            'location'     => $this->request->getPost('location'),
            'agenda'       => $this->request->getPost('agenda'),
            'decisions'    => $this->request->getPost('decisions'),
            'notes'        => $this->request->getPost('notes'),
            'status'       => $this->request->getPost('status'),
        ]);

        return redirect()->to('/meetings')->with('success', 'Agenda rapat berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $meeting = $this->meetingModel->find($id);

        if (!$meeting) {
            return redirect()->to('/meetings')->with('error', 'Data rapat tidak ditemukan.');
        }

        $data = [
            'title'   => 'Edit Agenda Rapat',
            'meeting' => $meeting
        ];

        return view('meetings/edit', $data);
    }

    public function update($id)
    {
        $meeting = $this->meetingModel->find($id);

        if (!$meeting) {
            return redirect()->to('/meetings')->with('error', 'Data rapat tidak ditemukan.');
        }

        $rules = [
            'title'        => 'required|min_length[3]',
            'meeting_date' => 'required|valid_date',
            'status'       => 'required|in_list[scheduled,completed,cancelled]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->meetingModel->update($id, [
            'title'        => $this->request->getPost('title'),
            'meeting_date' => $this->request->getPost('meeting_date'),
            'start_time'   => $this->request->getPost('start_time') ?: null,
            'end_time'     => $this->request->getPost('end_time') ?: null,
            'location'     => $this->request->getPost('location'),
            'agenda'       => $this->request->getPost('agenda'),
            'decisions'    => $this->request->getPost('decisions'),
            'notes'        => $this->request->getPost('notes'),
            'status'       => $this->request->getPost('status'),
        ]);

        return redirect()->to('/meetings')->with('success', 'Agenda rapat berhasil diperbarui.');
    }

    public function delete($id)
    {
        $meeting = $this->meetingModel->find($id);

        if (!$meeting) {
            return redirect()->to('/meetings')->with('error', 'Data rapat tidak ditemukan.');
        }

        $this->meetingModel->delete($id);

        return redirect()->to('/meetings')->with('success', 'Agenda rapat berhasil dihapus.');
    }
}