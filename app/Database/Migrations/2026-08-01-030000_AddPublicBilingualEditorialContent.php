<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\PublicExperience;
use RuntimeException;

class AddPublicBilingualEditorialContent extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $dictionary = [];

    /**
     * @var array<string, string>
     */
    private array $normalizedDictionary = [];

    public function up()
    {
        $this->addColumns();
        $this->prepareDictionary();
        $this->backfillEnglishContent();
    }

    public function down()
    {
        $this->removeNavigationEnglishLabels();

        $columns = [
            'public_pages' => [
                'draft_title_en',
                'published_title_en',
                'draft_meta_description_en',
                'published_meta_description_en',
            ],
            'public_page_sections' => [
                'draft_content_en',
                'published_content_en',
            ],
            'programs' => [
                'name_en',
                'label_en',
                'tagline_en',
                'short_description_en',
                'description_en',
                'focus_items_en',
                'campaign_items_en',
            ],
            'activities' => [
                'title_en',
                'location_en',
                'summary_en',
                'description_en',
                'result_en',
            ],
            'activity_images' => [
                'caption_en',
            ],
            'organizational_structures' => [
                'position_name_en',
                'division_en',
                'description_en',
                'short_bio_en',
            ],
            'site_settings' => [
                'setting_value_en',
            ],
        ];

        foreach ($columns as $table => $tableColumns) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            foreach ($tableColumns as $column) {
                if ($this->db->fieldExists($column, $table)) {
                    $this->forge->dropColumn($table, $column);
                }
            }
        }
    }

    private function addColumns(): void
    {
        $this->addColumnIfMissing('public_pages', 'draft_title_en', [
            'type' => 'VARCHAR',
            'constraint' => 180,
            'null' => true,
            'after' => 'draft_title',
        ]);
        $this->addColumnIfMissing('public_pages', 'published_title_en', [
            'type' => 'VARCHAR',
            'constraint' => 180,
            'null' => true,
            'after' => 'published_title',
        ]);
        $this->addColumnIfMissing('public_pages', 'draft_meta_description_en', [
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => true,
            'after' => 'draft_meta_description',
        ]);
        $this->addColumnIfMissing('public_pages', 'published_meta_description_en', [
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => true,
            'after' => 'published_meta_description',
        ]);

        $this->addColumnIfMissing('public_page_sections', 'draft_content_en', [
            'type' => 'LONGTEXT',
            'null' => true,
            'after' => 'draft_content',
        ]);
        $this->addColumnIfMissing('public_page_sections', 'published_content_en', [
            'type' => 'LONGTEXT',
            'null' => true,
            'after' => 'published_content',
        ]);

        $this->addColumnIfMissing('programs', 'name_en', [
            'type' => 'VARCHAR',
            'constraint' => 150,
            'null' => true,
            'after' => 'name',
        ]);
        $this->addColumnIfMissing('programs', 'label_en', [
            'type' => 'VARCHAR',
            'constraint' => 150,
            'null' => true,
            'after' => 'label',
        ]);
        $this->addColumnIfMissing('programs', 'tagline_en', [
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => true,
            'after' => 'tagline',
        ]);
        $this->addColumnIfMissing('programs', 'short_description_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'short_description',
        ]);
        $this->addColumnIfMissing('programs', 'description_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'description',
        ]);
        $this->addColumnIfMissing('programs', 'focus_items_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'focus_items',
        ]);
        $this->addColumnIfMissing('programs', 'campaign_items_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'campaign_items',
        ]);

        $this->addColumnIfMissing('activities', 'title_en', [
            'type' => 'VARCHAR',
            'constraint' => 150,
            'null' => true,
            'after' => 'title',
        ]);
        $this->addColumnIfMissing('activities', 'location_en', [
            'type' => 'VARCHAR',
            'constraint' => 200,
            'null' => true,
            'after' => 'location',
        ]);
        $this->addColumnIfMissing('activities', 'summary_en', [
            'type' => 'VARCHAR',
            'constraint' => 220,
            'null' => true,
            'after' => 'summary',
        ]);
        $this->addColumnIfMissing('activities', 'description_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'description',
        ]);
        $this->addColumnIfMissing('activities', 'result_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'result',
        ]);

        $this->addColumnIfMissing('activity_images', 'caption_en', [
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => true,
            'after' => 'caption',
        ]);

        $this->addColumnIfMissing('organizational_structures', 'position_name_en', [
            'type' => 'VARCHAR',
            'constraint' => 150,
            'null' => true,
            'after' => 'position_name',
        ]);
        $this->addColumnIfMissing('organizational_structures', 'division_en', [
            'type' => 'VARCHAR',
            'constraint' => 150,
            'null' => true,
            'after' => 'division',
        ]);
        $this->addColumnIfMissing('organizational_structures', 'description_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'description',
        ]);
        $this->addColumnIfMissing('organizational_structures', 'short_bio_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'short_bio',
        ]);

        $this->addColumnIfMissing('site_settings', 'setting_value_en', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'setting_value',
        ]);
    }

    private function addColumnIfMissing(
        string $table,
        string $column,
        array $definition
    ): void {
        if (
            !$this->db->tableExists($table)
            || $this->db->fieldExists($column, $table)
        ) {
            return;
        }

        $this->forge->addColumn($table, [
            $column => $definition,
        ]);
    }

    private function prepareDictionary(): void
    {
        $config = new PublicExperience();
        $this->dictionary = $config->englishDictionary;

        foreach ($config->copy as $definition) {
            if (
                !is_array($definition)
                || empty($definition['id'])
                || !isset($definition['en'])
            ) {
                continue;
            }

            $this->dictionary[(string) $definition['id']] =
                (string) $definition['en'];
        }

        foreach ($this->dictionary as $source => $translation) {
            $normalized = $this->normalize($source);

            if (
                $normalized !== ''
                && !isset($this->normalizedDictionary[$normalized])
            ) {
                $this->normalizedDictionary[$normalized] = $translation;
            }
        }
    }

    private function backfillEnglishContent(): void
    {
        $this->db->transBegin();

        try {
            $this->backfillPublicPages();
            $this->backfillPublicPageSections();
            $this->backfillPrograms();
            $this->backfillActivities();
            $this->backfillActivityImages();
            $this->backfillStructures();
            $this->backfillSiteSettings();
            $this->backfillNavigation();
            $this->upgradeRevisionSnapshots();

            if ($this->db->transCommit() === false) {
                throw new RuntimeException(
                    'Konten bilingual awal belum dapat disimpan.'
                );
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    private function backfillPublicPages(): void
    {
        if (!$this->db->tableExists('public_pages')) {
            return;
        }

        $builder = $this->db->table('public_pages');

        foreach ($builder->get()->getResultArray() as $row) {
            $builder->where('id', (int) $row['id'])->update([
                'draft_title_en' => $this->translatedOrNull(
                    $row['draft_title'] ?? null
                ),
                'published_title_en' => $this->translatedOrNull(
                    $row['published_title'] ?? null
                ),
                'draft_meta_description_en' => $this->translatedOrNull(
                    $row['draft_meta_description'] ?? null
                ),
                'published_meta_description_en' => $this->translatedOrNull(
                    $row['published_meta_description'] ?? null
                ),
            ]);
        }
    }

    private function backfillPublicPageSections(): void
    {
        if (!$this->db->tableExists('public_page_sections')) {
            return;
        }

        $builder = $this->db->table('public_page_sections');

        foreach ($builder->get()->getResultArray() as $row) {
            $builder->where('id', (int) $row['id'])->update([
                'draft_content_en' => $this->translateJson(
                    $row['draft_content'] ?? null
                ),
                'published_content_en' => $this->translateJson(
                    $row['published_content'] ?? null
                ),
            ]);
        }
    }

    private function backfillPrograms(): void
    {
        if (!$this->db->tableExists('programs')) {
            return;
        }

        $builder = $this->db->table('programs');
        $fields = [
            'name',
            'label',
            'tagline',
            'short_description',
            'description',
        ];

        foreach ($builder->get()->getResultArray() as $row) {
            $values = [];

            foreach ($fields as $field) {
                $values[$field . '_en'] = $this->translatedOrNull(
                    $row[$field] ?? null
                );
            }

            $values['focus_items_en'] = $this->translateJson(
                $row['focus_items'] ?? null
            );
            $values['campaign_items_en'] = $this->translateJson(
                $row['campaign_items'] ?? null
            );

            $builder->where('id', (int) $row['id'])->update($values);
        }
    }

    private function backfillActivities(): void
    {
        if (!$this->db->tableExists('activities')) {
            return;
        }

        $builder = $this->db->table('activities');
        $fields = [
            'title',
            'location',
            'summary',
            'description',
            'result',
        ];

        foreach ($builder->get()->getResultArray() as $row) {
            $values = [];

            foreach ($fields as $field) {
                $values[$field . '_en'] = $this->translatedOrNull(
                    $row[$field] ?? null
                );
            }

            $builder->where('id', (int) $row['id'])->update($values);
        }
    }

    private function backfillStructures(): void
    {
        if (!$this->db->tableExists('organizational_structures')) {
            return;
        }

        $builder = $this->db->table('organizational_structures');
        $fields = [
            'position_name',
            'division',
            'description',
            'short_bio',
        ];

        foreach ($builder->get()->getResultArray() as $row) {
            $values = [];

            foreach ($fields as $field) {
                $values[$field . '_en'] = $this->translatedOrNull(
                    $row[$field] ?? null
                );
            }

            $builder->where('id', (int) $row['id'])->update($values);
        }
    }

    private function backfillActivityImages(): void
    {
        if (!$this->db->tableExists('activity_images')) {
            return;
        }

        $builder = $this->db->table('activity_images');

        foreach ($builder->get()->getResultArray() as $row) {
            $builder->where('id', (int) $row['id'])->update([
                'caption_en' => $this->translatedOrNull(
                    $row['caption'] ?? null
                ),
            ]);
        }
    }

    private function backfillSiteSettings(): void
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $translatableKeys = [
            'organization_full_name',
            'organization_legal_name',
            'organization_tagline',
            'organization_description',
            'contact_address',
            'contact_village',
            'contact_district',
            'contact_city',
            'contact_province',
            'contact_location_description',
            'contact_office_hours',
            'contact_response_note',
            'footer_heading',
            'footer_description',
            'footer_note',
            'footer_navigation_heading',
            'footer_location_heading',
            'footer_contact_heading',
            'footer_contact_intro',
            'footer_map_label',
            'footer_map_action',
            'footer_copyright',
            'seo_title',
            'seo_description',
            'seo_keywords',
            'seo_og_image_alt',
        ];

        $builder = $this->db->table('site_settings');
        $rows = $builder
            ->whereIn('setting_key', $translatableKeys)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $builder->where('id', (int) $row['id'])->update([
                'setting_value_en' => $this->translatedOrNull(
                    $row['setting_value'] ?? null
                ),
            ]);
        }
    }

    private function backfillNavigation(): void
    {
        if (!$this->db->tableExists('website_navigation_menus')) {
            return;
        }

        $builder = $this->db->table('website_navigation_menus');

        foreach ($builder->get()->getResultArray() as $row) {
            $updates = [];

            foreach (['draft_items', 'published_items'] as $column) {
                $items = json_decode(
                    (string) ($row[$column] ?? ''),
                    true
                );

                if (!is_array($items)) {
                    continue;
                }

                foreach ($items as &$item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $item['label_en'] = $this->translate(
                        (string) ($item['label'] ?? '')
                    );
                }
                unset($item);

                $updates[$column] = json_encode(
                    $items,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );
            }

            if ($updates !== []) {
                $builder->where('id', (int) $row['id'])->update($updates);
            }
        }
    }

    private function upgradeRevisionSnapshots(): void
    {
        if (!$this->db->tableExists('public_page_revisions')) {
            return;
        }

        $builder = $this->db->table('public_page_revisions');

        foreach ($builder->get()->getResultArray() as $row) {
            $snapshot = json_decode(
                (string) ($row['snapshot_data'] ?? ''),
                true
            );

            if (!is_array($snapshot)) {
                continue;
            }

            $page = is_array($snapshot['page'] ?? null)
                ? $snapshot['page']
                : [];
            $page['title_en'] = $this->translatedOrNull(
                $page['title'] ?? null
            );
            $page['meta_description_en'] = $this->translatedOrNull(
                $page['meta_description'] ?? null
            );
            $snapshot['page'] = $page;

            $sections = is_array($snapshot['sections'] ?? null)
                ? $snapshot['sections']
                : [];

            foreach ($sections as &$section) {
                if (!is_array($section)) {
                    continue;
                }

                $section['content_en'] = $this->translateValue(
                    is_array($section['content'] ?? null)
                        ? $section['content']
                        : []
                );
            }
            unset($section);

            $snapshot['sections'] = $sections;
            $snapshot['schema_version'] = 2;
            $encoded = json_encode(
                $snapshot,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            if ($encoded !== false) {
                $builder->where('id', (int) $row['id'])->update([
                    'snapshot_data' => $encoded,
                ]);
            }
        }
    }

    private function removeNavigationEnglishLabels(): void
    {
        if (!$this->db->tableExists('website_navigation_menus')) {
            return;
        }

        $builder = $this->db->table('website_navigation_menus');

        foreach ($builder->get()->getResultArray() as $row) {
            $updates = [];

            foreach (['draft_items', 'published_items'] as $column) {
                $items = json_decode(
                    (string) ($row[$column] ?? ''),
                    true
                );

                if (!is_array($items)) {
                    continue;
                }

                foreach ($items as &$item) {
                    if (is_array($item)) {
                        unset($item['label_en']);
                    }
                }
                unset($item);

                $updates[$column] = json_encode(
                    $items,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );
            }

            if ($updates !== []) {
                $builder->where('id', (int) $row['id'])->update($updates);
            }
        }
    }

    private function translateJson(?string $json): ?string
    {
        if ($json === null || trim($json) === '') {
            return $json;
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return $json;
        }

        $translated = $this->translateValue($decoded);
        $encoded = json_encode(
            $translated,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        return $encoded === false ? $json : $encoded;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function translateValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->translateValue($item);
            }

            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        if (
            preg_match('~^(?:https?://|/|#)~i', trim($value))
            || preg_match('/^-?\d+(?:[.,]\d+)?$/', trim($value))
        ) {
            return $value;
        }

        return $this->translate($value);
    }

    private function translatedOrNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $this->translate((string) $value);
    }

    private function translate(string $text): string
    {
        if (trim($text) === '') {
            return $text;
        }

        $leadingLength = strlen($text) - strlen(ltrim($text));
        $trailingLength = strlen($text) - strlen(rtrim($text));
        $leading = $leadingLength > 0
            ? substr($text, 0, $leadingLength)
            : '';
        $trailing = $trailingLength > 0
            ? substr($text, -$trailingLength)
            : '';
        $value = trim($text);

        if (isset($this->dictionary[$value])) {
            return $leading . $this->dictionary[$value] . $trailing;
        }

        $normalized = $this->normalize($value);

        if (isset($this->normalizedDictionary[$normalized])) {
            return $leading
                . $this->normalizedDictionary[$normalized]
                . $trailing;
        }

        $lines = preg_split('/\R/u', $value);

        if (is_array($lines) && count($lines) > 1) {
            $translatedLines = array_map(
                fn (string $line): string => $this->translate($line),
                $lines
            );

            return $leading
                . implode("\n", $translatedLines)
                . $trailing;
        }

        return $text;
    }

    private function normalize(string $value): string
    {
        $decoded = html_entity_decode(
            $value,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        return preg_replace('/\s+/u', ' ', trim($decoded)) ?: '';
    }
}
