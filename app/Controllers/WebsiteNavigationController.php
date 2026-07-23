<?php

namespace App\Controllers;

use App\Models\WebsiteNavigationMenuModel;
use Config\WebsiteNavigation;
use RuntimeException;

class WebsiteNavigationController extends BaseController
{
    protected WebsiteNavigationMenuModel $menuModel;
    protected WebsiteNavigation $navigationConfig;

    public function __construct()
    {
        $this->menuModel =
            new WebsiteNavigationMenuModel();

        $this->navigationConfig =
            new WebsiteNavigation();
    }

    public function index()
    {
        $ready = $this->navigationReady();
        $menus = [];

        foreach (
            $this->navigationConfig->menus as
            $menuKey => $definition
        ) {
            $record = $ready
                ? $this->menuModel
                    ->findByKey($menuKey)
                : null;

            $draftItems = $this->decodeItems(
                $record['draft_items'] ?? null
            );

            $publishedItems = $this->decodeItems(
                $record['published_items'] ?? null
            );

            $menus[] = [
                'menu_key' => $menuKey,
                'name' => $definition['name'],
                'description' =>
                    $definition['description'],
                'draft_count' => count($draftItems),
                'published_count' =>
                    count($publishedItems),
                'has_unpublished_changes' =>
                    !empty(
                        $record[
                            'has_unpublished_changes'
                        ]
                    ),
                'published_at' =>
                    $record['published_at'] ?? null,
                'updated_at' =>
                    $record['updated_at'] ?? null,
            ];
        }

        return view('website_navigation/index', [
            'title' => 'Navigasi Website',
            'ready' => $ready,
            'menus' => $menus,
        ]);
    }

    public function edit(string $menuKey)
    {
        $this->assertReady();

        $definition = $this->definition($menuKey);
        $menu = $this->menuModel
            ->findByKey($menuKey);

        if (!$menu) {
            throw new RuntimeException(
                'Menu navigasi tidak ditemukan.'
            );
        }

        return view('website_navigation/edit', [
            'title' =>
                'Kelola ' . $definition['name'],
            'menuKey' => $menuKey,
            'definition' => $definition,
            'menu' => $menu,
            'items' => $this->decodeItems(
                $menu['draft_items'] ?? null
            ),
            'maximumItems' =>
                $this->navigationConfig
                    ->maximumItems,
        ]);
    }

    public function update(string $menuKey)
    {
        $this->assertReady();

        $definition = $this->definition($menuKey);
        $menu = $this->menuModel
            ->findByKey($menuKey);

        if (!$menu) {
            return redirect()
                ->to('/website/navigation')
                ->with(
                    'error',
                    'Menu navigasi tidak ditemukan.'
                );
        }

        $postedItems = $this->request
            ->getPost('items');

        if (!is_array($postedItems)) {
            $postedItems = [];
        }

        $revisionNote = trim(strip_tags(
            (string) $this->request
                ->getPost('revision_note')
        ));

        $errors = [];

        if (mb_strlen($revisionNote) > 255) {
            $errors[] =
                'Catatan revisi maksimal 255 karakter.';
        }

        if (
            count($postedItems)
            > $this->navigationConfig->maximumItems
        ) {
            $errors[] =
                'Jumlah item navigasi melebihi batas '
                . $this->navigationConfig->maximumItems
                . '.';
        }

        $cleanItems = [];
        $usedKeys = [];

        foreach (
            array_values($postedItems) as
            $index => $postedItem
        ) {
            if (!is_array($postedItem)) {
                continue;
            }

            $label = trim(strip_tags(
                (string) ($postedItem['label'] ?? '')
            ));

            $url = trim(strip_tags(
                (string) ($postedItem['url'] ?? '')
            ));

            $activeInput = trim(strip_tags(
                (string) (
                    $postedItem['active_pages']
                    ?? ''
                )
            ));

            $target = (
                $postedItem['target'] ?? 'self'
            ) === 'blank'
                ? 'blank'
                : 'self';

            $style = (
                $postedItem['style'] ?? 'default'
            ) === 'portal'
                ? 'portal'
                : 'default';

            if ($menuKey !== 'header') {
                $style = 'default';
            }

            $enabled = isset(
                $postedItem['enabled']
            );

            if ($label === '' && $url === '') {
                continue;
            }

            if ($label === '') {
                $errors[] =
                    'Item '
                    . ($index + 1)
                    . ': label wajib diisi.';
            }

            if (mb_strlen($label) > 80) {
                $errors[] =
                    'Item '
                    . ($index + 1)
                    . ': label maksimal 80 karakter.';
            }

            if (
                $url === ''
                || !$this->validNavigationUrl($url)
            ) {
                $errors[] =
                    'Item '
                    . ($index + 1)
                    . ': URL harus berupa path internal, anchor, atau URL http/https yang valid.';
            }

            $itemKey = $this->cleanItemKey(
                (string) (
                    $postedItem['item_key'] ?? ''
                ),
                $label,
                $index
            );

            while (isset($usedKeys[$itemKey])) {
                $itemKey .= '-' . ($index + 1);
            }

            $usedKeys[$itemKey] = true;

            $activePages = preg_split(
                '/[\s,]+/',
                $activeInput,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            if (!is_array($activePages)) {
                $activePages = [];
            }

            $activePages = array_values(array_unique(
                array_filter(array_map(
                    static function ($value): string {
                        $value = strtolower(trim(
                            (string) $value
                        ));

                        return preg_replace(
                            '/[^a-z0-9_-]/',
                            '',
                            $value
                        ) ?: '';
                    },
                    $activePages
                ))
            ));

            $cleanItems[] = [
                'item_key' => $itemKey,
                'label' => $label,
                'url' => $url,
                'active_pages' => $activePages,
                'target' => $target,
                'style' => $style,
                'enabled' => $enabled,
            ];
        }

        $enabledCount = count(array_filter(
            $cleanItems,
            static fn (array $item): bool =>
                !empty($item['enabled'])
        ));

        if ($enabledCount < 1) {
            $errors[] =
                'Minimal satu item navigasi harus aktif.';
        }

        if ($errors !== []) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }

        $encoded = json_encode(
            $cleanItems,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        if ($encoded === false) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Draft navigasi belum dapat diproses.'
                );
        }

        try {
            $this->menuModel->update(
                (int) $menu['id'],
                [
                    'draft_items' => $encoded,
                    'revision_note' =>
                        $revisionNote !== ''
                            ? $revisionNote
                            : null,
                    'has_unpublished_changes' => 1,
                    'last_edited_by' =>
                        $this->currentUserId(),
                ]
            );
        } catch (\Throwable $exception) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Draft navigasi belum dapat disimpan.'
                );
        }

        return redirect()->to(
            '/website/navigation/edit/' . $menuKey
        )->with(
            'success',
            'Draft navigasi berhasil disimpan.'
        );
    }

    public function publish(string $menuKey)
    {
        $this->assertReady();

        $menu = $this->menuModel
            ->findByKey($menuKey);

        if (!$menu) {
            return redirect()
                ->to('/website/navigation')
                ->with(
                    'error',
                    'Menu navigasi tidak ditemukan.'
                );
        }

        try {
            $this->menuModel->update(
                (int) $menu['id'],
                [
                    'published_items' =>
                        $menu['draft_items'],
                    'published_by' =>
                        $this->currentUserId(),
                    'published_at' =>
                        date('Y-m-d H:i:s'),
                    'has_unpublished_changes' => 0,
                ]
            );
        } catch (\Throwable $exception) {
            return redirect()->back()->with(
                'error',
                'Navigasi belum dapat dipublikasikan.'
            );
        }

        return redirect()->to(
            '/website/navigation/edit/' . $menuKey
        )->with(
            'success',
            'Navigasi website berhasil dipublikasikan.'
        );
    }

    public function restore(string $menuKey)
    {
        $this->assertReady();

        $menu = $this->menuModel
            ->findByKey($menuKey);

        if (
            !$menu
            || empty($menu['published_items'])
        ) {
            return redirect()->back()->with(
                'error',
                'Versi navigasi terpublikasi belum tersedia.'
            );
        }

        try {
            $this->menuModel->update(
                (int) $menu['id'],
                [
                    'draft_items' =>
                        $menu['published_items'],
                    'revision_note' => null,
                    'has_unpublished_changes' => 0,
                    'last_edited_by' =>
                        $this->currentUserId(),
                ]
            );
        } catch (\Throwable $exception) {
            return redirect()->back()->with(
                'error',
                'Draft navigasi belum dapat dipulihkan.'
            );
        }

        return redirect()->to(
            '/website/navigation/edit/' . $menuKey
        )->with(
            'success',
            'Draft dikembalikan ke versi terpublikasi.'
        );
    }

    public function preview(string $menuKey)
    {
        $this->assertReady();

        $definition = $this->definition($menuKey);
        $route = (string) (
            $definition['preview_route'] ?? '/'
        );

        return redirect()->to(
            $route
            . (
                str_contains($route, '?')
                    ? '&'
                    : '?'
            )
            . 'nav_preview=1'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(
        string $menuKey
    ): array {
        $definition = $this->navigationConfig
            ->menus[$menuKey] ?? null;

        if (!is_array($definition)) {
            throw new RuntimeException(
                'Definisi navigasi tidak ditemukan.'
            );
        }

        return $definition;
    }

    private function navigationReady(): bool
    {
        return db_connect()->tableExists(
            'website_navigation_menus'
        );
    }

    private function assertReady(): void
    {
        if (!$this->navigationReady()) {
            throw new RuntimeException(
                'Navigation Manager belum tersedia. Jalankan php spark migrate.'
            );
        }
    }

    private function currentUserId(): ?int
    {
        $userId = session()->get('user_id');

        return $userId !== null
            ? (int) $userId
            : null;
    }

    private function validNavigationUrl(
        string $value
    ): bool {
        if (str_starts_with($value, '#')) {
            return mb_strlen($value) > 1;
        }

        if (str_starts_with($value, '/')) {
            return !str_starts_with($value, '//');
        }

        if (!preg_match('#^https?://#i', $value)) {
            return false;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_URL
        ) !== false;
    }

    private function cleanItemKey(
        string $value,
        string $label,
        int $index
    ): string {
        $value = strtolower(trim($value));

        if ($value === '') {
            $value = strtolower(trim($label));
        }

        $value = preg_replace(
            '/[^a-z0-9_-]+/',
            '-',
            $value
        ) ?: '';

        $value = trim($value, '-_');

        return $value !== ''
            ? mb_substr($value, 0, 80)
            : 'item-' . ($index + 1);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function decodeItems($value): array
    {
        $decoded = json_decode(
            (string) $value,
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
