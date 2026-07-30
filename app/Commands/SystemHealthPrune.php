<?php

namespace App\Commands;

use App\Libraries\SystemMonitoringService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SystemHealthPrune extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'system:health:prune';
    protected $description = 'Bersihkan snapshot dan insiden monitoring lama.';
    protected $usage = 'system:health:prune [--days 30]';

    protected $options = [
        '--days' => 'Retensi snapshot dalam hari.',
    ];

    public function run(array $params)
    {
        $days = CLI::getOption('days');

        try {
            $result = (new SystemMonitoringService())->prune(
                $days !== null ? (int) $days : null
            );

            CLI::write('Retensi monitoring selesai.', 'green');
            CLI::write(
                'Snapshot lama: ' . $result['deleted_snapshots']
            );
            CLI::write(
                'Insiden lama: ' . $result['deleted_incidents']
            );

            return EXIT_SUCCESS;
        } catch (\Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }
}
