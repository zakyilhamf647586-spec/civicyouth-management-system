<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddPublicPageReviewWorkflow extends Migration
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $columns = [
        'workflow_status' => [
            'type' => 'VARCHAR',
            'constraint' => 30,
            'default' => 'published',
            'after' => 'has_unpublished_changes',
        ],
        'submitted_by' => [
            'type' => 'INT',
            'constraint' => 11,
            'unsigned' => true,
            'null' => true,
            'after' => 'revision_note',
        ],
        'submitted_at' => [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'submitted_by',
        ],
        'reviewed_by' => [
            'type' => 'INT',
            'constraint' => 11,
            'unsigned' => true,
            'null' => true,
            'after' => 'submitted_at',
        ],
        'reviewed_at' => [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'reviewed_by',
        ],
        'review_note' => [
            'type' => 'VARCHAR',
            'constraint' => 1000,
            'null' => true,
            'after' => 'reviewed_at',
        ],
        'approved_by' => [
            'type' => 'INT',
            'constraint' => 11,
            'unsigned' => true,
            'null' => true,
            'after' => 'review_note',
        ],
        'approved_at' => [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'approved_by',
        ],
    ];

    public function up()
    {
        if (!$this->db->tableExists('public_pages')) {
            throw new RuntimeException(
                'Tabel public_pages belum tersedia.'
            );
        }

        $existingFields = $this->db
            ->getFieldNames('public_pages');

        foreach ($this->columns as $name => $definition) {
            if (in_array($name, $existingFields, true)) {
                continue;
            }

            $this->forge->addColumn(
                'public_pages',
                [$name => $definition]
            );

            $existingFields[] = $name;
        }

        $this->db->query(
            "UPDATE public_pages
             SET workflow_status = CASE
                 WHEN has_unpublished_changes = 1
                     THEN 'draft'
                 WHEN published_at IS NOT NULL
                     THEN 'published'
                 ELSE 'draft'
             END
             WHERE workflow_status IS NULL
                OR workflow_status = ''
                OR workflow_status = 'published'"
        );
    }

    public function down()
    {
        if (!$this->db->tableExists('public_pages')) {
            return;
        }

        $existingFields = $this->db
            ->getFieldNames('public_pages');

        $dropColumns = [];

        foreach (array_keys($this->columns) as $name) {
            if (in_array($name, $existingFields, true)) {
                $dropColumns[] = $name;
            }
        }

        if ($dropColumns !== []) {
            $this->forge->dropColumn(
                'public_pages',
                $dropColumns
            );
        }
    }
}
