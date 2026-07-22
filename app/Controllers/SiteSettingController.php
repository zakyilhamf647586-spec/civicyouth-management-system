<?php

namespace App\Controllers;

use App\Models\SiteSettingModel;
use App\Libraries\SecureUploadService;

class SiteSettingController extends BaseController
{
    protected SiteSettingModel $settingModel;
    protected SecureUploadService $uploadService;

    public function __construct()
    {
        $this->settingModel = new SiteSettingModel();
        $this->uploadService = new SecureUploadService();
    }

    public function index()
    {
        return view('settings/website', [
            'title'    => 'Pengaturan Website',
            'settings' => $this->settingModel
                ->getSettingsArray(),
            'groups'   => $this->getSettingGroups(),
        ]);
    }

    public function update()
    {
        $groups = $this->getSettingGroups();
        $values = [];
        $errors = [];

        foreach ($groups as $group) {
            foreach ($group['fields'] as $key => $field) {
                if ($field['type'] === 'file') {
                    continue;
                }

                $value = trim(
                    (string) $this->request->getPost($key)
                );

                $maxLength = $field['max_length'] ?? 2000;

                if (
                    !empty($field['required'])
                    && $value === ''
                ) {
                    $errors[] = $field['label'] . ' wajib diisi.';
                    continue;
                }

                if (mb_strlen($value) > $maxLength) {
                    $errors[] =
                        $field['label']
                        . ' maksimal '
                        . $maxLength
                        . ' karakter.';

                    continue;
                }

                if (
                    $field['type'] === 'email'
                    && $value !== ''
                    && !filter_var(
                        $value,
                        FILTER_VALIDATE_EMAIL
                    )
                ) {
                    $errors[] =
                        $field['label']
                        . ' tidak valid.';

                    continue;
                }

                if (
                    $field['type'] === 'url'
                    && $value !== ''
                    && !filter_var(
                        $value,
                        FILTER_VALIDATE_URL
                    )
                ) {
                    $errors[] =
                        $field['label']
                        . ' harus berupa URL lengkap.';

                    continue;
                }

                $values[$key] = $value;
            }
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }

        $uploadedFiles = [];
        $oldFilesToDelete = [];

        try {
            $uploadDefinitions = [
                'site_logo' => 3,
                'site_favicon' => 2,
                'seo_og_image' => 4,
            ];

            foreach ($uploadDefinitions as $fieldName => $maxMb) {
                $oldValue = $this->settingModel->getValue(
                    $fieldName,
                    'assets/img/logo-rw01.png'
                );

                $upload = $this->processImageUpload(
                    $fieldName,
                    $oldValue,
                    $maxMb
                );

                $values[$fieldName] = $upload['value'];

                if ($upload['new_file'] !== null) {
                    $uploadedFiles[] = $upload['new_file'];
                }

                if ($upload['old_file'] !== null) {
                    $oldFilesToDelete[] = $upload['old_file'];
                }
            }

            if (!$this->settingModel->saveValues($values)) {
                throw new \RuntimeException(
                    'Pengaturan gagal disimpan.'
                );
            }

            foreach (array_unique($oldFilesToDelete) as $oldFile) {
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }

            return redirect()
                ->to('/settings/website')
                ->with(
                    'success',
                    'Pengaturan website berhasil diperbarui.'
                );
        } catch (\Throwable $exception) {
            foreach (array_unique($uploadedFiles) as $newFile) {
                if (is_file($newFile)) {
                    unlink($newFile);
                }
            }

            $message = $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Pengaturan website gagal diperbarui.';

            return redirect()->back()
                ->withInput()
                ->with('errors', [$message]);
        }
    }

    /**
     * @return array{value: ?string, new_file: ?string, old_file: ?string}
     */
    private function processImageUpload(
        string $fieldName,
        ?string $oldValue,
        int $maximumMegabytes
    ): array {
        $file = $this->request->getFile($fieldName);

        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [
                'value' => $oldValue,
                'new_file' => null,
                'old_file' => null,
            ];
        }

        $stored = $this->uploadService->storeImage(
            $file,
            'uploads/settings',
            [
                'max_bytes' => $maximumMegabytes * 1024 * 1024,
                'max_pixels' => 32_000_000,
                'target_max_width' => $fieldName === 'site_favicon' ? 1024 : 2400,
                'target_max_height' => $fieldName === 'site_favicon' ? 1024 : 2400,
                'allow_ico' => $fieldName === 'site_favicon',
            ]
        );

        $oldAbsolutePath = null;

        if (
            !empty($oldValue)
            && str_starts_with(
                trim(str_replace('\\', '/', $oldValue), '/'),
                'uploads/settings/'
            )
        ) {
            $oldAbsolutePath = FCPATH
                . 'uploads/settings/'
                . basename((string) $oldValue);
        }

        return [
            'value' => $stored['relative_path'],
            'new_file' => $stored['absolute_path'],
            'old_file' => $oldAbsolutePath,
        ];
    }

    private function getSettingGroups(): array
    {
        return [
            'identity' => [
                'title' => 'Identitas & Branding',
                'description' =>
                    'Atur identitas utama GARDA 01 yang tampil pada website.',

                'fields' => [
                    'organization_name' => [
                        'label' => 'Nama Utama',
                        'type'  => 'text',
                        'max_length' => 80,
                        'required' => true,
                    ],
                    'organization_full_name' => [
                        'label' => 'Kepanjangan GARDA 01',
                        'type'  => 'text',
                        'max_length' => 150,
                        'required' => true,
                    ],
                    'organization_legal_name' => [
                        'label' => 'Nama Resmi Organisasi',
                        'type'  => 'text',
                        'max_length' => 180,
                        'required' => true,
                    ],
                    'organization_tagline' => [
                        'label' => 'Slogan',
                        'type'  => 'text',
                        'max_length' => 150,
                        'required' => true,
                    ],
                    'organization_description' => [
                        'label' => 'Deskripsi Singkat',
                        'type'  => 'textarea',
                        'max_length' => 700,
                    ],
                    'site_logo' => [
                        'label' => 'Logo Utama',
                        'type'  => 'file',
                    ],
                    'site_favicon' => [
                        'label' => 'Favicon',
                        'type'  => 'file',
                    ],
                ],
            ],

            'contact' => [
                'title' => 'Kontak & Lokasi',
                'description' =>
                    'Informasi kontak resmi organisasi.',

                'fields' => [
                    'contact_email' => [
                        'label' => 'Email Resmi',
                        'type'  => 'email',
                        'max_length' => 150,
                    ],
                    'contact_whatsapp' => [
                        'label' => 'Nomor WhatsApp',
                        'type'  => 'tel',
                        'max_length' => 30,
                    ],
                    'contact_address' => [
                        'label' => 'Alamat',
                        'type'  => 'textarea',
                        'max_length' => 500,
                    ],
                    'contact_village' => [
                        'label' => 'Kelurahan',
                        'type'  => 'text',
                        'max_length' => 100,
                    ],
                    'contact_district' => [
                        'label' => 'Kecamatan',
                        'type'  => 'text',
                        'max_length' => 100,
                    ],
                    'contact_city' => [
                        'label' => 'Kota',
                        'type'  => 'text',
                        'max_length' => 100,
                    ],
                    'contact_province' => [
                        'label' => 'Provinsi',
                        'type'  => 'text',
                        'max_length' => 100,
                    ],
                    'contact_location_description' => [
                        'label' => 'Deskripsi Lokasi',
                        'type'  => 'textarea',
                        'max_length' => 700,
                    ],
                    'contact_maps_url' => [
                        'label' => 'URL Google Maps',
                        'type'  => 'url',
                        'max_length' => 500,
                    ],
                ],
            ],

            'social' => [
                'title' => 'Media Sosial',
                'description' =>
                    'Gunakan URL lengkap, termasuk https://.',

                'fields' => [
                    'instagram_url' => [
                        'label' => 'Instagram',
                        'type'  => 'url',
                        'max_length' => 255,
                    ],
                    'tiktok_url' => [
                        'label' => 'TikTok',
                        'type'  => 'url',
                        'max_length' => 255,
                    ],
                    'youtube_url' => [
                        'label' => 'YouTube',
                        'type'  => 'url',
                        'max_length' => 255,
                    ],
                    'facebook_url' => [
                        'label' => 'Facebook',
                        'type'  => 'url',
                        'max_length' => 255,
                    ],
                ],
            ],

            'footer' => [
                'title' => 'Footer Website',
                'description' =>
                    'Konten bagian bawah website publik.',

                'fields' => [
                    'footer_heading' => [
                        'label' => 'Judul Footer',
                        'type'  => 'text',
                        'max_length' => 100,
                    ],
                    'footer_description' => [
                        'label' => 'Deskripsi Footer',
                        'type'  => 'textarea',
                        'max_length' => 700,
                    ],
                    'footer_note' => [
                        'label' => 'Catatan Resmi',
                        'type'  => 'text',
                        'max_length' => 255,
                    ],
                    'footer_copyright' => [
                        'label' => 'Teks Hak Cipta',
                        'type'  => 'text',
                        'max_length' => 180,
                    ],
                ],
            ],

            'seo' => [
                'title' => 'Branding & SEO',
                'description' =>
                    'Atur metadata default website dan tampilan ketika dibagikan.',

                'fields' => [
                    'seo_title' => [
                        'label' => 'Judul SEO Default',
                        'type'  => 'text',
                        'max_length' => 180,
                    ],
                    'seo_description' => [
                        'label' => 'Deskripsi SEO',
                        'type'  => 'textarea',
                        'max_length' => 500,
                    ],
                    'seo_keywords' => [
                        'label' => 'Kata Kunci',
                        'type'  => 'text',
                        'max_length' => 500,
                    ],
                    'seo_og_image' => [
                        'label' => 'Gambar Berbagi Sosial',
                        'type'  => 'file',
                    ],
                ],
            ],
        ];
    }
}