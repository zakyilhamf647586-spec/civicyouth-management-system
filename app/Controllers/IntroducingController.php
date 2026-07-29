<?php

namespace App\Controllers;

class IntroducingController extends BaseController
{
    public function index(): string
    {
        return view('public/introducing', [
            'title' =>
                'GARDA 01 — Introducing | Generasi Aktif Randugarut',
            'metaDescription' =>
                'Gerbang digital GARDA 01, Generasi Aktif Randugarut: Guyub, Bergerak, dan Berdampak dari RW 01.',
        ]);
    }
}
