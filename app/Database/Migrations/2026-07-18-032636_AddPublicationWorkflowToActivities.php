<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicationWorkflowToActivities extends Migration
{
    public function up()
    {
        $this->forge->addColumn('activities', [
            'summary' => [
                'type'       => 'VARCHAR',
                'constraint' => 220,
                'null'       => true,
                'after'      => 'description',
            ],

            'publication_status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'draft',
                    'review',
                    'published',
                    'scheduled',
                    'archived',
                ],
                /*
                 * Default published digunakan untuk menjaga
                 * kegiatan lama tetap tampil setelah migration.
                 *
                 * Kegiatan baru nantinya dibuat sebagai draft
                 * melalui ActivityController.
                 */
                'default' => 'published',
                'after'   => 'status',
            ],

            'is_public' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'publication_status',
            ],

            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'is_public',
            ],

            'scheduled_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'is_featured',
            ],

            'published_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'scheduled_at',
            ],

            'review_notes' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'published_at',
            ],
        ]);

        /*
         * Menjaga data lama tetap dianggap telah dipublikasikan.
         */
        $this->db->query(
            "
            UPDATE activities
            SET
                publication_status = 'published',
                is_public = 1,
                published_at = COALESCE(
                    created_at,
                    updated_at,
                    NOW()
                )
            WHERE publication_status = 'published'
            "
        );
    }

    public function down()
    {
        $columns = [
            'review_notes',
            'published_at',
            'scheduled_at',
            'is_featured',
            'is_public',
            'publication_status',
            'summary',
        ];

        foreach ($columns as $column) {
            if (
                in_array(
                    $column,
                    $this->db->getFieldNames('activities'),
                    true
                )
            ) {
                $this->forge->dropColumn(
                    'activities',
                    $column
                );
            }
        }
    }
}