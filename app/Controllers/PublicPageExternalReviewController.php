<?php

namespace App\Controllers;

use App\Libraries\PublicPageExternalReviewService;
use App\Models\PublicPageModel;
use RuntimeException;

class PublicPageExternalReviewController extends BaseController
{
    protected PublicPageModel $pageModel;
    protected PublicPageExternalReviewService $externalService;

    public function __construct()
    {
        $this->pageModel = new PublicPageModel();
        $this->externalService =
            new PublicPageExternalReviewService();
    }

    public function index(string $pageKey)
    {
        $this->assertReady();

        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            throw new RuntimeException(
                'Halaman CMS tidak ditemukan.'
            );
        }

        return view(
            'public_pages/external_review/index',
            [
                'title' =>
                    'Review Eksternal '
                    . $page['name'],
                'page' => $page,
                'pageKey' => $pageKey,
                'tokens' =>
                    $this->externalService
                        ->linksForPage(
                            (int) $page['id']
                        ),
                'statusLabels' => [
                    'active' => 'Aktif',
                    'completed' =>
                        'Tanggapan Diterima',
                    'expired' => 'Kedaluwarsa',
                    'exhausted' =>
                        'Batas Akses Tercapai',
                    'revoked' => 'Dicabut',
                ],
                'decisionLabels' => [
                    'comment' => 'Komentar',
                    'approved' =>
                        'Layak Menurut Reviewer',
                    'changes_requested' =>
                        'Memerlukan Perbaikan',
                ],
            ]
        );
    }

    public function create(string $pageKey)
    {
        $this->assertReady();

        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            return redirect()->to('/website/pages')
                ->with(
                    'error',
                    'Halaman CMS tidak ditemukan.'
                );
        }

        try {
            $created = $this->externalService
                ->createLink(
                    $page,
                    [
                        'label' =>
                            $this->request->getPost(
                                'label'
                            ),
                        'reviewer_name' =>
                            $this->request->getPost(
                                'reviewer_name'
                            ),
                        'reviewer_email' =>
                            $this->request->getPost(
                                'reviewer_email'
                            ),
                        'expires_in_days' =>
                            $this->request->getPost(
                                'expires_in_days'
                            ),
                        'max_views' =>
                            $this->request->getPost(
                                'max_views'
                            ),
                        'allow_decision' =>
                            $this->request->getPost(
                                'allow_decision'
                            ),
                    ],
                    $this->currentUserId()
                );
        } catch (\Throwable $exception) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    $exception instanceof RuntimeException
                        ? $exception->getMessage()
                        : 'Tautan review belum dapat dibuat.'
                );
        }

        $reviewUrl = base_url(
            'review/page/'
            . $created['raw_token']
        );

        return redirect()->to(
            '/website/pages/external-review/'
            . $pageKey
        )->with(
            'external_preview_link',
            $reviewUrl
        )->with(
            'success',
            'Tautan review berhasil dibuat. Salin sekarang karena token mentah tidak disimpan di database.'
        );
    }

    public function revoke(
        string $pageKey,
        int $tokenId
    ) {
        $this->assertReady();

        $page = $this->pageModel
            ->findByKey($pageKey);

        if (!$page) {
            return redirect()->to('/website/pages')
                ->with(
                    'error',
                    'Halaman CMS tidak ditemukan.'
                );
        }

        $token = null;

        foreach (
            $this->externalService->linksForPage(
                (int) $page['id']
            ) as $candidate
        ) {
            if ((int) $candidate['id'] === $tokenId) {
                $token = $candidate;
                break;
            }
        }

        if (!$token) {
            return redirect()->back()->with(
                'error',
                'Tautan review tidak ditemukan.'
            );
        }

        try {
            $this->externalService->revoke(
                $token,
                $this->currentUserId()
            );
        } catch (\Throwable $exception) {
            return redirect()->back()->with(
                'error',
                'Tautan review belum dapat dicabut.'
            );
        }

        return redirect()->to(
            '/website/pages/external-review/'
            . $pageKey
        )->with(
            'success',
            'Tautan review berhasil dicabut.'
        );
    }

    private function assertReady(): void
    {
        if (!$this->externalService->ready()) {
            throw new RuntimeException(
                'Fitur review eksternal belum tersedia. Jalankan php spark migrate.'
            );
        }
    }

    private function currentUserId(): ?int
    {
        $userId = session()->get('user_id');

        return $userId !== null
            ? (int) $userId
            : null;
    }
}
