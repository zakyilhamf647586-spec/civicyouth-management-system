<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class InternalNoIndexFilter implements FilterInterface
{
    private const PUBLIC_FIRST_SEGMENTS = [
        '',
        'profil',
        'program',
        'pengurus',
        'kegiatan',
        'kontak',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        $path = trim((string) service('uri')->getPath(), '/');
        $firstSegment = explode('/', $path)[0] ?? '';

        if (!in_array($firstSegment, self::PUBLIC_FIRST_SEGMENTS, true)) {
            $response
                ->setHeader(
                    'X-Robots-Tag',
                    'noindex, nofollow, noarchive, nosnippet'
                )
                ->setHeader(
                    'Cache-Control',
                    'private, no-store, no-cache, max-age=0, must-revalidate'
                )
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('Expires', '0');
        }

        return $response;
    }
}
