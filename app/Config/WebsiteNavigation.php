<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WebsiteNavigation extends BaseConfig
{
    public int $maximumItems = 20;

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $menus = [
        'header' => [
            'name' => 'Navigasi Utama',
            'description' =>
                'Menu yang tampil pada navbar website publik.',
            'preview_route' => '/',
            'items' => [
                [
                    'item_key' => 'home',
                    'label' => 'Beranda',
                    'url' => '/',
                    'active_pages' => ['home'],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'profile',
                    'label' => 'Tentang',
                    'url' => '/profil',
                    'active_pages' => ['profile'],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'programs',
                    'label' => 'Program',
                    'url' => '/program',
                    'active_pages' => [
                        'programs',
                        'program_detail',
                    ],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'activities',
                    'label' => 'Kegiatan',
                    'url' => '/kegiatan',
                    'active_pages' => [
                        'activities',
                        'activity_detail',
                    ],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'officials',
                    'label' => 'Pengurus',
                    'url' => '/pengurus',
                    'active_pages' => ['officials'],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'contact',
                    'label' => 'Kontak',
                    'url' => '/kontak',
                    'active_pages' => ['contact'],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'portal',
                    'label' => 'Portal Pengurus',
                    'url' => '/login',
                    'active_pages' => [],
                    'target' => 'self',
                    'style' => 'portal',
                    'enabled' => true,
                ],
            ],
        ],

        'footer' => [
            'name' => 'Navigasi Footer',
            'description' =>
                'Daftar tautan pada kolom navigasi footer.',
            'preview_route' => '/',
            'items' => [
                [
                    'item_key' => 'home',
                    'label' => 'Beranda',
                    'url' => '/',
                    'active_pages' => ['home'],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'profile',
                    'label' => 'Tentang GARDA 01',
                    'url' => '/profil',
                    'active_pages' => ['profile'],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'programs',
                    'label' => 'Pilar Program',
                    'url' => '/program',
                    'active_pages' => [
                        'programs',
                        'program_detail',
                    ],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'activities',
                    'label' => 'Kegiatan',
                    'url' => '/kegiatan',
                    'active_pages' => [
                        'activities',
                        'activity_detail',
                    ],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'officials',
                    'label' => 'Pengurus',
                    'url' => '/pengurus',
                    'active_pages' => ['officials'],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
                [
                    'item_key' => 'contact',
                    'label' => 'Kontak & Kolaborasi',
                    'url' => '/kontak',
                    'active_pages' => ['contact'],
                    'target' => 'self',
                    'style' => 'default',
                    'enabled' => true,
                ],
            ],
        ],
    ];
}
