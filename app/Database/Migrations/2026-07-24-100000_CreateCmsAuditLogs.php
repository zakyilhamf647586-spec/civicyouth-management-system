<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCmsAuditLogs extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('cms_audit_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'module' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'severity' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'info',
            ],
            'subject_type' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true,
            ],
            'subject_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'subject_key' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'subject_label' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
                'null' => true,
            ],
            'summary' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'details' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'actor_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'internal',
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
            'source_ip_hash' => [
                'type' => 'CHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'request_method' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'request_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('module');
        $this->forge->addKey('event_type');
        $this->forge->addKey('severity');
        $this->forge->addKey('actor_type');
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['module', 'created_at']);
        $this->forge->addKey(['subject_type', 'subject_id']);

        $this->forge->createTable('cms_audit_logs', true);

        $this->db->table('cms_audit_logs')->insert([
            'module' => 'system',
            'event_type' => 'audit.enabled',
            'severity' => 'notice',
            'subject_type' => 'system',
            'subject_key' => 'cms_audit',
            'subject_label' => 'CMS Audit Center',
            'summary' => 'Audit aktivitas CMS terpusat diaktifkan.',
            'details' => 'Mulai saat ini perubahan CMS, workflow, review eksternal, dan penolakan izin dicatat secara terpusat.',
            'metadata' => json_encode(['schema_version' => 1]),
            'actor_type' => 'system',
            'actor_name' => 'GARDA 01 System',
            'actor_role' => 'System',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('cms_audit_logs', true);
    }
}
