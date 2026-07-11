<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\MeetingModel;
use App\Models\MemberModel;

class AttendanceController extends BaseController
{
    protected $attendanceModel;
    protected $meetingModel;
    protected $memberModel;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->meetingModel    = new MeetingModel();
        $this->memberModel     = new MemberModel();
    }

    public function index()
    {
        $attendances = $this->attendanceModel
            ->select('attendances.*, meetings.title as meeting_title, meetings.meeting_date, members.full_name, members.rt')
            ->join('meetings', 'meetings.id = attendances.meeting_id')
            ->join('members', 'members.id = attendances.member_id')
            ->orderBy('meetings.meeting_date', 'DESC')
            ->orderBy('attendances.id', 'DESC')
            ->findAll();

        $data = [
            'title'       => 'Absensi Rapat',
            'attendances' => $attendances
        ];

        return view('attendances/index', $data);
    }

    public function create()
    {
        $data = [
            'title'    => 'Tambah Absensi Rapat',
            'meetings' => $this->meetingModel
                ->orderBy('meeting_date', 'DESC')
                ->findAll(),
            'members'  => $this->memberModel
                ->where('membership_status', 'active')
                ->orderBy('full_name', 'ASC')
                ->findAll()
        ];

        return view('attendances/create', $data);
    }

    public function store()
    {
        $rules = [
            'meeting_id'        => 'required|numeric',
            'member_id'         => 'required|numeric',
            'attendance_status' => 'required|in_list[present,permission,absent]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->attendanceModel->save([
            'meeting_id'        => $this->request->getPost('meeting_id'),
            'member_id'         => $this->request->getPost('member_id'),
            'attendance_status' => $this->request->getPost('attendance_status'),
            'note'              => $this->request->getPost('note'),
        ]);

        return redirect()->to('/attendances')->with('success', 'Data absensi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $attendance = $this->attendanceModel->find($id);

        if (!$attendance) {
            return redirect()->to('/attendances')->with('error', 'Data absensi tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Absensi Rapat',
            'attendance' => $attendance,
            'meetings'   => $this->meetingModel
                ->orderBy('meeting_date', 'DESC')
                ->findAll(),
            'members'    => $this->memberModel
                ->where('membership_status', 'active')
                ->orderBy('full_name', 'ASC')
                ->findAll()
        ];

        return view('attendances/edit', $data);
    }

    public function update($id)
    {
        $attendance = $this->attendanceModel->find($id);

        if (!$attendance) {
            return redirect()->to('/attendances')->with('error', 'Data absensi tidak ditemukan.');
        }

        $rules = [
            'meeting_id'        => 'required|numeric',
            'member_id'         => 'required|numeric',
            'attendance_status' => 'required|in_list[present,permission,absent]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->attendanceModel->update($id, [
            'meeting_id'        => $this->request->getPost('meeting_id'),
            'member_id'         => $this->request->getPost('member_id'),
            'attendance_status' => $this->request->getPost('attendance_status'),
            'note'              => $this->request->getPost('note'),
        ]);

        return redirect()->to('/attendances')->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $attendance = $this->attendanceModel->find($id);

        if (!$attendance) {
            return redirect()->to('/attendances')->with('error', 'Data absensi tidak ditemukan.');
        }

        $this->attendanceModel->delete($id);

        return redirect()->to('/attendances')->with('success', 'Data absensi berhasil dihapus.');
    }
}