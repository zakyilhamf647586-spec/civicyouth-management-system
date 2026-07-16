<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContactMessagesTable extends Migration
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
                'constraint' => 120,
            ],

            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],

            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],

            'category' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'collaboration',
                    'activity',
                    'social',
                    'business',
                    'media',
                    'general',
                ],
                'default' => 'general',
            ],

            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],

            'message' => [
                'type' => 'TEXT',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'unread',
                    'read',
                    'replied',
                    'archived',
                ],
                'default' => 'unread',
            ],

            'source_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],

            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('status');
        $this->forge->addKey('category');
        $this->forge->addKey('created_at');

        $this->forge->createTable('contact_messages', true);
    }

    public function down()
    {
        $this->forge->dropTable('contact_messages', true);
    }
}