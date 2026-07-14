<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTemplateInputFieldsToContentPosts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('content_posts', [
            'event_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'template_type',
            ],
            'event_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'event_title',
            ],
            'event_time' => [
                'type' => 'TIME',
                'null' => true,
                'after' => 'event_date',
            ],
            'event_location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'event_time',
            ],
            'activity_description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'event_location',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('content_posts', [
            'event_title',
            'event_date',
            'event_time',
            'event_location',
            'activity_description',
        ]);
    }
}