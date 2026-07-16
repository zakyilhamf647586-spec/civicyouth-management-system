<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgramIdToActivities extends Migration
{
    public function up()
    {
        $this->forge->addColumn('activities', [
            'program_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query(
            'ALTER TABLE activities
             ADD INDEX idx_activities_program_id (program_id),
             ADD CONSTRAINT fk_activities_program
             FOREIGN KEY (program_id)
             REFERENCES programs(id)
             ON UPDATE CASCADE
             ON DELETE SET NULL'
        );
    }

    public function down()
    {
        $this->db->query(
            'ALTER TABLE activities
             DROP FOREIGN KEY fk_activities_program'
        );

        $this->forge->dropColumn('activities', 'program_id');
    }
}