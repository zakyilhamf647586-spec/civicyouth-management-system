<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoBioToOrganizationalStructures extends Migration
{
    public function up()
    {
        $this->forge->addColumn('organizational_structures', [
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'division',
            ],
            'short_bio' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'photo',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('organizational_structures', [
            'photo',
            'short_bio',
        ]);
    }
}