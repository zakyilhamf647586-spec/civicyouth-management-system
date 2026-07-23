<?php

use App\Models\WebsiteNavigationMenuModel;
use Config\WebsiteNavigation;

if (!function_exists(
    'website_navigation_preview_active'
)) {
    function website_navigation_preview_active(): bool
    {
        try {
            return (string) service('request')->getGet(
                'nav_preview'
            ) === '1'
                && (bool) session()->get('isLoggedIn')
                && function_exists('auth_can')
                && auth_can(
                    'website.navigation.preview'
                );
        } catch (\Throwable $exception) {
            return false;
        }
    }
}

if (!function_exists('website_navigation_items')) {
    /**
     * @return list<array<string, mixed>>
     */
    function website_navigation_items(
        string $menuKey
    ): array {
        static $cache = [];

        $mode = website_navigation_preview_active()
            ? 'draft'
            : 'published';

        $cacheKey = $menuKey . ':' . $mode;

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $config = new WebsiteNavigation();
        $defaults = $config->menus[$menuKey]['items']
            ?? [];

        $items = is_array($defaults)
            ? $defaults
            : [];

        try {
            $db = db_connect();

            if ($db->tableExists(
                'website_navigation_menus'
            )) {
                $storedItems =
                    (new WebsiteNavigationMenuModel())
                        ->items($menuKey, $mode);

                if ($storedItems !== []) {
                    $items = $storedItems;
                }
            }
        } catch (\Throwable $exception) {
            log_message(
                'warning',
                'Navigation fallback used for '
                . $menuKey
                . ': '
                . $exception->getMessage()
            );
        }

        $normalized = [];

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = trim((string) (
                $item['label'] ?? ''
            ));

            $url = trim((string) (
                $item['url'] ?? ''
            ));

            if (
                $label === ''
                || $url === ''
                || empty($item['enabled'])
            ) {
                continue;
            }

            $activePages = $item['active_pages']
                ?? [];

            if (!is_array($activePages)) {
                $activePages = [];
            }

            $normalized[] = [
                'item_key' => trim((string) (
                    $item['item_key']
                    ?? 'item-' . ($index + 1)
                )),
                'label' => $label,
                'url' => $url,
                'active_pages' =>
                    array_values(array_filter(
                        array_map(
                            static fn ($value): string =>
                                trim((string) $value),
                            $activePages
                        ),
                        static fn (string $value): bool =>
                            $value !== ''
                    )),
                'target' => (
                    $item['target'] ?? 'self'
                ) === 'blank'
                    ? 'blank'
                    : 'self',
                'style' => (
                    $item['style'] ?? 'default'
                ) === 'portal'
                    ? 'portal'
                    : 'default',
                'enabled' => true,
            ];
        }

        $cache[$cacheKey] = $normalized;

        return $normalized;
    }
}

if (!function_exists('website_navigation_url')) {
    function website_navigation_url(
        string $url,
        bool $preservePreview = true
    ): string {
        $url = trim($url);

        if ($url === '') {
            return '#';
        }

        if (
            str_starts_with($url, '#')
            || preg_match('#^https?://#i', $url)
        ) {
            return $url;
        }

        if (!str_starts_with($url, '/')) {
            $url = '/' . ltrim($url, '/');
        }

        $resolved = base_url($url);

        if (
            $preservePreview
            && website_navigation_preview_active()
        ) {
            $resolved .= str_contains($resolved, '?')
                ? '&nav_preview=1'
                : '?nav_preview=1';
        }

        return $resolved;
    }
}

if (!function_exists(
    'website_navigation_item_active'
)) {
    /**
     * @param array<string, mixed> $item
     */
    function website_navigation_item_active(
        array $item,
        string $activePage
    ): bool {
        $pages = $item['active_pages'] ?? [];

        return is_array($pages)
            && in_array($activePage, $pages, true);
    }
}
