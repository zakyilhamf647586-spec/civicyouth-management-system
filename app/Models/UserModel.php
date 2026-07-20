<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'role_id',
        'name',
        'email',
        'password',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByEmailWithRole(string $email): ?array
    {
        return $this
            ->select('users.*, roles.role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.email', mb_strtolower(trim($email)))
            ->first();
    }

    public function findActiveWithRole(int $userId): ?array
    {
        return $this
            ->select('users.*, roles.role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.id', $userId)
            ->where('users.status', 'active')
            ->first();
    }
}
