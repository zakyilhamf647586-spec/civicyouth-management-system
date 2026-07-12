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
        $selectedMeetingId = $this->request->getGet('meeting_id');
        $selectedMemberId  = $this->request->getGet('member_id');

        $data = [
            'title'               => 'Tambah Absensi Rapat',
            'selected_meeting_id' => $selectedMeetingId,
            'selected_member_id'  => $selectedMemberId,
            'meetings'            => $this->meetingModel
                ->orderBy('meeting_date', 'DESC')
                ->findAll(),
            'members'             => $this->memberModel
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

        $meetingId = $this->request->getPost('meeting_id');
        $memberId  = $this->request->getPost('member_id');

        $existingAttendance = $this->attendanceModel
            ->where('meeting_id', $meetingId)
            ->where('member_id', $memberId)
            ->first();

        if ($existingAttendance) {
            return redirect()->to('/attendances/edit/' . $existingAttendance['id'])
                ->with('error', 'Absensi anggota ini untuk rapat tersebut sudah ada. Silakan edit data yang sudah tercatat.');
        }

        $this->attendanceModel->save([
            'meeting_id'        => $meetingId,
            'member_id'         => $memberId,
            'attendance_status' => $this->request->getPost('attendance_status'),
            'note'              => $this->request->getPost('note'),
        ]);

        return redirect()->to('/attendances/recap/' . $meetingId)
            ->with('success', 'Data absensi berhasil ditambahkan.');
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

        $meetingId = $this->request->getPost('meeting_id');
        $memberId  = $this->request->getPost('member_id');

        $duplicateAttendance = $this->attendanceModel
            ->where('meeting_id', $meetingId)
            ->where('member_id', $memberId)
            ->where('id !=', $id)
            ->first();

        if ($duplicateAttendance) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'duplicate' => 'Absensi anggota ini untuk rapat tersebut sudah tercatat pada data lain.'
                ]);
        }

        $this->attendanceModel->update($id, [
            'meeting_id'        => $meetingId,
            'member_id'         => $memberId,
            'attendance_status' => $this->request->getPost('attendance_status'),
            'note'              => $this->request->getPost('note'),
        ]);

        return redirect()->to('/attendances/recap/' . $meetingId)
            ->with('success', 'Data absensi berhasil diperbarui.');
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

    public function recap($meetingId)
    {
        $meetingId = (int) $meetingId;

        $meeting = $this->meetingModel->find($meetingId);

        if (!$meeting) {
            return redirect()->to('/attendances')->with('error', 'Data rapat tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $members = $db->table('members')
            ->select('
                members.id as member_id,
                members.full_name,
                members.rt,
                members.position,
                members.membership_status,
                attendances.id as attendance_id,
                attendances.attendance_status,
                attendances.note
            ')
            ->join(
                'attendances',
                'attendances.member_id = members.id AND attendances.meeting_id = ' . $meetingId,
                'left'
            )
            ->where('members.membership_status', 'active')
            ->orderBy('members.rt', 'ASC')
            ->orderBy('members.full_name', 'ASC')
            ->get()
            ->getResultArray();

        $summary = [
            'total_members' => count($members),
            'present'       => 0,
            'permission'    => 0,
            'absent'        => 0,
            'not_recorded'  => 0,
        ];

        foreach ($members as $member) {
            if ($member['attendance_status'] === 'present') {
                $summary['present']++;
            } elseif ($member['attendance_status'] === 'permission') {
                $summary['permission']++;
            } elseif ($member['attendance_status'] === 'absent') {
                $summary['absent']++;
            } else {
                $summary['not_recorded']++;
            }
        }

        $data = [
            'title'   => 'Rekap Absensi Rapat',
            'meeting' => $meeting,
            'members' => $members,
            'summary' => $summary,
        ];

        return view('attendances/recap', $data);
    }

    public function recapPrint($meetingId)
    {
        $meetingId = (int) $meetingId;

        $meeting = $this->meetingModel->find($meetingId);

        if (!$meeting) {
            return redirect()->to('/attendances')->with('error', 'Data rapat tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $members = $db->table('members')
            ->select('
                members.id as member_id,
                members.full_name,
                members.rt,
                members.position,
                attendances.id as attendance_id,
                attendances.attendance_status,
                attendances.note
            ')
            ->join(
                'attendances',
                'attendances.member_id = members.id AND attendances.meeting_id = ' . $meetingId,
                'left'
            )
            ->where('members.membership_status', 'active')
            ->orderBy('members.rt', 'ASC')
            ->orderBy('members.full_name', 'ASC')
            ->get()
            ->getResultArray();

        $summary = [
            'total_members' => count($members),
            'present'       => 0,
            'permission'    => 0,
            'absent'        => 0,
            'not_recorded'  => 0,
        ];

        foreach ($members as $member) {
            if ($member['attendance_status'] === 'present') {
                $summary['present']++;
            } elseif ($member['attendance_status'] === 'permission') {
                $summary['permission']++;
            } elseif ($member['attendance_status'] === 'absent') {
                $summary['absent']++;
            } else {
                $summary['not_recorded']++;
            }
        }

        $data = [
            'title'   => 'Cetak Rekap Absensi Rapat',
            'meeting' => $meeting,
            'members' => $members,
            'summary' => $summary,
        ];

        return view('attendances/recap_print', $data);
    }

    public function bulk($meetingId)
    {
        $meetingId = (int) $meetingId;

        $meeting = $this->meetingModel->find($meetingId);

        if (!$meeting) {
            return redirect()->to('/meetings')->with('error', 'Data rapat tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $members = $db->table('members')
            ->select('
                members.id as member_id,
                members.full_name,
                members.rt,
                members.position,
                attendances.id as attendance_id,
                attendances.attendance_status,
                attendances.note
            ')
            ->join(
                'attendances',
                'attendances.member_id = members.id AND attendances.meeting_id = ' . $meetingId,
                'left'
            )
            ->where('members.membership_status', 'active')
            ->orderBy('members.rt', 'ASC')
            ->orderBy('members.full_name', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'   => 'Input Absensi Massal',
            'meeting' => $meeting,
            'members' => $members,
        ];

        return view('attendances/bulk', $data);
    }

    public function bulkSave($meetingId)
    {
        $meetingId = (int) $meetingId;

        $meeting = $this->meetingModel->find($meetingId);

        if (!$meeting) {
            return redirect()->to('/meetings')->with('error', 'Data rapat tidak ditemukan.');
        }

        $statuses = $this->request->getPost('attendance_status');
        $notes    = $this->request->getPost('note');

        if (empty($statuses) || !is_array($statuses)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Belum ada data absensi yang dipilih.');
        }

        foreach ($statuses as $memberId => $status) {
            $memberId = (int) $memberId;

            if (empty($status)) {
                continue;
            }

            if (!in_array($status, ['present', 'permission', 'absent'])) {
                continue;
            }

            $existingAttendance = $this->attendanceModel
                ->where('meeting_id', $meetingId)
                ->where('member_id', $memberId)
                ->first();

            $data = [
                'meeting_id'        => $meetingId,
                'member_id'         => $memberId,
                'attendance_status' => $status,
                'note'              => $notes[$memberId] ?? null,
            ];

            if ($existingAttendance) {
                $this->attendanceModel->update($existingAttendance['id'], $data);
            } else {
                $this->attendanceModel->insert($data);
            }
        }

        return redirect()->to('/attendances/recap/' . $meetingId)
            ->with('success', 'Absensi massal berhasil disimpan.');
    }
}