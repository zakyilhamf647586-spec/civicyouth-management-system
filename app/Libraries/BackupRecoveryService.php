<?php

namespace App\Libraries;

use Config\BackupRecovery;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupRecoveryService
{
    protected BackupRecovery $config;

    /** @var resource|null */
    protected $lockHandle = null;

    public function __construct()
    {
        $this->config = config(BackupRecovery::class);
        $this->ensureStorage();
    }

    /** @return array<string, mixed> */
    public function status(): array
    {
        $backups = $this->listBackups();
        $latest = $backups[0] ?? null;
        $totalBytes = 0;

        foreach ($backups as $backup) {
            $totalBytes += (int) ($backup['archive_size'] ?? 0);
        }

        $freeSpace = @disk_free_space($this->backupDirectory());

        return [
            'backup_directory' => $this->backupDirectory(),
            'directory_ready' => is_dir($this->backupDirectory())
                && is_writable($this->backupDirectory()),
            'temporary_ready' => is_dir($this->temporaryDirectory())
                && is_writable($this->temporaryDirectory()),
            'locked' => is_file($this->config->lockFile),
            'count' => count($backups),
            'total_bytes' => $totalBytes,
            'free_bytes' => $freeSpace !== false ? (int) $freeSpace : null,
            'latest' => $latest,
            'backups' => $backups,
            'native_tools' => [
                'mysqldump' => $this->findMySqlExecutable('mysqldump'),
                'mysql' => $this->findMySqlExecutable('mysql'),
            ],
            'retention' => [
                'keep_latest' => $this->integerEnv(
                    'backup.keepLatest',
                    $this->config->keepLatest
                ),
                'daily_days' => $this->integerEnv(
                    'backup.keepDailyDays',
                    $this->config->keepDailyDays
                ),
                'weekly_weeks' => $this->integerEnv(
                    'backup.keepWeeklyWeeks',
                    $this->config->keepWeeklyWeeks
                ),
                'monthly_months' => $this->integerEnv(
                    'backup.keepMonthlyMonths',
                    $this->config->keepMonthlyMonths
                ),
            ],
        ];
    }

    /** @param array<string, mixed> $options
     *  @return array<string, mixed>
     */
    public function create(array $options = []): array
    {
        $this->acquireLock('create');
        $stageDirectory = '';
        $temporaryArchive = '';

        try {
            $identifier = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
            $tag = $this->safeTag((string) ($options['tag'] ?? 'manual'));
            $archiveName = 'garda01-backup-' . $identifier . '.zip';
            $archivePath = $this->backupDirectory()
                . DIRECTORY_SEPARATOR . $archiveName;
            $temporaryArchive = $this->temporaryDirectory()
                . DIRECTORY_SEPARATOR . $archiveName . '.partial';
            $stageDirectory = $this->temporaryDirectory()
                . DIRECTORY_SEPARATOR . 'stage-' . $identifier;

            $this->makeDirectory($stageDirectory);

            $databasePath = $stageDirectory . DIRECTORY_SEPARATOR . 'database.sql';
            $databaseMethod = $this->dumpDatabase($databasePath);
            $databaseOnly = !empty($options['database_only']);
            $includedPaths = [];

            if (!$databaseOnly) {
                foreach ($this->config->includedDirectories as $label => $sourcePath) {
                    if (!is_dir($sourcePath)) {
                        continue;
                    }

                    $destination = $stageDirectory
                        . DIRECTORY_SEPARATOR . 'files'
                        . DIRECTORY_SEPARATOR . $label;

                    $this->copyDirectory($sourcePath, $destination);

                    $includedPaths[] = [
                        'label' => $label,
                        'source' => $this->portableSourceLabel($sourcePath),
                    ];
                }
            }

            $metadataDirectory = $stageDirectory . DIRECTORY_SEPARATOR . 'metadata';
            $this->makeDirectory($metadataDirectory);

            file_put_contents(
                $metadataDirectory . DIRECTORY_SEPARATOR . 'environment.json',
                $this->json([
                    'application' => 'GARDA 01 Portal',
                    'environment' => ENVIRONMENT,
                    'base_url_host' => (string) (
                        parse_url((string) config('App')->baseURL, PHP_URL_HOST) ?: ''
                    ),
                    'php_version' => PHP_VERSION,
                    'release' => trim((string) env('deployment.release', '')),
                    'commit' => trim((string) env('deployment.commit', '')),
                ])
            );

            $files = $this->stageFileManifest($stageDirectory);

            $internalManifest = [
                'schema_version' => 1,
                'backup_id' => $identifier,
                'archive_name' => $archiveName,
                'created_at' => date(DATE_ATOM),
                'tag' => $tag,
                'database_method' => $databaseMethod,
                'database_only' => $databaseOnly,
                'included_paths' => $includedPaths,
                'files' => $files,
            ];

            file_put_contents(
                $stageDirectory . DIRECTORY_SEPARATOR . 'manifest.json',
                $this->json($internalManifest)
            );

            $this->createZip($stageDirectory, $temporaryArchive);

            if (!@rename($temporaryArchive, $archivePath)) {
                throw new RuntimeException(
                    'Archive belum dapat dipindahkan secara atomik.'
                );
            }

            $archiveHash = hash_file('sha256', $archivePath);

            if ($archiveHash === false) {
                throw new RuntimeException('Checksum archive gagal dibuat.');
            }

            $sidecar = [
                'schema_version' => 1,
                'backup_id' => $identifier,
                'archive_name' => $archiveName,
                'archive_sha256' => $archiveHash,
                'archive_size' => filesize($archivePath) ?: 0,
                'created_at' => $internalManifest['created_at'],
                'tag' => $tag,
                'database_method' => $databaseMethod,
                'database_only' => $databaseOnly,
                'verification_status' => 'not_verified',
                'verified_at' => null,
                'protected' => !empty($options['protected']),
            ];

            $this->writeSidecar($archiveName, $sidecar);
            $this->deleteDirectory($stageDirectory);
            $this->releaseLock();

            return $sidecar;
        } catch (Throwable $exception) {
            if ($temporaryArchive !== '' && is_file($temporaryArchive)) {
                @unlink($temporaryArchive);
            }

            if ($stageDirectory !== '' && is_dir($stageDirectory)) {
                $this->deleteDirectory($stageDirectory);
            }

            $this->releaseLock();
            throw $exception;
        }
    }

    /** @return list<array<string, mixed>> */
    public function listBackups(): array
    {
        $files = glob(
            $this->backupDirectory()
            . DIRECTORY_SEPARATOR
            . 'garda01-backup-*.zip'
        );

        if (!is_array($files)) {
            return [];
        }

        $backups = [];

        foreach ($files as $archivePath) {
            $archiveName = basename($archivePath);
            $sidecar = $this->readSidecar($archiveName);

            if (!is_array($sidecar)) {
                $sidecar = [
                    'schema_version' => 1,
                    'backup_id' => null,
                    'archive_name' => $archiveName,
                    'archive_sha256' => null,
                    'archive_size' => filesize($archivePath) ?: 0,
                    'created_at' => date(
                        DATE_ATOM,
                        filemtime($archivePath) ?: time()
                    ),
                    'tag' => 'unknown',
                    'database_method' => 'unknown',
                    'database_only' => false,
                    'verification_status' => 'missing_manifest',
                    'verified_at' => null,
                    'protected' => false,
                ];
            }

            $backups[] = $sidecar;
        }

        usort(
            $backups,
            static fn (array $left, array $right): int =>
                strcmp(
                    (string) ($right['created_at'] ?? ''),
                    (string) ($left['created_at'] ?? '')
                )
        );

        return $backups;
    }

    /** @return array<string, mixed> */
    public function verify(string $archiveName): array
    {
        $archiveName = $this->validArchiveName($archiveName);
        $archivePath = $this->backupDirectory()
            . DIRECTORY_SEPARATOR . $archiveName;

        if (!is_file($archivePath)) {
            throw new RuntimeException('Archive backup tidak ditemukan.');
        }

        $sidecar = $this->readSidecar($archiveName);

        if (!is_array($sidecar)) {
            throw new RuntimeException('Manifest luar backup tidak ditemukan.');
        }

        $actualHash = hash_file('sha256', $archivePath);
        $expectedHash = (string) ($sidecar['archive_sha256'] ?? '');

        if (
            $actualHash === false
            || $expectedHash === ''
            || !hash_equals($expectedHash, $actualHash)
        ) {
            $sidecar['verification_status'] = 'failed_checksum';
            $sidecar['verified_at'] = date(DATE_ATOM);
            $this->writeSidecar($archiveName, $sidecar);

            throw new RuntimeException('Checksum archive tidak cocok.');
        }

        $zip = new ZipArchive();
        $opened = $zip->open($archivePath, ZipArchive::CHECKCONS);

        if ($opened !== true) {
            $sidecar['verification_status'] = 'failed_zip';
            $sidecar['verified_at'] = date(DATE_ATOM);
            $this->writeSidecar($archiveName, $sidecar);

            throw new RuntimeException('Struktur ZIP backup tidak valid.');
        }

        $manifestContent = $zip->getFromName('manifest.json');

        if (!is_string($manifestContent) || $manifestContent === '') {
            $zip->close();
            throw new RuntimeException(
                'Manifest internal backup tidak ditemukan.'
            );
        }

        $internal = json_decode($manifestContent, true);

        if (!is_array($internal) || !is_array($internal['files'] ?? null)) {
            $zip->close();
            throw new RuntimeException('Manifest internal backup tidak valid.');
        }

        $verifiedFiles = 0;

        foreach ($internal['files'] as $file) {
            if (!is_array($file) || empty($file['path'])) {
                continue;
            }

            $relativePath = (string) $file['path'];
            $stream = $zip->getStream($relativePath);

            if (!is_resource($stream)) {
                $zip->close();
                throw new RuntimeException(
                    'File dalam archive hilang: ' . $relativePath
                );
            }

            $context = hash_init('sha256');
            hash_update_stream($context, $stream);
            fclose($stream);
            $fileHash = hash_final($context);

            if (!hash_equals((string) ($file['sha256'] ?? ''), $fileHash)) {
                $zip->close();
                throw new RuntimeException(
                    'Checksum file tidak cocok: ' . $relativePath
                );
            }

            $verifiedFiles++;
        }

        $zip->close();

        $sidecar['verification_status'] = 'verified';
        $sidecar['verified_at'] = date(DATE_ATOM);
        $sidecar['verified_files'] = $verifiedFiles;
        $this->writeSidecar($archiveName, $sidecar);

        return [
            'archive_name' => $archiveName,
            'archive_sha256' => $actualHash,
            'verified_files' => $verifiedFiles,
            'verified_at' => $sidecar['verified_at'],
            'status' => 'verified',
        ];
    }

    /** @return array<string, mixed> */
    public function manifest(string $archiveName): array
    {
        $archiveName = $this->validArchiveName($archiveName);
        $sidecar = $this->readSidecar($archiveName);

        if (!is_array($sidecar)) {
            throw new RuntimeException('Manifest backup tidak ditemukan.');
        }

        return $sidecar;
    }

    /** @return array<string, mixed> */
    public function prune(bool $dryRun = false): array
    {
        $this->acquireLock('prune');

        try {
            $backups = $this->listBackups();
            $keep = $this->retentionKeepSet($backups);
            $deleted = [];
            $retained = [];

            foreach ($backups as $backup) {
                $archiveName = (string) ($backup['archive_name'] ?? '');

                if (
                    $archiveName === ''
                    || isset($keep[$archiveName])
                    || !empty($backup['protected'])
                ) {
                    $retained[] = $archiveName;
                    continue;
                }

                $deleted[] = $archiveName;

                if ($dryRun) {
                    continue;
                }

                @unlink(
                    $this->backupDirectory()
                    . DIRECTORY_SEPARATOR
                    . $archiveName
                );

                @unlink($this->sidecarPath($archiveName));
            }

            $this->releaseLock();

            return [
                'dry_run' => $dryRun,
                'deleted' => $deleted,
                'retained' => $retained,
            ];
        } catch (Throwable $exception) {
            $this->releaseLock();
            throw $exception;
        }
    }

    public function latestBackupAgeHours(): ?int
    {
        $latest = $this->listBackups()[0] ?? null;

        if (!is_array($latest)) {
            return null;
        }

        $timestamp = strtotime((string) ($latest['created_at'] ?? ''));

        return $timestamp === false
            ? null
            : (int) floor((time() - $timestamp) / 3600);
    }

    /** @return array<string, mixed> */
    public function restore(string $archiveName): array
    {
        $archiveName = $this->validArchiveName($archiveName);
        $this->verify($archiveName);
        $this->acquireLock('restore');

        $restoreRoot = '';

        try {
            $mysql = $this->findMySqlExecutable('mysql');

            if ($mysql === null) {
                throw new RuntimeException(
                    'Binary mysql tidak ditemukan. Atur backup.mysqlPath pada .env.'
                );
            }

            $archivePath = $this->backupDirectory()
                . DIRECTORY_SEPARATOR . $archiveName;

            $restoreRoot = $this->temporaryDirectory()
                . DIRECTORY_SEPARATOR
                . 'restore-'
                . date('Ymd-His')
                . '-'
                . bin2hex(random_bytes(3));

            $this->makeDirectory($restoreRoot);

            $zip = new ZipArchive();

            if ($zip->open($archivePath) !== true) {
                throw new RuntimeException(
                    'Archive backup tidak dapat dibuka.'
                );
            }

            if (!$zip->extractTo($restoreRoot)) {
                $zip->close();
                throw new RuntimeException(
                    'Archive backup tidak dapat diekstrak.'
                );
            }

            $zip->close();

            $databasePath = $restoreRoot
                . DIRECTORY_SEPARATOR
                . 'database.sql';

            if (!is_file($databasePath)) {
                throw new RuntimeException(
                    'database.sql tidak ditemukan.'
                );
            }

            $preparedDirectories = [];

            foreach ($this->config->includedDirectories as $label => $destination) {
                $source = $restoreRoot
                    . DIRECTORY_SEPARATOR
                    . 'files'
                    . DIRECTORY_SEPARATOR
                    . $label;

                if (!is_dir($source)) {
                    continue;
                }

                $prepared = $this->temporaryDirectory()
                    . DIRECTORY_SEPARATOR
                    . 'prepared-'
                    . $label
                    . '-'
                    . bin2hex(random_bytes(3));

                $this->copyDirectory($source, $prepared);

                $preparedDirectories[$label] = [
                    'prepared' => $prepared,
                    'destination' => $destination,
                ];
            }

            $this->importDatabase($mysql, $databasePath);

            $rollbackDirectories = [];

            try {
                foreach ($preparedDirectories as $label => $directory) {
                    $destination = $directory['destination'];
                    $prepared = $directory['prepared'];
                    $rollback = $this->temporaryDirectory()
                        . DIRECTORY_SEPARATOR
                        . 'rollback-'
                        . $label
                        . '-'
                        . bin2hex(random_bytes(3));

                    if (is_dir($destination)) {
                        if (!@rename($destination, $rollback)) {
                            throw new RuntimeException(
                                'Folder aktif tidak dapat diamankan: ' . $label
                            );
                        }

                        $rollbackDirectories[$label] = [
                            'rollback' => $rollback,
                            'destination' => $destination,
                        ];
                    }

                    if (!@rename($prepared, $destination)) {
                        throw new RuntimeException(
                            'Folder hasil restore tidak dapat diaktifkan: ' . $label
                        );
                    }
                }
            } catch (Throwable $exception) {
                foreach ($rollbackDirectories as $rollback) {
                    if (is_dir($rollback['destination'])) {
                        $this->deleteDirectory($rollback['destination']);
                    }

                    if (is_dir($rollback['rollback'])) {
                        @rename(
                            $rollback['rollback'],
                            $rollback['destination']
                        );
                    }
                }

                throw $exception;
            }

            foreach ($rollbackDirectories as $rollback) {
                if (is_dir($rollback['rollback'])) {
                    $this->deleteDirectory($rollback['rollback']);
                }
            }

            $this->deleteDirectory($restoreRoot);
            $this->releaseLock();

            return [
                'archive_name' => $archiveName,
                'restored_at' => date(DATE_ATOM),
                'database_restored' => true,
                'directories_restored' => array_keys($preparedDirectories),
            ];
        } catch (Throwable $exception) {
            if ($restoreRoot !== '' && is_dir($restoreRoot)) {
                $this->deleteDirectory($restoreRoot);
            }

            $this->releaseLock();
            throw $exception;
        }
    }

    protected function dumpDatabase(string $destination): string
    {
        $mysqldump = $this->findMySqlExecutable('mysqldump');

        if ($mysqldump !== null) {
            try {
                $this->nativeDatabaseDump($mysqldump, $destination);
                return 'mysqldump';
            } catch (Throwable $exception) {
                log_message(
                    'warning',
                    'Native backup fallback: ' . $exception->getMessage()
                );
            }
        }

        $this->phpDatabaseDump($destination);

        return 'php_fallback';
    }

    protected function nativeDatabaseDump(
        string $binary,
        string $destination
    ): void {
        $settings = $this->databaseSettings();
        $defaultsFile = $this->temporaryDefaultsFile($settings);

        $command = [
            $binary,
            '--defaults-extra-file=' . $defaultsFile,
            '--host=' . $settings['hostname'],
            '--port=' . (string) $settings['port'],
            '--user=' . $settings['username'],
            '--single-transaction',
            '--quick',
            '--triggers',
            '--hex-blob',
            '--default-character-set=utf8mb4',
            '--no-tablespaces',
            $settings['database'],
        ];

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['file', $destination, 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            ROOTPATH
        );

        if (!is_resource($process)) {
            @unlink($defaultsFile);
            throw new RuntimeException(
                'mysqldump tidak dapat dijalankan.'
            );
        }

        fclose($pipes[0]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        @unlink($defaultsFile);

        if (
            $exitCode !== 0
            || !is_file($destination)
            || filesize($destination) === 0
        ) {
            @unlink($destination);
            throw new RuntimeException(
                'mysqldump gagal: ' . trim((string) $error)
            );
        }
    }

    protected function phpDatabaseDump(string $destination): void
    {
        $db = db_connect();
        $db->initialize();
        $handle = fopen($destination, 'wb');

        if (!is_resource($handle)) {
            throw new RuntimeException(
                'File SQL backup tidak dapat dibuat.'
            );
        }

        fwrite(
            $handle,
            "-- GARDA 01 database backup\n"
            . "-- Generated: " . date(DATE_ATOM) . "\n\n"
            . "SET NAMES utf8mb4;\n"
            . "SET FOREIGN_KEY_CHECKS=0;\n\n"
        );

        $views = [];

        foreach ($db->listTables() as $table) {
            $escapedTable = str_replace('`', '``', $table);

            $createRow = $db->query(
                'SHOW CREATE TABLE `' . $escapedTable . '`'
            )->getRowArray();

            if (!is_array($createRow)) {
                continue;
            }

            $createStatement = $createRow['Create Table']
                ?? $createRow['Create View']
                ?? array_values($createRow)[1]
                ?? null;

            if (!is_string($createStatement)) {
                continue;
            }

            if (isset($createRow['Create View'])) {
                $views[] = [
                    'table' => $table,
                    'create' => $createStatement,
                ];
                continue;
            }

            fwrite(
                $handle,
                "DROP TABLE IF EXISTS `" . $escapedTable . "`;\n"
                . $createStatement . ";\n\n"
            );

            $query = $db->query(
                'SELECT * FROM `' . $escapedTable . '`'
            );

            $columns = null;
            $batch = [];

            while ($row = $query->getUnbufferedRow('array')) {
                if ($columns === null) {
                    $columns = array_map(
                        static fn (string $column): string =>
                            '`' . str_replace('`', '``', $column) . '`',
                        array_keys($row)
                    );
                }

                $values = [];

                foreach ($row as $value) {
                    $values[] = $value === null
                        ? 'NULL'
                        : $db->escape($value);
                }

                $batch[] = '(' . implode(', ', $values) . ')';

                if (count($batch) >= 100) {
                    $this->writeInsertBatch(
                        $handle,
                        $escapedTable,
                        $columns,
                        $batch
                    );
                    $batch = [];
                }
            }

            if ($batch !== []) {
                $this->writeInsertBatch(
                    $handle,
                    $escapedTable,
                    $columns ?? [],
                    $batch
                );
            }

            fwrite($handle, "\n");
        }

        foreach ($views as $view) {
            $escapedView = str_replace('`', '``', $view['table']);

            fwrite(
                $handle,
                "DROP VIEW IF EXISTS `" . $escapedView . "`;\n"
                . $view['create'] . ";\n\n"
            );
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        if (!is_file($destination) || filesize($destination) === 0) {
            throw new RuntimeException('PHP database backup kosong.');
        }
    }

    /**
     * @param resource $handle
     * @param list<string> $columns
     * @param list<string> $rows
     */
    protected function writeInsertBatch(
        $handle,
        string $table,
        array $columns,
        array $rows
    ): void {
        if ($columns === [] || $rows === []) {
            return;
        }

        fwrite(
            $handle,
            'INSERT INTO `' . $table . '` ('
            . implode(', ', $columns)
            . ") VALUES\n"
            . implode(",\n", $rows)
            . ";\n"
        );
    }

    protected function importDatabase(
        string $binary,
        string $sqlPath
    ): void {
        $settings = $this->databaseSettings();
        $defaultsFile = $this->temporaryDefaultsFile($settings);

        $command = [
            $binary,
            '--defaults-extra-file=' . $defaultsFile,
            '--host=' . $settings['hostname'],
            '--port=' . (string) $settings['port'],
            '--user=' . $settings['username'],
            '--default-character-set=utf8mb4',
            $settings['database'],
        ];

        $process = proc_open(
            $command,
            [
                0 => ['file', $sqlPath, 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            ROOTPATH
        );

        if (!is_resource($process)) {
            @unlink($defaultsFile);
            throw new RuntimeException(
                'mysql client tidak dapat dijalankan.'
            );
        }

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        @unlink($defaultsFile);

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Restore database gagal: '
                . trim((string) ($error !== '' ? $error : $output))
            );
        }
    }

    /** @return array{hostname:string,port:int,username:string,password:string,database:string} */
    protected function databaseSettings(): array
    {
        $databaseConfig = config('Database');
        $group = $databaseConfig->defaultGroup;
        $settings = $databaseConfig->{$group} ?? [];

        $result = [
            'hostname' => (string) ($settings['hostname'] ?? 'localhost'),
            'port' => (int) ($settings['port'] ?? 3306),
            'username' => (string) ($settings['username'] ?? ''),
            'password' => (string) ($settings['password'] ?? ''),
            'database' => (string) ($settings['database'] ?? ''),
        ];

        if ($result['username'] === '' || $result['database'] === '') {
            throw new RuntimeException(
                'Kredensial database belum lengkap.'
            );
        }

        return $result;
    }

    /** @param array{hostname:string,port:int,username:string,password:string,database:string} $settings */
    protected function temporaryDefaultsFile(array $settings): string
    {
        $path = $this->temporaryDirectory()
            . DIRECTORY_SEPARATOR
            . 'mysql-'
            . bin2hex(random_bytes(8))
            . '.cnf';

        $content = "[client]\n"
            . 'password="'
            . addcslashes($settings['password'], "\\\"\n\r")
            . "\"\n";

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException(
                'File credential sementara gagal dibuat.'
            );
        }

        @chmod($path, 0600);

        return $path;
    }

    protected function findMySqlExecutable(string $type): ?string
    {
        $configured = $type === 'mysqldump'
            ? trim((string) env(
                'backup.mysqldumpPath',
                $this->config->mysqlDumpPath
            ))
            : trim((string) env(
                'backup.mysqlPath',
                $this->config->mysqlClientPath
            ));

        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        $fileName = PHP_OS_FAMILY === 'Windows'
            ? $type . '.exe'
            : $type;

        $candidates = [
            'C:\\xampp\\mysql\\bin\\' . $fileName,
            '/usr/bin/' . $fileName,
            '/usr/local/bin/' . $fileName,
            '/opt/homebrew/bin/' . $fileName,
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $command = PHP_OS_FAMILY === 'Windows'
            ? ['where', $fileName]
            : ['which', $fileName];

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            return null;
        }

        fclose($pipes[0]);
        $output = trim((string) stream_get_contents($pipes[1]));
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0 || $output === '') {
            return null;
        }

        $firstLine = preg_split('/\R/', $output)[0] ?? '';

        return is_file($firstLine) ? $firstLine : null;
    }

    protected function createZip(
        string $sourceDirectory,
        string $archivePath
    ): void {
        $zip = new ZipArchive();

        if (
            $zip->open(
                $archivePath,
                ZipArchive::CREATE
                | ZipArchive::OVERWRITE
            ) !== true
        ) {
            throw new RuntimeException(
                'ZIP backup tidak dapat dibuat.'
            );
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $sourceDirectory,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $absolute = $file->getPathname();
            $relative = str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                substr(
                    $absolute,
                    strlen($sourceDirectory) + 1
                )
            );

            if (!$zip->addFile($absolute, $relative)) {
                $zip->close();
                throw new RuntimeException(
                    'File gagal dimasukkan ke ZIP: ' . $relative
                );
            }
        }

        if (!$zip->close()) {
            throw new RuntimeException(
                'ZIP backup gagal diselesaikan.'
            );
        }
    }

    /** @return list<array{path:string,size:int,sha256:string}> */
    protected function stageFileManifest(string $stageDirectory): array
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $stageDirectory,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relative = str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                substr(
                    $file->getPathname(),
                    strlen($stageDirectory) + 1
                )
            );

            if ($relative === 'manifest.json') {
                continue;
            }

            $hash = hash_file('sha256', $file->getPathname());

            if ($hash === false) {
                throw new RuntimeException(
                    'Checksum file gagal: ' . $relative
                );
            }

            $files[] = [
                'path' => $relative,
                'size' => (int) $file->getSize(),
                'sha256' => $hash,
            ];
        }

        usort(
            $files,
            static fn (array $left, array $right): int =>
                strcmp($left['path'], $right['path'])
        );

        return $files;
    }

    protected function copyDirectory(
        string $source,
        string $destination
    ): void {
        $this->makeDirectory($destination);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $source,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if (in_array(
                $item->getFilename(),
                $this->config->ignoredNames,
                true
            )) {
                continue;
            }

            $relative = substr(
                $item->getPathname(),
                strlen($source) + 1
            );

            $target = $destination
                . DIRECTORY_SEPARATOR
                . $relative;

            if ($item->isDir()) {
                $this->makeDirectory($target);
                continue;
            }

            $this->makeDirectory(dirname($target));

            if (!copy($item->getPathname(), $target)) {
                throw new RuntimeException(
                    'File gagal disalin: ' . $relative
                );
            }
        }
    }

    protected function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($directory);
    }

    protected function ensureStorage(): void
    {
        $this->makeDirectory($this->backupDirectory());
        $this->makeDirectory($this->temporaryDirectory());

        $htaccess = $this->backupDirectory()
            . DIRECTORY_SEPARATOR . '.htaccess';

        if (!is_file($htaccess)) {
            @file_put_contents($htaccess, "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
        }

        $index = $this->backupDirectory()
            . DIRECTORY_SEPARATOR . 'index.html';

        if (!is_file($index)) {
            @file_put_contents($index, '');
        }
    }

    protected function makeDirectory(string $directory): void
    {
        if (
            !is_dir($directory)
            && !mkdir($directory, 0775, true)
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                'Folder gagal dibuat: ' . $directory
            );
        }
    }

    protected function acquireLock(string $operation): void
    {
        $this->makeDirectory(dirname($this->config->lockFile));
        $handle = fopen($this->config->lockFile, 'c+');

        if (!is_resource($handle)) {
            throw new RuntimeException(
                'Lock backup tidak dapat dibuat.'
            );
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            throw new RuntimeException(
                'Operasi backup lain sedang berjalan.'
            );
        }

        ftruncate($handle, 0);
        fwrite(
            $handle,
            $this->json([
                'operation' => $operation,
                'started_at' => date(DATE_ATOM),
                'pid' => getmypid(),
            ])
        );
        fflush($handle);

        $this->lockHandle = $handle;
    }

    protected function releaseLock(): void
    {
        if (is_resource($this->lockHandle)) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
        }

        $this->lockHandle = null;

        if (is_file($this->config->lockFile)) {
            @unlink($this->config->lockFile);
        }
    }

    /**
     * @param list<array<string, mixed>> $backups
     * @return array<string, bool>
     */
    protected function retentionKeepSet(array $backups): array
    {
        $keep = [];
        $now = new \DateTimeImmutable();

        $keepLatest = $this->integerEnv(
            'backup.keepLatest',
            $this->config->keepLatest
        );
        $dailyDays = $this->integerEnv(
            'backup.keepDailyDays',
            $this->config->keepDailyDays
        );
        $weeklyWeeks = $this->integerEnv(
            'backup.keepWeeklyWeeks',
            $this->config->keepWeeklyWeeks
        );
        $monthlyMonths = $this->integerEnv(
            'backup.keepMonthlyMonths',
            $this->config->keepMonthlyMonths
        );

        foreach (array_slice($backups, 0, max(0, $keepLatest)) as $backup) {
            $keep[(string) $backup['archive_name']] = true;
        }

        $dailyBuckets = [];
        $weeklyBuckets = [];
        $monthlyBuckets = [];

        foreach ($backups as $backup) {
            $archiveName = (string) ($backup['archive_name'] ?? '');
            $createdAt = new \DateTimeImmutable(
                (string) ($backup['created_at'] ?? 'now')
            );

            $ageDays = (int) floor(
                ($now->getTimestamp() - $createdAt->getTimestamp()) / 86400
            );

            if ($ageDays <= $dailyDays) {
                $bucket = $createdAt->format('Y-m-d');

                if (!isset($dailyBuckets[$bucket])) {
                    $dailyBuckets[$bucket] = true;
                    $keep[$archiveName] = true;
                }
            }

            $ageWeeks = (int) floor($ageDays / 7);

            if ($ageWeeks <= $weeklyWeeks) {
                $bucket = $createdAt->format('o-W');

                if (!isset($weeklyBuckets[$bucket])) {
                    $weeklyBuckets[$bucket] = true;
                    $keep[$archiveName] = true;
                }
            }

            $ageMonths = (
                ((int) $now->format('Y') - (int) $createdAt->format('Y')) * 12
            ) + (
                (int) $now->format('n') - (int) $createdAt->format('n')
            );

            if ($ageMonths >= 0 && $ageMonths <= $monthlyMonths) {
                $bucket = $createdAt->format('Y-m');

                if (!isset($monthlyBuckets[$bucket])) {
                    $monthlyBuckets[$bucket] = true;
                    $keep[$archiveName] = true;
                }
            }
        }

        return $keep;
    }

    protected function backupDirectory(): string
    {
        return rtrim(
            (string) env(
                'backup.directory',
                $this->config->backupDirectory
            ),
            DIRECTORY_SEPARATOR
        );
    }

    protected function temporaryDirectory(): string
    {
        return rtrim(
            (string) env(
                'backup.temporaryDirectory',
                $this->config->temporaryDirectory
            ),
            DIRECTORY_SEPARATOR
        );
    }

    protected function sidecarPath(string $archiveName): string
    {
        return $this->backupDirectory()
            . DIRECTORY_SEPARATOR
            . $archiveName
            . '.manifest.json';
    }

    /** @return array<string, mixed>|null */
    protected function readSidecar(string $archiveName): ?array
    {
        $path = $this->sidecarPath($archiveName);

        if (!is_file($path)) {
            return null;
        }

        $decoded = json_decode(
            (string) file_get_contents($path),
            true
        );

        return is_array($decoded) ? $decoded : null;
    }

    /** @param array<string, mixed> $sidecar */
    protected function writeSidecar(
        string $archiveName,
        array $sidecar
    ): void {
        $path = $this->sidecarPath($archiveName);
        $temporary = $path . '.tmp';

        if (file_put_contents($temporary, $this->json($sidecar)) === false) {
            throw new RuntimeException(
                'Manifest backup gagal disimpan.'
            );
        }

        if (!@rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException(
                'Manifest backup gagal diaktifkan.'
            );
        }
    }

    protected function validArchiveName(string $archiveName): string
    {
        $archiveName = basename(trim($archiveName));

        if (
            preg_match(
                '/^garda01-backup-\d{8}-\d{6}-[a-f0-9]{8}\.zip$/',
                $archiveName
            ) !== 1
        ) {
            throw new RuntimeException(
                'Nama archive backup tidak valid.'
            );
        }

        return $archiveName;
    }

    protected function safeTag(string $tag): string
    {
        $tag = strtolower(trim(
            preg_replace(
                '/[^a-zA-Z0-9_-]+/',
                '-',
                $tag
            ) ?? ''
        ));

        return $tag !== '' ? substr($tag, 0, 60) : 'manual';
    }

    protected function portableSourceLabel(string $sourcePath): string
    {
        if (str_starts_with($sourcePath, FCPATH)) {
            return 'public/' . str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                substr($sourcePath, strlen(FCPATH))
            );
        }

        if (str_starts_with($sourcePath, WRITEPATH)) {
            return 'writable/' . str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                substr($sourcePath, strlen(WRITEPATH))
            );
        }

        return basename($sourcePath);
    }

    protected function integerEnv(string $key, int $default): int
    {
        return max(0, (int) env($key, $default));
    }

    /** @param mixed $value */
    protected function json($value): string
    {
        $json = json_encode(
            $value,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException(
                'Data JSON gagal diproses.'
            );
        }

        return $json;
    }
}
