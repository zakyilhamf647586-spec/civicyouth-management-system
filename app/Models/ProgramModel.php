<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramModel extends Model
{
    protected $table = 'programs';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name',
        'slug',
        'label',
        'tagline',
        'short_description',
        'description',
        'focus_items',
        'campaign_items',
        'icon',
        'cover_image',
        'status',
        'display_order',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[150]',
        'slug' => 'required|min_length[2]|max_length[150]|alpha_dash',
        'status' => 'required|in_list[draft,published,archived]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Nama program wajib diisi.',
        ],
        'slug' => [
            'required' => 'Slug program wajib diisi.',
            'alpha_dash' => 'Slug hanya boleh berisi huruf, angka, garis bawah, dan tanda hubung.',
        ],
    ];

    public function getPublishedPrograms(): array
    {
        $programs = $this
            ->where('status', 'published')
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return array_map(
            fn (array $program): array => $this->prepareProgram($program),
            $programs
        );
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $program = $this
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$program) {
            return null;
        }

        return $this->prepareProgram($program);
    }

    public function prepareProgram(array $program): array
    {
        $program['focus'] = $this->decodeList(
            $program['focus_items'] ?? null
        );

        $program['campaigns'] = $this->decodeList(
            $program['campaign_items'] ?? null
        );

        $program['number'] = str_pad(
            (string) ($program['display_order'] ?? 0),
            2,
            '0',
            STR_PAD_LEFT
        );

        return $program;
    }

    private function decodeList(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}