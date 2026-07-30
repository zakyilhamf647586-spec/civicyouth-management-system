<?php

namespace App\Commands;

use App\Libraries\SystemMonitoringService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SystemHealth extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'system:health';
    protected $description = 'Periksa kesehatan operasional GARDA 01.';
    protected $usage = 'system:health [--json] [--strict] [--snapshot]';

    protected $options = [
        '--json' => 'Tampilkan laporan JSON.',
        '--strict' => 'Warning juga menghasilkan exit code gagal.',
        '--snapshot' => 'Simpan hasil sebagai snapshot monitoring.',
    ];

    public function run(array $params)
    {
        $service = new SystemMonitoringService();

        try {
            $report = CLI::getOption('snapshot') !== null
                ? $service->snapshot('manual-cli')
                : $service->report();
        } catch (\Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }

        if (CLI::getOption('json') !== null) {
            $json = json_encode(
                $report,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            CLI::write($json !== false ? $json : '{}');
        } else {
            CLI::newLine();
            CLI::write('GARDA 01 — System Health', 'yellow');
            CLI::write(str_repeat('=', 34), 'dark_gray');
            CLI::write('Status : ' . strtoupper($report['status']));
            CLI::write('Score  : ' . $report['score'] . '/100');
            CLI::write('Waktu  : ' . $report['generated_at']);
            CLI::newLine();

            $rows = [];

            foreach ($report['checks'] as $check) {
                $rows[] = [
                    strtoupper($check['status']),
                    $check['title'],
                    $check['message'],
                ];
            }

            CLI::table($rows, ['Status', 'Komponen', 'Hasil']);
        }

        $strict = CLI::getOption('strict') !== null;

        if ($report['status'] === 'critical') {
            return EXIT_ERROR;
        }

        if ($strict && $report['status'] === 'degraded') {
            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
