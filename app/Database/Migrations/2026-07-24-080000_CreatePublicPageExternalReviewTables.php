<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreatePublicPageExternalReviewTables extends Migration
{
    public function up()
    {
        if (
            !$this->db->tableExists('public_pages')
            || !$this->db->tableExists(
                'public_page_revisions'
            )
        ) {
            throw new RuntimeException(
                'Workflow CMS dan Revision History belum tersedia.'
            );
        }

        $this->createPreviewTokens();
        $this->createExternalReviews();
        $this->createAccessLogs();
    }

    public function down()
    {
        $this->forge->dropTable(
            'public_page_preview_access_logs',
            true
        );

        $this->forge->dropTable(
            'public_page_external_reviews',
            true
        );

        $this->forge->dropTable(
            'public_page_preview_tokens',
            true
        );
    }

    private function createPreviewTokens(): void
    {
        if ($this->db->tableExists(
            'public_page_preview_tokens'
        )) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'public_page_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'public_page_revision_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'token_hash' => [
                'type' => 'CHAR',
                'constraint' => 64,
            ],
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'reviewer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'reviewer_email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'active',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'max_views' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 100,
            ],
            'view_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'allow_decision' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 1,
            ],
            'last_accessed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'revoked_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addUniqueKey('token_hash');
        $this->forge->addKey('public_page_id');
        $this->forge->addKey(
            'public_page_revision_id'
        );
        $this->forge->addKey('status');
        $this->forge->addKey('expires_at');

        $this->forge->createTable(
            'public_page_preview_tokens',
            true
        );
    }

    private function createExternalReviews(): void
    {
        if ($this->db->tableExists(
            'public_page_external_reviews'
        )) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'preview_token_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'public_page_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'public_page_revision_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'decision' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'comment',
            ],
            'reviewer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'reviewer_email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'comment' => [
                'type' => 'TEXT',
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
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('preview_token_id');
        $this->forge->addKey('public_page_id');
        $this->forge->addKey(
            'public_page_revision_id'
        );
        $this->forge->addKey('decision');
        $this->forge->addKey('created_at');

        $this->forge->createTable(
            'public_page_external_reviews',
            true
        );
    }

    private function createAccessLogs(): void
    {
        if ($this->db->tableExists(
            'public_page_preview_access_logs'
        )) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'preview_token_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'public_page_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
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
            'metadata' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('preview_token_id');
        $this->forge->addKey('public_page_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('created_at');

        $this->forge->createTable(
            'public_page_preview_access_logs',
            true
        );
    }
}
