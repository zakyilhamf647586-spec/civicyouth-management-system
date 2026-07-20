<?php

namespace App\Filters;

use App\Libraries\Authorization;
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
