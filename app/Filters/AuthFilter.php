<?php

namespace App\Filters;

use App\Libraries\CmsAuditService;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    private const IDLE_TIMEOUT_SECONDS = 3600;
    private const ABSOLUTE_LIFETIME_SECONDS = 43200;

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (
            !$session->get('isLoggedIn')
            || !(int) $session->get('user_id')
        ) {
            return redirect()->to('/login');
        }

        $now = time();
        $startedAt = (int) $session->get('auth_started_at');
        $lastSeenAt = (int) $session->get('auth_last_seen_at');

        if (
            $startedAt > 0
            && ($now - $startedAt) > self::ABSOLUTE_LIFETIME_SECONDS
        ) {
            return $this->terminateSession(
                'expired',
                'auth.session_absolute_expired',
                'Sesi Portal berakhir karena mencapai batas waktu maksimum.'
            );
        }

        if (
            $lastSeenAt > 0
            && ($now - $lastSeenAt) > self::IDLE_TIMEOUT_SECONDS
        ) {
            return $this->terminateSession(
                'idle',
                'auth.session_idle_expired',
                'Sesi Portal berakhir karena tidak ada aktivitas.'
            );
        }

        $userModel = new UserModel();
        $user = $userModel->findActiveWithRole(
            (int) $session->get('user_id')
        );

        if (!$user || empty($user['role_name'])) {
            return $this->terminateSession(
                'access-updated',
                'auth.session_account_unavailable',
                'Sesi dicabut karena akun tidak aktif atau perannya tidak tersedia.'
            );
        }

        $databaseVersion = $userModel->sessionVersion($user);
        $sessionVersion = (int) $session->get('session_version');

        if (
            $userModel->securitySchemaReady()
            && $sessionVersion > 0
            && $sessionVersion !== $databaseVersion
        ) {
            return $this->terminateSession(
                'access-updated',
                'auth.session_version_revoked',
                'Sesi dicabut karena kredensial atau akses akun telah berubah.',
                [
                    'session_version' => $sessionVersion,
                    'database_version' => $databaseVersion,
                ]
            );
        }

        $mustChangePassword = !empty(
            $user['must_change_password']
        );

        $session->set([
            'name'            => $user['name'],
            'email'           => $user['email'],
            'role_id'         => (int) $user['role_id'],
            'role_name'       => $user['role_name'],
            'session_version' => $databaseVersion,
            'must_change_password' => $mustChangePassword,
            'auth_started_at' => $startedAt > 0 ? $startedAt : $now,
            'auth_last_seen_at' => $now,
            'auth_checked_at' => $now,
            'isLoggedIn'      => true,
        ]);

        if (
            $mustChangePassword
            && !$this->isPasswordChangePath($request)
        ) {
            return redirect()->to('/account/password');
        }

        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        // No after-filter action is required.
    }

    private function isPasswordChangePath(RequestInterface $request): bool
    {
        $path = '/' . ltrim($request->getUri()->getPath(), '/');
        $path = preg_replace(
            '#^/index\.php(?=/|$)#',
            '',
            $path
        ) ?: '/';

        return in_array($path, [
            '/account/password',
            '/logout',
        ], true);
    }

    /** @param array<string, mixed> $metadata */
    private function terminateSession(
        string $notice,
        string $eventType,
        string $summary,
        array $metadata = []
    ) {
        $session = session();

        (new CmsAuditService())->record([
            'module' => 'security',
            'event_type' => $eventType,
            'severity' => 'security',
            'subject_type' => 'user_account',
            'subject_id' => (int) $session->get('user_id'),
            'subject_key' => 'user:' . (int) $session->get('user_id'),
            'subject_label' => $session->get('name') ?? 'Akun Portal',
            'summary' => $summary,
            'metadata' => $metadata,
        ]);

        $session->destroy();

        return redirect()->to('/login?notice=' . rawurlencode($notice));
    }
}
