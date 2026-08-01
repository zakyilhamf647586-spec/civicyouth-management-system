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
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $email = mb_strtolower(
            trim((string) $this->request->getPost('email'))
        );

        if (!$this->allowLoginAttempt($email)) {
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
                'rules' => 'required|min_length[6]|max_length[255]',
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
        $user = (new UserModel())->findByEmailWithRole($email);

        if (
            !$user
            || !password_verify($password, (string) $user['password'])
        ) {
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

        session()->regenerate(true);

        session()->set([
            'user_id'         => (int) $user['id'],
            'name'            => $user['name'],
            'email'           => $user['email'],
            'role_id'         => (int) $user['role_id'],
            'role_name'       => $user['role_name'],
            'auth_checked_at' => time(),
            'isLoggedIn'      => true,
        ]);

        return redirect()->to('/dashboard');
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

    public function logout()
    {
        $locale = $this->request->getCookie(
            'g01_locale'
        ) === 'en'
            ? 'en'
            : 'id';

        $this->request->setLocale($locale);
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
}
