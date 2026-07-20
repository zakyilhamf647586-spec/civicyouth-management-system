<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    private const REFRESH_INTERVAL_SECONDS = 300;

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (
            !$session->get('isLoggedIn')
            || !(int) $session->get('user_id')
        ) {
            return redirect()->to('/login');
        }

        $lastCheck = (int) $session->get('auth_checked_at');

        if (
            $lastCheck > 0
            && (time() - $lastCheck) < self::REFRESH_INTERVAL_SECONDS
        ) {
            return null;
        }

        $user = (new UserModel())->findActiveWithRole(
            (int) $session->get('user_id')
        );

        if (!$user || empty($user['role_name'])) {
            $session->destroy();

            return redirect()->to('/login');
        }

        $session->set([
            'name'            => $user['name'],
            'email'           => $user['email'],
            'role_id'         => (int) $user['role_id'],
            'role_name'       => $user['role_name'],
            'auth_checked_at' => time(),
            'isLoggedIn'      => true,
        ]);

        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        // No after-filter action is required.
    }
}
