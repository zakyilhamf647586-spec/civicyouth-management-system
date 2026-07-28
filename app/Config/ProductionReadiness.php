<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ProductionReadiness extends BaseConfig
{
    public string $minimumPhpVersion = '8.2.0';

    /** @var list<string> */
    public array $requiredExtensions = [
        'fileinfo',
        'gd',
        'intl',
        'json',
        'mbstring',
        'mysqli',
        'openssl',
        'zip',
    ];

    /** @var list<string> */
    public array $recommendedExtensions = [
        'curl',
        'opcache',
    ];

    /** @var list<string> */
    public array $criticalTables = [
        'roles',
        'users',
        'members',
        'activities',
        'programs',
        'public_pages',
        'public_page_sections',
        'site_settings',
    ];

    /** @var list<string> */
    public array $dangerousUploadExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8',
        'phtml', 'phar', 'cgi', 'pl', 'py', 'sh',
        'exe', 'bat', 'cmd', 'com', 'msi', 'jar',
    ];

    /** @var array<string, array{title:string,description:string}> */
    public array $manualChecks = [
        'dns' => [
            'title' => 'DNS mengarah ke server production',
            'description' => 'Domain utama dan www, bila digunakan, sudah mengarah ke server yang benar.',
        ],
        'ssl' => [
            'title' => 'Sertifikat SSL valid',
            'description' => 'HTTPS aktif dan redirect HTTP ke HTTPS bekerja.',
        ],
        'document_root' => [
            'title' => 'Document root hanya menunjuk ke /public',
            'description' => 'Folder app, vendor, writable, .env, dan source internal tidak dapat dibuka dari browser.',
        ],
        'contact_delivery' => [
            'title' => 'Form kontak diuji pada domain asli',
            'description' => 'Pesan masuk tercatat dan kanal notifikasi organisasi berfungsi.',
        ],
        'upload_download' => [
            'title' => 'Upload, impor, dan ekspor diuji',
            'description' => 'Foto, Excel, CSV, dan laporan berhasil diproses pada hosting production.',
        ],
        'browser_mobile' => [
            'title' => 'Smoke test desktop dan mobile selesai',
            'description' => 'Website publik dan Portal telah diperiksa pada desktop, tablet, dan ponsel.',
        ],
        'recovery' => [
            'title' => 'Backup pra-deploy dapat dipulihkan',
            'description' => 'Database, source, .env, dan upload pengguna memiliki salinan privat.',
        ],
    ];
}
