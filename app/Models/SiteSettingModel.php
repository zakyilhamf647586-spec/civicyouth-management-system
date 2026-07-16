<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteSettingModel extends Model
{
    protected $table      = 'site_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'setting_group',
        'setting_type',
        'label',
        'description',
        'sort_order',
        'is_public',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getSettingsArray(
        bool $publicOnly = false
    ): array {
        $builder = $this
            ->orderBy('setting_group', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($publicOnly) {
            $builder->where('is_public', 1);
        }

        $rows = $builder->findAll();
        $settings = [];

        foreach ($rows as $row) {
            $settings[$row['setting_key']] =
                $row['setting_value'];
        }

        return $settings;
    }

    public function getValue(
        string $key,
        ?string $default = null
    ): ?string {
        $row = $this
            ->where('setting_key', $key)
            ->first();

        if (!$row) {
            return $default;
        }

        return $row['setting_value'] ?? $default;
    }

    public function saveValues(array $values): bool
    {
        $database = db_connect();
        $database->transStart();

        foreach ($values as $key => $value) {
            $existing = $this
                ->where('setting_key', $key)
                ->first();

            if (!$existing) {
                continue;
            }

            $this->update($existing['id'], [
                'setting_value' => $value,
            ]);
        }

        $database->transComplete();

        return $database->transStatus() !== false;
    }
}