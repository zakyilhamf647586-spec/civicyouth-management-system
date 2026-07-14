<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiContentStudioTables extends Migration
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
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'auto_detect',
            ],
            'template_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'feed_square',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'caption' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'hashtags' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'mentions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'alt_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ai_summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'draft',
            ],
            'created_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->createTable('content_posts');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'content_post_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'image_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addKey('content_post_id');
        $this->forge->createTable('content_assets');
    }

    public function down()
    {
        $this->forge->dropTable('content_assets');
        $this->forge->dropTable('content_posts');
    }
}