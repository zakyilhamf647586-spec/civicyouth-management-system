<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicPageModel extends Model
{
    protected $table = 'public_pages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'page_key',
        'name',
        'route_path',
        'draft_title',
        'published_title',
        'draft_meta_description',
        'published_meta_description',
        'has_unpublished_changes',
        'revision_note',
        'last_edited_by',
        'published_by',
        'published_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByKey(string $pageKey): ?array
    {
        return $this
            ->where('page_key', $pageKey)
            ->first();
    }

    public function bundle(
        string $pageKey,
        string $mode = 'published'
    ): ?array {
        $mode = $mode === 'draft'
            ? 'draft'
            : 'published';

        $page = $this->findByKey($pageKey);

        if (!$page) {
            return null;
        }

        $sectionModel = new PublicPageSectionModel();

        $sectionRows = $sectionModel
            ->where(
                'public_page_id',
                (int) $page['id']
            )
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $sections = [];

        foreach ($sectionRows as $section) {
            $content = json_decode(
                (string) (
                    $section[$mode . '_content']
                    ?? ''
                ),
                true
            );

            $sections[$section['section_key']] = [
                'key' => $section['section_key'],
                'name' => $section['section_name'],
                'display_order' =>
                    (int) $section['display_order'],
                'enabled' =>
                    (bool) (
                        $section[$mode . '_enabled']
                        ?? false
                    ),
                'content' => is_array($content)
                    ? $content
                    : [],
            ];
        }

        return [
            'id' => (int) $page['id'],
            'page_key' => $page['page_key'],
            'name' => $page['name'],
            'route_path' => $page['route_path'],
            'title' => $page[$mode . '_title'] ?? null,
            'meta_description' =>
                $page[$mode . '_meta_description'] ?? null,
            'has_unpublished_changes' =>
                (bool) $page['has_unpublished_changes'],
            'published_at' => $page['published_at'],
            'updated_at' => $page['updated_at'],
            'revision_note' => $page['revision_note'],
            'sections' => $sections,
            'mode' => $mode,
        ];
    }
}
