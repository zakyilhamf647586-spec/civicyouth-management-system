<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SystemMonitoring extends BaseConfig
{
    public int $snapshotIntervalSeconds = 300;
    public int $snapshotRetentionDays = 30;

    public int $databaseWarningLatencyMs = 250;
    public int $databaseCriticalLatencyMs = 1000;

    public int $diskWarningBytes = 1073741824;
    public int $diskCriticalBytes = 536870912;

    public int $backupWarningAgeHours = 48;
    public int $backupCriticalAgeHours = 168;

    public int $logScanMaximumBytes = 2097152;
    public int $recentSecurityWindowHours = 24;
    public int $staleBackupLockMinutes = 120;

    /** @var list<string> */
    public array $writableDirectories = [
        WRITEPATH,
        WRITEPATH . 'cache',
        WRITEPATH . 'logs',
        WRITEPATH . 'session',
        WRITEPATH . 'uploads',
        WRITEPATH . 'backups',
        WRITEPATH . 'backup-temp',
        FCPATH . 'uploads',
    ];
}
