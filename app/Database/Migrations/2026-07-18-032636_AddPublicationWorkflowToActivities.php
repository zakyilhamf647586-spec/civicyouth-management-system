<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddPublicationWorkflowToActivities extends Migration
{
    private string $table = 'activities';

    public function up()
    {
        if (!$this->db->tableExists($this->table)) {
            throw new RuntimeException(
                'Tabel activities tidak ditemukan.'
            );
        }

        /*
         * Ringkasan pendek untuk card dan halaman publik.
         */
        $this->addColumnIfMissing('summary', [
            'type'       => 'VARCHAR',
            'constraint' => 220,
            'null'       => true,
            'after'      => 'description',
        ]);

        /*
         * Status publikasi dipisahkan dari status pelaksanaan.
         *
         * status:
         * planned, completed, cancelled
         *
         * publication_status:
         * draft, review, published, scheduled, archived
         */
        $this->addColumnIfMissing(
            'publication_status',
            [
                'type'       => 'ENUM',
                'constraint' => [
                    'draft',
                    'review',
                    'published',
                    'scheduled',
                    'archived',
                ],
                /*
                 * Data lama tetap tampil di website
                 * setelah migration dijalankan.
                 */
                'default' => 'published',
                'after'   => 'status',
            ]
        );

        /*
         * Kontrol visibilitas publik.
         */
        $this->addColumnIfMissing('is_public', [
            'type'       => 'TINYINT',
            'constraint' => 1,
            'unsigned'   => true,
            'default'    => 1,
            'after'      => 'publication_status',
        ]);

        /*
         * Penanda kegiatan unggulan / Cerita Dampak.
         */
        $this->addColumnIfMissing('is_featured', [
            'type'       => 'TINYINT',
            'constraint' => 1,
            'unsigned'   => true,
            'default'    => 0,
            'after'      => 'is_public',
        ]);

        /*
         * Jadwal penayangan otomatis.
         */
        $this->addColumnIfMissing('scheduled_at', [
            'type'  => 'DATETIME',
            'null'  => true,
            'after' => 'is_featured',
        ]);

        /*
         * Waktu kegiatan benar-benar diterbitkan.
         */
        $this->addColumnIfMissing('published_at', [
            'type'  => 'DATETIME',
            'null'  => true,
            'after' => 'scheduled_at',
        ]);

        /*
         * Catatan pemeriksaan dari reviewer.
         */
        $this->addColumnIfMissing('review_notes', [
            'type'  => 'TEXT',
            'null'  => true,
            'after' => 'published_at',
        ]);

        /*
         * Seluruh kegiatan lama dianggap sudah dipublikasikan
         * agar konten publik yang ada tidak tiba-tiba hilang.
         */
        $this->db->query(
            "
            UPDATE activities
            SET
                publication_status = 'published',
                is_public = 1,
                published_at = COALESCE(
                    published_at,
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
        if (!$this->db->tableExists($this->table)) {
            return;
        }

        /*
         * Urutan dibalik agar kolom terakhir dihapus dahulu.
         */
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
            if ($this->db->fieldExists(
                $column,
                $this->table
            )) {
                $this->forge->dropColumn(
                    $this->table,
                    $column
                );
            }
        }
    }

    private function addColumnIfMissing(
        string $column,
        array $definition
    ): void {
        if ($this->db->fieldExists(
            $column,
            $this->table
        )) {
            return;
        }

        $this->forge->addColumn(
            $this->table,
            [
                $column => $definition,
            ]
        );
    }
}