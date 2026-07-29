<?php

namespace App\Controllers;

use App\Libraries\CmsAuditService;
use App\Libraries\ProductionReadinessService;

class ProductionReadinessController extends BaseController
{
    protected ProductionReadinessService $service;
    protected CmsAuditService $auditService;

    public function __construct()
    {
        $this->service = new ProductionReadinessService();
        $this->auditService = new CmsAuditService();
    }

    public function index()
    {
        $report = $this->service->report();

        $this->auditService->record([
            'module' => 'system',
            'event_type' => 'system.production_readiness_viewed',
            'severity' => 'info',
            'subject_type' => 'deployment',
            'subject_key' => $report['environment']['release'] ?: 'unversioned',
            'subject_label' => 'Production Readiness',
            'summary' => 'Laporan kesiapan production dibuka.',
            'metadata' => [
                'score' => $report['summary']['score'],
                'blocking' => $report['summary']['blocking'],
                'environment' => $report['environment']['name'],
            ],
        ]);

        return view('production_readiness/index', [
            'title' => 'Kesiapan Produksi',
            'report' => $report,
            'categoryLabels' => [
                'runtime' => 'Runtime & PHP',
                'environment' => 'Environment',
                'security' => 'Keamanan',
                'database' => 'Database',
                'filesystem' => 'Filesystem',
                'recovery' => 'Backup & Recovery',
                'application' => 'Aplikasi',
                'deployment' => 'Aset Deployment',
                'manual' => 'Validasi Manual',
            ],
        ]);
    }

    public function export()
    {
        $report = $this->service->report();

        $this->auditService->record([
            'module' => 'system',
            'event_type' => 'system.production_readiness_exported',
            'severity' => 'notice',
            'subject_type' => 'deployment',
            'subject_key' => $report['environment']['release'] ?: 'unversioned',
            'subject_label' => 'Production Readiness',
            'summary' => 'Laporan kesiapan production diekspor.',
            'metadata' => [
                'score' => $report['summary']['score'],
                'blocking' => $report['summary']['blocking'],
            ],
        ]);

        $json = json_encode(
            $report,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        $fileName = 'garda01-production-readiness-'
            . date('Ymd-His')
            . '.json';

        return $this->response
            ->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->setHeader(
                'Content-Disposition',
                'attachment; filename="' . $fileName . '"'
            )
            ->setBody($json !== false ? $json : '{}');
    }
}
