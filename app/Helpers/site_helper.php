<?php

use App\Models\SiteSettingModel;

if (!function_exists('site_settings')) {
    function site_settings(): array
    {
        static $settings = null;

        if ($settings !== null) {
            return $settings;
        }

        try {
            $database = db_connect();

            if (!$database->tableExists('site_settings')) {
                return $settings = [];
            }

            $model = new SiteSettingModel();

            return $settings =
                $model->getSettingsArray(true);
        } catch (\Throwable $exception) {
            return $settings = [];
        }
    }
}

if (!function_exists('site_setting')) {
    function site_setting(
        string $key,
        ?string $default = ''
    ): ?string {
        $settings = site_settings();

        $value = $settings[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('site_asset_url')) {
    function site_asset_url(
        string $key,
        string $default
    ): string {
        $value = site_setting($key, $default);

        if (
            preg_match(
                '#^https?://#i',
                (string) $value
            )
        ) {
            return (string) $value;
        }

        return base_url(
            ltrim((string) $value, '/')
        );
    }
}

if (!function_exists('site_whatsapp_url')) {
    function site_whatsapp_url(
        ?string $message = null
    ): ?string {
        $number = site_setting(
            'contact_whatsapp',
            ''
        );

        $number = preg_replace(
            '/[^0-9]/',
            '',
            (string) $number
        );

        if ($number === '') {
            return null;
        }

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        $url = 'https://wa.me/' . $number;

        if ($message !== null && $message !== '') {
            $url .= '?text=' . rawurlencode($message);
        }

        return $url;
    }
}