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
            'rules' => 'required|min_length[8]|max_length[72]',
        ];

        $rules['password_confirm'] = [
            'label' => 'Konfirmasi kata sandi',
            'rules' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->normalizeEmail(
            (string) $this->request->getPost('email')
        );

        if ($this->userModel->emailExists($email)) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'errors',
                    ['Email sudah digunakan oleh akun lain.']
                );
        }

        $roleId = (int) $this->request->getPost('role_id');

        if (!$this->roleModel->find($roleId)) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'errors',
                    ['Peran pengguna tidak ditemukan.']
                );
        }

        $password = (string) $this->request->getPost(
            'password'
        );

        $inserted = $this->userModel->insert([
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
        ]);

        if (!$inserted) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'errors',
                    ['Akun pengguna gagal dibuat.']
                );
        }

        return redirect()->to('/users')
            ->with(
                'success',
                'Akun pengguna berhasil dibuat.'
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
                'rules' => 'min_length[8]|max_length[72]',
            ];

            $rules['password_confirm'] = [
                'label' => 'Konfirmasi kata sandi',
                'rules' => 'required|matches[password]',
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->normalizeEmail(
            (string) $this->request->getPost('email')
        );

        if ($this->userModel->emailExists($email, $id)) {
            return redirect()->back()
                ->withInput()
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
            return redirect()->back()
                ->withInput()
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
            return redirect()->back()
                ->withInput()
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
            return redirect()->back()
                ->withInput()
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

        if ($password !== '') {
            $data['password'] = password_hash(
                $password,
                PASSWORD_DEFAULT
            );
        }

        if (!$this->userModel->update($id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'errors',
                    ['Akun pengguna gagal diperbarui.']
                );
        }

        if ($isCurrentUser) {
            $this->refreshCurrentSession($id);
        }

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

        $continuityError = $this->adminContinuityError(
            $user,
            (int) $user['role_id'],
            $newStatus
        );

        if ($continuityError !== null) {
            return redirect()->to('/users')
                ->with('error', $continuityError);
        }

        if (!$this->userModel->update($id, [
            'status' => $newStatus,
        ])) {
            return redirect()->to('/users')
                ->with(
                    'error',
                    'Status akun gagal diperbarui.'
                );
        }

        $message = $newStatus === 'active'
            ? 'Akun berhasil diaktifkan.'
            : 'Akun berhasil dinonaktifkan.';

        return redirect()->to('/users')
            ->with('success', $message);
    }

    public function resetPassword(int $id)
    {
        $user = $this->findUserOrFail($id);

        $rules = [
            'new_password' => [
                'label' => 'Kata sandi baru',
                'rules' => 'required|min_length[8]|max_length[72]',
            ],
            'new_password_confirm' => [
                'label' => 'Konfirmasi kata sandi baru',
                'rules' => 'required|matches[new_password]',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $newPassword = (string) $this->request->getPost(
            'new_password'
        );

        if (!$this->userModel->update($id, [
            'password' => password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            ),
        ])) {
            return redirect()->back()
                ->with(
                    'errors',
                    ['Kata sandi gagal direset.']
                );
        }

        return redirect()
            ->to('/users/edit/' . $id)
            ->with(
                'success',
                'Kata sandi untuk '
                . $user['name']
                . ' berhasil direset.'
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
            'auth_checked_at' => time(),
        ]);
    }
}
