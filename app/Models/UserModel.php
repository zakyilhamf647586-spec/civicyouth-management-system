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

    public function findWithRole(int $userId): ?array
    {
        return $this
            ->select(
                'users.*, roles.role_name, ' .
                'roles.description AS role_description'
            )
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.id', $userId)
            ->first();
    }

    public function emailExists(
        string $email,
        ?int $ignoreUserId = null
    ): bool {
        $builder = $this->db
            ->table($this->table)
            ->where(
                'email',
                mb_strtolower(trim($email))
            );

        if ($ignoreUserId !== null) {
            $builder->where('id !=', $ignoreUserId);
        }

        return $builder->countAllResults() > 0;
    }

    public function accountStatistics(): array
    {
        $summary = $this->db
            ->table($this->table)
            ->select(
                "
                COUNT(*) AS total,
                SUM(status = 'active') AS active,
                SUM(status = 'inactive') AS inactive
                ",
                false
            )
            ->get()
            ->getRowArray();

        $adminRole = $this->db
            ->table('roles')
            ->where('role_name', 'Admin')
            ->get()
            ->getRowArray();

        $activeAdmins = 0;

        if ($adminRole) {
            $activeAdmins = $this->db
                ->table($this->table)
                ->where('role_id', (int) $adminRole['id'])
                ->where('status', 'active')
                ->countAllResults();
        }

        return [
            'total' => (int) ($summary['total'] ?? 0),
            'active' => (int) ($summary['active'] ?? 0),
            'inactive' => (int) ($summary['inactive'] ?? 0),
            'active_admins' => $activeAdmins,
        ];
    }

    public function countActiveByRoleId(int $roleId): int
    {
        return $this->db
            ->table($this->table)
            ->where('role_id', $roleId)
            ->where('status', 'active')
            ->countAllResults();
    }
}
