<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityImageModel extends Model
{
    protected $table      = 'activity_images';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'activity_id',
        'image_file',
        'caption',
        'is_cover',
        'display_order',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActivityImages(int $activityId): array
    {
        return $this
            ->where('activity_id', $activityId)
            ->orderBy('is_cover', 'DESC')
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getCoverImage(int $activityId): ?array
    {
        return $this
            ->where('activity_id', $activityId)
            ->where('is_cover', 1)
            ->first();
    }
}