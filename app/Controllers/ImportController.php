<?php

namespace App\Controllers;

use App\Models\MemberModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportController extends BaseController
{
    public function membersForm()
    {
        return view('members/import', [
            'title' => 'Import Data Anggota'
        ]);
    }

    public function membersTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Template Anggota');

        $headers = [
            'Nama Lengkap',
            'RT',
            'Gender',
            'Tanggal Lahir',
            'No HP',
            'Alamat',
            'Jabatan/Posisi',
            'Status'
        ];

        $column = 'A';

        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        $sheet->setCellValue('A2', 'Contoh Nama Anggota');
        $sheet->setCellValue('B2', 'RT 01');
        $sheet->setCellValue('C2', 'male');
        $sheet->setCellValue('D2', '2005-01-31');
        $sheet->setCellValue('E2', '081234567890');
        $sheet->setCellValue('F2', 'Randugarut RW 01');
        $sheet->setCellValue('G2', 'Anggota');
        $sheet->setCellValue('H2', 'active');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'template-import-anggota-rw01.xlsx';
        $filePath = WRITEPATH . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($fileName);
    }

    public function membersImport()
    {
        $rules = [
            'excel_file' => 'uploaded[excel_file]|max_size[excel_file,5120]|ext_in[excel_file,xlsx,xls,csv]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('error', 'File tidak valid. Gunakan file .xlsx, .xls, atau .csv maksimal 5MB.');
        }

        $file = $this->request->getFile('excel_file');

        if (!$file->isValid()) {
            return redirect()->back()
                ->with('error', 'File gagal diupload.');
        }

        $spreadsheet = IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $memberModel = new MemberModel();

        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $fullName = trim($row[0] ?? '');
            $rt       = trim($row[1] ?? '');
            $gender   = trim($row[2] ?? '');
            $birthDate = trim($row[3] ?? '');
            $phone    = trim($row[4] ?? '');
            $address  = trim($row[5] ?? '');
            $position = trim($row[6] ?? '');
            $status   = trim($row[7] ?? '');

            if ($fullName === '') {
                continue;
            }

            if (!in_array($rt, ['RT 01', 'RT 02', 'RT 03', 'RT 04'])) {
                $errorCount++;
                continue;
            }

            if (!in_array($gender, ['male', 'female'])) {
                $gender = null;
            }

            if (!in_array($status, ['active', 'inactive'])) {
                $status = 'active';
            }

            $existingMember = $memberModel
                ->where('full_name', $fullName)
                ->where('rt', $rt)
                ->first();

            if ($existingMember) {
                $skipCount++;
                continue;
            }

            $memberModel->insert([
                'full_name'         => $fullName,
                'rt'                => $rt,
                'gender'            => $gender,
                'birth_date'        => $birthDate ?: null,
                'phone'             => $phone ?: null,
                'address'           => $address ?: null,
                'position'          => $position ?: 'Anggota',
                'membership_status' => $status,
            ]);

            $successCount++;
        }

        return redirect()->to('/members')
            ->with('success', 'Import selesai. Berhasil: ' . $successCount . ', dilewati: ' . $skipCount . ', error: ' . $errorCount . '.');
    }
}