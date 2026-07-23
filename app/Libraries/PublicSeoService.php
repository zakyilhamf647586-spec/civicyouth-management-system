<?php

namespace App\Libraries;

use App\Models\ActivityModel;
use App\Models\ProgramModel;
use App\Models\PublicPageModel;

class PublicSeoService
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function metadata(array $context): array
    {
        $activePage = (string) (
            $context['active_page'] ?? ''
        );

        $activity = is_array(
            $context['activity'] ?? null
        )
            ? $context['activity']
            : null;

        $program = is_array(
            $context['program'] ?? null
        )
            ? $context['program']
            : null;

        $canonical = $this->canonicalUrl(
            (string) (
                $context['canonical_url'] ?? ''
            )
        );

        $image = $this->contextImage(
            $activePage,
            $activity,
            $program,
            (string) (
                $context['image'] ?? ''
            )
        );

        $pageType = $this->pageType($activePage);

        return [
            'canonical' => $canonical,
            'image' => $image,
            'image_alt' => trim((string) (
                $context['image_alt']
                ?? site_setting(
                    'seo_og_image_alt',
                    site_setting(
                        'organization_name',
                        'GARDA 01'
                    )
                )
            )),
            'open_graph_type' =>
                $activePage === 'activity_detail'
                    ? 'article'
                    : 'website',
            'schema_page_type' => $pageType,
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function structuredData(
        array $context
    ): array {
        $title = trim((string) (
            $context['title'] ?? ''
        ));

        $description = trim((string) (
            $context['description'] ?? ''
        ));

        $activePage = trim((string) (
            $context['active_page'] ?? ''
        ));

        $canonical = $this->canonicalUrl(
            (string) (
                $context['canonical_url'] ?? ''
            )
        );

        $image = $this->absoluteAssetUrl(
            (string) (
                $context['image'] ?? ''
            )
        );

        $pageType = (string) (
            $context['page_type']
            ?? $this->pageType($activePage)
        );

        $activity = is_array(
            $context['activity'] ?? null
        )
            ? $context['activity']
            : null;

        $program = is_array(
            $context['program'] ?? null
        )
            ? $context['program']
            : null;

        $programs = is_array(
            $context['programs'] ?? null
        )
            ? $context['programs']
            : [];

        $activities = is_array(
            $context['activities'] ?? null
        )
            ? $context['activities']
            : [];

        $organizationId = rtrim(
            base_url('/'),
            '/'
        ) . '/#organization';

        $websiteId = rtrim(
            base_url('/'),
            '/'
        ) . '/#website';

        $webPageId = $canonical . '#webpage';

        $graph = [
            $this->organizationSchema(
                $organizationId
            ),
            [
                '@type' => 'WebSite',
                '@id' => $websiteId,
                'url' => base_url('/'),
                'name' => site_setting(
                    'organization_name',
                    'GARDA 01'
                ),
                'alternateName' => site_setting(
                    'organization_full_name',
                    'Generasi Aktif Randugarut'
                ),
                'publisher' => [
                    '@id' => $organizationId,
                ],
                'inLanguage' => 'id-ID',
            ],
        ];

        $breadcrumb = $this->breadcrumbSchema(
            $activePage,
            $activity,
            $program,
            $canonical
        );

        $pageSchema = [
            '@type' => $pageType,
            '@id' => $webPageId,
            'url' => $canonical,
            'name' => $title,
            'description' => $description,
            'isPartOf' => [
                '@id' => $websiteId,
            ],
            'about' => [
                '@id' => $organizationId,
            ],
            'publisher' => [
                '@id' => $organizationId,
            ],
            'inLanguage' => 'id-ID',
        ];

        if ($image !== '') {
            $pageSchema['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => $image,
            ];
        }

        if ($breadcrumb !== null) {
            $pageSchema['breadcrumb'] = [
                '@id' => $breadcrumb['@id'],
            ];
        }

        $graph[] = $pageSchema;

        if ($breadcrumb !== null) {
            $graph[] = $breadcrumb;
        }

        if (
            $activePage === 'activity_detail'
            && $activity !== null
        ) {
            $graph[] = $this->activityArticleSchema(
                $activity,
                $canonical,
                $image,
                $description,
                $organizationId
            );
        }

        if (
            $activePage === 'program_detail'
            && $program !== null
        ) {
            $graph[] = $this->programSchema(
                $program,
                $canonical,
                $image,
                $organizationId
            );
        }

        if (
            $activePage === 'programs'
            && $programs !== []
        ) {
            $itemList = $this->programItemList(
                $programs,
                $canonical
            );

            if ($itemList !== null) {
                $graph[] = $itemList;
            }
        }

        if (
            $activePage === 'activities'
            && $activities !== []
        ) {
            $itemList = $this->activityItemList(
                $activities,
                $canonical
            );

            if ($itemList !== null) {
                $graph[] = $itemList;
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_filter(
                $graph,
                'is_array'
            )),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function sitemapEntries(): array
    {
        $entries = [];
        $pageDates = [];

        try {
            $database = db_connect();

            if ($database->tableExists('public_pages')) {
                $pageRows = (new PublicPageModel())
                    ->select(
                        'page_key, route_path, '
                        . 'published_at, updated_at'
                    )
                    ->findAll();

                foreach ($pageRows as $row) {
                    $pageDates[
                        (string) $row['route_path']
                    ] = $row['published_at']
                        ?: $row['updated_at'];
                }
            }
        } catch (\Throwable $exception) {
            log_message(
                'warning',
                'Sitemap public page metadata fallback: '
                . $exception->getMessage()
            );
        }

        $staticPages = [
            [
                'path' => '/',
                'priority' => '1.0',
                'changefreq' => 'weekly',
            ],
            [
                'path' => '/profil',
                'priority' => '0.8',
                'changefreq' => 'monthly',
            ],
            [
                'path' => '/program',
                'priority' => '0.9',
                'changefreq' => 'weekly',
            ],
            [
                'path' => '/kegiatan',
                'priority' => '0.9',
                'changefreq' => 'weekly',
            ],
            [
                'path' => '/pengurus',
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ],
            [
                'path' => '/kontak',
                'priority' => '0.7',
                'changefreq' => 'monthly',
            ],
        ];

        foreach ($staticPages as $staticPage) {
            $path = $staticPage['path'];

            $entries[] = [
                'loc' => base_url(
                    ltrim($path, '/')
                ),
                'lastmod' => $this->dateValue(
                    $pageDates[$path] ?? null
                ),
                'changefreq' =>
                    $staticPage['changefreq'],
                'priority' =>
                    $staticPage['priority'],
                'source' => 'static',
            ];
        }

        try {
            $programs = (new ProgramModel())
                ->where('status', 'published')
                ->orderBy('display_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            foreach ($programs as $program) {
                if (empty($program['slug'])) {
                    continue;
                }

                $entries[] = [
                    'loc' => base_url(
                        'program/' . $program['slug']
                    ),
                    'lastmod' => $this->dateValue(
                        $program['updated_at']
                        ?? $program['created_at']
                        ?? null
                    ),
                    'changefreq' => 'monthly',
                    'priority' => '0.8',
                    'source' => 'program',
                ];
            }
        } catch (\Throwable $exception) {
            log_message(
                'warning',
                'Sitemap program fallback: '
                . $exception->getMessage()
            );
        }

        try {
            $activities = (new ActivityModel())
                ->applyPublicVisibility()
                ->select(
                    'activities.id, '
                    . 'activities.updated_at, '
                    . 'activities.created_at, '
                    . 'activities.published_at'
                )
                ->orderBy(
                    'activities.activity_date',
                    'DESC'
                )
                ->orderBy('activities.id', 'DESC')
                ->findAll();

            foreach ($activities as $activity) {
                $entries[] = [
                    'loc' => base_url(
                        'kegiatan/' . $activity['id']
                    ),
                    'lastmod' => $this->dateValue(
                        $activity['updated_at']
                        ?? $activity['published_at']
                        ?? $activity['created_at']
                        ?? null
                    ),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                    'source' => 'activity',
                ];
            }
        } catch (\Throwable $exception) {
            log_message(
                'warning',
                'Sitemap activity fallback: '
                . $exception->getMessage()
            );
        }

        $unique = [];

        foreach ($entries as $entry) {
            $unique[$entry['loc']] = $entry;
        }

        return array_values($unique);
    }

    /**
     * @return array<string, mixed>
     */
    public function audit(): array
    {
        $entries = $this->sitemapEntries();

        $sourceCounts = [
            'static' => 0,
            'program' => 0,
            'activity' => 0,
        ];

        foreach ($entries as $entry) {
            $source = $entry['source'] ?? '';

            if (isset($sourceCounts[$source])) {
                $sourceCounts[$source]++;
            }
        }

        $pageIssues = [];
        $pageCount = 0;

        try {
            $database = db_connect();

            if ($database->tableExists('public_pages')) {
                $pages = (new PublicPageModel())
                    ->orderBy('id', 'ASC')
                    ->findAll();

                $pageCount = count($pages);

                foreach ($pages as $page) {
                    if (empty(
                        trim((string) (
                            $page['published_title']
                            ?? ''
                        ))
                    )) {
                        $pageIssues[] =
                            ($page['name'] ?? 'Halaman')
                            . ': judul SEO publik kosong.';
                    }

                    if (empty(
                        trim((string) (
                            $page[
                                'published_meta_description'
                            ] ?? ''
                        ))
                    )) {
                        $pageIssues[] =
                            ($page['name'] ?? 'Halaman')
                            . ': meta description publik kosong.';
                    }
                }
            }
        } catch (\Throwable $exception) {
            $pageIssues[] =
                'Data CMS halaman belum dapat diaudit.';
        }

        $programIssues = [];
        $publishedPrograms = 0;

        try {
            $programRows = (new ProgramModel())
                ->where('status', 'published')
                ->findAll();

            $publishedPrograms = count($programRows);

            foreach ($programRows as $program) {
                if (empty(trim((string) (
                    $program['short_description']
                    ?? ''
                )))) {
                    $programIssues[] =
                        ($program['name'] ?? 'Program')
                        . ': deskripsi singkat kosong.';
                }

                if (empty(trim((string) (
                    $program['cover_image']
                    ?? ''
                )))) {
                    $programIssues[] =
                        ($program['name'] ?? 'Program')
                        . ': cover belum tersedia.';
                }
            }
        } catch (\Throwable $exception) {
            $programIssues[] =
                'Data program belum dapat diaudit.';
        }

        $activityIssues = [];
        $publicActivities = 0;

        try {
            $activityRows = (new ActivityModel())
                ->applyPublicVisibility()
                ->select(
                    'activities.id, '
                    . 'activities.title, '
                    . 'activities.summary, '
                    . 'activities.description, '
                    . 'activities.documentation_file'
                )
                ->findAll();

            $publicActivities = count($activityRows);

            foreach ($activityRows as $activity) {
                if (
                    empty(trim((string) (
                        $activity['summary'] ?? ''
                    )))
                    && empty(trim((string) (
                        $activity['description'] ?? ''
                    )))
                ) {
                    $activityIssues[] =
                        ($activity['title']
                            ?? 'Kegiatan')
                        . ': ringkasan atau deskripsi kosong.';
                }

                if (empty(trim((string) (
                    $activity[
                        'documentation_file'
                    ] ?? ''
                )))) {
                    $activityIssues[] =
                        ($activity['title']
                            ?? 'Kegiatan')
                        . ': gambar utama belum tersedia.';
                }
            }
        } catch (\Throwable $exception) {
            $activityIssues[] =
                'Data kegiatan belum dapat diaudit.';
        }

        $settingChecks = [
            'seo_title' => 'Judul SEO default',
            'seo_description' =>
                'Deskripsi SEO default',
            'seo_og_image' =>
                'Gambar berbagi sosial',
            'seo_og_image_alt' =>
                'Alt gambar berbagi sosial',
        ];

        $settingIssues = [];

        foreach ($settingChecks as $key => $label) {
            if (empty(trim((string) site_setting(
                $key,
                ''
            )))) {
                $settingIssues[] =
                    $label . ' belum diisi.';
            }
        }

        $allIssues = array_merge(
            $settingIssues,
            $pageIssues,
            $programIssues,
            $activityIssues
        );

        $maximumScore = 100;
        $deduction = min(
            60,
            count($allIssues) * 4
        );

        $baseUrl = (string) config('App')->baseURL;
        $isLocalBaseUrl = preg_match(
            '/localhost|127\.0\.0\.1/i',
            $baseUrl
        ) === 1;

        if ($isLocalBaseUrl) {
            $allIssues[] =
                'Base URL masih menggunakan alamat lokal.';
            $deduction += 15;
        }

        return [
            'score' => max(
                0,
                $maximumScore - $deduction
            ),
            'sitemap_total' => count($entries),
            'source_counts' => $sourceCounts,
            'page_count' => $pageCount,
            'program_count' => $publishedPrograms,
            'activity_count' => $publicActivities,
            'issues' => array_slice(
                array_values(array_unique($allIssues)),
                0,
                25
            ),
            'issue_count' => count(array_unique(
                $allIssues
            )),
            'base_url' => $baseUrl,
            'local_base_url' => $isLocalBaseUrl,
            'verification' => [
                'google' => trim((string) site_setting(
                    'seo_google_verification',
                    ''
                )) !== '',
                'bing' => trim((string) site_setting(
                    'seo_bing_verification',
                    ''
                )) !== '',
            ],
        ];
    }

    public function robotsText(): string
    {
        $disallowed = [
            '/login',
            '/logout',
            '/dashboard',
            '/users',
            '/members',
            '/structures',
            '/meetings',
            '/attendances',
            '/cash',
            '/activities',
            '/messages',
            '/reports',
            '/exports',
            '/imports',
            '/publications',
            '/content-studio',
            '/programs',
            '/settings',
            '/website',
        ];

        $lines = [
            'User-agent: *',
            'Allow: /',
        ];

        foreach ($disallowed as $path) {
            $lines[] = 'Disallow: ' . $path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: ' . base_url(
            'sitemap.xml'
        );

        return implode("\n", $lines) . "\n";
    }

    private function canonicalUrl(
        string $provided = ''
    ): string {
        $url = trim($provided);

        if ($url === '') {
            $url = current_url();
        }

        $parts = parse_url($url);

        if (!is_array($parts)) {
            return $url;
        }

        $scheme = $parts['scheme'] ?? null;
        $host = $parts['host'] ?? null;
        $port = isset($parts['port'])
            ? ':' . $parts['port']
            : '';
        $path = $parts['path'] ?? '/';

        if ($scheme !== null && $host !== null) {
            return $scheme
                . '://'
                . $host
                . $port
                . $path;
        }

        return base_url(
            ltrim($path, '/')
        );
    }

    private function contextImage(
        string $activePage,
        ?array $activity,
        ?array $program,
        string $explicitImage
    ): string {
        if (trim($explicitImage) !== '') {
            return $this->absoluteAssetUrl(
                $explicitImage
            );
        }

        if (
            $activePage === 'activity_detail'
            && $activity !== null
            && !empty(
                $activity['documentation_file']
            )
        ) {
            return base_url(
                'uploads/activities/'
                . basename((string) $activity[
                    'documentation_file'
                ])
            );
        }

        if (
            $activePage === 'program_detail'
            && $program !== null
            && !empty($program['cover_image'])
        ) {
            return $this->absoluteAssetUrl(
                (string) $program['cover_image']
            );
        }

        return site_asset_url(
            'seo_og_image',
            'assets/img/logo-rw01.png'
        );
    }

    private function absoluteAssetUrl(
        string $value
    ): string {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return base_url(ltrim($value, '/'));
    }

    private function pageType(
        string $activePage
    ): string {
        return match ($activePage) {
            'programs',
            'activities',
            'officials' => 'CollectionPage',

            'contact' => 'ContactPage',
            'profile' => 'AboutPage',
            'activity_detail' => 'Article',
            default => 'WebPage',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function organizationSchema(
        string $organizationId
    ): array {
        $sameAs = array_values(array_filter([
            site_setting('instagram_url', ''),
            site_setting('tiktok_url', ''),
            site_setting('youtube_url', ''),
            site_setting('facebook_url', ''),
        ]));

        $schema = [
            '@type' => 'Organization',
            '@id' => $organizationId,
            'url' => base_url('/'),
            'name' => site_setting(
                'organization_name',
                'GARDA 01'
            ),
            'alternateName' => site_setting(
                'organization_full_name',
                'Generasi Aktif Randugarut'
            ),
            'legalName' => site_setting(
                'organization_legal_name',
                'Karang Taruna RW 01 Kelurahan Randugarut'
            ),
            'description' => site_setting(
                'organization_description',
                site_setting(
                    'seo_description',
                    'Website resmi GARDA 01.'
                )
            ),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => site_asset_url(
                    'site_logo',
                    'assets/img/logo-rw01.png'
                ),
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => site_setting(
                    'contact_address',
                    'RW 01 Kelurahan Randugarut'
                ),
                'addressLocality' => site_setting(
                    'contact_city',
                    'Kota Semarang'
                ),
                'addressRegion' => site_setting(
                    'contact_province',
                    'Jawa Tengah'
                ),
                'addressCountry' => 'ID',
            ],
        ];

        $email = trim((string) site_setting(
            'contact_email',
            ''
        ));

        $telephone = trim((string) site_setting(
            'contact_whatsapp',
            ''
        ));

        if ($email !== '') {
            $schema['email'] = $email;
        }

        if ($telephone !== '') {
            $schema['telephone'] = $telephone;
        }

        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function breadcrumbSchema(
        string $activePage,
        ?array $activity,
        ?array $program,
        string $canonical
    ): ?array {
        $items = [
            [
                'name' => 'Beranda',
                'url' => base_url('/'),
            ],
        ];

        switch ($activePage) {
            case 'home':
                return null;

            case 'profile':
                $items[] = [
                    'name' => 'Profil',
                    'url' => base_url('profil'),
                ];
                break;

            case 'programs':
                $items[] = [
                    'name' => 'Program',
                    'url' => base_url('program'),
                ];
                break;

            case 'program_detail':
                $items[] = [
                    'name' => 'Program',
                    'url' => base_url('program'),
                ];
                $items[] = [
                    'name' => (string) (
                        $program['name']
                        ?? 'Detail Program'
                    ),
                    'url' => $canonical,
                ];
                break;

            case 'activities':
                $items[] = [
                    'name' => 'Kegiatan',
                    'url' => base_url('kegiatan'),
                ];
                break;

            case 'activity_detail':
                $items[] = [
                    'name' => 'Kegiatan',
                    'url' => base_url('kegiatan'),
                ];
                $items[] = [
                    'name' => (string) (
                        $activity['title']
                        ?? 'Detail Kegiatan'
                    ),
                    'url' => $canonical,
                ];
                break;

            case 'officials':
                $items[] = [
                    'name' => 'Pengurus',
                    'url' => base_url('pengurus'),
                ];
                break;

            case 'contact':
                $items[] = [
                    'name' => 'Kontak',
                    'url' => base_url('kontak'),
                ];
                break;

            default:
                return null;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical . '#breadcrumb',
            'itemListElement' => array_map(
                static function (
                    array $item,
                    int $index
                ): array {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $item['name'],
                        'item' => $item['url'],
                    ];
                },
                $items,
                array_keys($items)
            ),
        ];
    }

    /**
     * @param array<string, mixed> $activity
     * @return array<string, mixed>
     */
    private function activityArticleSchema(
        array $activity,
        string $canonical,
        string $image,
        string $description,
        string $organizationId
    ): array {
        $schema = [
            '@type' => 'Article',
            '@id' => $canonical . '#article',
            'mainEntityOfPage' => [
                '@id' => $canonical . '#webpage',
            ],
            'headline' => (string) (
                $activity['title']
                ?? 'Kegiatan GARDA 01'
            ),
            'description' => $description,
            'author' => [
                '@id' => $organizationId,
            ],
            'publisher' => [
                '@id' => $organizationId,
            ],
            'datePublished' => $this->isoDate(
                $activity['published_at']
                ?? $activity['created_at']
                ?? $activity['activity_date']
                ?? null
            ),
            'dateModified' => $this->isoDate(
                $activity['updated_at']
                ?? $activity['published_at']
                ?? null
            ),
            'inLanguage' => 'id-ID',
        ];

        if ($image !== '') {
            $schema['image'] = [$image];
        }

        if (!empty($activity['program_name'])) {
            $schema['articleSection'] =
                $activity['program_name'];
        }

        if (!empty($activity['location'])) {
            $schema['contentLocation'] = [
                '@type' => 'Place',
                'name' => $activity['location'],
            ];
        }

        return array_filter(
            $schema,
            static fn ($value): bool =>
                $value !== null
                && $value !== ''
        );
    }

    /**
     * @param array<string, mixed> $program
     * @return array<string, mixed>
     */
    private function programSchema(
        array $program,
        string $canonical,
        string $image,
        string $organizationId
    ): array {
        $schema = [
            '@type' => 'CreativeWork',
            '@id' => $canonical . '#program',
            'url' => $canonical,
            'name' => (string) (
                $program['name']
                ?? 'Program GARDA 01'
            ),
            'alternateName' => (string) (
                $program['label'] ?? ''
            ),
            'description' => (string) (
                $program['short_description']
                ?? $program['description']
                ?? ''
            ),
            'creator' => [
                '@id' => $organizationId,
            ],
            'publisher' => [
                '@id' => $organizationId,
            ],
            'inLanguage' => 'id-ID',
        ];

        if ($image !== '') {
            $schema['image'] = $image;
        }

        return array_filter(
            $schema,
            static fn ($value): bool =>
                $value !== null
                && $value !== ''
        );
    }

    /**
     * @param list<array<string, mixed>> $programs
     * @return array<string, mixed>|null
     */
    private function programItemList(
        array $programs,
        string $canonical
    ): ?array {
        $elements = [];

        foreach ($programs as $index => $program) {
            if (empty($program['slug'])) {
                continue;
            }

            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => (string) (
                    $program['name']
                    ?? 'Program'
                ),
                'url' => base_url(
                    'program/' . $program['slug']
                ),
            ];
        }

        if ($elements === []) {
            return null;
        }

        return [
            '@type' => 'ItemList',
            '@id' => $canonical . '#program-list',
            'name' => 'Program GARDA 01',
            'itemListElement' => $elements,
        ];
    }

    /**
     * @param list<array<string, mixed>> $activities
     * @return array<string, mixed>|null
     */
    private function activityItemList(
        array $activities,
        string $canonical
    ): ?array {
        $elements = [];

        foreach (
            array_values($activities) as
            $index => $activity
        ) {
            if (empty($activity['id'])) {
                continue;
            }

            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => (string) (
                    $activity['title']
                    ?? 'Kegiatan'
                ),
                'url' => base_url(
                    'kegiatan/' . $activity['id']
                ),
            ];
        }

        if ($elements === []) {
            return null;
        }

        return [
            '@type' => 'ItemList',
            '@id' => $canonical . '#activity-list',
            'name' => 'Kegiatan GARDA 01',
            'itemListElement' => $elements,
        ];
    }

    private function dateValue($value): ?string
    {
        $timestamp = strtotime((string) $value);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function isoDate($value): ?string
    {
        $timestamp = strtotime((string) $value);

        if ($timestamp === false) {
            return null;
        }

        return date(DATE_ATOM, $timestamp);
    }
}
