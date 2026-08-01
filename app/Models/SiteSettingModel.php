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
        'setting_value_en',
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
        bool $publicOnly = false,
        string $locale = 'id'
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
            $value = $row['setting_value'];

            if (
                $locale === 'en'
                && trim((string) (
                    $row['setting_value_en'] ?? ''
                )) !== ''
            ) {
                $value = $row['setting_value_en'];
            }

            $settings[$row['setting_key']] = $value;
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

    public function getEnglishSettingsArray(
        bool $publicOnly = false
    ): array {
        $builder = $this
            ->orderBy('setting_group', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($publicOnly) {
            $builder->where('is_public', 1);
        }

        $settings = [];

        foreach ($builder->findAll() as $row) {
            $settings[$row['setting_key']] =
                $row['setting_value_en'] ?? null;
        }

        return $settings;
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

    public function saveEnglishValues(array $values): bool
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
                'setting_value_en' => $value,
            ]);
        }

        $database->transComplete();

        return $database->transStatus() !== false;
    }

    public function saveLocalizedValues(
        array $values,
        array $englishValues
    ): bool {
        $database = db_connect();
        $database->transBegin();

        try {
            $keys = array_values(array_unique(array_merge(
                array_keys($values),
                array_keys($englishValues)
            )));

            if ($keys !== []) {
                $rows = $this
                    ->select('id, setting_key')
                    ->whereIn('setting_key', $keys)
                    ->findAll();

                foreach ($rows as $row) {
                    $key = (string) $row['setting_key'];
                    $update = [];

                    if (array_key_exists($key, $values)) {
                        $update['setting_value'] = $values[$key];
                    }

                    if (array_key_exists($key, $englishValues)) {
                        $update['setting_value_en'] =
                            $englishValues[$key];
                    }

                    if ($update !== []) {
                        $this->update((int) $row['id'], $update);
                    }
                }
            }

            if ($database->transCommit() === false) {
                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            $database->transRollback();

            return false;
        }
    }
}
