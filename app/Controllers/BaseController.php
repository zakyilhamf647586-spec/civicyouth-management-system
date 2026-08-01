<?php

namespace App\Controllers;

use App\Models\PublicPageModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Request-scoped external preview context.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $externalCmsPreviewContext = null;

    protected $helpers = [
        'url',
        'form',
        'site',
        'authorization',
        'public_cms',
        'website_navigation',
        'public_experience',
    ];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $path = '/' . ltrim(
            $request->getUri()->getPath(),
            '/'
        );

        $path = preg_replace(
            '#^/index\.php(?=/|$)#',
            '',
            $path
        ) ?: '/';

        if (
            $path === '/en'
            || str_starts_with($path, '/en/')
        ) {
            $request->setLocale('en');
        } elseif (
            preg_match(
                '#^/(?:'
                . 'home'
                . '|profil'
                . '|program(?:/[^/]+)?'
                . '|pengurus'
                . '|kegiatan(?:/[0-9]+)?'
                . '|kontak(?:/kirim)?'
                . '|login'
                . ')?$#',
                $path
            ) === 1
            || str_starts_with(
                $path,
                '/review/page/'
            )
            || str_starts_with(
                $path,
                '/website/pages/preview/'
            )
            || str_starts_with(
                $path,
                '/website/navigation/preview/'
            )
        ) {
            $request->setLocale('id');
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function useExternalCmsPreview(
        array $context
    ): void {
        $this->externalCmsPreviewContext = $context;
    }

    /**
     * @param array<string, mixed> $event
     */
    protected function recordCmsAudit(
        array $event
    ): void {
        (new \App\Libraries\CmsAuditService())
            ->record($event);
    }

    /**
     * Redirect back while preserving non-sensitive form values.
     *
     * CodeIgniter's withInput() stores the complete POST body in flashdata.
     * Account forms contain passwords, so those fields must be removed before
     * old input is made available to the next request.
     *
     * @param list<string> $sensitiveFields
     */
    protected function redirectBackWithSafeInput(
        array $sensitiveFields = [
            'password',
            'password_confirm',
            'current_password',
            'new_password',
            'new_password_confirm',
        ]
    ): RedirectResponse {
        $superglobals = service('superglobals');
        $post = $superglobals->getPostArray();

        foreach ($sensitiveFields as $field) {
            unset($post[$field]);
        }

        $security = config('Security');
        unset($post[$security->tokenName]);

        session()->setFlashdata('_ci_old_input', [
            'get' => $superglobals->getGetArray(),
            'post' => $post,
        ]);

        return redirect()->back();
    }

    /**
     * @return array{
     *     page: ?array,
     *     preview: bool,
     *     external: bool,
     *     external_token: ?string,
     *     external_meta: ?array,
     *     canonical_url: ?string
     * }
     */
    protected function publicCmsPage(
        string $pageKey
    ): array {
        if (
            is_array($this->externalCmsPreviewContext)
            && (
                $this->externalCmsPreviewContext[
                    'page_key'
                ] ?? null
            ) === $pageKey
        ) {
            $externalPage =
                $this->externalCmsPreviewContext[
                    'bundle'
                ] ?? null;

            if (
                $externalPage !== null
                && function_exists('public_localize_bundle')
            ) {
                $externalPage = public_localize_bundle(
                    $externalPage
                );
            }

            return [
                'page' => $externalPage,
                'preview' => true,
                'external' => true,
                'external_token' =>
                    $this->externalCmsPreviewContext[
                        'raw_token'
                    ] ?? null,
                'external_meta' =>
                    $this->externalCmsPreviewContext[
                        'meta'
                    ] ?? null,
                'canonical_url' =>
                    $this->externalCmsPreviewContext[
                        'canonical_url'
                    ] ?? null,
            ];
        }

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
                    'external' => false,
                    'external_token' => null,
                    'external_meta' => null,
                    'canonical_url' => null,
                ];
            }

            $page = (new PublicPageModel())
                ->bundle($pageKey, $mode);

            if (
                $page !== null
                && function_exists(
                    'public_localize_bundle'
                )
            ) {
                $page = public_localize_bundle($page);
            }

            return [
                'page' => $page,
                'preview' =>
                    $previewAllowed
                    && $page !== null,
                'external' => false,
                'external_token' => null,
                'external_meta' => null,
                'canonical_url' => null,
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
                'external' => false,
                'external_token' => null,
                'external_meta' => null,
                'canonical_url' => null,
            ];
        }
    }
}
