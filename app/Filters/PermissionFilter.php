<?php

namespace App\Filters;

use App\Libraries\Authorization;
use App\Libraries\CmsAuditService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $permission = trim((string) ($arguments[0] ?? ''));
        $authorization = new Authorization();

        if ($permission !== '' && $authorization->can($permission)) {
            return null;
        }

        helper(['url', 'authorization']);

        (new CmsAuditService())->record([
            'module' => 'security',
            'event_type' => 'access.permission_denied',
            'severity' => 'security',
            'subject_type' => 'permission',
            'subject_key' => $permission,
            'subject_label' => $authorization->permissionLabel($permission),
            'summary' => 'Akses Portal ditolak karena izin tidak tersedia.',
            'metadata' => [
                'permission' => $permission,
                'role' => $authorization->roleName(),
            ],
        ]);

        $body = view('errors/403', [
            'permission'      => $permission,
            'permissionLabel' => $authorization->permissionLabel($permission),
            'roleName'        => $authorization->roleName() ?: 'Tidak diketahui',
        ]);

        return service('response')
            ->setStatusCode(403)
            ->setContentType('text/html', 'UTF-8')
            ->setBody($body);
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        // No after-filter action is required.
    }
}
