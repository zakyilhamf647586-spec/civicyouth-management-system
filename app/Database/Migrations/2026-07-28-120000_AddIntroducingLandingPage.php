<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIntroducingLandingPage extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('public_pages')) {
            $this->db->table('public_pages')
                ->where('page_key', 'home')
                ->where('route_path', '/')
                ->update([
                    'route_path' => '/home',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        $this->rewriteNavigationHomeUrl(
            '/',
            '/home'
        );
    }

    public function down()
    {
        if ($this->db->tableExists('public_pages')) {
            $this->db->table('public_pages')
                ->where('page_key', 'home')
                ->where('route_path', '/home')
                ->update([
                    'route_path' => '/',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        $this->rewriteNavigationHomeUrl(
            '/home',
            '/'
        );
    }

    private function rewriteNavigationHomeUrl(
        string $from,
        string $to
    ): void {
        if (!$this->db->tableExists(
            'website_navigation_menus'
        )) {
            return;
        }

        $builder = $this->db->table(
            'website_navigation_menus'
        );

        $rows = $builder
            ->select(
                'id, draft_items, published_items'
            )
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $changes = [];

            foreach ([
                'draft_items',
                'published_items',
            ] as $column) {
                $items = json_decode(
                    (string) ($row[$column] ?? ''),
                    true
                );

                if (!is_array($items)) {
                    continue;
                }

                $changed = false;

                foreach ($items as &$item) {
                    if (
                        !is_array($item)
                        || ($item['item_key'] ?? '')
                            !== 'home'
                        || ($item['url'] ?? '')
                            !== $from
                    ) {
                        continue;
                    }

                    $item['url'] = $to;
                    $changed = true;
                }

                unset($item);

                if (!$changed) {
                    continue;
                }

                $encoded = json_encode(
                    $items,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );

                if ($encoded !== false) {
                    $changes[$column] = $encoded;
                }
            }

            if ($changes === []) {
                continue;
            }

            $changes['updated_at'] =
                date('Y-m-d H:i:s');

            $builder
                ->where('id', (int) $row['id'])
                ->update($changes);
        }
    }
}
