<?php

use Config\PublicExperience;

if (!function_exists('public_locale')) {
    function public_locale(): string
    {
        try {
            return service('request')->getLocale() === 'en'
                ? 'en'
                : 'id';
        } catch (\Throwable $exception) {
            return 'id';
        }
    }
}

if (!function_exists('public_t')) {
    /**
     * @param array<string, scalar|null> $replace
     */
    function public_t(
        string $key,
        string $fallback = '',
        array $replace = []
    ): string {
        static $config;

        $config ??= new PublicExperience();
        $locale = public_locale();
        $definition = $config->copy[$key] ?? null;

        $value = is_array($definition)
            ? (string) (
                $definition[$locale]
                ?? $definition['id']
                ?? $fallback
            )
            : $fallback;

        foreach ($replace as $name => $replacement) {
            $value = str_replace(
                '{' . $name . '}',
                (string) $replacement,
                $value
            );
        }

        return $value;
    }
}

if (!function_exists('public_locale_path')) {
    function public_locale_path(
        string $url,
        ?string $locale = null
    ): string {
        $url = trim($url);
        $locale = $locale === 'en' ? 'en' : (
            $locale === 'id' ? 'id' : public_locale()
        );

        if (
            $url === ''
            || str_starts_with($url, '#')
            || str_starts_with($url, 'mailto:')
            || str_starts_with($url, 'tel:')
            || preg_match('#^https?://#i', $url)
        ) {
            return $url;
        }

        $fragment = '';
        $query = '';

        if (str_contains($url, '#')) {
            [$url, $fragmentValue] = explode('#', $url, 2);
            $fragment = '#' . $fragmentValue;
        }

        if (str_contains($url, '?')) {
            [$url, $queryValue] = explode('?', $url, 2);
            $query = '?' . $queryValue;
        }

        $path = '/' . ltrim($url, '/');
        $path = preg_replace('#^/index\.php(?=/|$)#', '', $path)
            ?: '/';

        $config = new PublicExperience();
        $direct = $config->localizedPaths;
        $reverse = array_flip($direct);

        if ($locale === 'en') {
            if (isset($direct[$path])) {
                $path = $direct[$path];
            } elseif (preg_match(
                '#^/program/([^/]+)$#',
                $path,
                $matches
            )) {
                $path = '/en/programs/' . $matches[1];
            } elseif (preg_match(
                '#^/kegiatan/([0-9]+)$#',
                $path,
                $matches
            )) {
                $path = '/en/activities/' . $matches[1];
            }
        } else {
            if (isset($reverse[$path])) {
                $path = $reverse[$path];
            } elseif (preg_match(
                '#^/en/programs/([^/]+)$#',
                $path,
                $matches
            )) {
                $path = '/program/' . $matches[1];
            } elseif (preg_match(
                '#^/en/activities/([0-9]+)$#',
                $path,
                $matches
            )) {
                $path = '/kegiatan/' . $matches[1];
            }
        }

        return $path . $query . $fragment;
    }
}

if (!function_exists('public_url')) {
    function public_url(
        string $path = '/',
        ?string $locale = null
    ): string {
        $resolved = public_locale_path($path, $locale);

        if (
            str_starts_with($resolved, '#')
            || str_starts_with($resolved, 'mailto:')
            || str_starts_with($resolved, 'tel:')
            || preg_match('#^https?://#i', $resolved)
        ) {
            return $resolved;
        }

        return base_url(ltrim($resolved, '/'));
    }
}

if (!function_exists('public_language_url')) {
    function public_language_url(string $locale): string
    {
        try {
            $request = service('request');
            $path = '/' . ltrim(
                $request->getUri()->getPath(),
                '/'
            );

            $path = public_locale_path($path, $locale);
            $query = $request->getUri()->getQuery();

            if ($query !== '') {
                $path .= '?' . $query;
            }

            return base_url(ltrim($path, '/'));
        } catch (\Throwable $exception) {
            return public_url('/', $locale);
        }
    }
}

if (!function_exists('public_translate_text')) {
    function public_translate_text(string $text): string
    {
        if (public_locale() !== 'en' || trim($text) === '') {
            return $text;
        }

        static $dictionary;
        static $normalizedDictionary;

        if ($dictionary === null) {
            $config = new PublicExperience();
            $dictionary = $config->englishDictionary;

            foreach ($config->copy as $definition) {
                if (
                    !is_array($definition)
                    || empty($definition['id'])
                    || !isset($definition['en'])
                ) {
                    continue;
                }

                $dictionary[(string) $definition['id']] =
                    (string) $definition['en'];
            }

            $normalizedDictionary = [];

            foreach ($dictionary as $source => $translation) {
                $decodedSource = html_entity_decode(
                    (string) $source,
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );
                $normalizedSource = preg_replace(
                    '/\s+/u',
                    ' ',
                    trim($decodedSource)
                );

                if (
                    is_string($normalizedSource)
                    && $normalizedSource !== ''
                    && !isset(
                        $normalizedDictionary[$normalizedSource]
                    )
                ) {
                    $normalizedDictionary[$normalizedSource] =
                        (string) $translation;
                }
            }
        }

        $leadingLength = strlen($text) - strlen(ltrim($text));
        $trailingLength = strlen($text) - strlen(rtrim($text));
        $leading = $leadingLength > 0
            ? substr($text, 0, $leadingLength)
            : '';
        $trailing = $trailingLength > 0
            ? substr($text, -$trailingLength)
            : '';
        $value = trim($text);

        if (isset($dictionary[$value])) {
            return $leading
                . $dictionary[$value]
                . $trailing;
        }

        $decodedValue = html_entity_decode(
            $value,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        if (
            $decodedValue !== $value
            && isset($dictionary[$decodedValue])
        ) {
            return $leading
                . htmlspecialchars(
                    $dictionary[$decodedValue],
                    ENT_QUOTES
                    | ENT_SUBSTITUTE
                    | ENT_HTML5,
                    'UTF-8'
                )
                . $trailing;
        }

        $normalizedValue = preg_replace(
            '/\s+/u',
            ' ',
            trim($decodedValue)
        );

        if (
            is_string($normalizedValue)
            && isset($normalizedDictionary[$normalizedValue])
        ) {
            $translation = $normalizedDictionary[
                $normalizedValue
            ];

            if ($decodedValue !== $value) {
                $translation = htmlspecialchars(
                    $translation,
                    ENT_QUOTES
                    | ENT_SUBSTITUTE
                    | ENT_HTML5,
                    'UTF-8'
                );
            }

            return $leading
                . $translation
                . $trailing;
        }

        $translatedLines = [];
        $lines = preg_split('/\R/u', $value);

        if (is_array($lines) && count($lines) > 1) {
            foreach ($lines as $line) {
                $trimmed = trim($line);
                $translatedLines[] = $dictionary[$trimmed]
                    ?? $line;
            }

            return $leading
                . implode("\n", $translatedLines)
                . $trailing;
        }

        return $text;
    }
}

if (!function_exists('public_translate_html')) {
    function public_translate_html(string $html): string
    {
        if (public_locale() !== 'en' || $html === '') {
            return $html;
        }

        $protected = [];

        $html = preg_replace_callback(
            '#<(script|style)\b[^>]*>.*?</\1>#isu',
            static function (array $matches) use (&$protected): string {
                $token = '___G01_PROTECTED_'
                    . count($protected)
                    . '___';
                $protected[$token] = $matches[0];

                return $token;
            },
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/>([^<>]+)</u',
            static fn (array $matches): string =>
                '>' . public_translate_text($matches[1]) . '<',
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/\b(aria-label|placeholder|title)="([^"]*)"/u',
            static fn (array $matches): string =>
                $matches[1]
                . '="'
                . public_translate_text($matches[2])
                . '"',
            $html
        ) ?? $html;

        if ($protected !== []) {
            $html = strtr($html, $protected);
        }

        return $html;
    }
}

if (!function_exists('public_localize_bundle')) {
    /**
     * @param mixed $value
     * @return mixed
     */
    function public_localize_bundle($value)
    {
        if (public_locale() !== 'en') {
            return $value;
        }

        if (is_array($value)) {
            $localized = $value;

            foreach ($value as $key => $item) {
                if (
                    !is_string($key)
                    || !str_ends_with($key, '_en')
                ) {
                    continue;
                }

                $baseKey = substr($key, 0, -3);

                if (
                    $baseKey !== ''
                    && (
                        (is_string($item) && trim($item) !== '')
                        || (is_array($item) && $item !== [])
                    )
                ) {
                    $localized[$baseKey] = $item;
                }

                unset($localized[$key]);
            }

            foreach ($localized as $key => $item) {
                if (
                    is_string($item)
                    && str_ends_with((string) $key, 'url')
                ) {
                    $localized[$key] = public_locale_path(
                        $item,
                        'en'
                    );
                } else {
                    $localized[$key] =
                        public_localize_bundle($item);
                }
            }

            return $localized;
        }

        return is_string($value)
            ? public_translate_text($value)
            : $value;
    }
}
