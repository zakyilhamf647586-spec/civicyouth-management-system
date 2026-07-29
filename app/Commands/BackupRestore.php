<?php

namespace App\Commands;

use App\Libraries\BackupRecoveryService;
use App\Libraries\CmsAuditService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupRestore extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'backup:restore';
    protected $description =
        'Pulihkan database dan upload dari backup terverifikasi.';
    protected $usage =
        'backup:restore --file <archive.zip> --yes --maintenance-confirmed YES';

    protected $options = [
        '--file' => 'Nama archive backup.',
        '--yes' => 'Konfirmasi operasi destruktif.',
        '--maintenance-confirmed' => 'Harus bernilai YES.',
        '--skip-safety-backup' => 'Lewati backup pra-restore.',
    ];

    public function run(array $params)
    {
        $file = (string) (
            CLI::getOption('file')
            ?: ($params[0] ?? '')
        );

        $confirmed = CLI::getOption('yes') !== null;
        $maintenance = (string) (
            CLI::getOption('maintenance-confirmed') ?? ''
        );

        if ($file === '' || !$confirmed || $maintenance !== 'YES') {
            CLI::error(
                'Restore ditolak. Gunakan --file, --yes, dan '
                . '--maintenance-confirmed YES.'
            );
            CLI::write(
                'Pastikan trafik telah dihentikan pada web server.',
                'yellow'
            );
            return EXIT_ERROR;
        }

        $service = new BackupRecoveryService();

        try {
            if (CLI::getOption('skip-safety-backup') === null) {
                CLI::write(
                    'Membuat safety backup pra-restore...',
                    'yellow'
                );

                $safety = $service->create([
                    'tag' => 'pre-restore-safety',
                    'protected' => true,
                ]);

                CLI::write(
                    'Safety backup: ' . $safety['archive_name'],
                    'green'
                );
            }

            $result = $service->restore($file);

            (new CmsAuditService())->record([
                'module' => 'system',
                'event_type' => 'system.backup_restored',
                'severity' => 'warning',
                'subject_type' => 'backup',
                'subject_label' => $file,
                'summary' => 'Disaster recovery restore selesai.',
                'metadata' => [
                    'restored_at' => $result['restored_at'] ?? null,
                    'directories' => $result['directories_restored'] ?? [],
                ],
                'actor_type' => 'system',
                'actor_name' => 'Restore CLI',
            ]);

            CLI::write('Restore selesai.', 'green');
            CLI::write(
                'Jalankan migrate:status, cache:clear, dan smoke test.',
                'yellow'
            );

            return EXIT_SUCCESS;
        } catch (\Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }
}
