<?php

if (!function_exists('public_cms_value')) {
    function public_cms_value(
        ?array $page,
        string $sectionKey,
        string $fieldKey,
        ?string $fallback = ''
    ): string {
        $value = $page['sections'][$sectionKey]
            ['content'][$fieldKey] ?? null;

        if ($value === null || $value === '') {
            return (string) $fallback;
        }

        return (string) $value;
    }
}

if (!function_exists('public_cms_section_enabled')) {
    function public_cms_section_enabled(
        ?array $page,
        string $sectionKey,
        bool $fallback = true
    ): bool {
        if (
            !isset($page['sections'][$sectionKey])
        ) {
            return $fallback;
        }

        return (bool) (
            $page['sections'][$sectionKey]['enabled']
            ?? $fallback
        );
    }
}

if (!function_exists('public_cms_url')) {
    function public_cms_url(
        ?array $page,
        string $sectionKey,
        string $fieldKey,
        string $fallback
    ): string {
        $value = public_cms_value(
            $page,
            $sectionKey,
            $fieldKey,
            $fallback
        );

        if (
            preg_match('#^https?://#i', $value)
        ) {
            return $value;
        }

        return base_url(
            ltrim($value, '/')
        );
    }
}

if (!function_exists('public_cms_lines')) {
    /**
     * @return list<string>
     */
    function public_cms_lines(
        ?array $page,
        string $sectionKey,
        string $fieldKey,
        string $fallback = ''
    ): array {
        $value = public_cms_value(
            $page,
            $sectionKey,
            $fieldKey,
            $fallback
        );

        $lines = preg_split(
            '/\R+/',
            trim($value)
        ) ?: [];

        return array_values(array_filter(
            array_map('trim', $lines),
            static fn (string $line): bool =>
                $line !== ''
        ));
    }
}
