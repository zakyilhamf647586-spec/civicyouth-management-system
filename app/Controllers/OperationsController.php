<?php

namespace App\Controllers;

use App\Libraries\CmsAuditService;
use App\Libraries\SystemMonitoringService;
use RuntimeException;

class OperationsController extends BaseController
{
    protected SystemMonitoringService $monitoring;
    protected CmsAuditService $audit;

    public function __construct()
    {
        $this->monitoring = new SystemMonitoringService();
        $this->audit = new CmsAuditService();
    }

    public function index()
    {
        try {
            $report = $this->monitoring->snapshotIfDue('dashboard');
        } catch (\Throwable $exception) {
            $report = $this->monitoring->report();
        }

        $this->audit->record([
            'module' => 'system',
            'event_type' => 'system.operations_dashboard_viewed',
            'severity' => 'info',
            'subject_type' => 'monitoring',
            'subject_label' => 'Operational Dashboard',
            'summary' => 'Dashboard operasional dibuka.',
            'metadata' => [
                'status' => $report['status'],
                'score' => $report['score'],
            ],
        ]);

        return view('operations/index', [
            'title' => 'Operational Dashboard',
            'report' => $report,
            'statistics' => $this->monitoring->statistics(),
            'snapshots' => $this->monitoring->recentSnapshots(48),
            'incidents' => $this->monitoring->recentIncidents(20),
            'securityEvents' => $this->recentSecurityEvents(),
        ]);
    }

    public function snapshot()
    {
        try {
            $report = $this->monitoring->snapshot('manual-web');

            $this->audit->record([
                'module' => 'system',
                'event_type' => 'system.health_snapshot_created',
                'severity' => $report['status'] === 'critical'
                    ? 'warning'
                    : 'notice',
                'subject_type' => 'monitoring',
                'subject_id' => $report['snapshot_id'] ?? null,
                'subject_label' => 'Health Snapshot',
                'summary' => 'Snapshot kesehatan manual dibuat.',
                'metadata' => [
                    'status' => $report['status'],
                    'score' => $report['score'],
                ],
            ]);

            return redirect()->to('/system/operations')->with(
                'success',
                'Snapshot kesehatan berhasil dibuat.'
            );
        } catch (\Throwable $exception) {
            return redirect()->to('/system/operations')->with(
                'error',
                $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Snapshot kesehatan belum dapat dibuat.'
            );
        }
    }

    public function acknowledge(int $incidentId)
    {
        $userId = (int) session()->get('user_id');

        if (!$this->monitoring->acknowledgeIncident($incidentId, $userId)) {
            return redirect()->to('/system/operations')->with(
                'error',
                'Insiden tidak ditemukan atau sudah selesai.'
            );
        }

        $this->audit->record([
            'module' => 'system',
            'event_type' => 'system.health_incident_acknowledged',
            'severity' => 'notice',
            'subject_type' => 'health_incident',
            'subject_id' => $incidentId,
            'subject_label' => 'Incident #' . $incidentId,
            'summary' => 'Insiden kesehatan telah diakui oleh operator.',
        ]);

        return redirect()->to('/system/operations')->with(
            'success',
            'Insiden ditandai telah diketahui. Status akan selesai otomatis setelah komponen pulih.'
        );
    }

    public function export()
    {
        $payload = [
            'report' => $this->monitoring->report(),
            'statistics' => $this->monitoring->statistics(),
            'snapshots' => $this->monitoring->recentSnapshots(96),
            'incidents' => $this->monitoring->recentIncidents(50),
            'exported_at' => date(DATE_ATOM),
        ];

        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        $this->audit->record([
            'module' => 'system',
            'event_type' => 'system.operations_report_exported',
            'severity' => 'notice',
            'subject_type' => 'monitoring',
            'subject_label' => 'Operational Report',
            'summary' => 'Laporan operasional diekspor.',
        ]);

        return $this->response
            ->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->setHeader(
                'Content-Disposition',
                'attachment; filename="garda01-operations-'
                . date('Ymd-His')
                . '.json"'
            )
            ->setBody($json !== false ? $json : '{}');
    }

    /** @return list<array<string, mixed>> */
    private function recentSecurityEvents(): array
    {
        try {
            $db = db_connect();

            if (!$db->tableExists('cms_audit_logs')) {
                return [];
            }

            return $db->table('cms_audit_logs')
                ->select(
                    'id, event_type, severity, summary, actor_name, request_path, created_at'
                )
                ->groupStart()
                ->where('severity', 'security')
                ->orWhere('severity', 'warning')
                ->groupEnd()
                ->orderBy('id', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();
        } catch (\Throwable $exception) {
            return [];
        }
    }
}
