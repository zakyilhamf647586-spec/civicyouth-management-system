<?php

namespace App\Controllers;

use App\Libraries\BackupRecoveryService;
use App\Libraries\CmsAuditService;
use RuntimeException;

class BackupRecoveryController extends BaseController
{
    protected BackupRecoveryService $service;
    protected CmsAuditService $audit;

    public function __construct()
    {
        $this->service = new BackupRecoveryService();
        $this->audit = new CmsAuditService();
    }

    public function index()
    {
        return view('backup_recovery/index', [
            'title' => 'Backup & Pemulihan',
            'status' => $this->service->status(),
        ]);
    }

    public function create()
    {
        try {
            $backup = $this->service->create([
                'tag' => 'manual-web',
            ]);

            $this->audit->record([
                'module' => 'system',
                'event_type' => 'system.backup_created',
                'severity' => 'notice',
                'subject_type' => 'backup',
                'subject_key' => $backup['backup_id'] ?? null,
                'subject_label' => $backup['archive_name'] ?? 'Backup',
                'summary' => 'Backup manual berhasil dibuat.',
                'metadata' => [
                    'archive_size' => $backup['archive_size'] ?? null,
                    'database_method' => $backup['database_method'] ?? null,
                    'tag' => $backup['tag'] ?? null,
                ],
            ]);

            return redirect()->to('/system/backups')->with(
                'success',
                'Backup berhasil dibuat. Jalankan verifikasi integritasnya.'
            );
        } catch (\Throwable $exception) {
            return redirect()->to('/system/backups')->with(
                'error',
                $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Backup belum dapat dibuat.'
            );
        }
    }

    public function verify()
    {
        $archiveName = trim(
            (string) $this->request->getPost('archive_name')
        );

        try {
            $result = $this->service->verify($archiveName);

            $this->audit->record([
                'module' => 'system',
                'event_type' => 'system.backup_verified',
                'severity' => 'info',
                'subject_type' => 'backup',
                'subject_key' => $result['archive_sha256'] ?? null,
                'subject_label' => $result['archive_name'] ?? 'Backup',
                'summary' => 'Integritas backup berhasil diverifikasi.',
                'metadata' => [
                    'verified_files' => $result['verified_files'] ?? null,
                ],
            ]);

            return redirect()->to('/system/backups')->with(
                'success',
                'Backup berhasil diverifikasi.'
            );
        } catch (\Throwable $exception) {
            $this->audit->record([
                'module' => 'system',
                'event_type' => 'system.backup_verification_failed',
                'severity' => 'warning',
                'subject_type' => 'backup',
                'subject_label' => $archiveName,
                'summary' => 'Verifikasi backup gagal.',
                'details' => $exception->getMessage(),
            ]);

            return redirect()->to('/system/backups')->with(
                'error',
                $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Backup tidak dapat diverifikasi.'
            );
        }
    }

    public function prune()
    {
        try {
            $result = $this->service->prune(false);

            $this->audit->record([
                'module' => 'system',
                'event_type' => 'system.backup_pruned',
                'severity' => 'notice',
                'subject_type' => 'backup',
                'subject_label' => 'Retention Policy',
                'summary' => 'Retensi backup dijalankan.',
                'metadata' => [
                    'deleted_count' => count($result['deleted']),
                    'retained_count' => count($result['retained']),
                ],
            ]);

            return redirect()->to('/system/backups')->with(
                'success',
                count($result['deleted'])
                . ' backup lama dibersihkan berdasarkan kebijakan retensi.'
            );
        } catch (\Throwable $exception) {
            return redirect()->to('/system/backups')->with(
                'error',
                $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Retensi backup belum dapat dijalankan.'
            );
        }
    }

    public function manifest(string $archiveName)
    {
        try {
            $manifest = $this->service->manifest($archiveName);
        } catch (\Throwable $exception) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Manifest backup tidak ditemukan.',
                ]);
        }

        return $this->response
            ->setHeader('Cache-Control', 'private, no-store')
            ->setJSON($manifest);
    }
}
