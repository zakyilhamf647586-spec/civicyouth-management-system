<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemHealthMonitoring extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('system_health_snapshots')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'overall_status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'healthy',
                ],
                'score' => [
                    'type' => 'TINYINT',
                    'constraint' => 3,
                    'unsigned' => true,
                    'default' => 0,
                ],
                'source' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'scheduler',
                ],
                'checks_total' => [
                    'type' => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned' => true,
                    'default' => 0,
                ],
                'checks_pass' => [
                    'type' => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned' => true,
                    'default' => 0,
                ],
                'checks_warning' => [
                    'type' => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned' => true,
                    'default' => 0,
                ],
                'checks_critical' => [
                    'type' => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned' => true,
                    'default' => 0,
                ],
                'database_latency_ms' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                ],
                'disk_free_bytes' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                ],
                'memory_usage_bytes' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                ],
                'backup_age_hours' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'null' => true,
                ],
                'payload_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('overall_status');
            $this->forge->addKey('created_at');
            $this->forge->createTable('system_health_snapshots', true);
        }

        if (!$this->db->tableExists('system_health_incidents')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'component' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'open',
                ],
                'severity' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'warning',
                ],
                'title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 180,
                ],
                'message' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'occurrence_count' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'default' => 1,
                ],
                'first_seen_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
                'last_seen_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
                'resolved_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'acknowledged_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'acknowledged_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'last_snapshot_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                ],
                'metadata_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey(['component', 'status']);
            $this->forge->addKey('severity');
            $this->forge->addKey('last_seen_at');
            $this->forge->createTable('system_health_incidents', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('system_health_incidents', true);
        $this->forge->dropTable('system_health_snapshots', true);
    }
}
