<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    private const LOGIN_IP_CAPACITY = 20;
    private const LOGIN_PAIR_CAPACITY = 6;
    private const LOGIN_WINDOW_SECONDS = 900;

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(
                session()->get('must_change_password')
                    ? '/account/password'
                    : '/dashboard'
            );
        }

        $notice = trim((string) $this->request->getGet('notice'));
        $noticeMessages = $this->request->getLocale() === 'en'
            ? [
                'idle' => 'Your session ended after a period of inactivity. Please sign in again.',
                'expired' => 'Your session reached its time limit. Please sign in again.',
                'access-updated' => 'Your account access changed or the session was revoked. Please sign in again.',
            ]
            : [
                'idle' => 'Sesi berakhir karena tidak ada aktivitas. Silakan masuk kembali.',
                'expired' => 'Batas waktu sesi telah berakhir. Silakan masuk kembali.',
                'access-updated' => 'Akses akun berubah atau sesi telah dicabut. Silakan masuk kembali.',
            ];

        return view('auth/login', [
            'loginNotice' => $noticeMessages[$notice] ?? null,
        ]);
    }

    public function attemptLogin()
    {
        $email = mb_strtolower(
            trim((string) $this->request->getPost('email'))
        );

        if (!$this->allowLoginAttempt($email)) {
            if ($this->shouldAuditRateLimit()) {
                $this->recordLoginEvent(
                    'auth.login_rate_limited',
                    'Percobaan login dibatasi sementara.',
                    $email,
                    null,
                    'security',
                    ['outcome' => 'rate_limited']
                );
            }

            return $this->redirectBackWithSafeInput()
                ->with(
                    'error',
                    public_t(
                        'auth.rate_limit',
                        'Terlalu banyak percobaan masuk. Tunggu beberapa menit lalu coba kembali.'
                    )
                );
        }

        $rules = [
            'email' => [
                'label' => public_t(
                    'auth.label_email',
                    'Alamat email'
                ),
                'rules' => 'required|valid_email|max_length[150]',
                'errors' => [
                    'required' => public_t(
                        'validation.required'
                    ),
                    'valid_email' => public_t(
                        'validation.valid_email'
                    ),
                    'max_length' => public_t(
                        'validation.max_length'
                    ),
                ],
            ],
            'password' => [
                'label' => public_t(
                    'auth.label_password',
                    'Kata sandi'
                ),
                'rules' => 'required|min_length[6]|max_length[72]',
                'errors' => [
                    'required' => public_t(
                        'validation.required'
                    ),
                    'min_length' => public_t(
                        'validation.min_length'
                    ),
                    'max_length' => public_t(
                        'validation.max_length'
                    ),
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->redirectBackWithSafeInput()
                ->with('errors', $this->validator->getErrors());
        }

        $password = (string) $this->request->getPost('password');
        $userModel = new UserModel();
        $user = $userModel->findByEmailWithRole($email);

        if (
            !$user
            || !password_verify($password, (string) $user['password'])
        ) {
            $this->recordLoginEvent(
                'auth.login_failed',
                'Percobaan login gagal.',
                $email,
                $user,
                'security',
                ['outcome' => 'invalid_credentials']
            );

            return $this->redirectBackWithSafeInput()
                ->with(
                    'error',
                    public_t(
                        'auth.invalid',
                        'Email atau kata sandi tidak sesuai.'
                    )
                );
        }

        if (($user['status'] ?? '') !== 'active') {
            $this->recordLoginEvent(
                'auth.login_blocked_inactive',
                'Login ditolak karena akun tidak aktif.',
                $email,
                $user,
                'security',
                ['outcome' => 'inactive_account']
            );

            return $this->redirectBackWithSafeInput()
                ->with(
                    'error',
                    public_t(
                        'auth.inactive',
                        'Akun Anda sedang tidak aktif.'
                    )
                );
        }

        if (empty($user['role_name'])) {
            $this->recordLoginEvent(
                'auth.login_blocked_role_missing',
                'Login ditolak karena peran akun belum tersedia.',
                $email,
                $user,
                'warning',
                ['outcome' => 'role_missing']
            );

            return $this->redirectBackWithSafeInput()
                ->with(
                    'error',
                    public_t(
                        'auth.role_missing',
                        'Peran akun belum dikonfigurasi. Hubungi administrator.'
                    )
                );
        }

        $this->clearLoginPairThrottle($email);

        if (password_needs_rehash(
            (string) $user['password'],
            PASSWORD_DEFAULT
        )) {
            $userModel->update((int) $user['id'], [
                'password' => password_hash(
                    $password,
                    PASSWORD_DEFAULT
                ),
            ]);
        }

        $userModel->recordSuccessfulLogin(
            (int) $user['id'],
            $this->request->getIPAddress(),
            (string) $this->request->getUserAgent()
        );

        session()->regenerate(true);

        $now = time();
        $mustChangePassword = !empty(
            $user['must_change_password']
        );

        session()->set([
            'user_id'         => (int) $user['id'],
            'name'            => $user['name'],
            'email'           => $user['email'],
            'role_id'         => (int) $user['role_id'],
            'role_name'       => $user['role_name'],
            'session_version' => $userModel->sessionVersion($user),
            'must_change_password' => $mustChangePassword,
            'auth_started_at' => $now,
            'auth_last_seen_at' => $now,
            'auth_checked_at' => $now,
            'isLoggedIn'      => true,
        ]);

        $this->recordLoginEvent(
            'auth.login_success',
            'Login Portal berhasil.',
            $email,
            $user,
            'info',
            [
                'outcome' => 'success',
                'must_change_password' => $mustChangePassword,
            ]
        );

        return redirect()->to(
            $mustChangePassword
                ? '/account/password'
                : '/dashboard'
        );
    }

    private function allowLoginAttempt(string $email): bool
    {
        $ipAddress = $this->request->getIPAddress();
        $throttler = service('throttler');

        $ipAllowed = $throttler->check(
            'portal-login-ip-' . hash('sha256', $ipAddress),
            self::LOGIN_IP_CAPACITY,
            self::LOGIN_WINDOW_SECONDS
        );

        $pairAllowed = $throttler->check(
            'portal-login-pair-'
                . hash('sha256', $ipAddress . '|' . $email),
            self::LOGIN_PAIR_CAPACITY,
            self::LOGIN_WINDOW_SECONDS
        );

        return $ipAllowed && $pairAllowed;
    }

    private function clearLoginPairThrottle(string $email): void
    {
        $key = 'portal-login-pair-'
            . hash(
                'sha256',
                $this->request->getIPAddress()
                    . '|'
                    . $email
            );

        service('throttler')->remove($key);
    }

    private function shouldAuditRateLimit(): bool
    {
        return service('throttler')->check(
            'portal-login-rate-limit-audit-'
                . hash(
                    'sha256',
                    $this->request->getIPAddress()
                ),
            1,
            self::LOGIN_WINDOW_SECONDS
        );
    }

    public function logout()
    {
        $locale = $this->request->getCookie(
            'g01_locale'
        ) === 'en'
            ? 'en'
            : 'id';

        $this->request->setLocale($locale);

        if (session()->get('isLoggedIn')) {
            $this->recordCmsAudit([
                'module' => 'security',
                'event_type' => 'auth.logout',
                'severity' => 'info',
                'subject_type' => 'user_account',
                'subject_id' => (int) session()->get('user_id'),
                'subject_key' => 'user:' . (int) session()->get('user_id'),
                'subject_label' => session()->get('name') ?? 'Akun Portal',
                'summary' => 'Pengguna keluar dari GARDA 01 Portal.',
            ]);
        }

        session()->destroy();

        return redirect()->to(
            public_url('/login', $locale)
        )
            ->with(
                'success',
                public_t(
                    'auth.logout',
                    'Anda telah keluar dari GARDA 01 Portal.'
                )
            );
    }

    /**
     * @param array<string, mixed>|null $user
     * @param array<string, mixed> $metadata
     */
    private function recordLoginEvent(
        string $eventType,
        string $summary,
        string $email,
        ?array $user,
        string $severity,
        array $metadata = []
    ): void {
        $userId = (int) ($user['id'] ?? 0);

        $this->recordCmsAudit([
            'module' => 'security',
            'event_type' => $eventType,
            'severity' => $severity,
            'subject_type' => 'user_account',
            'subject_id' => $userId > 0 ? $userId : null,
            'subject_key' => $userId > 0
                ? 'user:' . $userId
                : 'email:' . hash('sha256', $email),
            'subject_label' => $this->maskEmail($email),
            'summary' => $summary,
            'metadata' => $metadata,
            'actor_type' => $eventType === 'auth.login_success'
                ? 'internal'
                : 'system',
            'user_id' => $userId > 0 ? $userId : null,
            'actor_name' => $eventType === 'auth.login_success'
                ? ($user['name'] ?? 'Pengguna Portal')
                : 'Login Portal',
            'actor_role' => $eventType === 'auth.login_success'
                ? ($user['role_name'] ?? 'Tidak diketahui')
                : 'Authentication',
        ]);
    }

    private function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return 'Akun tidak dikenali';
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible . '***@' . $domain;
    }
}
