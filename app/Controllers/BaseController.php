<?php

namespace App\Controllers;

use App\Models\PublicPageModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $helpers = [
        'url',
        'form',
        'site',
        'authorization',
        'public_cms',
    ];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
    }

    /**
     * @return array{page: ?array, preview: bool}
     */
    protected function publicCmsPage(
        string $pageKey
    ): array {
        $previewRequested =
            (string) $this->request->getGet(
                'cms_preview'
            ) === '1';

        $previewAllowed =
            $previewRequested
            && (bool) session()->get('isLoggedIn')
            && auth_can('website.pages.preview');

        $mode = $previewAllowed
            ? 'draft'
            : 'published';

        try {
            $db = db_connect();

            if (
                !$db->tableExists('public_pages')
                || !$db->tableExists(
                    'public_page_sections'
                )
            ) {
                return [
                    'page' => null,
                    'preview' => false,
                ];
            }

            $page = (new PublicPageModel())
                ->bundle($pageKey, $mode);

            return [
                'page' => $page,
                'preview' =>
                    $previewAllowed
                    && $page !== null,
            ];
        } catch (\Throwable $exception) {
            log_message(
                'warning',
                'Public CMS fallback used for '
                . $pageKey
                . ': '
                . $exception->getMessage()
            );

            return [
                'page' => null,
                'preview' => false,
            ];
        }
    }
}
