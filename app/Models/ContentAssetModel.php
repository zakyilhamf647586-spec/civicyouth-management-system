<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentAssetModel extends Model
{
    protected $table = 'content_assets';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'content_post_id',
        'image_path',
        'original_name',
        'sort_order',
    ];

    protected $useTimestamps = true;
}