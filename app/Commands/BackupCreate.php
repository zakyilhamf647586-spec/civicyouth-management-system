<?php

namespace App\Commands;

use App\Libraries\BackupRecoveryService;
use App\Libraries\CmsAuditService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupCreate extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'backup:create';
    protected $description = 'Buat backup database dan upload pengguna.';
    protected $usage =
        'backup:create [--tag scheduled] [--database-only] [--protected]';

    protected $options = [
        '--tag' => 'Label backup.',
        '--database-only' => 'Hanya backup database.',
        '--protected' => 'Lindungi dari retensi otomatis.',
    ];

    public function run(array $params)
    {
        try {
            $result = (new BackupRecoveryService())->create([
                'tag' => (string) (CLI::getOption('tag') ?: 'manual-cli'),
                'database_only' => CLI::getOption('database-only') !== null,
                'protected' => CLI::getOption('protected') !== null,
            ]);

            (new CmsAuditService())->record([
                'module' => 'system',
                'event_type' => 'system.backup_created',
                'severity' => 'notice',
                'subject_type' => 'backup',
                'subject_key' => $result['backup_id'] ?? null,
                'subject_label' => $result['archive_name'] ?? 'Backup',
                'summary' => 'Backup CLI berhasil dibuat.',
                'metadata' => [
                    'archive_size' => $result['archive_size'] ?? null,
                    'tag' => $result['tag'] ?? null,
                ],
                'actor_type' => 'system',
                'actor_name' => 'Backup CLI',
            ]);

            CLI::write('Backup berhasil dibuat.', 'green');
            CLI::write('Archive : ' . $result['archive_name']);
            CLI::write('SHA-256 : ' . $result['archive_sha256']);

            return EXIT_SUCCESS;
        } catch (\Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }
}
