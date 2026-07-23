<?php

namespace App\Models;

use CodeIgniter\Model;

class WebsiteNavigationMenuModel extends Model
{
    protected $table = 'website_navigation_menus';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'menu_key',
        'menu_name',
        'draft_items',
        'published_items',
        'revision_note',
        'has_unpublished_changes',
        'last_edited_by',
        'published_by',
        'published_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByKey(string $menuKey): ?array
    {
        return $this
            ->where('menu_key', $menuKey)
            ->first();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function items(
        string $menuKey,
        string $mode = 'published'
    ): array {
        $menu = $this->findByKey($menuKey);

        if (!$menu) {
            return [];
        }

        $column = $mode === 'draft'
            ? 'draft_items'
            : 'published_items';

        $decoded = json_decode(
            (string) ($menu[$column] ?? ''),
            true
        );

        return is_array($decoded)
            ? array_values(array_filter(
                $decoded,
                'is_array'
            ))
            : [];
    }
}
