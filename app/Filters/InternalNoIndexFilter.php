<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class InternalNoIndexFilter implements FilterInterface
{
    private const PUBLIC_FIRST_SEGMENTS = [
        '',
        'home',
        'profil',
        'program',
        'pengurus',
        'kegiatan',
        'kontak',
        'sitemap.xml',
        'robots.txt',
    ];

    private const PUBLIC_ENGLISH_PATHS = [
        '',
        'home',
        'about',
        'programs',
        'team',
        'activities',
        'contact',
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
        $path = trim($request->getUri()->getPath(), '/');
        $segments = $path === ''
            ? []
            : explode('/', $path);

        $indexPage = trim(
            (string) config('App')->indexPage,
            '/'
        );

        if (
            $indexPage !== ''
            && ($segments[0] ?? '') === $indexPage
        ) {
            array_shift($segments);
        }

        if (!$this->isPublicPath($segments)) {
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

    /**
     * @param list<string> $segments
     */
    private function isPublicPath(array $segments): bool
    {
        $firstSegment = $segments[0] ?? '';

        if ($firstSegment !== 'en') {
            return in_array(
                $firstSegment,
                self::PUBLIC_FIRST_SEGMENTS,
                true
            );
        }

        $englishPath = implode(
            '/',
            array_slice($segments, 1)
        );

        if (in_array(
            $englishPath,
            self::PUBLIC_ENGLISH_PATHS,
            true
        )) {
            return true;
        }

        return preg_match(
            '#^(?:programs/[^/]+|activities/[0-9]+)$#',
            $englishPath
        ) === 1;
    }
}
