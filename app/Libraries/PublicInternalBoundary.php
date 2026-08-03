<?php

namespace App\Libraries;

final class PublicInternalBoundary
{
    /**
     * Exact paths that belong to authentication or the internal Portal.
     *
     * @var list<string>
     */
    private const RESTRICTED_EXACT_PATHS = [
        '/login',
        '/en/login',
        '/logout',
    ];

    /**
     * Internal route prefixes that must never be published in public menus.
     *
     * @var list<string>
     */
    private const RESTRICTED_PREFIXES = [
        '/account',
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
        '/website',
        '/system',
        '/publications',
        '/content-studio',
        '/programs',
        '/settings',
    ];

    /** @var list<string> */
    private const RESTRICTED_ITEM_KEYS = [
        'portal',
        'portal-pengurus',
        'portal-internal',
        'login',
        'masuk',
        'admin',
        'internal',
    ];

    /** @var list<string> */
    private const RESTRICTED_LABELS = [
        'portal pengurus',
        'portal internal',
        'team portal',
        'internal portal',
        'login',
        'masuk portal',
        'masuk ke portal',
    ];

    /**
     * @param array<string, mixed> $item
     */
    public static function isRestrictedItem(array $item): bool
    {
        $itemKey = self::normalizeToken(
            (string) ($item['item_key'] ?? '')
        );

        if (in_array($itemKey, self::RESTRICTED_ITEM_KEYS, true)) {
            return true;
        }

        $style = self::normalizeToken(
            (string) ($item['style'] ?? 'default')
        );

        if ($style === 'portal') {
            return true;
        }

        foreach (['label', 'label_en'] as $labelField) {
            $label = self::normalizeLabel(
                (string) ($item[$labelField] ?? '')
            );

            if (in_array($label, self::RESTRICTED_LABELS, true)) {
                return true;
            }
        }

        return self::isRestrictedUrl(
            (string) ($item['url'] ?? '')
        );
    }

    public static function isRestrictedUrl(string $url): bool
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES));

        if ($url === '' || str_starts_with($url, '#')) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (is_string($host) && $host !== '') {
            $host = strtolower($host);

            if (
                str_starts_with($host, 'portal.')
                || str_starts_with($host, 'admin.')
                || str_starts_with($host, 'internal.')
            ) {
                return true;
            }
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return false;
        }

        $path = '/' . ltrim(rawurldecode($path), '/');
        $path = rtrim($path, '/');
        $path = $path === '' ? '/' : strtolower($path);

        if (in_array($path, self::RESTRICTED_EXACT_PATHS, true)) {
            return true;
        }

        foreach (self::RESTRICTED_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    public static function filterNavigationItems(array $items): array
    {
        return array_values(array_filter(
            $items,
            static fn (array $item): bool => !self::isRestrictedItem($item)
        ));
    }

    /** @return list<string> */
    public static function restrictedExactPaths(): array
    {
        return self::RESTRICTED_EXACT_PATHS;
    }

    /** @return list<string> */
    public static function restrictedPrefixes(): array
    {
        return self::RESTRICTED_PREFIXES;
    }

    private static function normalizeToken(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_-]+/', '-', $value) ?: '';

        return trim($value, '-_');
    }

    private static function normalizeLabel(string $value): string
    {
        $value = strtolower(trim(strip_tags($value)));
        $value = preg_replace('/\s+/', ' ', $value) ?: '';

        return $value;
    }
}
