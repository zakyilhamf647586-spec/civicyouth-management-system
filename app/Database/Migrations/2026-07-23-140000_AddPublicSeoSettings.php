<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddPublicSeoSettings extends Migration
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $settings = [
        'seo_og_image_alt' => [
            'value' =>
                'GARDA 01 — Generasi Aktif Randugarut',
            'type' => 'text',
            'label' => 'Alt Gambar Berbagi Sosial',
            'description' =>
                'Deskripsi singkat gambar saat website dibagikan.',
            'sort_order' => 5,
        ],
        'seo_twitter_handle' => [
            'value' => '',
            'type' => 'text',
            'label' => 'Akun X / Twitter',
            'description' =>
                'Opsional. Contoh: @garda01randugarut.',
            'sort_order' => 6,
        ],
        'seo_google_verification' => [
            'value' => '',
            'type' => 'text',
            'label' => 'Kode Verifikasi Google',
            'description' =>
                'Isi hanya nilai content dari meta verifikasi.',
            'sort_order' => 7,
        ],
        'seo_bing_verification' => [
            'value' => '',
            'type' => 'text',
            'label' => 'Kode Verifikasi Bing',
            'description' =>
                'Isi hanya nilai content dari meta msvalidate.01.',
            'sort_order' => 8,
        ],
    ];

    public function up()
    {
        if (!$this->db->tableExists('site_settings')) {
            throw new RuntimeException(
                'Tabel site_settings belum tersedia.'
            );
        }

        $builder = $this->db->table('site_settings');
        $now = date('Y-m-d H:i:s');

        foreach ($this->settings as $key => $setting) {
            $existing = $builder
                ->where('setting_key', $key)
                ->get()
                ->getRowArray();

            if ($existing) {
                continue;
            }

            $builder->insert([
                'setting_key' => $key,
                'setting_value' => $setting['value'],
                'setting_group' => 'seo',
                'setting_type' => $setting['type'],
                'label' => $setting['label'],
                'description' =>
                    $setting['description'],
                'sort_order' =>
                    (int) $setting['sort_order'],
                'is_public' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $this->db
            ->table('site_settings')
            ->whereIn(
                'setting_key',
                array_keys($this->settings)
            )
            ->delete();
    }
}
