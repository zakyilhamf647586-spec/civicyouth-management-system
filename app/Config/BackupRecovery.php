<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class BackupRecovery extends BaseConfig
{
    public string $backupDirectory = WRITEPATH . 'backups';
    public string $temporaryDirectory = WRITEPATH . 'backup-temp';
    public string $lockFile = WRITEPATH . 'backup-operation.lock';

    public int $keepLatest = 10;
    public int $keepDailyDays = 14;
    public int $keepWeeklyWeeks = 8;
    public int $keepMonthlyMonths = 12;

    public int $warningAgeHours = 48;
    public int $criticalAgeHours = 168;
    public int $minimumFreeSpaceBytes = 1073741824;

    public string $mysqlDumpPath = '';
    public string $mysqlClientPath = '';

    /** @var array<string, string> */
    public array $includedDirectories = [
        'public_uploads' => FCPATH . 'uploads',
        'internal_uploads' => WRITEPATH . 'uploads',
    ];

    /** @var list<string> */
    public array $ignoredNames = [
        '.DS_Store',
        'Thumbs.db',
        'desktop.ini',
    ];
}
