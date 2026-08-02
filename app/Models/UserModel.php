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
        'session_version',
        'must_change_password',
        'password_changed_at',
        'last_login_at',
        'last_login_ip_hash',
        'last_login_user_agent',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    private ?bool $securitySchemaAvailable = null;

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
        $mustChangeSelect = $this->securitySchemaReady()
            ? ",\n                SUM(must_change_password = 1) AS must_change_password"
            : ', 0 AS must_change_password';

        $summary = $this->db
            ->table($this->table)
            ->select(
                "
                COUNT(*) AS total,
                SUM(status = 'active') AS active,
                SUM(status = 'inactive') AS inactive
                " . $mustChangeSelect . "
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
        $defaultAdminActive = false;

        if ($adminRole) {
            $activeAdmins = $this->db
                ->table($this->table)
                ->where('role_id', (int) $adminRole['id'])
                ->where('status', 'active')
                ->countAllResults();

            $defaultAdminActive = $this->db
                ->table($this->table)
                ->where('role_id', (int) $adminRole['id'])
                ->where('status', 'active')
                ->where('email', 'admin@civicyouth.local')
                ->countAllResults() > 0;
        }

        return [
            'total' => (int) ($summary['total'] ?? 0),
            'active' => (int) ($summary['active'] ?? 0),
            'inactive' => (int) ($summary['inactive'] ?? 0),
            'must_change_password' => (int) (
                $summary['must_change_password'] ?? 0
            ),
            'active_admins' => $activeAdmins,
            'default_admin_active' => $defaultAdminActive,
            'personal_active_admins' => max(
                0,
                $activeAdmins - ($defaultAdminActive ? 1 : 0)
            ),
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

    public function securitySchemaReady(): bool
    {
        if ($this->securitySchemaAvailable !== null) {
            return $this->securitySchemaAvailable;
        }

        try {
            $required = [
                'session_version',
                'must_change_password',
                'password_changed_at',
                'last_login_at',
                'last_login_ip_hash',
                'last_login_user_agent',
            ];

            foreach ($required as $field) {
                if (!$this->db->fieldExists($field, $this->table)) {
                    return $this->securitySchemaAvailable = false;
                }
            }

            return $this->securitySchemaAvailable = true;
        } catch (\Throwable $exception) {
            return $this->securitySchemaAvailable = false;
        }
    }

    /** @param array<string, mixed> $user */
    public function sessionVersion(array $user): int
    {
        return max(1, (int) ($user['session_version'] ?? 1));
    }

    /** @param array<string, mixed> $user */
    public function nextSessionVersion(array $user): int
    {
        return $this->sessionVersion($user) + 1;
    }

    public function recordSuccessfulLogin(
        int $userId,
        string $ipAddress,
        string $userAgent
    ): bool {
        if (!$this->securitySchemaReady()) {
            return true;
        }

        return $this->db->table($this->table)
            ->where('id', $userId)
            ->update([
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip_hash' => $this->hashIp($ipAddress),
                'last_login_user_agent' => mb_substr(
                    trim($userAgent),
                    0,
                    255
                ) ?: null,
            ]);
    }

    private function hashIp(string $ipAddress): ?string
    {
        $ipAddress = trim($ipAddress);

        if ($ipAddress === '') {
            return null;
        }

        return hash_hmac(
            'sha256',
            $ipAddress,
            (string) config('App')->baseURL
        );
    }
}
