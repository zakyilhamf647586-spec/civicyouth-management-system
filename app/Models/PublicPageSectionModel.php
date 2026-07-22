<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicPageSectionModel extends Model
{
    protected $table = 'public_page_sections';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'public_page_id',
        'section_key',
        'section_name',
        'display_order',
        'draft_content',
        'published_content',
        'draft_enabled',
        'published_enabled',
        'updated_by',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
