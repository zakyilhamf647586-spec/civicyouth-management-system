<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'role_name',
        'description',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function orderedRoles(): array
    {
        return $this
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function findAdminRole(): ?array
    {
        return $this
            ->where('role_name', 'Admin')
            ->first();
    }
}
