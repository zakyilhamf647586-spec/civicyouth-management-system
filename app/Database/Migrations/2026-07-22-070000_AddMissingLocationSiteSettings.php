<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddMissingLocationSiteSettings extends Migration
{
    private const MARKER = '[managed:phase1-location]';

    public function up()
    {
        if (!$this->db->tableExists('site_settings')) {
            throw new RuntimeException('Tabel site_settings belum tersedia.');
        }

        $settings = [
            [
                'setting_key' => 'contact_location_description',
                'setting_value' => 'Wilayah RW 01 Kelurahan Randugarut, Kecamatan Tugu, Kota Semarang.',
                'setting_group' => 'contact',
                'setting_type' => 'textarea',
                'label' => 'Deskripsi Lokasi',
                'description' => self::MARKER,
                'sort_order' => 8,
                'is_public' => 1,
            ],
            [
                'setting_key' => 'contact_maps_url',
                'setting_value' => '',
                'setting_group' => 'contact',
                'setting_type' => 'url',
                'label' => 'URL Google Maps',
                'description' => self::MARKER,
                'sort_order' => 9,
                'is_public' => 1,
            ],
        ];

        $builder = $this->db->table('site_settings');
        $now = date('Y-m-d H:i:s');

        $this->db->transBegin();

        try {
            foreach ($settings as $setting) {
                $exists = $builder
                    ->where('setting_key', $setting['setting_key'])
                    ->countAllResults() > 0;

                if ($exists) {
                    continue;
                }

                $setting['created_at'] = $now;
                $setting['updated_at'] = $now;

                if ($builder->insert($setting) === false) {
                    throw new RuntimeException(
                        'Setting lokasi gagal ditambahkan.'
                    );
                }
            }

            if ($this->db->transCommit() === false) {
                throw new RuntimeException(
                    'Migration setting lokasi gagal diselesaikan.'
                );
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $this->db->table('site_settings')
            ->whereIn('setting_key', [
                'contact_location_description',
                'contact_maps_url',
            ])
            ->where('description', self::MARKER)
            ->delete();
    }
}
