<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = [
            'email' => [
                'label' => 'Alamat email',
                'rules' => 'required|valid_email|max_length[150]',
            ],
            'password' => [
                'label' => 'Kata sandi',
                'rules' => 'required|min_length[6]|max_length[255]',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = mb_strtolower(
            trim((string) $this->request->getPost('email'))
        );

        $password = (string) $this->request->getPost('password');
        $user = (new UserModel())->findByEmailWithRole($email);

        if (
            !$user
            || !password_verify($password, (string) $user['password'])
        ) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Email atau kata sandi tidak sesuai.'
                );
        }

        if (($user['status'] ?? '') !== 'active') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda sedang tidak aktif.');
        }

        if (empty($user['role_name'])) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Peran akun belum dikonfigurasi. Hubungi administrator.'
                );
        }

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

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')
            ->with('success', 'Anda telah keluar dari GARDA 01 Portal.');
    }
}
