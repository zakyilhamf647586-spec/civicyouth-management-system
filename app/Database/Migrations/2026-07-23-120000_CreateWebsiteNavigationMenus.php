<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\WebsiteNavigation;
use RuntimeException;

class CreateWebsiteNavigationMenus extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists(
            'website_navigation_menus'
        )) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'menu_key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 60,
                ],
                'menu_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'draft_items' => [
                    'type' => 'LONGTEXT',
                ],
                'published_items' => [
                    'type' => 'LONGTEXT',
                ],
                'revision_note' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'has_unpublished_changes' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
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
            $this->forge->addUniqueKey('menu_key');

            $this->forge->createTable(
                'website_navigation_menus',
                true
            );
        }

        $config = new WebsiteNavigation();
        $builder = $this->db->table(
            'website_navigation_menus'
        );

        $now = date('Y-m-d H:i:s');

        foreach (
            $config->menus as
            $menuKey => $definition
        ) {
            $existing = $builder
                ->where('menu_key', $menuKey)
                ->get()
                ->getRowArray();

            if ($existing) {
                continue;
            }

            $encoded = json_encode(
                $definition['items'] ?? [],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            if ($encoded === false) {
                throw new RuntimeException(
                    'Default navigasi gagal diproses.'
                );
            }

            $builder->insert([
                'menu_key' => $menuKey,
                'menu_name' => $definition['name'],
                'draft_items' => $encoded,
                'published_items' => $encoded,
                'revision_note' => null,
                'has_unpublished_changes' => 0,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable(
            'website_navigation_menus',
            true
        );
    }
}
