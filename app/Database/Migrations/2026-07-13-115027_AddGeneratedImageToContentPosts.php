<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGeneratedImageToContentPosts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('content_posts', [
            'generated_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'ai_summary',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('content_posts', 'generated_image');
    }
}