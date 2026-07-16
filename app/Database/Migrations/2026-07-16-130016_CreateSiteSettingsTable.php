<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSiteSettingsTable extends Migration
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

            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'setting_group' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'general',
            ],

            'setting_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'text',
            ],

            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 0,
            ],

            'is_public' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addUniqueKey('setting_key');
        $this->forge->addKey('setting_group');
        $this->forge->addKey('sort_order');

        $this->forge->createTable('site_settings', true);
    }

    public function down()
    {
        $this->forge->dropTable('site_settings', true);
    }
}