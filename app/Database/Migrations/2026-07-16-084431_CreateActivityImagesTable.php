<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityImagesTable extends Migration
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

            'activity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            'image_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'caption' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'is_cover' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            'display_order' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
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
        $this->forge->addKey('activity_id');
        $this->forge->addKey('display_order');

        $this->forge->addForeignKey(
            'activity_id',
            'activities',
            'id',
            'CASCADE',
            'CASCADE',
            'fk_activity_images_activity'
        );

        $this->forge->createTable('activity_images');
    }

    public function down()
    {
        $this->forge->dropTable('activity_images');
    }
}