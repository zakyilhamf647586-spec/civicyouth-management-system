<?php

namespace App\Database\Migrations;

use App\Libraries\PublicInternalBoundary;
use CodeIgniter\Database\Migration;
use RuntimeException;

class EnforcePublicInternalSeparation extends Migration
{
    private const CONTACT_NOTE_ID_OLD =
        'Pesan akan masuk ke Portal Pengurus GARDA 01.';

    private const CONTACT_NOTE_ID_NEW =
        'Pesan akan diteruskan kepada tim GARDA 01 untuk ditinjau dan ditindaklanjuti.';

    private const CONTACT_NOTE_EN_OLD =
        'Your message will be delivered to the GARDA 01 Team Portal.';

    private const CONTACT_NOTE_EN_NEW =
        'Your message will be forwarded to the GARDA 01 team for review and follow-up.';

    public function up()
    {
        $this->db->transBegin();

        try {
            $this->sanitizeNavigationMenus();
            $this->sanitizeContactMicrocopy();

            if ($this->db->transCommit() === false) {
                throw new RuntimeException(
                    'Pemisahan website publik dan Portal internal belum dapat diterapkan.'
                );
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    public function down()
    {
        // Intentionally non-destructive. Rolling back must not restore public
        // links to authentication or internal Portal routes.
    }

    private function sanitizeNavigationMenus(): void
    {
        if (!$this->db->tableExists('website_navigation_menus')) {
            return;
        }

        $builder = $this->db->table('website_navigation_menus');
        $rows = $builder
            ->select('id, draft_items, published_items')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $changes = [];

            foreach (['draft_items', 'published_items'] as $column) {
                $items = json_decode(
                    (string) ($row[$column] ?? ''),
                    true
                );

                if (!is_array($items)) {
                    continue;
                }

                $items = array_values(array_filter($items, 'is_array'));
                $filtered = PublicInternalBoundary::filterNavigationItems($items);

                if ($filtered === $items) {
                    continue;
                }

                $encoded = json_encode(
                    $filtered,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                if ($encoded === false) {
                    throw new RuntimeException(
                        'Data navigasi publik belum dapat diproses.'
                    );
                }

                $changes[$column] = $encoded;
            }

            if ($changes === []) {
                continue;
            }

            $changes['updated_at'] = date('Y-m-d H:i:s');

            $builder
                ->where('id', (int) $row['id'])
                ->update($changes);
        }
    }

    private function sanitizeContactMicrocopy(): void
    {
        if (
            !$this->db->tableExists('public_pages')
            || !$this->db->tableExists('public_page_sections')
        ) {
            return;
        }

        $page = $this->db
            ->table('public_pages')
            ->select('id')
            ->where('page_key', 'contact')
            ->get()
            ->getRowArray();

        if (!$page) {
            return;
        }

        $builder = $this->db->table('public_page_sections');
        $section = $builder
            ->where('public_page_id', (int) $page['id'])
            ->where('section_key', 'form_intro')
            ->get()
            ->getRowArray();

        if (!$section) {
            return;
        }

        $changes = [];

        foreach ([
            'draft_content' => [self::CONTACT_NOTE_ID_OLD, self::CONTACT_NOTE_ID_NEW],
            'published_content' => [self::CONTACT_NOTE_ID_OLD, self::CONTACT_NOTE_ID_NEW],
            'draft_content_en' => [self::CONTACT_NOTE_EN_OLD, self::CONTACT_NOTE_EN_NEW],
            'published_content_en' => [self::CONTACT_NOTE_EN_OLD, self::CONTACT_NOTE_EN_NEW],
        ] as $column => [$oldValue, $newValue]) {
            if (!$this->db->fieldExists($column, 'public_page_sections')) {
                continue;
            }

            $content = json_decode(
                (string) ($section[$column] ?? ''),
                true
            );

            if (!is_array($content)) {
                continue;
            }

            if (($content['submit_note'] ?? null) !== $oldValue) {
                continue;
            }

            $content['submit_note'] = $newValue;
            $encoded = json_encode(
                $content,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if ($encoded === false) {
                throw new RuntimeException(
                    'Microcopy kontak belum dapat diproses.'
                );
            }

            $changes[$column] = $encoded;
        }

        if ($changes === []) {
            return;
        }

        $changes['updated_at'] = date('Y-m-d H:i:s');

        $builder
            ->where('id', (int) $section['id'])
            ->update($changes);
    }
}
