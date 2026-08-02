<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Permissions;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class AuthorizationAudit extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'auth:permissions:audit';
    protected $description =
        'Audit matriks peran, permission route, dan guard aksi pada view Portal.';
    protected $usage = 'auth:permissions:audit [--strict] [--json]';
    protected $options = [
        '--strict' => 'Jadikan peringatan sebagai exit code gagal.',
        '--json' => 'Tampilkan hasil dalam format JSON.',
    ];

    /** @var list<array{status:string,check:string,result:string}> */
    private array $results = [];
    private int $failures = 0;
    private int $warnings = 0;

    /** @var array<string, list<string>> */
    private array $viewContracts = [
        'members/index.php' => [
            'members.import',
            'members.export',
            'members.create',
            'members.update',
            'members.delete',
        ],
        'structures/index.php' => [
            'structures.create',
            'structures.update',
            'structures.delete',
        ],
        'meetings/index.php' => [
            'meetings.create',
            'meetings.update',
            'meetings.delete',
            'attendances.recap',
        ],
        'attendances/index.php' => [
            'attendances.create',
            'attendances.update',
            'attendances.delete',
        ],
        'attendances/recap.php' => [
            'attendances.bulk',
            'attendances.create',
            'attendances.update',
        ],
        'cash/index.php' => [
            'cash.export',
            'cash.create',
            'cash.update',
            'cash.delete',
        ],
        'programs/index.php' => [
            'programs.create',
            'programs.update',
            'programs.publish',
            'programs.archive',
        ],
        'content_studio/index.php' => [
            'content_studio.create',
            'content_studio.delete',
        ],
        'contact_messages/show.php' => [
            'messages.manage',
        ],
        'reports/index.php' => [
            'reports.members',
            'reports.cash',
            'reports.meetings',
        ],
        'seo/index.php' => [
            'website.pages.view',
            'programs.view',
            'activities.view',
            'settings.website.manage',
        ],
    ];

    public function run(array $params)
    {
        $config = config(Permissions::class);
        $knownRoles = [
            'admin',
            'ketua',
            'sekretaris',
            'bendahara',
            'pengurus',
        ];

        $configuredRoles = array_keys($config->rolePermissions);

        $this->check(
            $configuredRoles === $knownRoles,
            'Daftar peran',
            'Peran Portal sesuai urutan standar.',
            'Peran terkonfigurasi tidak sesuai standar: '
                . implode(', ', $configuredRoles)
        );

        $adminPermissions = $config->rolePermissions['admin'] ?? [];

        $this->check(
            $adminPermissions === ['*'],
            'Wildcard Admin',
            'Admin memiliki wildcard tunggal.',
            'Admin harus menggunakan wildcard tunggal *.'
        );

        foreach ($knownRoles as $role) {
            if ($role === 'admin') {
                continue;
            }

            $permissions = $config->rolePermissions[$role] ?? [];

            $this->check(
                !in_array('*', $permissions, true),
                'Least privilege: ' . ucfirst($role),
                'Tidak memakai wildcard global.',
                'Role non-Admin tidak boleh memiliki wildcard global.'
            );
        }

        $routePermissions = $this->routePermissions();
        $viewPermissions = $this->viewPermissions();
        $referencedPermissions = array_values(array_unique(array_merge(
            $routePermissions,
            $viewPermissions
        )));
        sort($referencedPermissions);

        $missingLabels = array_values(array_filter(
            $referencedPermissions,
            static fn (string $permission): bool => !array_key_exists(
                $permission,
                $config->permissionLabels
            )
        ));

        $this->check(
            $missingLabels === [],
            'Label permission',
            count($referencedPermissions)
                . ' permission route/view memiliki label.',
            'Permission tanpa label: ' . implode(', ', $missingLabels)
        );

        $grantProblems = [];

        foreach ($config->rolePermissions as $role => $permissions) {
            foreach ($permissions as $permission) {
                if ($permission === '*' || str_ends_with($permission, '.*')) {
                    continue;
                }

                if (!array_key_exists($permission, $config->permissionLabels)) {
                    $grantProblems[] = $role . ':' . $permission;
                }
            }
        }

        $this->check(
            $grantProblems === [],
            'Grant eksplisit',
            'Seluruh grant eksplisit memiliki definisi label.',
            'Grant tidak dikenal: ' . implode(', ', $grantProblems)
        );

        $this->checkViewContracts();
        $this->checkAdminOnlyPermissions($config);

        $summary = [
            'status' => $this->failures > 0
                ? 'failed'
                : ($this->warnings > 0 ? 'warning' : 'passed'),
            'failures' => $this->failures,
            'warnings' => $this->warnings,
            'route_permissions' => count($routePermissions),
            'view_permissions' => count($viewPermissions),
            'results' => $this->results,
        ];

        if (CLI::getOption('json') !== null) {
            CLI::write((string) json_encode(
                $summary,
                JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
            ));
        } else {
            CLI::write('GARDA 01 — Authorization Audit', 'yellow');
            CLI::newLine();

            $rows = array_map(
                static fn (array $result): array => [
                    $result['status'],
                    $result['check'],
                    $result['result'],
                ],
                $this->results
            );

            CLI::table($rows, ['Status', 'Pemeriksaan', 'Hasil']);
            CLI::newLine();
            CLI::write(
                'Ringkasan: '
                    . $this->failures
                    . ' gagal, '
                    . $this->warnings
                    . ' peringatan.',
                $this->failures > 0 ? 'red' : 'green'
            );
        }

        $strict = CLI::getOption('strict') !== null;

        return $this->failures > 0
            || ($strict && $this->warnings > 0)
            ? EXIT_ERROR
            : EXIT_SUCCESS;
    }

    /** @return list<string> */
    private function routePermissions(): array
    {
        $routesPath = APPPATH . 'Config/Routes.php';
        $content = is_file($routesPath)
            ? (string) file_get_contents($routesPath)
            : '';

        $pattern = <<<'REGEX'
/\$guard\(\s*'([^']+)'\s*\)/
REGEX;

        preg_match_all(
            $pattern,
            $content,
            $matches
        );



        $permissions = array_values(array_unique($matches[1] ?? []));
        sort($permissions);

        $this->check(
            $permissions !== [],
            'Permission route',
            count($permissions) . ' permission route terdeteksi.',
            'Tidak ada permission route yang dapat dibaca.'
        );

        return $permissions;
    }

    /** @return list<string> */
    private function viewPermissions(): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(APPPATH . 'Views')
        );
        $files = new RegexIterator($iterator, '/\.php$/i');
        $permissions = [];

        foreach ($files as $file) {
            $content = (string) file_get_contents($file->getPathname());

            preg_match_all(
                "/auth_can\\(\\s*'([^']+)'\\s*\\)/",
                $content,
                $singleMatches
            );

            foreach ($singleMatches[1] ?? [] as $permission) {
                $permissions[] = $permission;
            }

            preg_match_all(
                '/auth_can_any\\(\\s*\\[(.*?)\\]\\s*\\)/s',
                $content,
                $arrayMatches
            );

            foreach ($arrayMatches[1] ?? [] as $arrayBody) {
                preg_match_all(
                    "/'([a-z0-9_]+(?:\\.[a-z0-9_]+)+)'/i",
                    $arrayBody,
                    $permissionMatches
                );

                foreach ($permissionMatches[1] ?? [] as $permission) {
                    $permissions[] = $permission;
                }
            }
        }

        $permissions = array_values(array_unique($permissions));
        sort($permissions);

        $this->check(
            $permissions !== [],
            'Permission view',
            count($permissions) . ' permission view terdeteksi.',
            'Tidak ada guard permission pada view.'
        );

        return $permissions;
    }

    private function checkViewContracts(): void
    {
        foreach ($this->viewContracts as $relativePath => $permissions) {
            $path = APPPATH . 'Views/' . $relativePath;

            if (!is_file($path)) {
                $this->fail(
                    'Kontrak view: ' . $relativePath,
                    'File view tidak ditemukan.'
                );
                continue;
            }

            $content = (string) file_get_contents($path);
            $missing = array_values(array_filter(
                $permissions,
                static fn (string $permission): bool => !str_contains(
                    $content,
                    "'" . $permission . "'"
                )
            ));

            $this->check(
                $missing === [],
                'Kontrak view: ' . $relativePath,
                count($permissions) . ' guard aksi tersedia.',
                'Guard belum ditemukan: ' . implode(', ', $missing)
            );
        }
    }

    private function checkAdminOnlyPermissions(Permissions $config): void
    {
        $adminOnly = [
            'users.view',
            'users.create',
            'users.update',
            'users.status',
            'users.reset_password',
            'users.revoke_sessions',
            'system.backups.prune',
        ];
        $violations = [];

        foreach ($adminOnly as $permission) {
            foreach ($config->rolePermissions as $role => $grants) {
                if ($role === 'admin') {
                    continue;
                }

                if ($this->grantsPermission($grants, $permission)) {
                    $violations[] = $role . ':' . $permission;
                }
            }
        }

        $this->check(
            $violations === [],
            'Permission berisiko tinggi',
            'Manajemen akun dan prune backup tetap Admin-only.',
            'Grant berisiko ditemukan: ' . implode(', ', $violations)
        );
    }

    /** @param list<string> $grants */
    private function grantsPermission(array $grants, string $permission): bool
    {
        foreach ($grants as $grant) {
            if ($grant === '*' || $grant === $permission) {
                return true;
            }

            if (
                str_ends_with($grant, '.*')
                && str_starts_with($permission, substr($grant, 0, -1))
            ) {
                return true;
            }
        }

        return false;
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
        $this->results[] = [
            'status' => 'PASS',
            'check' => $check,
            'result' => $result,
        ];
    }

    private function warn(string $check, string $result): void
    {
        $this->warnings++;
        $this->results[] = [
            'status' => 'WARN',
            'check' => $check,
            'result' => $result,
        ];
    }

    private function fail(string $check, string $result): void
    {
        $this->failures++;
        $this->results[] = [
            'status' => 'FAIL',
            'check' => $check,
            'result' => $result,
        ];
    }
}
