<?php

namespace App\Controllers;

use App\Models\UserModel;

class AccountSecurityController extends BaseController
{
    private const PASSWORD_MINIMUM = 12;
    private const PASSWORD_MAXIMUM_BYTES = 72;
    private const VERIFY_CAPACITY = 5;
    private const VERIFY_WINDOW_SECONDS = 900;

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function password()
    {
        $user = $this->currentUser();

        if (!$user) {
            return redirect()->to('/logout');
        }

        return view('account/password', [
            'title' => 'Keamanan Akun',
            'user' => $user,
            'securityReady' => $this->userModel->securitySchemaReady(),
            'mustChangePassword' => !empty(
                $user['must_change_password']
            ),
        ]);
    }

    public function updatePassword()
    {
        $user = $this->currentUser();

        if (!$user) {
            return redirect()->to('/logout');
        }

        if (!$this->allowVerificationAttempt((int) $user['id'])) {
            $this->recordSecurityEvent(
                'account.password_change_rate_limited',
                'Percobaan verifikasi kata sandi dibatasi sementara.',
                $user,
                'security'
            );

            return $this->redirectBackWithSafeInput()
                ->with(
                    'error',
                    'Terlalu banyak percobaan verifikasi. Tunggu 15 menit lalu coba kembali.'
                );
        }

        $rules = [
            'current_password' => [
                'label' => 'Kata sandi saat ini',
                'rules' => 'required|max_length[72]',
            ],
            'new_password' => [
                'label' => 'Kata sandi baru',
                'rules' => 'required|min_length['
                    . self::PASSWORD_MINIMUM
                    . ']|max_length[72]',
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

        $currentPassword = (string) $this->request->getPost(
            'current_password'
        );
        $newPassword = (string) $this->request->getPost(
            'new_password'
        );

        if (!password_verify($currentPassword, (string) $user['password'])) {
            $this->recordSecurityEvent(
                'account.password_verification_failed',
                'Verifikasi kata sandi akun gagal.',
                $user,
                'security'
            );

            return $this->redirectBackWithSafeInput()
                ->with('error', 'Kata sandi saat ini tidak sesuai.');
        }

        $policyError = $this->passwordPolicyError($newPassword);

        if ($policyError !== null) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', [$policyError]);
        }

        if (password_verify($newPassword, (string) $user['password'])) {
            return $this->redirectBackWithSafeInput()
                ->with(
                    'errors',
                    ['Kata sandi baru harus berbeda dari kata sandi saat ini.']
                );
        }

        $newVersion = $this->userModel->nextSessionVersion($user);
        $data = [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ];

        if ($this->userModel->securitySchemaReady()) {
            $data += [
                'must_change_password' => 0,
                'password_changed_at' => date('Y-m-d H:i:s'),
                'session_version' => $newVersion,
            ];
        }

        if (!$this->userModel->update((int) $user['id'], $data)) {
            return $this->redirectBackWithSafeInput()
                ->with('error', 'Kata sandi gagal diperbarui.');
        }

        service('throttler')->remove(
            $this->verificationThrottleKey((int) $user['id'])
        );

        session()->regenerate(true);
        session()->set([
            'session_version' => $newVersion,
            'must_change_password' => false,
            'auth_started_at' => time(),
            'auth_last_seen_at' => time(),
            'auth_checked_at' => time(),
            'isLoggedIn' => true,
        ]);

        $this->recordSecurityEvent(
            'account.password_changed',
            'Kata sandi akun berhasil diperbarui.',
            $user,
            'notice'
        );

        return redirect()->to('/account/password')
            ->with(
                'success',
                'Kata sandi berhasil diperbarui. Sesi lain pada akun ini otomatis dicabut.'
            );
    }

    public function revokeSessions()
    {
        $user = $this->currentUser();

        if (!$user) {
            return redirect()->to('/logout');
        }

        if (!$this->userModel->securitySchemaReady()) {
            return redirect()->to('/account/password')
                ->with(
                    'error',
                    'Jalankan migration keamanan akun sebelum mencabut sesi.'
                );
        }

        if (!$this->allowVerificationAttempt((int) $user['id'])) {
            return redirect()->to('/account/password')
                ->with(
                    'error',
                    'Terlalu banyak percobaan verifikasi. Tunggu 15 menit lalu coba kembali.'
                );
        }

        if (!$this->validate([
            'current_password' => [
                'label' => 'Kata sandi saat ini',
                'rules' => 'required|max_length[72]',
            ],
        ])) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', $this->validator->getErrors());
        }

        $currentPassword = (string) $this->request->getPost(
            'current_password'
        );

        if (
            $currentPassword === ''
            || !password_verify(
                $currentPassword,
                (string) $user['password']
            )
        ) {
            $this->recordSecurityEvent(
                'account.session_revoke_verification_failed',
                'Verifikasi pencabutan sesi akun gagal.',
                $user,
                'security'
            );

            return redirect()->to('/account/password')
                ->with('error', 'Kata sandi saat ini tidak sesuai.');
        }

        $newVersion = $this->userModel->nextSessionVersion($user);

        if (!$this->userModel->update((int) $user['id'], [
            'session_version' => $newVersion,
        ])) {
            return redirect()->to('/account/password')
                ->with('error', 'Sesi lain gagal dicabut.');
        }

        service('throttler')->remove(
            $this->verificationThrottleKey((int) $user['id'])
        );

        session()->regenerate(true);
        session()->set([
            'session_version' => $newVersion,
            'auth_started_at' => time(),
            'auth_last_seen_at' => time(),
            'auth_checked_at' => time(),
            'isLoggedIn' => true,
        ]);

        $this->recordSecurityEvent(
            'account.sessions_revoked',
            'Seluruh sesi lain akun telah dicabut.',
            $user,
            'security'
        );

        return redirect()->to('/account/password')
            ->with(
                'success',
                'Semua sesi lain telah dicabut. Perangkat ini tetap masuk dengan sesi baru.'
            );
    }

    private function currentUser(): ?array
    {
        return $this->userModel->findActiveWithRole(
            (int) session()->get('user_id')
        );
    }

    private function allowVerificationAttempt(int $userId): bool
    {
        return service('throttler')->check(
            $this->verificationThrottleKey($userId),
            self::VERIFY_CAPACITY,
            self::VERIFY_WINDOW_SECONDS
        );
    }

    private function verificationThrottleKey(int $userId): string
    {
        return 'account-security-verify-'
            . hash(
                'sha256',
                $userId . '|' . $this->request->getIPAddress()
            );
    }

    private function passwordPolicyError(string $password): ?string
    {
        if (strlen($password) > self::PASSWORD_MAXIMUM_BYTES) {
            return 'Kata sandi maksimal 72 byte.';
        }

        $normalized = mb_strtolower(trim($password));
        $blocked = [
            'admin123',
            'admin12345678',
            'password1234',
            'password12345',
            '123456789012',
            'garda012026',
        ];

        if (in_array($normalized, $blocked, true)) {
            return 'Kata sandi tersebut terlalu mudah ditebak. Gunakan frasa yang lebih panjang dan unik.';
        }

        return null;
    }

    /** @param array<string, mixed> $user */
    private function recordSecurityEvent(
        string $eventType,
        string $summary,
        array $user,
        string $severity
    ): void {
        $this->recordCmsAudit([
            'module' => 'security',
            'event_type' => $eventType,
            'severity' => $severity,
            'subject_type' => 'user_account',
            'subject_id' => (int) ($user['id'] ?? 0),
            'subject_key' => 'user:' . (int) ($user['id'] ?? 0),
            'subject_label' => $user['name'] ?? 'Akun Portal',
            'summary' => $summary,
        ]);
    }
}
