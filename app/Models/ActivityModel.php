<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityModel extends Model
{
    protected $table      = 'activities';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'program_id',
        'title',
        'activity_date',
        'location',
        'description',
        'summary',
        'result',
        'documentation_link',
        'documentation_file',
        'status',
        'publication_status',
        'is_public',
        'is_featured',
        'scheduled_at',
        'published_at',
        'review_notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const EXECUTION_STATUSES = [
        'planned',
        'completed',
        'cancelled',
    ];

    public const PUBLICATION_STATUSES = [
        'draft',
        'review',
        'published',
        'scheduled',
        'archived',
    ];

    public static function executionStatusLabels(): array
    {
        return [
            'planned'   => 'Direncanakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    public static function publicationStatusLabels(): array
    {
        return [
            'draft'     => 'Draft',
            'review'    => 'Menunggu Tinjauan',
            'published' => 'Dipublikasikan',
            'scheduled' => 'Dijadwalkan',
            'archived'  => 'Diarsipkan',
        ];
    }

    public static function publicationStatusDescriptions(): array
    {
        return [
            'draft' =>
                'Konten masih disusun dan belum terlihat di website publik.',

            'review' =>
                'Konten sudah diajukan dan menunggu pemeriksaan pengurus.',

            'published' =>
                'Konten sedang ditampilkan pada website publik.',

            'scheduled' =>
                'Konten akan tampil otomatis sesuai waktu yang ditentukan.',

            'archived' =>
                'Konten disimpan sebagai arsip dan tidak tampil publik.',
        ];
    }

    /**
     * Terapkan batas visibilitas untuk query website publik.
     */
    public function applyPublicVisibility(): self
    {
        $now = date('Y-m-d H:i:s');

        $this
            ->where('activities.is_public', 1)
            ->groupStart()
                ->where(
                    'activities.publication_status',
                    'published'
                )
                ->orGroupStart()
                    ->where(
                        'activities.publication_status',
                        'scheduled'
                    )
                    ->where(
                        'activities.scheduled_at IS NOT NULL',
                        null,
                        false
                    )
                    ->where(
                        'activities.scheduled_at <=',
                        $now
                    )
                ->groupEnd()
            ->groupEnd();

        return $this;
    }

    /**
     * Pemeriksaan sederhana untuk satu record kegiatan.
     */
    public static function isVisibleToPublic(array $activity): bool
    {
        if ((int) ($activity['is_public'] ?? 0) !== 1) {
            return false;
        }

        $status = $activity['publication_status'] ?? 'draft';

        if ($status === 'published') {
            return true;
        }

        if ($status !== 'scheduled') {
            return false;
        }

        $scheduledAt = $activity['scheduled_at'] ?? null;

        if (empty($scheduledAt)) {
            return false;
        }

        $timestamp = strtotime((string) $scheduledAt);

        return $timestamp !== false && $timestamp <= time();
    }
}
