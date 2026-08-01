<?php

namespace App\Libraries;

use App\Models\PublicPageModel;
use App\Models\PublicPageRevisionModel;
use App\Models\PublicPageSectionModel;
use RuntimeException;

class PublicPageRevisionService
{
    protected PublicPageModel $pageModel;
    protected PublicPageSectionModel $sectionModel;
    protected PublicPageRevisionModel $revisionModel;

    public function __construct()
    {
        $this->pageModel = new PublicPageModel();
        $this->sectionModel =
            new PublicPageSectionModel();
        $this->revisionModel =
            new PublicPageRevisionModel();
    }

    public function ready(): bool
    {
        try {
            return db_connect()->tableExists(
                'public_page_revisions'
            );
        } catch (\Throwable $exception) {
            return false;
        }
    }

    public function countForPage(
        int $pageId
    ): int {
        if (!$this->ready()) {
            return 0;
        }

        return $this->revisionModel
            ->countForPage($pageId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function historyForPage(
        int $pageId
    ): array {
        if (!$this->ready()) {
            return [];
        }

        return $this->revisionModel
            ->historyForPage($pageId);
    }

    public function findForPage(
        int $pageId,
        int $revisionId
    ): ?array {
        if (!$this->ready()) {
            return null;
        }

        return $this->revisionModel
            ->findForPage(
                $pageId,
                $revisionId
            );
    }

    public function capture(
        int $pageId,
        string $mode,
        string $sourceType,
        ?int $actorId,
        ?string $revisionNote = null,
        ?int $sourceRevisionId = null
    ): int {
        if (!$this->ready()) {
            throw new RuntimeException(
                'Revision History belum tersedia.'
            );
        }

        $mode = $mode === 'published'
            ? 'published'
            : 'draft';

        $page = $this->pageModel
            ->find($pageId);

        if (!$page) {
            throw new RuntimeException(
                'Halaman untuk snapshot tidak ditemukan.'
            );
        }

        $snapshot = $this->buildSnapshot(
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
                'Snapshot halaman gagal diproses.'
            );
        }

        $versionNumber = $this->revisionModel
            ->nextVersionNumber($pageId);

        $inserted = $this->revisionModel->insert([
            'public_page_id' => $pageId,
            'version_number' => $versionNumber,
            'source_type' => $sourceType,
            'snapshot_mode' => $mode,
            'workflow_status' =>
                $page['workflow_status'] ?? null,
            'revision_note' =>
                $this->cleanNote($revisionNote),
            'source_revision_id' =>
                $sourceRevisionId,
            'snapshot_data' => $encoded,
            'created_by' => $actorId,
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        if (!$inserted) {
            throw new RuntimeException(
                'Snapshot halaman gagal disimpan.'
            );
        }

        return (int) $inserted;
    }

    /**
     * @return array<string, mixed>
     */
    public function decode(
        array $revision
    ): array {
        $decoded = json_decode(
            (string) (
                $revision['snapshot_data'] ?? ''
            ),
            true
        );

        if (!is_array($decoded)) {
            throw new RuntimeException(
                'Data snapshot versi tidak valid.'
            );
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    public function compareWithCurrentDraft(
        array $revision,
        int $pageId
    ): array {
        $revisionSnapshot = $this->decode(
            $revision
        );

        $page = $this->pageModel->find($pageId);

        if (!$page) {
            throw new RuntimeException(
                'Halaman pembanding tidak ditemukan.'
            );
        }

        $currentSnapshot = $this->buildSnapshot(
            $page,
            'draft'
        );

        return $this->compareSnapshots(
            $revisionSnapshot,
            $currentSnapshot
        );
    }

    public function restoreToDraft(
        int $pageId,
        array $revision,
        ?int $actorId
    ): int {
        if (!$this->ready()) {
            throw new RuntimeException(
                'Revision History belum tersedia.'
            );
        }

        if (
            (int) (
                $revision['public_page_id'] ?? 0
            ) !== $pageId
        ) {
            throw new RuntimeException(
                'Versi tidak berasal dari halaman ini.'
            );
        }

        $snapshot = $this->decode($revision);
        $pageData = $snapshot['page'] ?? null;
        $sectionSnapshots =
            $snapshot['sections'] ?? null;

        if (
            !is_array($pageData)
            || !is_array($sectionSnapshots)
        ) {
            throw new RuntimeException(
                'Struktur snapshot versi tidak lengkap.'
            );
        }

        $page = $this->pageModel->find($pageId);

        if (!$page) {
            throw new RuntimeException(
                'Halaman tujuan tidak ditemukan.'
            );
        }

        $sections = $this->sectionModel
            ->where('public_page_id', $pageId)
            ->findAll();

        $snapshotMap = [];

        foreach ($sectionSnapshots as $section) {
            if (
                !is_array($section)
                || empty($section['section_key'])
            ) {
                continue;
            }

            $snapshotMap[
                (string) $section['section_key']
            ] = $section;
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $versionNumber = (int) (
                $revision['version_number'] ?? 0
            );

            $this->pageModel->update(
                $pageId,
                [
                    'draft_title' =>
                        $pageData['title'] ?? null,
                    'draft_title_en' =>
                        $pageData['title_en'] ?? null,
                    'draft_meta_description' =>
                        $pageData[
                            'meta_description'
                        ] ?? null,
                    'draft_meta_description_en' =>
                        $pageData[
                            'meta_description_en'
                        ] ?? null,
                    'has_unpublished_changes' => 1,
                    'workflow_status' => 'draft',
                    'revision_note' =>
                        'Dipulihkan dari versi #'
                        . $versionNumber,
                    'submitted_by' => null,
                    'submitted_at' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'last_edited_by' => $actorId,
                ]
            );

            foreach ($sections as $section) {
                $sectionKey = (string) (
                    $section['section_key'] ?? ''
                );

                if (!isset($snapshotMap[$sectionKey])) {
                    continue;
                }

                $sectionSnapshot =
                    $snapshotMap[$sectionKey];

                $content = json_encode(
                    is_array(
                        $sectionSnapshot['content']
                        ?? null
                    )
                        ? $sectionSnapshot['content']
                        : [],
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );

                if ($content === false) {
                    throw new RuntimeException(
                        'Konten section snapshot gagal diproses.'
                    );
                }

                $contentEn = json_encode(
                    is_array(
                        $sectionSnapshot['content_en']
                        ?? null
                    )
                        ? $sectionSnapshot['content_en']
                        : [],
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                );

                if ($contentEn === false) {
                    throw new RuntimeException(
                        'Konten English section snapshot gagal diproses.'
                    );
                }

                $this->sectionModel->update(
                    (int) $section['id'],
                    [
                        'draft_content' => $content,
                        'draft_content_en' => $contentEn,
                        'draft_enabled' =>
                            !empty(
                                $sectionSnapshot[
                                    'enabled'
                                ]
                            ) ? 1 : 0,
                        'updated_by' => $actorId,
                    ]
                );
            }

            $newRevisionId = $this->capture(
                $pageId,
                'draft',
                'rollback',
                $actorId,
                'Rollback dari versi #'
                    . $versionNumber,
                (int) $revision['id']
            );

            if ($db->transCommit() === false) {
                throw new RuntimeException(
                    'Pemulihan versi belum dapat diselesaikan.'
                );
            }

            return $newRevisionId;
        } catch (\Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $page
     * @return array<string, mixed>
     */
    private function buildSnapshot(
        array $page,
        string $mode
    ): array {
        $mode = $mode === 'published'
            ? 'published'
            : 'draft';

        $sections = $this->sectionModel
            ->where(
                'public_page_id',
                (int) $page['id']
            )
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $sectionSnapshots = [];

        foreach ($sections as $section) {
            $decoded = json_decode(
                (string) (
                    $section[$mode . '_content']
                    ?? ''
                ),
                true
            );

            $decodedEn = json_decode(
                (string) (
                    $section[$mode . '_content_en']
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
                'content_en' => is_array($decodedEn)
                    ? $decodedEn
                    : [],
            ];
        }

        return [
            'schema_version' => 2,
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
                'title_en' =>
                    $page[$mode . '_title_en'] ?? null,
                'meta_description' =>
                    $page[
                        $mode . '_meta_description'
                    ] ?? null,
                'meta_description_en' =>
                    $page[
                        $mode . '_meta_description_en'
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

    /**
     * @param array<string, mixed> $left
     * @param array<string, mixed> $right
     * @return array<string, mixed>
     */
    private function compareSnapshots(
        array $left,
        array $right
    ): array {
        $leftPage = is_array(
            $left['page'] ?? null
        )
            ? $left['page']
            : [];

        $rightPage = is_array(
            $right['page'] ?? null
        )
            ? $right['page']
            : [];

        $titleChanged = (
            $leftPage['title'] ?? null
        ) !== (
            $rightPage['title'] ?? null
        );

        $metaChanged = (
            $leftPage['meta_description'] ?? null
        ) !== (
            $rightPage['meta_description'] ?? null
        );

        $titleEnChanged = (
            $leftPage['title_en'] ?? null
        ) !== (
            $rightPage['title_en'] ?? null
        );

        $metaEnChanged = (
            $leftPage['meta_description_en'] ?? null
        ) !== (
            $rightPage['meta_description_en'] ?? null
        );

        $leftSections = $this->sectionMap(
            $left['sections'] ?? []
        );

        $rightSections = $this->sectionMap(
            $right['sections'] ?? []
        );

        $sectionKeys = array_values(array_unique(
            array_merge(
                array_keys($leftSections),
                array_keys($rightSections)
            )
        ));

        $sectionChanges = [];
        $changedFieldCount = 0;

        foreach ($sectionKeys as $sectionKey) {
            $leftSection =
                $leftSections[$sectionKey] ?? null;

            $rightSection =
                $rightSections[$sectionKey] ?? null;

            $enabledChanged = (
                $leftSection['enabled'] ?? null
            ) !== (
                $rightSection['enabled'] ?? null
            );

            $leftFields = $this->flatten(
                is_array(
                    $leftSection['content'] ?? null
                )
                    ? $leftSection['content']
                    : []
            );

            $rightFields = $this->flatten(
                is_array(
                    $rightSection['content'] ?? null
                )
                    ? $rightSection['content']
                    : []
            );

            foreach ($this->flatten(
                is_array(
                    $leftSection['content_en'] ?? null
                )
                    ? $leftSection['content_en']
                    : []
            ) as $fieldKey => $fieldValue) {
                $leftFields['en.' . $fieldKey] = $fieldValue;
            }

            foreach ($this->flatten(
                is_array(
                    $rightSection['content_en'] ?? null
                )
                    ? $rightSection['content_en']
                    : []
            ) as $fieldKey => $fieldValue) {
                $rightFields['en.' . $fieldKey] = $fieldValue;
            }

            $fieldKeys = array_values(array_unique(
                array_merge(
                    array_keys($leftFields),
                    array_keys($rightFields)
                )
            ));

            $differentFields = [];

            foreach ($fieldKeys as $fieldKey) {
                if (
                    ($leftFields[$fieldKey] ?? null)
                    !==
                    ($rightFields[$fieldKey] ?? null)
                ) {
                    $differentFields[] = $fieldKey;
                }
            }

            $changedFieldCount += count(
                $differentFields
            );

            if (
                $enabledChanged
                || $differentFields !== []
                || $leftSection === null
                || $rightSection === null
            ) {
                $sectionChanges[] = [
                    'section_key' => $sectionKey,
                    'section_name' =>
                        $leftSection['section_name']
                        ?? $rightSection['section_name']
                        ?? $sectionKey,
                    'enabled_changed' =>
                        $enabledChanged,
                    'changed_fields' =>
                        $differentFields,
                ];
            }
        }

        return [
            'title_changed' => $titleChanged,
            'meta_changed' => $metaChanged,
            'title_en_changed' => $titleEnChanged,
            'meta_en_changed' => $metaEnChanged,
            'changed_section_count' =>
                count($sectionChanges),
            'changed_field_count' =>
                $changedFieldCount,
            'section_changes' => $sectionChanges,
            'has_changes' =>
                $titleChanged
                || $metaChanged
                || $titleEnChanged
                || $metaEnChanged
                || $sectionChanges !== [],
        ];
    }

    /**
     * @param mixed $sections
     * @return array<string, array<string, mixed>>
     */
    private function sectionMap($sections): array
    {
        if (!is_array($sections)) {
            return [];
        }

        $map = [];

        foreach ($sections as $section) {
            if (
                !is_array($section)
                || empty($section['section_key'])
            ) {
                continue;
            }

            $map[(string) $section['section_key']] =
                $section;
        }

        return $map;
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, string>
     */
    private function flatten(
        array $values,
        string $prefix = ''
    ): array {
        $flat = [];

        foreach ($values as $key => $value) {
            $path = $prefix === ''
                ? (string) $key
                : $prefix . '.' . $key;

            if (is_array($value)) {
                $flat += $this->flatten(
                    $value,
                    $path
                );
                continue;
            }

            $flat[$path] = (string) $value;
        }

        return $flat;
    }

    private function cleanNote(
        ?string $note
    ): ?string {
        $note = trim(strip_tags(
            (string) $note
        ));

        if ($note === '') {
            return null;
        }

        return mb_substr($note, 0, 255);
    }
}
