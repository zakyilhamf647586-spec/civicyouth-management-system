<?php

namespace App\Controllers;

use App\Libraries\CmsAuditService;
use App\Models\CmsAuditLogModel;
use RuntimeException;

class CmsAuditController extends BaseController
{
    protected CmsAuditLogModel $auditModel;
    protected CmsAuditService $auditService;

    public function __construct()
    {
        $this->auditModel = new CmsAuditLogModel();
        $this->auditService = new CmsAuditService();
    }

    public function index()
    {
        $this->assertReady();

        $filters = $this->filters();

        $logs = $this->auditModel
            ->applyFilters($filters)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate(30, 'cms_audit');

        return view('cms_audit/index', [
            'title' => 'Audit Aktivitas CMS',
            'logs' => $logs,
            'pager' => $this->auditModel->pager,
            'filters' => $filters,
            'statistics' => $this->auditService->statistics(),
            'moduleLabels' => $this->auditService->moduleLabels(),
            'severityLabels' => $this->auditService->severityLabels(),
            'eventTypes' => (new CmsAuditLogModel())->availableEventTypes(),
        ]);
    }

    public function show(int $id)
    {
        $this->assertReady();

        $log = $this->auditModel->find($id);

        if (!$log) {
            throw new RuntimeException('Catatan audit tidak ditemukan.');
        }

        $metadata = json_decode((string) ($log['metadata'] ?? ''), true);

        return view('cms_audit/show', [
            'title' => 'Detail Audit #' . $id,
            'log' => $log,
            'metadata' => is_array($metadata) ? $metadata : [],
            'moduleLabels' => $this->auditService->moduleLabels(),
            'severityLabels' => $this->auditService->severityLabels(),
        ]);
    }

    public function export()
    {
        $this->assertReady();

        $filters = $this->filters();

        $logs = (new CmsAuditLogModel())
            ->applyFilters($filters)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll(5000);

        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            throw new RuntimeException('File ekspor belum dapat dibuat.');
        }

        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, [
            'Waktu',
            'Modul',
            'Event',
            'Severity',
            'Aktor',
            'Role',
            'Tipe Aktor',
            'Subjek',
            'Ringkasan',
            'Metode',
            'Path',
        ]);

        foreach ($logs as $log) {
            fputcsv($stream, [
                $log['created_at'] ?? '',
                $log['module'] ?? '',
                $log['event_type'] ?? '',
                $log['severity'] ?? '',
                $log['actor_name'] ?? '',
                $log['actor_role'] ?? '',
                $log['actor_type'] ?? '',
                $log['subject_label'] ?? $log['subject_key'] ?? '',
                $log['summary'] ?? '',
                $log['request_method'] ?? '',
                $log['request_path'] ?? '',
            ]);
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        $fileName = 'garda01-cms-audit-' . date('Ymd-His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader(
                'Content-Disposition',
                'attachment; filename="' . $fileName . '"'
            )
            ->setBody($content !== false ? $content : '');
    }

    /** @return array<string, string> */
    private function filters(): array
    {
        return [
            'q' => mb_substr(trim((string) $this->request->getGet('q')), 0, 100),
            'module' => mb_substr(trim((string) $this->request->getGet('module')), 0, 50),
            'event_type' => mb_substr(trim((string) $this->request->getGet('event_type')), 0, 100),
            'severity' => mb_substr(trim((string) $this->request->getGet('severity')), 0, 20),
            'actor_type' => mb_substr(trim((string) $this->request->getGet('actor_type')), 0, 20),
            'date_from' => $this->validDate((string) $this->request->getGet('date_from')),
            'date_to' => $this->validDate((string) $this->request->getGet('date_to')),
        ];
    }

    private function validDate(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return '';
        }

        $parts = array_map('intval', explode('-', $value));

        return checkdate($parts[1] ?? 0, $parts[2] ?? 0, $parts[0] ?? 0)
            ? $value
            : '';
    }

    private function assertReady(): void
    {
        if (!$this->auditService->ready()) {
            throw new RuntimeException(
                'Audit CMS belum tersedia. Jalankan php spark migrate.'
            );
        }
    }
}
