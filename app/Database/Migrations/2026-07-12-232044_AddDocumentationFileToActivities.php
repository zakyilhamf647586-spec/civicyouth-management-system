<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocumentationFileToActivities extends Migration
{
    public function up()
    {
        $this->forge->addColumn('activities', [
            'documentation_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'documentation_link',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('activities', 'documentation_file');
    }
}