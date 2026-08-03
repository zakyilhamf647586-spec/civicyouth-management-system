<?php

namespace App\Commands;

use App\Libraries\PublicInternalBoundary;
use App\Models\WebsiteNavigationMenuModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\WebsiteNavigation;

class PublicBoundaryAudit extends BaseCommand
{
    protected $group = 'GARDA 01';
    protected $name = 'public:boundary:audit';
    protected $description =
        'Audit pemisahan website publik dari autentikasi dan route Portal internal.';
    protected $usage = 'public:boundary:audit [--strict] [--json]';
    protected $options = [
        '--strict' => 'Jadikan peringatan sebagai exit code gagal.',
        '--json' => 'Tampilkan hasil dalam format JSON.',
    ];

    /** @var list<array{status:string,check:string,result:string}> */
    private array $results = [];
    private int $failures = 0;
    private int $warnings = 0;

    public function run(array $params)
    {
        $this->auditDefaultNavigation();
        $this->auditStoredNavigation();
        $this->auditPublicViews();
        $this->auditRoutes();
        $this->auditRobots();

        $summary = [
            'status' => $this->failures > 0
                ? 'failed'
                : ($this->warnings > 0 ? 'warning' : 'passed'),
            'failures' => $this->failures,
            'warnings' => $this->warnings,
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
            CLI::write('GARDA 01 — Public/Internal Boundary Audit', 'yellow');
            CLI::newLine();

            CLI::table(
                array_map(
                    static fn (array $result): array => [
                        $result['status'],
                        $result['check'],
                        $result['result'],
                    ],
                    $this->results
                ),
                ['Status', 'Pemeriksaan', 'Hasil']
            );

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

    private function auditDefaultNavigation(): void
    {
        $config = new WebsiteNavigation();
        $restricted = [];

        foreach ($config->menus as $menuKey => $definition) {
            foreach (($definition['items'] ?? []) as $index => $item) {
                if (
                    is_array($item)
                    && PublicInternalBoundary::isRestrictedItem($item)
                ) {
                    $restricted[] = $menuKey . '#' . ($index + 1);
                }
            }
        }

        $this->check(
            $restricted === [],
            'Default navigasi publik',
            'Tidak memuat link autentikasi atau Portal internal.',
            'Item terlarang: ' . implode(', ', $restricted)
        );
    }

    private function auditStoredNavigation(): void
    {
        try {
            $db = db_connect();

            if (!$db->tableExists('website_navigation_menus')) {
                $this->warning(
                    'Navigasi tersimpan',
                    'Tabel website_navigation_menus belum tersedia.'
                );
                return;
            }

            $model = new WebsiteNavigationMenuModel();
            $rows = $model->findAll();
            $restricted = [];

            foreach ($rows as $row) {
                foreach (['draft_items', 'published_items'] as $column) {
                    $items = json_decode(
                        (string) ($row[$column] ?? ''),
                        true
                    );

                    if (!is_array($items)) {
                        continue;
                    }

                    foreach ($items as $index => $item) {
                        if (
                            is_array($item)
                            && PublicInternalBoundary::isRestrictedItem($item)
                        ) {
                            $restricted[] =
                                ($row['menu_key'] ?? 'unknown')
                                . ':'
                                . $column
                                . '#'
                                . ($index + 1);
                        }
                    }
                }
            }

            $this->check(
                $restricted === [],
                'Navigasi database',
                'Draft dan published menu bebas link internal.',
                'Item terlarang: ' . implode(', ', $restricted)
            );
        } catch (\Throwable $exception) {
            $this->warning(
                'Navigasi database',
                'Belum dapat diperiksa: ' . $exception->getMessage()
            );
        }
    }

    private function auditPublicViews(): void
    {
        $files = [
            APPPATH . 'Views/partials/public_navbar.php',
            APPPATH . 'Views/partials/public_footer.php',
            APPPATH . 'Views/partials/public_experience.php',
            APPPATH . 'Views/public/introducing.php',
            APPPATH . 'Views/public/contact.php',
        ];

        $patterns = [
            '/public_url\(\s*[\'\"]\/login[\'\"]\s*\)/i',
            '/Portal\s+(Pengurus|Internal)/i',
            '/Team\s+Portal|Internal\s+Portal/i',
        ];

        $matches = [];

        foreach ($files as $file) {
            $content = is_file($file)
                ? (string) file_get_contents($file)
                : '';

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content) === 1) {
                    $matches[] = basename($file);
                    break;
                }
            }
        }

        $matches = array_values(array_unique($matches));

        $this->check(
            $matches === [],
            'Permukaan website publik',
            'Tidak mempromosikan akses Portal internal.',
            'Referensi internal ditemukan: ' . implode(', ', $matches)
        );
    }

    private function auditRoutes(): void
    {
        $routesPath = APPPATH . 'Config/Routes.php';
        $content = is_file($routesPath)
            ? (string) file_get_contents($routesPath)
            : '';

        $checks = [
            'Introducing /' => "\$routes->get('/', 'IntroducingController::index');",
            'Beranda /home' => "\$routes->get('/home', 'PublicController::index');",
            'Login langsung' => "\$routes->get('/login', 'AuthController::login');",
        ];

        foreach ($checks as $label => $needle) {
            $this->check(
                str_contains($content, $needle),
                $label,
                'Route tersedia sesuai arsitektur.',
                'Route wajib tidak ditemukan.'
            );
        }
    }

    private function auditRobots(): void
    {
        $robotsPath = FCPATH . 'robots.txt';
        $content = is_file($robotsPath)
            ? strtolower((string) file_get_contents($robotsPath))
            : '';

        $this->check(
            str_contains($content, 'disallow: /login')
                && str_contains($content, 'disallow: /system')
                && str_contains($content, 'disallow: /website'),
            'Robots internal',
            'Route autentikasi dan sistem tidak diindeks.',
            'robots.txt belum menutup seluruh route internal utama.'
        );
    }

    private function check(
        bool $passed,
        string $check,
        string $success,
        string $failure
    ): void {
        if ($passed) {
            $this->results[] = [
                'status' => 'PASS',
                'check' => $check,
                'result' => $success,
            ];
            return;
        }

        $this->failures++;
        $this->results[] = [
            'status' => 'FAIL',
            'check' => $check,
            'result' => $failure,
        ];
    }

    private function warning(string $check, string $message): void
    {
        $this->warnings++;
        $this->results[] = [
            'status' => 'WARN',
            'check' => $check,
            'result' => $message,
        ];
    }
}
