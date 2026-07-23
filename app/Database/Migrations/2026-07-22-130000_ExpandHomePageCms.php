<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\PublicCms;
use RuntimeException;

class ExpandHomePageCms extends Migration
{
    /**
     * Section baru yang hanya dibuat oleh migration ini.
     *
     * @var list<string>
     */
    private array $newSections = [
        'statistics',
        'programs',
        'impact',
        'latest',
    ];

    /**
     * Field tambahan pada section lama.
     *
     * @var array<string, list<string>>
     */
    private array $addedFields = [
        'hero' => [
            'watermark',
            'featured_label',
            'featured_link_label',
            'featured_empty_title',
            'featured_empty_body',
        ],
        'about' => [
            'value_one_title',
            'value_one_body',
            'value_two_title',
            'value_two_body',
            'value_three_title',
            'value_three_body',
        ],
        'collaboration' => [
            'list_label',
            'watermark',
        ],
    ];

    public function up()
    {
        if (
            !$this->db->tableExists('public_pages')
            || !$this->db->tableExists(
                'public_page_sections'
            )
        ) {
            throw new RuntimeException(
                'Fondasi CMS publik belum tersedia. Jalankan migration Fase 2A terlebih dahulu.'
            );
        }

        $config = new PublicCms();
        $homeDefinition = $config->pages['home'] ?? null;

        if (!is_array($homeDefinition)) {
            throw new RuntimeException(
                'Definisi CMS Beranda tidak ditemukan.'
            );
        }

        $pageBuilder = $this->db->table('public_pages');
        $sectionBuilder = $this->db->table(
            'public_page_sections'
        );

        $homePage = $pageBuilder
            ->where('page_key', 'home')
            ->get()
            ->getRowArray();

        if (!$homePage) {
            throw new RuntimeException(
                'Record halaman Beranda belum tersedia.'
            );
        }

        $this->db->transBegin();

        try {
            $order = 10;
            $now = date('Y-m-d H:i:s');

            foreach (
                $homeDefinition['sections'] as
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
                        (int) $homePage['id']
                    )
                    ->where('section_key', $sectionKey)
                    ->get()
                    ->getRowArray();

                if (!$existing) {
                    $encoded = $this->encode($defaults);

                    $sectionBuilder->insert([
                        'public_page_id' =>
                            (int) $homePage['id'],
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

                $draft = $this->decode(
                    $existing['draft_content'] ?? null
                );

                $published = $this->decode(
                    $existing['published_content'] ?? null
                );

                $draft = array_replace(
                    $defaults,
                    $draft
                );

                $published = array_replace(
                    $defaults,
                    $published
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

            if ($this->db->transCommit() === false) {
                throw new RuntimeException(
                    'Perluasan CMS Beranda gagal diselesaikan.'
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
        ) {
            return;
        }

        $pageBuilder = $this->db->table('public_pages');
        $sectionBuilder = $this->db->table(
            'public_page_sections'
        );

        $homePage = $pageBuilder
            ->where('page_key', 'home')
            ->get()
            ->getRowArray();

        if (!$homePage) {
            return;
        }

        $this->db->transBegin();

        try {
            $sectionBuilder
                ->where(
                    'public_page_id',
                    (int) $homePage['id']
                )
                ->whereIn('section_key', $this->newSections)
                ->delete();

            foreach (
                $this->addedFields as
                $sectionKey => $fieldKeys
            ) {
                $section = $sectionBuilder
                    ->where(
                        'public_page_id',
                        (int) $homePage['id']
                    )
                    ->where('section_key', $sectionKey)
                    ->get()
                    ->getRowArray();

                if (!$section) {
                    continue;
                }

                $draft = $this->decode(
                    $section['draft_content'] ?? null
                );

                $published = $this->decode(
                    $section['published_content'] ?? null
                );

                foreach ($fieldKeys as $fieldKey) {
                    unset($draft[$fieldKey]);
                    unset($published[$fieldKey]);
                }

                $sectionBuilder
                    ->where('id', (int) $section['id'])
                    ->update([
                        'draft_content' =>
                            $this->encode($draft),
                        'published_content' =>
                            $this->encode($published),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }

            if ($this->db->transCommit() === false) {
                throw new RuntimeException(
                    'Rollback perluasan CMS Beranda gagal.'
                );
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
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
                'Konten CMS Beranda gagal diproses.'
            );
        }

        return $encoded;
    }
}
