<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateContentPostAuditLogs extends Migration
{
    private string $table = 'content_post_audit_logs';

    public function up()
    {
        if (!$this->db->tableExists('content_posts')) {
            throw new RuntimeException(
                'Tabel content_posts belum tersedia.'
            );
        }

        if ($this->db->tableExists($this->table)) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'content_post_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'summary' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'from_status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
            ],
            'to_status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
            ],
            'changed_fields' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'actor_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'actor_role' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('content_post_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey([
            'content_post_id',
            'created_at',
        ]);

        $this->forge->createTable($this->table, true);
    }

    public function down()
    {
        $this->forge->dropTable($this->table, true);
    }
}
