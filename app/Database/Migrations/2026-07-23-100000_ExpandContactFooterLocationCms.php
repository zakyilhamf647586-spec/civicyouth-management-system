<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\PublicCms;
use RuntimeException;

class ExpandContactFooterLocationCms extends Migration
{
    /**
     * @var list<string>
     */
    private array $newSections = [
        'channels',
        'collaboration',
        'location',
        'notice',
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $addedFields = [
        'hero' => [
            'values_line',
            'identity_kicker',
            'identity_title',
            'identity_subtitle',
        ],
        'form_intro' => [
            'submit_label',
            'submit_note',
        ],
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $siteSettings = [
        'contact_office_hours' => [
            'value' =>
                'Setiap hari, respons menyesuaikan ketersediaan pengurus.',
            'group' => 'contact',
            'type' => 'text',
            'label' => 'Jam Respons / Operasional',
            'description' =>
                'Informasi waktu layanan atau respons organisasi.',
            'sort_order' => 10,
        ],
        'contact_response_note' => [
            'value' =>
                'Pesan akan ditinjau oleh pengurus dan ditindaklanjuti sesuai kebutuhan.',
            'group' => 'contact',
            'type' => 'textarea',
            'label' => 'Catatan Waktu Respons',
            'description' =>
                'Catatan tambahan mengenai proses respons.',
            'sort_order' => 11,
        ],
        'footer_navigation_heading' => [
            'value' => 'Navigasi',
            'group' => 'footer',
            'type' => 'text',
            'label' => 'Judul Kolom Navigasi',
            'description' =>
                'Judul daftar navigasi pada footer.',
            'sort_order' => 4,
        ],
        'footer_location_heading' => [
            'value' => 'Organisasi & Lokasi',
            'group' => 'footer',
            'type' => 'text',
            'label' => 'Judul Kolom Lokasi',
            'description' =>
                'Judul informasi organisasi dan lokasi.',
            'sort_order' => 5,
        ],
        'footer_contact_heading' => [
            'value' => 'Hubungi Kami',
            'group' => 'footer',
            'type' => 'text',
            'label' => 'Judul Kolom Kontak',
            'description' =>
                'Judul kanal kontak pada footer.',
            'sort_order' => 6,
        ],
        'footer_contact_intro' => [
            'value' =>
                'Sampaikan undangan, gagasan, informasi, atau tawaran kolaborasi melalui kanal resmi GARDA 01.',
            'group' => 'footer',
            'type' => 'textarea',
            'label' => 'Pengantar Kolom Kontak',
            'description' =>
                'Teks pengantar sebelum daftar kanal kontak.',
            'sort_order' => 7,
        ],
        'footer_map_label' => [
            'value' => 'Lokasi Organisasi',
            'group' => 'footer',
            'type' => 'text',
            'label' => 'Label Kartu Maps',
            'description' =>
                'Label kecil pada kartu Google Maps.',
            'sort_order' => 8,
        ],
        'footer_map_action' => [
            'value' => 'Buka di Google Maps',
            'group' => 'footer',
            'type' => 'text',
            'label' => 'Teks Aksi Maps',
            'description' =>
                'Teks aksi membuka lokasi.',
            'sort_order' => 9,
        ],
    ];

    public function up()
    {
        if (
            !$this->db->tableExists('public_pages')
            || !$this->db->tableExists(
                'public_page_sections'
            )
            || !$this->db->tableExists('site_settings')
        ) {
            throw new RuntimeException(
                'Fondasi CMS publik atau site settings belum tersedia.'
            );
        }

        $config = new PublicCms();
        $definition = $config->pages['contact'] ?? null;

        if (!is_array($definition)) {
            throw new RuntimeException(
                'Definisi CMS Kontak tidak ditemukan.'
            );
        }

        $this->db->transBegin();

        try {
            $this->expandContactPage($definition);
            $this->insertSiteSettings();

            if ($this->db->transCommit() === false) {
                throw new RuntimeException(
                    'Perluasan CMS Kontak dan Footer gagal.'
                );
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    public function down()
    {
        if (
            !$this->db->tableExists('public_pages')
            || !$this->db->tableExists(
                'public_page_sections'
            )
            || !$this->db->tableExists('site_settings')
        ) {
            return;
        }

        $this->db->transBegin();

        try {
            $page = $this->db
                ->table('public_pages')
                ->where('page_key', 'contact')
                ->get()
                ->getRowArray();

            if ($page) {
                $sections = $this->db->table(
                    'public_page_sections'
                );

                $sections
                    ->where(
                        'public_page_id',
                        (int) $page['id']
                    )
                    ->whereIn(
                        'section_key',
                        $this->newSections
                    )
                    ->delete();

                foreach (
                    $this->addedFields as
                    $sectionKey => $fieldKeys
                ) {
                    $section = $sections
                        ->where(
                            'public_page_id',
                            (int) $page['id']
                        )
                        ->where(
                            'section_key',
                            $sectionKey
                        )
                        ->get()
                        ->getRowArray();

                    if (!$section) {
                        continue;
                    }

                    $draft = $this->decode(
                        $section['draft_content'] ?? null
                    );

                    $published = $this->decode(
                        $section[
                            'published_content'
                        ] ?? null
                    );

                    foreach ($fieldKeys as $fieldKey) {
                        unset($draft[$fieldKey]);
                        unset($published[$fieldKey]);
                    }

                    $sections
                        ->where(
                            'id',
                            (int) $section['id']
                        )
                        ->update([
                            'draft_content' =>
                                $this->encode($draft),
                            'published_content' =>
                                $this->encode($published),
                            'updated_at' =>
                                date('Y-m-d H:i:s'),
                        ]);
                }
            }

            $this->db
                ->table('site_settings')
                ->whereIn(
                    'setting_key',
                    array_keys($this->siteSettings)
                )
                ->delete();

            if ($this->db->transCommit() === false) {
                throw new RuntimeException(
                    'Rollback CMS Kontak dan Footer gagal.'
                );
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function expandContactPage(
        array $definition
    ): void {
        $pageBuilder = $this->db->table('public_pages');
        $sectionBuilder = $this->db->table(
            'public_page_sections'
        );

        $page = $pageBuilder
            ->where('page_key', 'contact')
            ->get()
            ->getRowArray();

        if (!$page) {
            throw new RuntimeException(
                'Record halaman Kontak belum tersedia.'
            );
        }

        $order = 10;
        $now = date('Y-m-d H:i:s');

        foreach (
            $definition['sections'] as
            $sectionKey => $sectionDefinition
        ) {
            $defaults = [];

            foreach (
                $sectionDefinition['fields'] as
                $fieldKey => $fieldDefinition
            ) {
                $defaults[$fieldKey] =
                    $fieldDefinition['default'] ?? '';
            }

            $existing = $sectionBuilder
                ->where(
                    'public_page_id',
                    (int) $page['id']
                )
                ->where('section_key', $sectionKey)
                ->get()
                ->getRowArray();

            if (!$existing) {
                $encoded = $this->encode($defaults);

                $sectionBuilder->insert([
                    'public_page_id' =>
                        (int) $page['id'],
                    'section_key' => $sectionKey,
                    'section_name' =>
                        $sectionDefinition['name'],
                    'display_order' => $order,
                    'draft_content' => $encoded,
                    'published_content' => $encoded,
                    'draft_enabled' => 1,
                    'published_enabled' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $order += 10;
                continue;
            }

            $draft = array_replace(
                $defaults,
                $this->decode(
                    $existing['draft_content'] ?? null
                )
            );

            $published = array_replace(
                $defaults,
                $this->decode(
                    $existing[
                        'published_content'
                    ] ?? null
                )
            );

            $sectionBuilder
                ->where('id', (int) $existing['id'])
                ->update([
                    'section_name' =>
                        $sectionDefinition['name'],
                    'display_order' => $order,
                    'draft_content' =>
                        $this->encode($draft),
                    'published_content' =>
                        $this->encode($published),
                    'updated_at' => $now,
                ]);

            $order += 10;
        }
    }

    private function insertSiteSettings(): void
    {
        $builder = $this->db->table('site_settings');
        $now = date('Y-m-d H:i:s');

        foreach ($this->siteSettings as $key => $setting) {
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
                'setting_group' => $setting['group'],
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

    /**
     * @return array<string, mixed>
     */
    private function decode($value): array
    {
        $decoded = json_decode(
            (string) $value,
            true
        );

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $value
     */
    private function encode(array $value): string
    {
        $encoded = json_encode(
            $value,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        if ($encoded === false) {
            throw new RuntimeException(
                'Konten CMS Kontak gagal diproses.'
            );
        }

        return $encoded;
    }
}
