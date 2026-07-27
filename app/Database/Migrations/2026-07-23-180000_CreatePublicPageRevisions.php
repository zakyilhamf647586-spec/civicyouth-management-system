<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreatePublicPageRevisions extends Migration
{
    public function up()
    {
        if (
            !$this->db->tableExists('public_pages')
            || !$this->db->tableExists(
                'public_page_sections'
            )
        ) {
            throw new RuntimeException(
                'Fondasi CMS halaman publik belum tersedia.'
            );
        }

        if (!$this->db->tableExists(
            'public_page_revisions'
        )) {
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
                'version_number' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'source_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                ],
                'snapshot_mode' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                ],
                'workflow_status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                ],
                'revision_note' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'source_revision_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                ],
                'snapshot_data' => [
                    'type' => 'LONGTEXT',
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
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('public_page_id');
            $this->forge->addKey('created_at');
            $this->forge->addUniqueKey([
                'public_page_id',
                'version_number',
            ]);

            $this->forge->createTable(
                'public_page_revisions',
                true
            );
        }

        $this->seedInitialSnapshots();
    }

    public function down()
    {
        $this->forge->dropTable(
            'public_page_revisions',
            true
        );
    }

    private function seedInitialSnapshots(): void
    {
        $revisionBuilder = $this->db->table(
            'public_page_revisions'
        );

        $pages = $this->db
            ->table('public_pages')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($pages as $page) {
            $exists = $revisionBuilder
                ->where(
                    'public_page_id',
                    (int) $page['id']
                )
                ->countAllResults() > 0;

            if ($exists) {
                continue;
            }

            $mode = !empty($page['published_at'])
                ? 'published'
                : 'draft';

            $snapshot = $this->snapshot(
                $page,
                $mode
            );

            $encoded = json_encode(
                $snapshot,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            if ($encoded === false) {
                throw new RuntimeException(
                    'Snapshot awal halaman gagal diproses.'
                );
            }

            $revisionBuilder->insert([
                'public_page_id' =>
                    (int) $page['id'],
                'version_number' => 1,
                'source_type' =>
                    $mode === 'published'
                        ? 'initial_published'
                        : 'initial_draft',
                'snapshot_mode' => $mode,
                'workflow_status' =>
                    $page['workflow_status']
                    ?? (
                        $mode === 'published'
                            ? 'published'
                            : 'draft'
                    ),
                'revision_note' =>
                    'Snapshot awal sebelum Revision History diaktifkan.',
                'source_revision_id' => null,
                'snapshot_data' => $encoded,
                'created_by' =>
                    $mode === 'published'
                        ? ($page['published_by'] ?? null)
                        : ($page['last_edited_by'] ?? null),
                'created_at' =>
                    $page['published_at']
                    ?? $page['updated_at']
                    ?? date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $page
     * @return array<string, mixed>
     */
    private function snapshot(
        array $page,
        string $mode
    ): array {
        $mode = $mode === 'published'
            ? 'published'
            : 'draft';

        $sections = $this->db
            ->table('public_page_sections')
            ->where(
                'public_page_id',
                (int) $page['id']
            )
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $sectionSnapshots = [];

        foreach ($sections as $section) {
            $decoded = json_decode(
                (string) (
                    $section[$mode . '_content']
                    ?? ''
                ),
                true
            );

            $sectionSnapshots[] = [
                'section_key' =>
                    (string) $section['section_key'],
                'section_name' =>
                    (string) $section['section_name'],
                'display_order' =>
                    (int) $section['display_order'],
                'enabled' =>
                    (bool) (
                        $section[$mode . '_enabled']
                        ?? false
                    ),
                'content' => is_array($decoded)
                    ? $decoded
                    : [],
            ];
        }

        return [
            'schema_version' => 1,
            'captured_mode' => $mode,
            'page' => [
                'page_key' =>
                    (string) $page['page_key'],
                'name' =>
                    (string) $page['name'],
                'route_path' =>
                    (string) $page['route_path'],
                'title' =>
                    $page[$mode . '_title'] ?? null,
                'meta_description' =>
                    $page[
                        $mode . '_meta_description'
                    ] ?? null,
                'workflow_status' =>
                    $page['workflow_status'] ?? null,
                'revision_note' =>
                    $page['revision_note'] ?? null,
                'published_at' =>
                    $page['published_at'] ?? null,
            ],
            'sections' => $sectionSnapshots,
        ];
    }
}
