<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProgramsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],

            'tagline' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'short_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'focus_items' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array berisi fokus program',
            ],

            'campaign_items' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array berisi contoh program atau kampanye',
            ],

            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'cover_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'draft',
                    'published',
                    'archived',
                ],
                'default' => 'published',
            ],

            'display_order' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 0,
            ],

            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('status');
        $this->forge->addKey('display_order');

        $this->forge->createTable('programs');
    }

    public function down()
    {
        $this->forge->dropTable('programs');
    }
}