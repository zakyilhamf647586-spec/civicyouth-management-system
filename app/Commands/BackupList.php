<?php

namespace App\Commands;

use App\Libraries\BackupRecoveryService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupList extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'backup:list';
    protected $description = 'Tampilkan daftar backup GARDA 01.';

    public function run(array $params)
    {
        $backups = (new BackupRecoveryService())->listBackups();

        if ($backups === []) {
            CLI::write('Belum ada backup.', 'yellow');
            return EXIT_SUCCESS;
        }

        $rows = [];

        foreach ($backups as $backup) {
            $rows[] = [
                $backup['archive_name'] ?? '-',
                $backup['created_at'] ?? '-',
                $backup['tag'] ?? '-',
                $this->formatBytes((int) ($backup['archive_size'] ?? 0)),
                $backup['verification_status'] ?? '-',
                !empty($backup['protected']) ? 'yes' : 'no',
            ];
        }

        CLI::table(
            $rows,
            ['Archive', 'Created', 'Tag', 'Size', 'Verification', 'Protected']
        );

        return EXIT_SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $value = max(0, $bytes);
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        return number_format($value, $unit === 0 ? 0 : 2)
            . ' ' . $units[$unit];
    }
}
