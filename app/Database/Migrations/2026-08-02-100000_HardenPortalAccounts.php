<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HardenPortalAccounts extends Migration
{
    /** @var array<string, array<string, mixed>> */
    private array $securityFields = [
        'session_version' => [
            'type' => 'INT',
            'constraint' => 11,
            'unsigned' => true,
            'default' => 1,
            'after' => 'status',
        ],
        'must_change_password' => [
            'type' => 'TINYINT',
            'constraint' => 1,
            'unsigned' => true,
            'default' => 0,
            'after' => 'session_version',
        ],
        'password_changed_at' => [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'must_change_password',
        ],
        'last_login_at' => [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'password_changed_at',
        ],
        'last_login_ip_hash' => [
            'type' => 'CHAR',
            'constraint' => 64,
            'null' => true,
            'after' => 'last_login_at',
        ],
        'last_login_user_agent' => [
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => true,
            'after' => 'last_login_ip_hash',
        ],
    ];

    public function up()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        $missingFields = [];

        foreach ($this->securityFields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'users')) {
                $missingFields[$name] = $definition;
            }
        }

        if ($missingFields !== []) {
            $this->forge->addColumn('users', $missingFields);
        }

        $this->db->table('users')
            ->set(
                'password_changed_at',
                'COALESCE(updated_at, created_at, NOW())',
                false
            )
            ->where('password_changed_at IS NULL', null, false)
            ->update();

        // The bundled development account is never allowed to silently
        // continue with its original credential after this migration.
        $this->db->table('users')
            ->set('must_change_password', 1)
            ->set('session_version', 'session_version + 1', false)
            ->where('email', 'admin@civicyouth.local')
            ->where('status', 'active')
            ->update();
    }

    public function down()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        foreach (array_reverse(array_keys($this->securityFields)) as $field) {
            if ($this->db->fieldExists($field, 'users')) {
                $this->forge->dropColumn('users', $field);
            }
        }
    }
}
