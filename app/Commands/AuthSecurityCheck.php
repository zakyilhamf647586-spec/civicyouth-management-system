<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AuthSecurityCheck extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'auth:security-check';
    protected $description =
        'Memeriksa migration, sesi, Admin, kredensial demo, dan audit autentikasi.';
    protected $usage = 'auth:security-check [--strict]';
    protected $options = [
        '--strict' => 'Jadikan peringatan sebagai exit code gagal.',
    ];

    /** @var list<list<string>> */
    private array $rows = [];
    private int $failures = 0;
    private int $warnings = 0;

    public function run(array $params)
    {
        CLI::write('GARDA 01 — Account & Access Security', 'yellow');
        CLI::newLine();

        try {
            $db = db_connect();
            $userModel = new UserModel();

            $this->check(
                $db->tableExists('users'),
                'Tabel akun',
                'Tabel users tersedia.',
                'Tabel users belum tersedia.'
            );

            $this->check(
                $userModel->securitySchemaReady(),
                'Migration hardening',
                'Kolom versi sesi dan status kata sandi tersedia.',
                'Jalankan php spark migrate.'
            );

            $this->check(
                $db->tableExists('cms_audit_logs'),
                'Audit keamanan',
                'Central audit tersedia.',
                'Tabel cms_audit_logs belum tersedia.'
            );

            $statistics = $userModel->accountStatistics();
            $activeAdmins = (int) ($statistics['active_admins'] ?? 0);

            $this->check(
                $activeAdmins >= 1,
                'Kontinuitas Admin',
                $activeAdmins . ' Admin aktif tersedia.',
                'Tidak ada Admin aktif.'
            );

            $orphanRoles = $db->table('users')
                ->select('users.id')
                ->join('roles', 'roles.id = users.role_id', 'left')
                ->where('users.status', 'active')
                ->where('roles.id IS NULL', null, false)
                ->countAllResults();

            $this->check(
                $orphanRoles === 0,
                'Peran akun aktif',
                'Seluruh akun aktif memiliki peran.',
                $orphanRoles . ' akun aktif tidak memiliki peran.'
            );

            $demo = $userModel->findByEmailWithRole(
                'admin@civicyouth.local'
            );
            $demoActive = is_array($demo)
                && ($demo['status'] ?? '') === 'active';
            $demoCredentialKnown = $demoActive
                && password_verify(
                    'admin123',
                    (string) ($demo['password'] ?? '')
                );

            $this->check(
                !$demoCredentialKnown,
                'Kredensial demo',
                'Kata sandi bawaan tidak lagi berlaku.',
                'Akun demo aktif masih memakai kata sandi bawaan.'
            );

            if ($demoActive && !$demoCredentialKnown) {
                $this->warn(
                    'Transisi Admin',
                    'Akun admin@civicyouth.local masih aktif; siapkan Admin personal sebelum production.'
                );
            } else {
                $this->pass(
                    'Transisi Admin',
                    $demoActive
                        ? 'Kredensial demo sudah diganti.'
                        : 'Akun demo tidak aktif.'
                );
            }

            $mustChange = (int) (
                $statistics['must_change_password'] ?? 0
            );

            if ($mustChange > 0) {
                $this->warn(
                    'Kata sandi awal/reset',
                    $mustChange . ' akun wajib membuat kata sandi pribadi.'
                );
            } else {
                $this->pass(
                    'Kata sandi awal/reset',
                    'Tidak ada akun yang tertahan pada kata sandi awal/reset.'
                );
            }

            $sessionConfig = config('Session');

            $this->check(
                $sessionConfig->regenerateDestroy === true,
                'Regenerasi sesi',
                'Session ID lama dihancurkan saat regenerasi.',
                'Session regenerateDestroy belum aktif.'
            );

            $this->check(
                $sessionConfig->expiration > 0
                    && $sessionConfig->expiration <= 7200,
                'Masa berlaku cookie sesi',
                'Sesi browser dibatasi '
                    . $sessionConfig->expiration
                    . ' detik.',
                'Masa berlaku sesi terlalu panjang atau tanpa batas.'
            );
        } catch (\Throwable $exception) {
            $this->fail(
                'Pemeriksaan runtime',
                $exception->getMessage()
            );
        }

        CLI::table($this->rows, [
            'Status',
            'Pemeriksaan',
            'Hasil',
        ]);
        CLI::newLine();

        CLI::write(
            'Ringkasan: '
                . $this->failures
                . ' gagal, '
                . $this->warnings
                . ' peringatan.',
            $this->failures > 0 ? 'red' : 'green'
        );

        $strict = CLI::getOption('strict') !== null;

        return $this->failures > 0
            || ($strict && $this->warnings > 0)
            ? EXIT_ERROR
            : EXIT_SUCCESS;
    }

    private function check(
        bool $condition,
        string $check,
        string $success,
        string $failure
    ): void {
        if ($condition) {
            $this->pass($check, $success);
            return;
        }

        $this->fail($check, $failure);
    }

    private function pass(string $check, string $result): void
    {
        $this->rows[] = [
            'PASS',
            $check,
            $result,
        ];
    }

    private function warn(string $check, string $result): void
    {
        $this->warnings++;
        $this->rows[] = [
            'WARN',
            $check,
            $result,
        ];
    }

    private function fail(string $check, string $result): void
    {
        $this->failures++;
        $this->rows[] = [
            'FAIL',
            $check,
            $result,
        ];
    }
}
