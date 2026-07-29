<?php

namespace App\Commands;

use App\Libraries\BackupRecoveryService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupVerify extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'backup:verify';
    protected $description = 'Verifikasi checksum dan isi archive backup.';
    protected $usage = 'backup:verify --file <archive.zip>';

    protected $options = [
        '--file' => 'Nama archive backup.',
    ];

    public function run(array $params)
    {
        $file = (string) (
            CLI::getOption('file')
            ?: ($params[0] ?? '')
        );

        if ($file === '') {
            CLI::error('Gunakan --file nama-archive.zip');
            return EXIT_ERROR;
        }

        try {
            $result = (new BackupRecoveryService())->verify($file);

            CLI::write('Backup terverifikasi.', 'green');
            CLI::write('File diperiksa: ' . $result['verified_files']);
            CLI::write('SHA-256: ' . $result['archive_sha256']);

            return EXIT_SUCCESS;
        } catch (\Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }
}
