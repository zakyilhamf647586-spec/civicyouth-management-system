<?php

namespace App\Commands;

use App\Libraries\ProductionReadinessService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ProductionCheck extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'production:check';
    protected $description = 'Periksa kesiapan environment untuk deployment production.';
    protected $usage = 'production:check [--json] [--strict]';

    /** @var array<string, string> */
    protected $options = [
        '--json' => 'Tampilkan laporan JSON.',
        '--strict' => 'Gagal bila masih ada warning.',
    ];

    public function run(array $params)
    {
        $report = (new ProductionReadinessService())->report();

        if (array_key_exists('json', $params)) {
            $json = json_encode(
                $report,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            CLI::write($json !== false ? $json : '{}');
        } else {
            CLI::newLine();
            CLI::write('GARDA 01 — Production Readiness', 'yellow');
            CLI::write(str_repeat('=', 38), 'dark_gray');
            CLI::write('Environment : ' . $report['environment']['name']);
            CLI::write('Base URL    : ' . $report['environment']['base_url']);
            CLI::write('PHP         : ' . $report['environment']['php_version']);
            CLI::write('Score       : ' . $report['summary']['score'] . '/100');
            CLI::write('Blocking    : ' . $report['summary']['blocking']);
            CLI::newLine();

            $rows = [];

            foreach ($report['checks'] as $check) {
                if ($check['status'] === 'manual') {
                    continue;
                }

                $rows[] = [
                    match ($check['status']) {
                        'pass' => 'PASS',
                        'warning' => 'WARN',
                        default => 'FAIL',
                    },
                    $check['category'],
                    $check['title'],
                    $check['message'],
                ];
            }

            CLI::table(
                $rows,
                ['Status', 'Kategori', 'Pemeriksaan', 'Hasil']
            );
        }

        $blocking = (int) $report['summary']['blocking'];
        $warnings = (int) $report['summary']['counts']['warning'];
        $strict = array_key_exists('strict', $params);

        return $blocking > 0 || ($strict && $warnings > 0)
            ? EXIT_ERROR
            : EXIT_SUCCESS;
    }
}
