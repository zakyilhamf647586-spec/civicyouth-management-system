<?php

namespace App\Commands;

use App\Libraries\BackupRecoveryService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupPrune extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'backup:prune';
    protected $description = 'Bersihkan backup lama berdasarkan retensi.';
    protected $usage = 'backup:prune [--dry-run]';

    protected $options = [
        '--dry-run' => 'Tampilkan kandidat tanpa menghapus.',
    ];

    public function run(array $params)
    {
        $dryRun = CLI::getOption('dry-run') !== null;

        try {
            $result = (new BackupRecoveryService())->prune($dryRun);

            CLI::write(
                $dryRun ? 'Simulasi retensi selesai.' : 'Retensi selesai.',
                'green'
            );
            CLI::write('Dihapus/kandidat: ' . count($result['deleted']));
            CLI::write('Dipertahankan: ' . count($result['retained']));

            foreach ($result['deleted'] as $archive) {
                CLI::write('- ' . $archive, 'yellow');
            }

            return EXIT_SUCCESS;
        } catch (\Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }
}
