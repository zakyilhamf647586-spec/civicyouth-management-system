<?php

namespace App\Controllers;

use App\Libraries\PublicPageExternalReviewService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ExternalPageReviewController extends PublicController
{
    protected PublicPageExternalReviewService $externalService;

    public function __construct()
    {
        $this->externalService =
            new PublicPageExternalReviewService();
    }

    public function show(string $rawToken)
    {
        $this->assertRequestRate(
            'external-preview',
            30,
            MINUTE
        );

        $resolved = $this->externalService->resolve(
            $rawToken,
            true,
            $this->request->getIPAddress(),
            (string) $this->request->getUserAgent()
        );

        if (!$resolved) {
            throw PageNotFoundException::forPageNotFound(
                'Tautan review tidak ditemukan atau sudah tidak berlaku.'
            );
        }

        $pageKey = (string) (
            $resolved['page']['page_key'] ?? ''
        );

        $this->response
            ->setHeader(
                'Cache-Control',
                'private, no-store, no-cache, must-revalidate, max-age=0'
            )
            ->setHeader('Pragma', 'no-cache')
            ->setHeader(
                'Referrer-Policy',
                'no-referrer'
            )
            ->setHeader(
                'X-Robots-Tag',
                'noindex, nofollow, noarchive'
            );

        $this->useExternalCmsPreview([
            'page_key' => $pageKey,
            'bundle' => $resolved['bundle'],
            'raw_token' => $rawToken,
            'canonical_url' => base_url(
                ltrim(
                    (string) (
                        $resolved['page']['route_path']
                        ?? '/'
                    ),
                    '/'
                )
            ),
            'meta' => [
                'token_id' => (int) (
                    $resolved['token']['id'] ?? 0
                ),
                'label' => (string) (
                    $resolved['token']['label']
                    ?? 'Review Eksternal'
                ),
                'reviewer_name' => (string) (
                    $resolved['token'][
                        'reviewer_name'
                    ] ?? ''
                ),
                'reviewer_email' => (string) (
                    $resolved['token'][
                        'reviewer_email'
                    ] ?? ''
                ),
                'expires_at' => $resolved['token'][
                    'expires_at'
                ] ?? null,
                'allow_decision' => !empty(
                    $resolved['token'][
                        'allow_decision'
                    ]
                ),
                'view_count' => (int) (
                    $resolved['token'][
                        'view_count'
                    ] ?? 0
                ),
                'max_views' => (int) (
                    $resolved['token']['max_views']
                    ?? 0
                ),
                'version_number' => (int) (
                    $resolved['bundle'][
                        'version_number'
                    ] ?? 0
                ),
                'can_submit' => !empty(
                    $resolved['can_submit']
                ),
                'latest_review' =>
                    $resolved['latest_review']
                    ?? null,
            ],
        ]);

        return match ($pageKey) {
            'home' => parent::index(),
            'profile' => parent::profile(),
            'contact' => $this->contactPreview(),
            default => throw PageNotFoundException::
                forPageNotFound(
                    'Halaman review tidak didukung.'
                ),
        };
    }

    public function feedback(string $rawToken)
    {
        $this->assertRequestRate(
            'external-feedback-'
                . hash('sha256', $rawToken),
            5,
            10 * MINUTE
        );

        $resolved = $this->externalService->resolve(
            $rawToken,
            false,
            $this->request->getIPAddress(),
            (string) $this->request->getUserAgent()
        );

        if (!$resolved) {
            throw PageNotFoundException::forPageNotFound(
                'Tautan review tidak ditemukan atau sudah tidak berlaku.'
            );
        }

        try {
            $this->externalService->submitFeedback(
                $resolved,
                [
                    'decision' =>
                        $this->request->getPost(
                            'decision'
                        ),
                    'reviewer_name' =>
                        $this->request->getPost(
                            'reviewer_name'
                        ),
                    'reviewer_email' =>
                        $this->request->getPost(
                            'reviewer_email'
                        ),
                    'comment' =>
                        $this->request->getPost(
                            'comment'
                        ),
                ],
                $this->request->getIPAddress(),
                (string) $this->request
                    ->getUserAgent()
            );
        } catch (\Throwable $exception) {
            return redirect()->to(
                '/review/page/' . $rawToken
                . '#external-review-panel'
            )->withInput()->with(
                'external_review_error',
                $exception->getMessage()
            );
        }

        return redirect()->to(
            '/review/page/' . $rawToken
            . '#external-review-panel'
        )->with(
            'external_review_success',
            'Terima kasih. Tanggapan Anda berhasil disimpan untuk ditinjau pengurus GARDA 01.'
        );
    }

    private function contactPreview()
    {
        $cmsState = $this->publicCmsPage(
            'contact'
        );

        $cmsPage = $cmsState['page'];

        return view('public/contact', [
            'title' => $cmsPage['title']
                ?? 'Kontak dan Kolaborasi | GARDA 01',
            'metaDescription' =>
                $cmsPage['meta_description']
                ?? 'Hubungi GARDA 01 untuk kolaborasi kegiatan dan pemberdayaan masyarakat.',
            'activePage' => 'contact',
            'cmsPage' => $cmsPage,
            'cmsPreview' => true,
            'externalPreview' => true,
            'externalPreviewToken' =>
                $cmsState['external_token'],
            'externalPreviewMeta' =>
                $cmsState['external_meta'],
            'canonicalUrl' =>
                $cmsState['canonical_url'],
        ]);
    }

    private function assertRequestRate(
        string $scope,
        int $capacity,
        int $seconds
    ): void {
        $key = $scope
            . '-'
            . hash(
                'sha256',
                $this->request->getIPAddress()
            );

        if (!service('throttler')->check(
            $key,
            $capacity,
            $seconds
        )) {
            throw PageNotFoundException::forPageNotFound(
                'Tautan review tidak ditemukan atau sudah tidak berlaku.'
            );
        }
    }
}
