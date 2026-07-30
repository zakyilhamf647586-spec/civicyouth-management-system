<?php

namespace App\Commands;

use App\Libraries\CmsAuditService;
use App\Libraries\SystemMonitoringService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SystemHealthSnapshot extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'system:health:snapshot';
    protected $description = 'Simpan snapshot kesehatan untuk monitoring terjadwal.';

    public function run(array $params)
    {
        try {
            $report = (new SystemMonitoringService())->snapshot('scheduler');

            (new CmsAuditService())->record([
                'module' => 'system',
                'event_type' => 'system.health_snapshot_created',
                'severity' => $report['status'] === 'critical'
                    ? 'warning'
                    : 'info',
                'subject_type' => 'monitoring',
                'subject_id' => $report['snapshot_id'] ?? null,
                'subject_label' => 'Scheduled Health Snapshot',
                'summary' => 'Snapshot kesehatan terjadwal dibuat.',
                'metadata' => [
                    'status' => $report['status'],
                    'score' => $report['score'],
                ],
                'actor_type' => 'system',
                'actor_name' => 'Health Scheduler',
            ]);

            CLI::write(
                'Snapshot #' . ($report['snapshot_id'] ?? '-')
                . ' — ' . strtoupper($report['status'])
                . ' — ' . $report['score'] . '/100',
                $report['status'] === 'critical' ? 'red' : 'green'
            );

            return $report['status'] === 'critical'
                ? EXIT_ERROR
                : EXIT_SUCCESS;
        } catch (\Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }
}
