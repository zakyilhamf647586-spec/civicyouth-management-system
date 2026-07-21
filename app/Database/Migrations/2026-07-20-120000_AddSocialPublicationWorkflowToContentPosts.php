<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddSocialPublicationWorkflowToContentPosts extends Migration
{
    private string $table = 'content_posts';

    public function up()
    {
        if (!$this->db->tableExists($this->table)) {
            throw new RuntimeException(
                'Tabel content_posts belum tersedia. Jalankan migration AI Content Studio terlebih dahulu.'
            );
        }

        $columns = [
            'content_code' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
                'after' => 'id',
            ],
            'program_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'content_code',
            ],
            'activity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'program_id',
            ],
            'channel' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'instagram',
                'after' => 'activity_id',
            ],
            'publication_type' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'carousel',
                'after' => 'channel',
            ],
            'canva_template_code' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
                'after' => 'publication_type',
            ],
            'cover_hook' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'activity_description',
            ],
            'content_goal' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'cover_hook',
            ],
            'target_audience' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'content_goal',
            ],
            'call_to_action' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'target_audience',
            ],
            'canva_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'call_to_action',
            ],
            'instagram_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'canva_url',
            ],
            'owner' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'instagram_url',
            ],
            'reviewer' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'owner',
            ],
            'priority' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'normal',
                'after' => 'reviewer',
            ],
            'workflow_status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'brief',
                'after' => 'priority',
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'workflow_status',
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'scheduled_at',
            ],
            'approval_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'published_at',
            ],
            'approved_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'approval_notes',
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by',
            ],
            'archived_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_at',
            ],
        ];

        foreach ($columns as $name => $definition) {
            if (!$this->db->fieldExists($name, $this->table)) {
                $this->forge->addColumn(
                    $this->table,
                    [$name => $definition]
                );
            }
        }

        $rows = $this->db
            ->table($this->table)
            ->select('id, content_code, workflow_status, created_at')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $update = [];

            if (empty($row['content_code'])) {
                $timestamp = !empty($row['created_at'])
                    ? strtotime((string) $row['created_at'])
                    : false;

                $period = $timestamp
                    ? date('Ym', $timestamp)
                    : date('Ym');

                $update['content_code'] = sprintf(
                    'PUB-%s-%04d',
                    $period,
                    (int) $row['id']
                );
            }

            if (empty($row['workflow_status'])) {
                $update['workflow_status'] = 'draft';
            }

            if ($update !== []) {
                $this->db
                    ->table($this->table)
                    ->where('id', $row['id'])
                    ->update($update);
            }
        }

        $this->createIndexIfMissing(
            'idx_content_posts_content_code',
            'CREATE UNIQUE INDEX idx_content_posts_content_code ON content_posts (content_code)'
        );

        $this->createIndexIfMissing(
            'idx_content_posts_workflow_status',
            'CREATE INDEX idx_content_posts_workflow_status ON content_posts (workflow_status)'
        );

        $this->createIndexIfMissing(
            'idx_content_posts_scheduled_at',
            'CREATE INDEX idx_content_posts_scheduled_at ON content_posts (scheduled_at)'
        );
    }

    public function down()
    {
        foreach ([
            'idx_content_posts_content_code',
            'idx_content_posts_workflow_status',
            'idx_content_posts_scheduled_at',
        ] as $indexName) {
            if ($this->indexExists($indexName)) {
                $this->db->query(
                    sprintf(
                        'DROP INDEX `%s` ON `%s`',
                        $indexName,
                        $this->table
                    )
                );
            }
        }

        $columns = [
            'content_code',
            'program_id',
            'activity_id',
            'channel',
            'publication_type',
            'canva_template_code',
            'cover_hook',
            'content_goal',
            'target_audience',
            'call_to_action',
            'canva_url',
            'instagram_url',
            'owner',
            'reviewer',
            'priority',
            'workflow_status',
            'scheduled_at',
            'published_at',
            'approval_notes',
            'approved_by',
            'approved_at',
            'archived_at',
        ];

        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => $this->db->fieldExists(
                $column,
                $this->table
            )
        ));

        if ($existing !== []) {
            $this->forge->dropColumn($this->table, $existing);
        }
    }

    private function createIndexIfMissing(
        string $indexName,
        string $sql
    ): void {
        if (!$this->indexExists($indexName)) {
            $this->db->query($sql);
        }
    }

    private function indexExists(string $indexName): bool
    {
        return $this->db
            ->query(
                'SHOW INDEX FROM `' . $this->table . '` WHERE Key_name = ?',
                [$indexName]
            )
            ->getNumRows() > 0;
    }
}
