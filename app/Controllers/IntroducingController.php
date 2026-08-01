<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class IntroducingController extends BaseController
{
    public function index(): string|RedirectResponse
    {
        $path = '/' . ltrim(
            $this->request->getUri()->getPath(),
            '/'
        );
        $path = preg_replace(
            '#^/index\.php(?=/|$)#',
            '',
            $path
        ) ?: '/';

        if (
            $path === '/'
            && $this->request->getCookie('g01_locale')
                === 'en'
        ) {
            return redirect()->to(
                public_url('/', 'en')
            );
        }

        return view('public/introducing', [
            'title' => public_t(
                'seo.introducing_title',
                'GARDA 01 — Introducing | Generasi Aktif Randugarut'
            ),
            'metaDescription' => public_t(
                'seo.introducing_description',
                'Gerbang digital GARDA 01, Generasi Aktif Randugarut: Guyub, Bergerak, dan Berdampak dari RW 01.'
            ),
        ]);
    }
}
