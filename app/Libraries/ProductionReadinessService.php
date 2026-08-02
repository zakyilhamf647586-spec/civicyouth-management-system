<?php

namespace App\Libraries;

use Config\ProductionReadiness;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

class ProductionReadinessService
{
    protected ProductionReadiness $config;

    /** @var list<array<string, mixed>> */
    protected array $checks = [];

    protected $database = null;

    public function __construct()
    {
        $this->config = config(ProductionReadiness::class);
    }

    /** @return array<string, mixed> */
    public function report(): array
    {
        $this->checks = [];
        $this->database = null;

        $this->runtimeChecks();
        $this->environmentChecks();
        $this->securityChecks();
        $this->databaseChecks();
        $this->filesystemChecks();
        $this->recoveryChecks();
        $this->applicationChecks();
        $this->deploymentChecks();
        $this->manualChecks();

        $counts = [
            'pass' => 0,
            'warning' => 0,
            'fail' => 0,
            'manual' => 0,
        ];

        $blocking = 0;
        $automated = 0;
        $points = 0.0;

        foreach ($this->checks as $check) {
            $status = (string) $check['status'];
            $counts[$status]++;

            if ($status !== 'manual') {
                $automated++;
            }

            if ($status === 'pass') {
                $points += 1.0;
            } elseif ($status === 'warning') {
                $points += 0.5;
            }

            if (
                $status === 'fail'
                && !empty($check['blocking'])
            ) {
                $blocking++;
            }
        }

        $score = $automated > 0
            ? (int) round(($points / $automated) * 100)
            : 0;

        $readiness = $blocking > 0
            ? 'blocked'
            : (
                $counts['warning'] > 0
                    ? 'ready_with_warnings'
                    : 'ready'
            );

        $categories = [
            'runtime' => [],
            'environment' => [],
            'security' => [],
            'database' => [],
            'filesystem' => [],
            'recovery' => [],
            'application' => [],
            'deployment' => [],
            'manual' => [],
        ];

        foreach ($this->checks as $check) {
            $categories[$check['category']][] = $check;
        }

        $deploymentContext = $this->deploymentContext($this->checks);

        return [
            'generated_at' => date(DATE_ATOM),
            'environment' => [
                'name' => ENVIRONMENT,
                'base_url' => (string) config('App')->baseURL,
                'php_version' => PHP_VERSION,
                'release' => trim((string) env('deployment.release', '')),
                'commit' => substr(
                    trim((string) env('deployment.commit', '')),
                    0,
                    12
                ),
            ],
            'summary' => [
                'score' => $score,
                'readiness' => $readiness,
                'blocking' => $blocking,
                'automated' => $automated,
                'total' => count($this->checks),
                'counts' => $counts,
            ],
            'deployment_context' => $deploymentContext,
            'categories' => $categories,
            'checks' => $this->checks,
        ];
    }

    /**
     * Separate work that can be completed in the project now from checks
     * that only become meaningful after a production host exists.
     *
     * @param list<array<string, mixed>> $checks
     * @return array<string, mixed>
     */
    protected function deploymentContext(array $checks): array
    {
        $baseUrl = trim((string) config('App')->baseURL);
        $host = strtolower((string) (parse_url($baseUrl, PHP_URL_HOST) ?? ''));
        $localHosts = ['localhost', '127.0.0.1', '::1'];
        $isLocalHost = $host === ''
            || in_array($host, $localHosts, true)
            || str_ends_with($host, '.local');
        $isProduction = ENVIRONMENT === 'production' && !$isLocalHost;

        $streams = [
            'now' => [
                'items' => [],
                'blocking' => 0,
                'warnings' => 0,
            ],
            'hosting' => [
                'items' => [],
                'blocking' => 0,
                'warnings' => 0,
            ],
            'final' => [
                'items' => [],
                'blocking' => 0,
                'warnings' => 0,
            ],
        ];

        foreach ($checks as $check) {
            if (($check['status'] ?? '') === 'pass') {
                continue;
            }

            $stream = $this->deploymentStreamFor((string) ($check['id'] ?? ''));
            $streams[$stream]['items'][] = $check;

            if (
                ($check['status'] ?? '') === 'fail'
                && !empty($check['blocking'])
            ) {
                $streams[$stream]['blocking']++;
            } elseif (($check['status'] ?? '') === 'warning') {
                $streams[$stream]['warnings']++;
            }
        }

        $currentStatus = $streams['now']['blocking'] > 0
            ? 'needs_attention'
            : ($streams['now']['warnings'] > 0 ? 'in_progress' : 'on_track');

        return [
            'is_local' => $isLocalHost || ENVIRONMENT !== 'production',
            'is_production' => $isProduction,
            'host' => $host,
            'current_status' => $currentStatus,
            'streams' => $streams,
        ];
    }

    protected function deploymentStreamFor(string $checkId): string
    {
        if (str_starts_with($checkId, 'manual.')) {
            return 'final';
        }

        if (str_starts_with($checkId, 'deployment.')) {
            return 'hosting';
        }

        $hostingChecks = [
            'runtime.recommended.opcache',
            'environment.mode',
            'environment.https',
            'environment.public_host',
            'environment.force_https',
            'environment.display_errors',
            'environment.release',
            'security.encryption',
            'security.cookie_secure',
            'database.credentials',
            'database.root',
            'filesystem.env',
            'filesystem.git',
            'filesystem.dev_dependencies',
            'filesystem.backups',
            'recovery.mysql',
        ];

        return in_array($checkId, $hostingChecks, true)
            ? 'hosting'
            : 'now';
    }

    protected function runtimeChecks(): void
    {
        $minimum = $this->config->minimumPhpVersion;

        $this->add(
            'runtime.php',
            'runtime',
            'Versi PHP',
            version_compare(PHP_VERSION, $minimum, '>=')
                ? 'pass'
                : 'fail',
            true,
            'PHP aktif: ' . PHP_VERSION . '. Minimum: ' . $minimum . '.',
            'Gunakan PHP ' . $minimum . ' atau versi stabil yang lebih baru.'
        );

        foreach ($this->config->requiredExtensions as $extension) {
            $loaded = extension_loaded($extension);

            $this->add(
                'runtime.extension.' . $extension,
                'runtime',
                'Ekstensi PHP: ' . $extension,
                $loaded ? 'pass' : 'fail',
                true,
                $loaded ? 'Ekstensi tersedia.' : 'Ekstensi belum tersedia.',
                'Aktifkan ekstensi ' . $extension . ' pada PHP production.'
            );
        }

        foreach ($this->config->recommendedExtensions as $extension) {
            $loaded = extension_loaded($extension);

            $this->add(
                'runtime.recommended.' . $extension,
                'runtime',
                'Ekstensi rekomendasi: ' . $extension,
                $loaded ? 'pass' : 'warning',
                false,
                $loaded ? 'Ekstensi tersedia.' : 'Ekstensi belum aktif.',
                $extension === 'opcache'
                    ? 'Aktifkan OPcache untuk performa production.'
                    : 'Aktifkan cURL bila integrasi HTTP atau AI digunakan.'
            );
        }

        $autoload = is_file(ROOTPATH . 'vendor/autoload.php');

        $this->add(
            'runtime.autoload',
            'runtime',
            'Composer autoload',
            $autoload ? 'pass' : 'fail',
            true,
            $autoload ? 'vendor/autoload.php tersedia.' : 'Composer autoload tidak ditemukan.',
            'Jalankan composer install --no-dev --optimize-autoloader.'
        );

        $lock = is_file(ROOTPATH . 'composer.lock');

        $this->add(
            'runtime.lock',
            'runtime',
            'Composer lock file',
            $lock ? 'pass' : 'warning',
            false,
            $lock ? 'composer.lock tersedia.' : 'composer.lock tidak ditemukan.',
            'Deploy dependency berdasarkan composer.lock.'
        );

        $memory = $this->iniBytes((string) ini_get('memory_limit'));
        $memoryOkay = $memory === -1 || $memory >= 256 * 1024 * 1024;

        $this->add(
            'runtime.memory',
            'runtime',
            'PHP memory_limit',
            $memoryOkay ? 'pass' : 'warning',
            false,
            'memory_limit: ' . (string) ini_get('memory_limit'),
            'Gunakan sekurangnya 256M untuk Excel, laporan, dan gambar.'
        );

        $upload = $this->iniBytes((string) ini_get('upload_max_filesize'));
        $post = $this->iniBytes((string) ini_get('post_max_size'));
        $uploadOkay = ($upload === -1 || $upload >= 8 * 1024 * 1024)
            && ($post === -1 || $post >= 8 * 1024 * 1024);

        $this->add(
            'runtime.upload_limits',
            'runtime',
            'Batas upload PHP',
            $uploadOkay ? 'pass' : 'warning',
            false,
            'upload_max_filesize: ' . ini_get('upload_max_filesize')
                . '; post_max_size: ' . ini_get('post_max_size'),
            'Gunakan sekurangnya 8M dan pastikan post_max_size cukup.'
        );
    }

    protected function environmentChecks(): void
    {
        $app = config('App');
        $baseUrl = trim((string) $app->baseURL);
        $parsed = parse_url($baseUrl);
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));

        $this->add(
            'environment.mode',
            'environment',
            'Mode aplikasi production',
            ENVIRONMENT === 'production' ? 'pass' : 'fail',
            true,
            'CI_ENVIRONMENT: ' . ENVIRONMENT,
            'Atur CI_ENVIRONMENT = production pada server.'
        );

        $valid = filter_var($baseUrl, FILTER_VALIDATE_URL) !== false;

        $this->add(
            'environment.base_url',
            'environment',
            'Base URL valid',
            $valid ? 'pass' : 'fail',
            true,
            $valid ? 'Base URL dapat diproses.' : 'Base URL tidak valid.',
            'Gunakan URL lengkap dengan trailing slash.'
        );

        $this->add(
            'environment.https',
            'environment',
            'Base URL menggunakan HTTPS',
            $scheme === 'https' ? 'pass' : 'fail',
            true,
            $scheme === 'https' ? 'HTTPS digunakan.' : 'HTTPS belum digunakan.',
            'Gunakan https://domain-anda/ pada app.baseURL.'
        );

        $local = $host === ''
            || $host === 'localhost'
            || $host === '127.0.0.1'
            || str_ends_with($host, '.local');

        $this->add(
            'environment.public_host',
            'environment',
            'Base URL bukan alamat lokal',
            !$local ? 'pass' : 'fail',
            true,
            $local ? 'Alamat lokal masih digunakan.' : 'Hostname production terdeteksi.',
            'Ganti localhost dengan domain production.'
        );

        $this->add(
            'environment.trailing_slash',
            'environment',
            'Base URL memiliki trailing slash',
            str_ends_with($baseUrl, '/') ? 'pass' : 'warning',
            false,
            str_ends_with($baseUrl, '/') ? 'Format URL benar.' : 'Trailing slash belum tersedia.',
            'Tambahkan / pada akhir app.baseURL.'
        );

        $this->add(
            'environment.index_page',
            'environment',
            'index.php disembunyikan',
            trim((string) $app->indexPage) === '' ? 'pass' : 'warning',
            false,
            trim((string) $app->indexPage) === ''
                ? 'indexPage kosong.'
                : 'indexPage masih ' . $app->indexPage . '.',
            "Atur app.indexPage = '' setelah rewrite bekerja."
        );

        $this->add(
            'environment.force_https',
            'environment',
            'Force HTTPS aplikasi',
            $app->forceGlobalSecureRequests ? 'pass' : 'fail',
            true,
            $app->forceGlobalSecureRequests
                ? 'Redirect HTTPS framework aktif.'
                : 'forceGlobalSecureRequests masih nonaktif.',
            'Atur app.forceGlobalSecureRequests = true.'
        );

        $timezone = trim((string) $app->appTimezone);

        $this->add(
            'environment.timezone',
            'environment',
            'Zona waktu aplikasi',
            $timezone === 'Asia/Jakarta' ? 'pass' : 'warning',
            false,
            'Timezone: ' . ($timezone !== '' ? $timezone : '-'),
            'Gunakan app.appTimezone = Asia/Jakarta.'
        );

        $displayErrors = strtolower(trim((string) ini_get('display_errors')));
        $displayOff = in_array($displayErrors, ['', '0', 'off', 'false'], true);

        $this->add(
            'environment.display_errors',
            'environment',
            'Error detail tidak ditampilkan',
            $displayOff ? 'pass' : 'fail',
            true,
            $displayOff ? 'display_errors nonaktif.' : 'display_errors masih aktif.',
            'Nonaktifkan display_errors pada PHP production.'
        );

        $release = trim((string) env('deployment.release', ''));
        $commit = trim((string) env('deployment.commit', ''));

        $this->add(
            'environment.release',
            'environment',
            'Metadata release tersedia',
            $release !== '' && $commit !== '' ? 'pass' : 'warning',
            false,
            $release !== '' && $commit !== ''
                ? 'Release dan commit tercatat.'
                : 'Release atau commit belum dicatat.',
            'Isi deployment.release dan deployment.commit.'
        );
    }

    protected function securityChecks(): void
    {
        $encryption = config('Encryption');
        $key = trim((string) $encryption->key);
        $keyMaterial = preg_replace('/^(hex2bin:|base64:)/i', '', $key);
        $placeholder = $key === ''
            || preg_match('/change|example|your_|replace|secret/i', $key) === 1;
        $strong = !$placeholder && strlen((string) $keyMaterial) >= 32;

        $this->add(
            'security.encryption',
            'security',
            'Encryption key production',
            $strong ? 'pass' : 'fail',
            true,
            $strong
                ? 'Encryption key tersedia dan tidak ditampilkan.'
                : 'Encryption key kosong, pendek, atau placeholder.',
            'Gunakan key acak minimal 32 byte di .env production.'
        );

        $cookie = config('Cookie');

        $this->add(
            'security.cookie_secure',
            'security',
            'Cookie Secure',
            $cookie->secure ? 'pass' : 'fail',
            true,
            $cookie->secure
                ? 'Cookie hanya dikirim melalui HTTPS.'
                : 'Cookie Secure belum aktif.',
            'Atur cookie.secure = true.'
        );

        $this->add(
            'security.cookie_httponly',
            'security',
            'Cookie HTTPOnly',
            $cookie->httponly ? 'pass' : 'fail',
            true,
            $cookie->httponly
                ? 'Cookie tidak dapat dibaca JavaScript.'
                : 'HTTPOnly belum aktif.',
            'Atur cookie.httponly = true.'
        );

        $sameSite = ucfirst(strtolower(trim((string) $cookie->samesite)));

        $this->add(
            'security.samesite',
            'security',
            'Cookie SameSite',
            in_array($sameSite, ['Lax', 'Strict'], true) ? 'pass' : 'warning',
            false,
            'SameSite: ' . ($sameSite !== '' ? $sameSite : '-'),
            'Gunakan Lax atau Strict.'
        );

        $session = config('Session');
        $sessionName = trim((string) $session->cookieName);

        $this->add(
            'security.session_name',
            'security',
            'Nama cookie sesi khusus',
            $sessionName !== '' && $sessionName !== 'ci_session'
                ? 'pass'
                : 'warning',
            false,
            $sessionName !== 'ci_session'
                ? 'Nama cookie sesi telah dikustomisasi.'
                : 'Nama cookie sesi masih default.',
            'Gunakan session.cookieName = garda01_session.'
        );

        $this->add(
            'security.session_regenerate_destroy',
            'security',
            'Session ID lama dihancurkan',
            $session->regenerateDestroy ? 'pass' : 'fail',
            true,
            $session->regenerateDestroy
                ? 'Session ID lama dihancurkan setelah regenerasi.'
                : 'Session ID lama masih dipertahankan setelah regenerasi.',
            'Atur session.regenerateDestroy = true.'
        );

        $sessionExpiration = (int) $session->expiration;

        $this->add(
            'security.session_expiration',
            'security',
            'Masa berlaku sesi dibatasi',
            $sessionExpiration > 0 && $sessionExpiration <= 7200
                ? 'pass'
                : 'warning',
            false,
            $sessionExpiration > 0
                ? 'Cookie sesi berlaku ' . $sessionExpiration . ' detik.'
                : 'Cookie sesi berlaku hingga browser ditutup.',
            'Gunakan masa berlaku sesi maksimal dua jam.'
        );

        $security = config('Security');
        $csrf = in_array(
            strtolower((string) $security->csrfProtection),
            ['cookie', 'session'],
            true
        );

        $this->add(
            'security.csrf',
            'security',
            'Proteksi CSRF',
            $csrf ? 'pass' : 'fail',
            true,
            $csrf ? 'CSRF aktif.' : 'CSRF belum aktif.',
            'Gunakan proteksi cookie atau session.'
        );

        $filters = is_file(APPPATH . 'Config/Filters.php')
            ? (string) file_get_contents(APPPATH . 'Config/Filters.php')
            : '';

        $this->add(
            'security.headers',
            'security',
            'Security headers global',
            str_contains($filters, "'gardaheaders'") ? 'pass' : 'fail',
            true,
            str_contains($filters, "'gardaheaders'")
                ? 'Filter security headers ditemukan.'
                : 'Filter security headers tidak terdeteksi.',
            'Pertahankan gardaheaders sebagai filter global.'
        );

        $uploadHtaccess = is_file(FCPATH . 'uploads/.htaccess');

        $this->add(
            'security.upload_htaccess',
            'security',
            'Proteksi folder upload',
            $uploadHtaccess ? 'pass' : 'fail',
            true,
            $uploadHtaccess
                ? 'public/uploads/.htaccess tersedia.'
                : 'Proteksi upload tidak ditemukan.',
            'Tolak eksekusi script pada public/uploads.'
        );

        $dangerous = $this->dangerousUploadFiles();

        $this->add(
            'security.upload_files',
            'security',
            'Tidak ada executable pada upload',
            $dangerous === [] ? 'pass' : 'fail',
            true,
            $dangerous === []
                ? 'Tidak ditemukan ekstensi berbahaya.'
                : count($dangerous) . ' file berbahaya terdeteksi.',
            $dangerous === []
                ? 'Pertahankan validasi upload.'
                : 'Hapus dan investigasi file sebelum deployment.'
        );

        $publicEnv = is_file(FCPATH . '.env');

        $this->add(
            'security.public_env',
            'security',
            '.env tidak berada di public',
            !$publicEnv ? 'pass' : 'fail',
            true,
            !$publicEnv
                ? '.env tidak ditemukan di web root.'
                : '.env berada di public.',
            'Pindahkan .env ke root aplikasi privat.'
        );

        $exposed = [];
        foreach (['app', 'vendor', 'writable', '.git', 'composer.json', 'spark'] as $entry) {
            if (file_exists(FCPATH . $entry)) {
                $exposed[] = $entry;
            }
        }

        $this->add(
            'security.public_source',
            'security',
            'Source internal tidak berada di public',
            $exposed === [] ? 'pass' : 'fail',
            true,
            $exposed === []
                ? 'Tidak ada source internal pada web root.'
                : 'Terdeteksi: ' . implode(', ', $exposed),
            'Arahkan document root hanya ke folder public.'
        );
    }

    protected function databaseChecks(): void
    {
        try {
            $db = db_connect();
            $db->initialize();
            $db->query('SELECT 1');
            $this->database = $db;

            $this->add(
                'database.connection',
                'database',
                'Koneksi database',
                'pass',
                true,
                'Koneksi dan query sederhana berhasil.',
                'Pertahankan credential production hanya di .env.'
            );
        } catch (Throwable $exception) {
            $this->add(
                'database.connection',
                'database',
                'Koneksi database',
                'fail',
                true,
                'Database belum dapat diakses.',
                'Periksa host, port, database, username, password, dan privilege.'
            );
            return;
        }

        $databaseConfig = config('Database');
        $group = $databaseConfig->defaultGroup;
        $settings = $databaseConfig->{$group} ?? [];

        $username = trim((string) ($settings['username'] ?? ''));
        $password = (string) ($settings['password'] ?? '');
        $databaseName = trim((string) ($settings['database'] ?? ''));

        $this->add(
            'database.credentials',
            'database',
            'Kredensial database terisi',
            $username !== '' && $databaseName !== '' ? 'pass' : 'fail',
            true,
            $username !== '' && $databaseName !== ''
                ? 'Username dan nama database tersedia.'
                : 'Username atau nama database kosong.',
            'Gunakan user database khusus aplikasi.'
        );

        $unsafeRoot = strtolower($username) === 'root' && $password === '';

        $this->add(
            'database.root',
            'database',
            'Tidak memakai root tanpa password',
            !$unsafeRoot ? 'pass' : 'fail',
            true,
            !$unsafeRoot
                ? 'Kredensial bukan root kosong.'
                : 'Database menggunakan root tanpa password.',
            'Buat user production khusus dengan password kuat.'
        );

        $missing = [];
        foreach ($this->config->criticalTables as $table) {
            if (!$db->tableExists($table)) {
                $missing[] = $table;
            }
        }

        $this->add(
            'database.tables',
            'database',
            'Tabel inti tersedia',
            $missing === [] ? 'pass' : 'fail',
            true,
            $missing === []
                ? 'Seluruh tabel inti ditemukan.'
                : 'Tabel belum tersedia: ' . implode(', ', $missing),
            'Jalankan migration terbaru.'
        );

        $migrationTable = (string) (config('Migrations')->table ?? 'migrations');
        $migrationReady = $db->tableExists($migrationTable);

        $this->add(
            'database.migration_table',
            'database',
            'Tabel riwayat migration',
            $migrationReady ? 'pass' : 'fail',
            true,
            $migrationReady
                ? 'Tabel migration tersedia.'
                : 'Tabel migration belum ditemukan.',
            'Jalankan php spark migrate.'
        );

        if ($migrationReady) {
            $pending = $this->pendingMigrations($migrationTable);

            $this->add(
                'database.pending',
                'database',
                'Tidak ada migration tertunda',
                $pending === [] ? 'pass' : 'fail',
                true,
                $pending === []
                    ? 'Semua migration file telah tercatat.'
                    : count($pending) . ' migration belum dijalankan.',
                $pending === []
                    ? 'Periksa pada setiap release.'
                    : 'Jalankan php spark migrate sebelum go-live.'
            );
        }

        $accountSecurityFields = [
            'session_version',
            'must_change_password',
            'password_changed_at',
            'last_login_at',
            'last_login_ip_hash',
            'last_login_user_agent',
        ];
        $missingAccountSecurityFields = [];

        if ($db->tableExists('users')) {
            foreach ($accountSecurityFields as $field) {
                if (!$db->fieldExists($field, 'users')) {
                    $missingAccountSecurityFields[] = $field;
                }
            }
        } else {
            $missingAccountSecurityFields = $accountSecurityFields;
        }

        $this->add(
            'database.account_security',
            'database',
            'Fondasi keamanan akun tersedia',
            $missingAccountSecurityFields === [] ? 'pass' : 'fail',
            true,
            $missingAccountSecurityFields === []
                ? 'Versi sesi, status kata sandi, dan jejak login tersedia.'
                : 'Kolom belum tersedia: '
                    . implode(', ', $missingAccountSecurityFields),
            'Jalankan php spark migrate.'
        );

        $activeAdmins = 0;
        $defaultAdmin = false;

        try {
            if ($db->tableExists('users') && $db->tableExists('roles')) {
                $adminRole = $db->table('roles')
                    ->groupStart()
                    ->where('role_name', 'Admin')
                    ->orWhere('role_name', 'admin')
                    ->groupEnd()
                    ->get()
                    ->getRowArray();

                if ($adminRole) {
                    $activeAdmins = $db->table('users')
                        ->where('role_id', (int) $adminRole['id'])
                        ->where('status', 'active')
                        ->countAllResults();
                }

                $defaultAdmin = $db->table('users')
                    ->where('email', 'admin@civicyouth.local')
                    ->where('status', 'active')
                    ->countAllResults() > 0;
            }
        } catch (Throwable $exception) {
            $activeAdmins = 0;
        }

        $this->add(
            'database.admin',
            'database',
            'Admin aktif tersedia',
            $activeAdmins >= 1 ? 'pass' : 'fail',
            true,
            $activeAdmins >= 1
                ? $activeAdmins . ' akun Admin aktif tersedia.'
                : 'Tidak ada akun Admin aktif.',
            'Pastikan minimal satu Admin aktif.'
        );

        $this->add(
            'database.demo_admin',
            'database',
            'Akun demo bawaan sudah diganti',
            !$defaultAdmin ? 'pass' : 'fail',
            true,
            !$defaultAdmin
                ? 'Email admin demo aktif tidak ditemukan.'
                : 'admin@civicyouth.local masih aktif.',
            'Pastikan Admin personal sudah aktif dan berhasil login, buat backup, lalu nonaktifkan akun demo. Jangan menonaktifkan satu-satunya Admin.'
        );

        $this->add(
            'database.audit',
            'database',
            'Audit CMS tersedia',
            $db->tableExists('cms_audit_logs') ? 'pass' : 'warning',
            false,
            $db->tableExists('cms_audit_logs')
                ? 'Tabel audit CMS tersedia.'
                : 'Tabel audit CMS belum tersedia.',
            'Jalankan migration Fase 3D.'
        );
    }

    protected function filesystemChecks(): void
    {
        $paths = [
            'writable' => WRITEPATH,
            'cache' => WRITEPATH . 'cache',
            'logs' => WRITEPATH . 'logs',
            'session' => WRITEPATH . 'session',
            'uploads_internal' => WRITEPATH . 'uploads',
            'uploads_public' => FCPATH . 'uploads',
        ];

        foreach ($paths as $key => $path) {
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);

            $this->add(
                'filesystem.' . $key,
                'filesystem',
                'Folder dapat ditulis: ' . $key,
                $writable ? 'pass' : 'fail',
                true,
                !$exists
                    ? 'Folder belum tersedia.'
                    : ($writable ? 'Folder tersedia dan writable.' : 'Folder tidak writable.'),
                'Gunakan permission 755/775 sesuai user web server. Hindari 777.'
            );
        }

        $envExists = is_file(ROOTPATH . '.env');

        $this->add(
            'filesystem.env',
            'filesystem',
            'File .env production tersedia',
            $envExists ? 'pass' : 'fail',
            true,
            $envExists
                ? '.env tersedia di root aplikasi.'
                : '.env belum ditemukan.',
            'Buat .env dari .env.production.example langsung di server.'
        );

        $publicShape = strtolower(
            basename(rtrim(FCPATH, DIRECTORY_SEPARATOR))
        ) === 'public';

        $this->add(
            'filesystem.public_shape',
            'filesystem',
            'Struktur folder public dikenali',
            $publicShape ? 'pass' : 'warning',
            false,
            $publicShape
                ? 'FCPATH menunjuk folder public.'
                : 'Nama web root bukan public.',
            'Pastikan document root hanya berisi front controller dan aset publik.'
        );

        $this->add(
            'filesystem.git',
            'filesystem',
            'Metadata Git tidak dibawa',
            !is_dir(ROOTPATH . '.git') ? 'pass' : 'warning',
            false,
            !is_dir(ROOTPATH . '.git')
                ? 'Folder .git tidak ditemukan.'
                : 'Folder .git masih ada pada release.',
            'Gunakan production package agar .git tidak ikut.'
        );

        $dev = is_dir(ROOTPATH . 'vendor/phpunit')
            || is_dir(ROOTPATH . 'vendor/fakerphp');

        $this->add(
            'filesystem.dev_dependencies',
            'filesystem',
            'Dependency development tidak dibawa',
            !$dev ? 'pass' : 'warning',
            false,
            !$dev
                ? 'Dependency development tidak terdeteksi.'
                : 'PHPUnit atau Faker masih tersedia.',
            'Jalankan composer install --no-dev --optimize-autoloader.'
        );

        $backupFiles = is_dir(ROOTPATH . 'backups')
            ? glob(ROOTPATH . 'backups/*')
            : [];

        $hasBackups = is_array($backupFiles) && $backupFiles !== [];

        $this->add(
            'filesystem.backups',
            'filesystem',
            'Backup tidak berada di release',
            !$hasBackups ? 'pass' : 'warning',
            false,
            !$hasBackups
                ? 'Tidak ada backup pada source release.'
                : 'Folder backups berisi file.',
            'Simpan backup di lokasi privat terpisah.'
        );
    }

    protected function recoveryChecks(): void
    {
        try {
            $service = new BackupRecoveryService();
            $status = $service->status();
            $latest = $status['latest'] ?? null;
            $isProduction = ENVIRONMENT === 'production';

            $storageReady = !empty($status['directory_ready'])
                && !empty($status['temporary_ready']);

            $this->add(
                'recovery.storage',
                'recovery',
                'Storage backup siap',
                $storageReady ? 'pass' : 'fail',
                true,
                $storageReady
                    ? 'Folder backup dan temporary writable.'
                    : 'Folder backup atau temporary belum writable.',
                'Perbaiki permission writable/backups dan writable/backup-temp.'
            );

            $hasBackup = is_array($latest);

            $this->add(
                'recovery.exists',
                'recovery',
                'Backup aplikasi tersedia',
                $hasBackup
                    ? 'pass'
                    : ($isProduction ? 'fail' : 'warning'),
                $isProduction,
                $hasBackup
                    ? 'Backup terbaru ditemukan.'
                    : 'Belum ada backup aplikasi.',
                'Jalankan php spark backup:create dan simpan salinan kedua di luar server.'
            );

            if ($hasBackup) {
                $ageHours = $service->latestBackupAgeHours();
                $warningHours = (int) env('backup.warningAgeHours', 48);
                $criticalHours = (int) env('backup.criticalAgeHours', 168);

                $ageStatus = $ageHours !== null && $ageHours <= $warningHours
                    ? 'pass'
                    : (
                        $ageHours !== null && $ageHours <= $criticalHours
                            ? 'warning'
                            : ($isProduction ? 'fail' : 'warning')
                    );

                $this->add(
                    'recovery.age',
                    'recovery',
                    'Backup terbaru masih segar',
                    $ageStatus,
                    $isProduction && $ageStatus === 'fail',
                    $ageHours !== null
                        ? 'Usia backup: ' . $ageHours . ' jam.'
                        : 'Usia backup tidak dapat dihitung.',
                    'Jalankan backup otomatis sekurangnya sekali sehari.'
                );

                $verified = (
                    $latest['verification_status'] ?? ''
                ) === 'verified';

                $this->add(
                    'recovery.verified',
                    'recovery',
                    'Backup terbaru terverifikasi',
                    $verified
                        ? 'pass'
                        : ($isProduction ? 'fail' : 'warning'),
                    $isProduction && !$verified,
                    $verified
                        ? 'Checksum dan isi archive telah diverifikasi.'
                        : 'Backup terbaru belum terverifikasi.',
                    'Jalankan php spark backup:verify --file ARCHIVE.zip.'
                );
            }

            $restoreTool = !empty($status['native_tools']['mysql']);

            $this->add(
                'recovery.mysql',
                'recovery',
                'mysql client untuk restore tersedia',
                $restoreTool ? 'pass' : 'warning',
                false,
                $restoreTool
                    ? 'Binary mysql terdeteksi.'
                    : 'Binary mysql belum ditemukan.',
                'Atur backup.mysqlPath pada .env agar restore CLI siap.'
            );

            if ($status['free_bytes'] !== null) {
                $minimum = (int) env(
                    'backup.minimumFreeSpaceBytes',
                    1073741824
                );

                $this->add(
                    'recovery.space',
                    'recovery',
                    'Ruang storage backup memadai',
                    $status['free_bytes'] >= $minimum
                        ? 'pass'
                        : 'warning',
                    false,
                    'Ruang kosong: '
                        . number_format(
                            $status['free_bytes'] / 1073741824,
                            2
                        )
                        . ' GB.',
                    'Sediakan minimal 1 GB atau beberapa kali ukuran data aktif.'
                );
            }


            $monitoring = new SystemMonitoringService();
            $monitoringReady = $monitoring->monitoringReady();

            $this->add(
                'recovery.monitoring_tables',
                'recovery',
                'Tabel monitoring operasional tersedia',
                $monitoringReady
                    ? 'pass'
                    : ($isProduction ? 'fail' : 'warning'),
                $isProduction && !$monitoringReady,
                $monitoringReady
                    ? 'Snapshot dan incident center siap digunakan.'
                    : 'Tabel monitoring belum tersedia.',
                'Jalankan migration Fase 4C.'
            );

            if ($monitoringReady) {
                $statistics = $monitoring->statistics();
                $lastSnapshot = $statistics['last_snapshot_at'] ?? null;
                $snapshotAgeMinutes = null;

                if (is_string($lastSnapshot) && $lastSnapshot !== '') {
                    $lastTimestamp = strtotime($lastSnapshot);

                    if ($lastTimestamp !== false) {
                        $snapshotAgeMinutes = (int) floor(
                            (time() - $lastTimestamp) / 60
                        );
                    }
                }

                $snapshotFresh = $snapshotAgeMinutes !== null
                    && $snapshotAgeMinutes <= 15;

                $this->add(
                    'recovery.monitoring_snapshot',
                    'recovery',
                    'Snapshot monitoring terbaru',
                    $snapshotFresh
                        ? 'pass'
                        : ($isProduction ? 'fail' : 'warning'),
                    $isProduction && !$snapshotFresh,
                    $snapshotAgeMinutes !== null
                        ? 'Usia snapshot: ' . $snapshotAgeMinutes . ' menit.'
                        : 'Belum ada snapshot monitoring.',
                    'Aktifkan scheduler system:health:snapshot setiap lima menit.'
                );
            }
        } catch (\Throwable $exception) {
            $this->add(
                'recovery.service',
                'recovery',
                'Layanan backup dapat diperiksa',
                ENVIRONMENT === 'production'
                    ? 'fail'
                    : 'warning',
                ENVIRONMENT === 'production',
                'Layanan backup belum dapat diperiksa.',
                'Periksa extension zip, konfigurasi, dan permission writable.'
            );
        }
    }

    protected function applicationChecks(): void
    {
        $routes = is_file(APPPATH . 'Config/Routes.php')
            ? (string) file_get_contents(APPPATH . 'Config/Routes.php')
            : '';

        $needles = [
            '/sitemap.xml',
            '/robots.txt',
            '/login',
            '/website/audit',
            '/system/readiness',
            '/system/operations',
            '/health/live',
            '/health/ready',
        ];

        $missing = [];
        foreach ($needles as $needle) {
            if (!str_contains($routes, $needle)) {
                $missing[] = $needle;
            }
        }

        $this->add(
            'application.routes',
            'application',
            'Route penting tersedia',
            $missing === [] ? 'pass' : 'fail',
            true,
            $missing === []
                ? 'Route publik, autentikasi, audit, health, dan operations tersedia.'
                : 'Route tidak ditemukan: ' . implode(', ', $missing),
            'Periksa app/Config/Routes.php.'
        );

        $htaccess = is_file(FCPATH . '.htaccess')
            ? (string) file_get_contents(FCPATH . '.htaccess')
            : '';

        $rewrite = str_contains($htaccess, 'RewriteEngine On')
            && str_contains($htaccess, 'index.php');

        $this->add(
            'application.rewrite',
            'application',
            'Rewrite Apache tersedia',
            $rewrite ? 'pass' : 'warning',
            false,
            $rewrite
                ? 'public/.htaccess memuat rewrite.'
                : 'Rewrite Apache tidak terdeteksi.',
            'Untuk Apache/cPanel aktifkan mod_rewrite dan AllowOverride All.'
        );

        $logo = is_file(FCPATH . 'assets/img/logo-rw01.png');

        $this->add(
            'application.brand',
            'application',
            'Aset identitas tersedia',
            $logo ? 'pass' : 'warning',
            false,
            $logo ? 'Logo utama tersedia.' : 'Logo utama default tidak ditemukan.',
            'Periksa logo, favicon, dan upload pengaturan.'
        );

        $robots = is_file(FCPATH . 'robots.txt');

        $this->add(
            'application.robots',
            'application',
            'Fallback robots.txt tersedia',
            $robots ? 'pass' : 'warning',
            false,
            $robots ? 'public/robots.txt tersedia.' : 'Fallback robots.txt belum tersedia.',
            'Pertahankan endpoint dinamis dan fallback statis.'
        );
    }

    protected function deploymentChecks(): void
    {
        $assets = [
            '.env.production.example' => 'Template .env production',
            'deploy/apache-vhost.example.conf' => 'Template Apache VirtualHost',
            'deploy/nginx-server.example.conf' => 'Template Nginx server block',
            'deploy/DEPLOYMENT-RUNBOOK.md' => 'Runbook deployment',
            'scripts/production-preflight.ps1' => 'Script preflight Windows',
            'scripts/production-package.ps1' => 'Pembuat package production',
        ];

        foreach ($assets as $relative => $title) {
            $ready = is_file(
                ROOTPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative)
            );

            $this->add(
                'deployment.' . preg_replace('/[^a-z0-9]+/i', '_', $relative),
                'deployment',
                $title,
                $ready ? 'pass' : 'warning',
                false,
                $ready ? $relative . ' tersedia.' : $relative . ' belum tersedia.',
                'Gunakan aset deployment resmi dari package Fase 4A.'
            );
        }
    }

    protected function manualChecks(): void
    {
        foreach ($this->config->manualChecks as $key => $manual) {
            $this->add(
                'manual.' . $key,
                'manual',
                $manual['title'],
                'manual',
                false,
                $manual['description'],
                'Konfirmasi pada checklist deployment organisasi.'
            );
        }
    }

    protected function add(
        string $id,
        string $category,
        string $title,
        string $status,
        bool $blocking,
        string $message,
        string $recommendation
    ): void {
        if (!in_array($status, ['pass', 'warning', 'fail', 'manual'], true)) {
            throw new RuntimeException('Status readiness tidak valid.');
        }

        $this->checks[] = [
            'id' => $id,
            'category' => $category,
            'title' => $title,
            'status' => $status,
            'blocking' => $blocking,
            'message' => $message,
            'recommendation' => $recommendation,
        ];
    }

    /** @return list<string> */
    protected function pendingMigrations(string $table): array
    {
        if ($this->database === null) {
            return [];
        }

        $fileVersions = [];
        $files = glob(APPPATH . 'Database/Migrations/*.php');

        if (is_array($files)) {
            foreach ($files as $file) {
                if (
                    preg_match(
                        '/^(\d{4}-\d{2}-\d{2}-\d{6})_/',
                        basename($file),
                        $matches
                    ) === 1
                ) {
                    $fileVersions[] = $matches[1];
                }
            }
        }

        $rows = $this->database
            ->table($table)
            ->select('version')
            ->get()
            ->getResultArray();

        $migrated = array_map(
            static fn (array $row): string => (string) ($row['version'] ?? ''),
            $rows
        );

        $pending = array_values(array_diff(
            array_unique($fileVersions),
            array_unique($migrated)
        ));

        sort($pending);

        return $pending;
    }

    /** @return list<string> */
    protected function dangerousUploadFiles(): array
    {
        $root = FCPATH . 'uploads';

        if (!is_dir($root)) {
            return [];
        }

        $dangerous = array_fill_keys(
            array_map('strtolower', $this->config->dangerousUploadExtensions),
            true
        );

        $matches = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $root,
                    RecursiveDirectoryIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                if (isset($dangerous[strtolower($file->getExtension())])) {
                    $matches[] = $file->getFilename();

                    if (count($matches) >= 20) {
                        break;
                    }
                }
            }
        } catch (Throwable $exception) {
            return ['[folder upload tidak dapat diperiksa]'];
        }

        return $matches;
    }

    protected function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '-1') {
            return -1;
        }

        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
