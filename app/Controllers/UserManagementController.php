<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class UserManagementController extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        $keyword = trim(
            (string) $this->request->getGet('keyword')
        );

        $roleId = trim(
            (string) $this->request->getGet('role_id')
        );

        $status = trim(
            (string) $this->request->getGet('status')
        );

        $query = $this->userModel
            ->select(
                'users.*, roles.role_name, ' .
                'roles.description AS role_description'
            )
            ->join('roles', 'roles.id = users.role_id', 'left');

        if ($keyword !== '') {
            $query
                ->groupStart()
                ->like('users.name', $keyword)
                ->orLike('users.email', $keyword)
                ->orLike('roles.role_name', $keyword)
                ->groupEnd();
        }

        if ($roleId !== '' && ctype_digit($roleId)) {
            $query->where('users.role_id', (int) $roleId);
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('users.status', $status);
        }

        $query
            ->orderBy('users.status', 'ASC')
            ->orderBy('roles.id', 'ASC')
            ->orderBy('users.name', 'ASC');

        return view('users/index', [
            'title' => 'Manajemen Akun',
            'users' => $query->paginate(12, 'users'),
            'pager' => $this->userModel->pager,
            'roles' => $this->roleModel->orderedRoles(),
            'statistics' => $this->userModel
                ->accountStatistics(),
            'keyword' => $keyword,
            'selectedRole' => $roleId,
            'selectedStatus' => $status,
            'currentUserId' => (int) session()->get('user_id'),
        ]);
    }

    public function create()
    {
        return view('users/create', [
            'title' => 'Tambah Akun Pengguna',
            'roles' => $this->roleModel->orderedRoles(),
        ]);
    }

    public function store()
    {
        $rules = $this->baseRules();

        $rules['password'] = [
            'label' => 'Kata sandi',
            'rules' => 'required|min_length[12]|max_length[72]',
        ];

        $rules['password_confirm'] = [
            'label' => 'Konfirmasi kata sandi',
            'rules' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->normalizeEmail(
            (string) $this->request->getPost('email')
        );

        if ($this->userModel->emailExists($email)) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    ['Email sudah digunakan oleh akun lain.']
                );
        }

        $roleId = (int) $this->request->getPost('role_id');

        if (!$this->roleModel->find($roleId)) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    ['Peran pengguna tidak ditemukan.']
                );
        }

        $password = (string) $this->request->getPost(
            'password'
        );

        $policyError = $this->passwordPolicyError($password);

        if ($policyError !== null) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', [$policyError]);
        }

        $insertData = [
            'role_id' => $roleId,
            'name' => trim(
                (string) $this->request->getPost('name')
            ),
            'email' => $email,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            ),
            'status' => (string) $this->request
                ->getPost('status'),
        ];

        if ($this->userModel->securitySchemaReady()) {
            $insertData += [
                'session_version' => 1,
                'must_change_password' => 1,
                'password_changed_at' => date('Y-m-d H:i:s'),
            ];
        }

        $inserted = $this->userModel->insert($insertData, true);

        if (!$inserted) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    ['Akun pengguna gagal dibuat.']
                );
        }

        $this->recordAccountAudit(
            'account.created',
            'Akun pengguna baru dibuat.',
            (int) $inserted,
            $insertData['name'],
            'notice',
            [
                'role_id' => $roleId,
                'status' => $insertData['status'],
                'must_change_password' => true,
            ]
        );

        return redirect()->to('/users')
            ->with(
                'success',
                'Akun berhasil dibuat. Pengguna wajib mengganti kata sandi awal saat login pertama.'
            );
    }

    public function edit(int $id)
    {
        $user = $this->findUserOrFail($id);

        return view('users/edit', [
            'title' => 'Edit Akun Pengguna',
            'user' => $user,
            'roles' => $this->roleModel->orderedRoles(),
            'isCurrentUser' => $id
                === (int) session()->get('user_id'),
        ]);
    }

    public function update(int $id)
    {
        $user = $this->findUserOrFail($id);
        $isCurrentUser = $id
            === (int) session()->get('user_id');

        $rules = $this->baseRules();
        $password = (string) $this->request->getPost(
            'password'
        );

        if ($password !== '') {
            $rules['password'] = [
                'label' => 'Kata sandi baru',
                'rules' => 'min_length[12]|max_length[72]',
            ];

            $rules['password_confirm'] = [
                'label' => 'Konfirmasi kata sandi',
                'rules' => 'required|matches[password]',
            ];
        }

        if (!$this->validate($rules)) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', $this->validator->getErrors());
        }

        if ($isCurrentUser && $password !== '') {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    [
                        'Gunakan halaman Keamanan Akun untuk mengganti kata sandi akun yang sedang dipakai.',
                    ]
                );
        }

        if ($password !== '') {
            $policyError = $this->passwordPolicyError($password);

            if ($policyError !== null) {
                return $this->redirectBackWithSafeInput()
                    ->with('errors', [$policyError]);
            }

            if (password_verify($password, (string) $user['password'])) {
                return $this->redirectBackWithSafeInput()
                    ->with(
                        'errors',
                        ['Kata sandi baru harus berbeda dari kata sandi saat ini.']
                    );
            }
        }

        $email = $this->normalizeEmail(
            (string) $this->request->getPost('email')
        );

        if ($this->userModel->emailExists($email, $id)) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    ['Email sudah digunakan oleh akun lain.']
                );
        }

        $newRoleId = (int) $this->request->getPost(
            'role_id'
        );

        $newStatus = (string) $this->request->getPost(
            'status'
        );

        if (!$this->roleModel->find($newRoleId)) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    ['Peran pengguna tidak ditemukan.']
                );
        }

        if (
            $isCurrentUser
            && (
                $newRoleId !== (int) $user['role_id']
                || $newStatus !== 'active'
            )
        ) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    [
                        'Anda tidak dapat mengubah peran atau ' .
                        'menonaktifkan akun yang sedang digunakan.',
                    ]
                );
        }

        $continuityError = $this->adminContinuityError(
            $user,
            $newRoleId,
            $newStatus
        );

        if ($continuityError !== null) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', [$continuityError]);
        }

        $data = [
            'role_id' => $newRoleId,
            'name' => trim(
                (string) $this->request->getPost('name')
            ),
            'email' => $email,
            'status' => $newStatus,
        ];

        $changedFields = [];

        foreach (['role_id', 'name', 'email', 'status'] as $field) {
            if ((string) ($user[$field] ?? '') !== (string) $data[$field]) {
                $changedFields[] = $field;
            }
        }

        $securityChanged = in_array('role_id', $changedFields, true)
            || in_array('email', $changedFields, true)
            || in_array('status', $changedFields, true)
            || $password !== '';

        if ($password !== '') {
            $data['password'] = password_hash(
                $password,
                PASSWORD_DEFAULT
            );
            $changedFields[] = 'password';
        }

        if (
            $securityChanged
            && $this->userModel->securitySchemaReady()
        ) {
            $data['session_version'] = $this->userModel
                ->nextSessionVersion($user);

            if ($password !== '') {
                $data['must_change_password'] = 1;
                $data['password_changed_at'] = date('Y-m-d H:i:s');
            }
        }

        if (!$this->userModel->update($id, $data)) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    ['Akun pengguna gagal diperbarui.']
                );
        }

        if ($isCurrentUser) {
            $this->refreshCurrentSession($id);
        }

        $this->recordAccountAudit(
            'account.updated',
            'Akun pengguna diperbarui.',
            $id,
            $data['name'],
            $securityChanged ? 'security' : 'notice',
            [
                'changed_fields' => $changedFields,
                'sessions_revoked' => $securityChanged,
            ]
        );

        return redirect()->to('/users')
            ->with(
                'success',
                'Akun pengguna berhasil diperbarui.'
            );
    }

    public function updateStatus(int $id)
    {
        $user = $this->findUserOrFail($id);
        $newStatus = trim(
            (string) $this->request->getPost('status')
        );

        if (!in_array(
            $newStatus,
            ['active', 'inactive'],
            true
        )) {
            return redirect()->to('/users')
                ->with(
                    'error',
                    'Status akun tidak valid.'
                );
        }

        if ($id === (int) session()->get('user_id')) {
            return redirect()->to('/users')
                ->with(
                    'error',
                    'Akun yang sedang digunakan tidak dapat dinonaktifkan.'
                );
        }

        if ($newStatus === (string) ($user['status'] ?? '')) {
            return redirect()->to('/users')
                ->with('success', 'Status akun tidak berubah.');
        }

        $continuityError = $this->adminContinuityError(
            $user,
            (int) $user['role_id'],
            $newStatus
        );

        if ($continuityError !== null) {
            return redirect()->to('/users')
                ->with('error', $continuityError);
        }

        $statusData = [
            'status' => $newStatus,
        ];

        if ($this->userModel->securitySchemaReady()) {
            $statusData['session_version'] = $this->userModel
                ->nextSessionVersion($user);
        }

        if (!$this->userModel->update($id, $statusData)) {
            return redirect()->to('/users')
                ->with(
                    'error',
                    'Status akun gagal diperbarui.'
                );
        }

        $message = $newStatus === 'active'
            ? 'Akun berhasil diaktifkan.'
            : 'Akun berhasil dinonaktifkan.';

        $this->recordAccountAudit(
            $newStatus === 'active'
                ? 'account.activated'
                : 'account.deactivated',
            $message,
            $id,
            $user['name'] ?? 'Akun Portal',
            'security',
            [
                'status' => $newStatus,
                'sessions_revoked' => true,
            ]
        );

        return redirect()->to('/users')
            ->with('success', $message);
    }

    public function resetPassword(int $id)
    {
        $user = $this->findUserOrFail($id);

        if ($id === (int) session()->get('user_id')) {
            return redirect()->to('/account/password')
                ->with(
                    'error',
                    'Gunakan halaman ini untuk mengganti kata sandi akun Anda dengan verifikasi kata sandi saat ini.'
                );
        }

        $rules = [
            'new_password' => [
                'label' => 'Kata sandi baru',
                'rules' => 'required|min_length[12]|max_length[72]',
            ],
            'new_password_confirm' => [
                'label' => 'Konfirmasi kata sandi baru',
                'rules' => 'required|matches[new_password]',
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', $this->validator->getErrors());
        }

        $newPassword = (string) $this->request->getPost(
            'new_password'
        );

        $policyError = $this->passwordPolicyError($newPassword);

        if ($policyError !== null) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', [$policyError]);
        }

        if (password_verify($newPassword, (string) $user['password'])) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    ['Kata sandi reset harus berbeda dari kata sandi saat ini.']
                );
        }

        $resetData = [
            'password' => password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            ),
        ];

        if ($this->userModel->securitySchemaReady()) {
            $resetData += [
                'session_version' => $this->userModel
                    ->nextSessionVersion($user),
                'must_change_password' => 1,
                'password_changed_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (!$this->userModel->update($id, $resetData)) {
            return redirect()->back()
                ->with(
                    'errors',
                    ['Kata sandi gagal direset.']
                );
        }

        $this->recordAccountAudit(
            'account.password_reset',
            'Kata sandi akun direset oleh pengelola akun.',
            $id,
            $user['name'] ?? 'Akun Portal',
            'security',
            [
                'must_change_password' => true,
                'sessions_revoked' => true,
            ]
        );

        return redirect()
            ->to('/users/edit/' . $id)
            ->with(
                'success',
                'Kata sandi untuk '
                . $user['name']
                . ' berhasil direset. Seluruh sesi lama dicabut dan pengguna wajib menggantinya saat login.'
            );
    }

    public function revokeSessions(int $id)
    {
        $user = $this->findUserOrFail($id);

        if (!$this->userModel->securitySchemaReady()) {
            return redirect()->to('/users/edit/' . $id)
                ->with(
                    'errors',
                    ['Jalankan migration keamanan akun sebelum mencabut sesi.']
                );
        }

        if ($id === (int) session()->get('user_id')) {
            return redirect()->to('/account/password')
                ->with(
                    'error',
                    'Gunakan verifikasi kata sandi untuk mencabut sesi lain akun Anda.'
                );
        }

        if (!$this->userModel->update($id, [
            'session_version' => $this->userModel
                ->nextSessionVersion($user),
        ])) {
            return redirect()->to('/users/edit/' . $id)
                ->with('errors', ['Sesi pengguna gagal dicabut.']);
        }

        $this->recordAccountAudit(
            'account.sessions_revoked_by_admin',
            'Seluruh sesi akun dicabut oleh pengelola akun.',
            $id,
            $user['name'] ?? 'Akun Portal',
            'security',
            ['sessions_revoked' => true]
        );

        return redirect()->to('/users/edit/' . $id)
            ->with(
                'success',
                'Seluruh sesi untuk ' . $user['name'] . ' berhasil dicabut.'
            );
    }

    private function baseRules(): array
    {
        return [
            'name' => [
                'label' => 'Nama pengguna',
                'rules' => 'required|min_length[3]|max_length[150]',
            ],
            'email' => [
                'label' => 'Alamat email',
                'rules' => 'required|valid_email|max_length[150]',
            ],
            'role_id' => [
                'label' => 'Peran pengguna',
                'rules' => 'required|integer',
            ],
            'status' => [
                'label' => 'Status akun',
                'rules' => 'required|in_list[active,inactive]',
            ],
        ];
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function findUserOrFail(int $id): array
    {
        $user = $this->userModel->findWithRole($id);

        if (!$user) {
            throw PageNotFoundException::forPageNotFound(
                'Akun pengguna tidak ditemukan.'
            );
        }

        return $user;
    }

    private function adminContinuityError(
        array $user,
        int $newRoleId,
        string $newStatus
    ): ?string {
        $adminRole = $this->roleModel->findAdminRole();

        if (!$adminRole) {
            return null;
        }

        $adminRoleId = (int) $adminRole['id'];
        $wasActiveAdmin =
            (int) $user['role_id'] === $adminRoleId
            && ($user['status'] ?? '') === 'active';

        $willRemainActiveAdmin =
            $newRoleId === $adminRoleId
            && $newStatus === 'active';

        if (!$wasActiveAdmin || $willRemainActiveAdmin) {
            return null;
        }

        if (
            $this->userModel->countActiveByRoleId(
                $adminRoleId
            ) <= 1
        ) {
            return
                'Tindakan ditolak karena sistem harus tetap ' .
                'memiliki minimal satu akun Admin aktif.';
        }

        return null;
    }

    private function refreshCurrentSession(int $userId): void
    {
        $updatedUser = $this->userModel->findWithRole(
            $userId
        );

        if (!$updatedUser) {
            return;
        }

        session()->set([
            'name' => $updatedUser['name'],
            'email' => $updatedUser['email'],
            'role_id' => (int) $updatedUser['role_id'],
            'role_name' => $updatedUser['role_name'],
            'session_version' => $this->userModel
                ->sessionVersion($updatedUser),
            'must_change_password' => !empty(
                $updatedUser['must_change_password']
            ),
            'auth_checked_at' => time(),
        ]);
    }

    private function passwordPolicyError(string $password): ?string
    {
        if (strlen($password) > 72) {
            return 'Kata sandi maksimal 72 byte.';
        }

        $blocked = [
            'admin123',
            'admin12345678',
            'password1234',
            'password12345',
            '123456789012',
            'garda012026',
        ];

        if (in_array(mb_strtolower(trim($password)), $blocked, true)) {
            return 'Kata sandi tersebut terlalu mudah ditebak. Gunakan frasa yang lebih panjang dan unik.';
        }

        return null;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function recordAccountAudit(
        string $eventType,
        string $summary,
        int $userId,
        string $userLabel,
        string $severity,
        array $metadata = []
    ): void {
        $this->recordCmsAudit([
            'module' => 'security',
            'event_type' => $eventType,
            'severity' => $severity,
            'subject_type' => 'user_account',
            'subject_id' => $userId,
            'subject_key' => 'user:' . $userId,
            'subject_label' => $userLabel,
            'summary' => $summary,
            'metadata' => $metadata,
        ]);
    }
}
