<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\CashTransactionModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExportController extends BaseController
{
    public function members()
    {
        $memberModel = new MemberModel();

        $members = $memberModel
            ->orderBy('rt', 'ASC')
            ->orderBy('full_name', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Data Anggota');

        $headers = [
            'No',
            'Nama Lengkap',
            'RT',
            'Gender',
            'Tanggal Lahir',
            'No HP',
            'Alamat',
            'Jabatan/Posisi',
            'Status',
            'Tanggal Input',
        ];

        $column = 'A';

        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        $row = 2;
        $no = 1;

        foreach ($members as $member) {
            $gender = '-';

            if (($member['gender'] ?? '') === 'male') {
                $gender = 'Laki-laki';
            } elseif (($member['gender'] ?? '') === 'female') {
                $gender = 'Perempuan';
            }

            $status = '-';

            if (($member['membership_status'] ?? '') === 'active') {
                $status = 'Aktif';
            } elseif (($member['membership_status'] ?? '') === 'inactive') {
                $status = 'Tidak Aktif';
            }

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $member['full_name'] ?? '-');
            $sheet->setCellValue('C' . $row, $member['rt'] ?? '-');
            $sheet->setCellValue('D' . $row, $gender);
            $sheet->setCellValue('E' . $row, $member['birth_date'] ?? '-');
            $sheet->setCellValue('F' . $row, $member['phone'] ?? '-');
            $sheet->setCellValue('G' . $row, $member['address'] ?? '-');
            $sheet->setCellValue('H' . $row, $member['position'] ?? '-');
            $sheet->setCellValue('I' . $row, $status);
            $sheet->setCellValue('J' . $row, $member['created_at'] ?? '-');

            $row++;
        }

        $lastRow = $row - 1;

        $this->applyExcelStyle($sheet, 'A1:J' . $lastRow);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'data-anggota-karang-taruna-rw01.xlsx';
        $filePath = WRITEPATH . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($fileName);
    }

    public function cash()
    {
        $cashModel = new CashTransactionModel();

        $transactions = $cashModel
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Kas Organisasi');

        $headers = [
            'No',
            'Tanggal',
            'Jenis',
            'Kategori',
            'Nominal',
            'Keterangan',
            'Dicatat Oleh',
            'Tanggal Input',
        ];

        $column = 'A';

        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        $row = 2;
        $no = 1;

        foreach ($transactions as $transaction) {
            $type = '-';

            if (($transaction['transaction_type'] ?? '') === 'income') {
                $type = 'Pemasukan';
            } elseif (($transaction['transaction_type'] ?? '') === 'expense') {
                $type = 'Pengeluaran';
            }

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $transaction['transaction_date'] ?? '-');
            $sheet->setCellValue('C' . $row, $type);
            $sheet->setCellValue('D' . $row, $transaction['category'] ?? '-');
            $sheet->setCellValue('E' . $row, $transaction['amount'] ?? 0);
            $sheet->setCellValue('F' . $row, $transaction['description'] ?? '-');
            $sheet->setCellValue('G' . $row, $transaction['created_by'] ?? '-');
            $sheet->setCellValue('H' . $row, $transaction['created_at'] ?? '-');

            $row++;
        }

        $lastRow = $row - 1;

        $this->applyExcelStyle($sheet, 'A1:H' . $lastRow);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('E2:E' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        $fileName = 'kas-organisasi-karang-taruna-rw01.xlsx';
        $filePath = WRITEPATH . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $this->response->download($filePath, null)->setFileName($fileName);
    }

    private function applyExcelStyle($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'argb' => 'FF111827',
                    ],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('A1:' . explode(':', $range)[1])->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A1:' . preg_replace('/\d+/', '1', explode(':', $range)[1]))->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FF08264A',
                ],
            ],
        ]);
    }
}