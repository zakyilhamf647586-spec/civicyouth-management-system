<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\PublicCms;
use RuntimeException;

class CreatePublicCmsFoundation extends Migration
{
    public function up()
    {
        if (
            $this->db->tableExists('public_pages')
            && $this->db->tableExists('public_page_sections')
        ) {
            return;
        }

        $this->db->transBegin();

        try {
            if (!$this->db->tableExists('public_pages')) {
                $this->forge->addField([
                    'id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'auto_increment' => true,
                    ],
                    'page_key' => [
                        'type' => 'VARCHAR',
                        'constraint' => 60,
                    ],
                    'name' => [
                        'type' => 'VARCHAR',
                        'constraint' => 100,
                    ],
                    'route_path' => [
                        'type' => 'VARCHAR',
                        'constraint' => 120,
                    ],
                    'draft_title' => [
                        'type' => 'VARCHAR',
                        'constraint' => 180,
                        'null' => true,
                    ],
                    'published_title' => [
                        'type' => 'VARCHAR',
                        'constraint' => 180,
                        'null' => true,
                    ],
                    'draft_meta_description' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'null' => true,
                    ],
                    'published_meta_description' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'null' => true,
                    ],
                    'has_unpublished_changes' => [
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'unsigned' => true,
                        'default' => 0,
                    ],
                    'revision_note' => [
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'null' => true,
                    ],
                    'last_edited_by' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                    ],
                    'published_by' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                    ],
                    'published_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                    'created_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                    'updated_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);

                $this->forge->addKey('id', true);
                $this->forge->addUniqueKey('page_key');
                $this->forge->addKey('route_path');
                $this->forge->createTable('public_pages', true);
            }

            if (!$this->db->tableExists('public_page_sections')) {
                $this->forge->addField([
                    'id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'auto_increment' => true,
                    ],
                    'public_page_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                    ],
                    'section_key' => [
                        'type' => 'VARCHAR',
                        'constraint' => 80,
                    ],
                    'section_name' => [
                        'type' => 'VARCHAR',
                        'constraint' => 120,
                    ],
                    'display_order' => [
                        'type' => 'SMALLINT',
                        'constraint' => 5,
                        'unsigned' => true,
                        'default' => 0,
                    ],
                    'draft_content' => [
                        'type' => 'LONGTEXT',
                        'null' => true,
                    ],
                    'published_content' => [
                        'type' => 'LONGTEXT',
                        'null' => true,
                    ],
                    'draft_enabled' => [
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'unsigned' => true,
                        'default' => 1,
                    ],
                    'published_enabled' => [
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'unsigned' => true,
                        'default' => 1,
                    ],
                    'updated_by' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                    ],
                    'created_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                    'updated_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);

                $this->forge->addKey('id', true);
                $this->forge->addKey('public_page_id');
                $this->forge->addUniqueKey([
                    'public_page_id',
                    'section_key',
                ]);
                $this->forge->createTable(
                    'public_page_sections',
                    true
                );
            }

            $this->seedDefaultPages();

            if ($this->db->transCommit() === false) {
                throw new RuntimeException(
                    'Migration fondasi CMS publik gagal diselesaikan.'
                );
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    public function down()
    {
        $this->forge->dropTable(
            'public_page_sections',
            true
        );

        $this->forge->dropTable(
            'public_pages',
            true
        );
    }

    private function seedDefaultPages(): void
    {
        $config = new PublicCms();
        $pageBuilder = $this->db->table('public_pages');
        $sectionBuilder = $this->db->table(
            'public_page_sections'
        );

        $now = date('Y-m-d H:i:s');

        foreach ($config->pages as $pageKey => $definition) {
            $existingPage = $pageBuilder
                ->where('page_key', $pageKey)
                ->get()
                ->getRowArray();

            if (!$existingPage) {
                $pageBuilder->insert([
                    'page_key' => $pageKey,
                    'name' => $definition['name'],
                    'route_path' => $definition['route'],
                    'draft_title' =>
                        $definition['default_title'],
                    'published_title' =>
                        $definition['default_title'],
                    'draft_meta_description' =>
                        $definition[
                            'default_meta_description'
                        ],
                    'published_meta_description' =>
                        $definition[
                            'default_meta_description'
                        ],
                    'has_unpublished_changes' => 0,
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $pageId = (int) $this->db->insertID();
            } else {
                $pageId = (int) $existingPage['id'];
            }

            $order = 10;

            foreach (
                $definition['sections'] as
                $sectionKey => $sectionDefinition
            ) {
                $exists = $sectionBuilder
                    ->where('public_page_id', $pageId)
                    ->where('section_key', $sectionKey)
                    ->countAllResults() > 0;

                if ($exists) {
                    $order += 10;
                    continue;
                }

                $content = [];

                foreach (
                    $sectionDefinition['fields'] as
                    $fieldKey => $fieldDefinition
                ) {
                    $content[$fieldKey] =
                        $fieldDefinition['default'] ?? '';
                }

                $encoded = json_encode(
                    $content,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );

                if ($encoded === false) {
                    throw new RuntimeException(
                        'Konten awal CMS publik gagal diproses.'
                    );
                }

                $sectionBuilder->insert([
                    'public_page_id' => $pageId,
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
            }
        }
    }
}
