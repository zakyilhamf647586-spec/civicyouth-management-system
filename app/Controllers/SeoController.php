<?php

namespace App\Controllers;

use App\Libraries\PublicSeoService;

class SeoController extends BaseController
{
    protected PublicSeoService $seoService;

    public function __construct()
    {
        $this->seoService = new PublicSeoService();
    }

    public function index()
    {
        return view('seo/index', [
            'title' => 'SEO & Sitemap',
            'audit' => $this->seoService->audit(),
            'sitemapUrl' => base_url('sitemap.xml'),
            'robotsUrl' => base_url('robots.txt'),
        ]);
    }

    public function sitemap()
    {
        $entries = $this->seoService
            ->sitemapEntries();

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($entries as $entry) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>'
                . $this->xmlEscape(
                    (string) $entry['loc']
                )
                . '</loc>';

            if (!empty($entry['lastmod'])) {
                $xml[] = '    <lastmod>'
                    . $this->xmlEscape(
                        (string) $entry['lastmod']
                    )
                    . '</lastmod>';
            }

            if (!empty($entry['changefreq'])) {
                $xml[] = '    <changefreq>'
                    . $this->xmlEscape(
                        (string) $entry[
                            'changefreq'
                        ]
                    )
                    . '</changefreq>';
            }

            if (!empty($entry['priority'])) {
                $xml[] = '    <priority>'
                    . $this->xmlEscape(
                        (string) $entry['priority']
                    )
                    . '</priority>';
            }

            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return $this->response
            ->setHeader(
                'Cache-Control',
                'public, max-age=900'
            )
            ->setContentType(
                'application/xml',
                'UTF-8'
            )
            ->setBody(implode("\n", $xml));
    }

    public function robots()
    {
        return $this->response
            ->setHeader(
                'Cache-Control',
                'public, max-age=900'
            )
            ->setContentType(
                'text/plain',
                'UTF-8'
            )
            ->setBody(
                $this->seoService->robotsText()
            );
    }

    private function xmlEscape(
        string $value
    ): string {
        return htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );
    }
}
