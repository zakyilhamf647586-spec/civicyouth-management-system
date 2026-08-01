<?php

namespace App\Controllers;

use App\Models\ContactMessageModel;

class PublicContactController extends BaseController
{
    private const CONTACT_IP_CAPACITY = 5;
    private const CONTACT_WINDOW_SECONDS = 600;

    protected ContactMessageModel $messageModel;

    public function __construct()
    {
        $this->messageModel = new ContactMessageModel();
    }

    public function index()
    {
        $cmsState = $this->publicCmsPage('contact');
        $cmsPage = $cmsState['page'];

        return view('public/contact', [
            'title' => $cmsPage['title']
                ?? public_t(
                    'seo.contact_title',
                    'Kontak dan Kolaborasi | GARDA 01'
                ),

            'metaDescription' =>
                $cmsPage['meta_description']
                ?? public_t(
                    'seo.contact_description',
                    'Hubungi GARDA 01 untuk kolaborasi kegiatan, program sosial, lingkungan, kepemudaan, usaha, media, dan pemberdayaan masyarakat.'
                ),

            'activePage' => 'contact',
            'cmsPage' => $cmsPage,
            'cmsPreview' => $cmsState['preview'],
            'externalPreview' =>
                $cmsState['external'],
            'externalPreviewToken' =>
                $cmsState['external_token'],
            'externalPreviewMeta' =>
                $cmsState['external_meta'],
            'canonicalUrl' =>
                $cmsState['canonical_url'],
        ]);
    }

    public function submit()
    {
        /*
         * Honeypot sederhana untuk menahan bot.
         * Pengunjung asli tidak akan mengisi field website.
         */
        if (
            trim((string) $this->request->getPost('website')) !== ''
        ) {
            return redirect()->to(public_url('/kontak'))
                ->with(
                    'success',
                    public_t(
                        'contact.success_generic',
                        'Pesan Anda berhasil dikirim.'
                    )
                );
        }

        if (!$this->allowSubmission()) {
            return redirect()->to(public_url('/kontak'))
                ->with(
                    'error',
                    public_t(
                        'contact.rate_limit',
                        'Terlalu banyak pesan dikirim dari jaringan ini. Tunggu beberapa menit lalu coba kembali.'
                    )
                );
        }

        /*
         * Batas satu pengiriman per 60 detik dalam sesi yang sama.
         */
        $lastSubmission = (int) session()->get(
            'contact_last_submission'
        );

        if (
            $lastSubmission > 0
            && (time() - $lastSubmission) < 60
        ) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    public_t(
                        'contact.session_limit',
                        'Mohon tunggu sebentar sebelum mengirim pesan berikutnya.'
                    )
                );
        }

        $rules = [
            'name' => [
                'label' => public_t(
                    'contact.label_name',
                    'Nama lengkap'
                ),
                'rules' => 'required|min_length[3]|max_length[120]',
                'errors' => [
                    'required' => public_t(
                        'validation.required'
                    ),
                    'min_length' => public_t(
                        'validation.min_length'
                    ),
                    'max_length' => public_t(
                        'validation.max_length'
                    ),
                ],
            ],

            'email' => [
                'label' => public_t(
                    'contact.label_email',
                    'Alamat email'
                ),
                'rules' => 'permit_empty|valid_email|max_length[150]',
                'errors' => [
                    'valid_email' => public_t(
                        'validation.valid_email'
                    ),
                    'max_length' => public_t(
                        'validation.max_length'
                    ),
                ],
            ],

            'phone' => [
                'label' => public_t(
                    'contact.label_phone',
                    'Nomor WhatsApp'
                ),
                'rules' => 'required|min_length[8]|max_length[30]',
                'errors' => [
                    'required' => public_t(
                        'validation.required'
                    ),
                    'min_length' => public_t(
                        'validation.min_length'
                    ),
                    'max_length' => public_t(
                        'validation.max_length'
                    ),
                ],
            ],

            'category' => [
                'label' => public_t(
                    'contact.label_category',
                    'Kategori pesan'
                ),
                'rules' =>
                    'required|in_list[collaboration,activity,social,business,media,general]',
                'errors' => [
                    'required' => public_t(
                        'validation.required'
                    ),
                    'in_list' => public_t(
                        'validation.in_list'
                    ),
                ],
            ],

            'subject' => [
                'label' => public_t(
                    'contact.label_subject',
                    'Subjek'
                ),
                'rules' => 'required|min_length[4]|max_length[180]',
                'errors' => [
                    'required' => public_t(
                        'validation.required'
                    ),
                    'min_length' => public_t(
                        'validation.min_length'
                    ),
                    'max_length' => public_t(
                        'validation.max_length'
                    ),
                ],
            ],

            'message' => [
                'label' => public_t(
                    'contact.label_message',
                    'Isi pesan'
                ),
                'rules' => 'required|min_length[10]|max_length[2000]',
                'errors' => [
                    'required' => public_t(
                        'validation.required'
                    ),
                    'min_length' => public_t(
                        'validation.min_length'
                    ),
                    'max_length' => public_t(
                        'validation.max_length'
                    ),
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'errors',
                    $this->validator->getErrors()
                );
        }

        $this->messageModel->insert([
            'name' => trim(
                (string) $this->request->getPost('name')
            ),

            'email' => trim(
                (string) $this->request->getPost('email')
            ) ?: null,

            'phone' => trim(
                (string) $this->request->getPost('phone')
            ),

            'category' => $this->request->getPost('category'),

            'subject' => trim(
                (string) $this->request->getPost('subject')
            ),

            'message' => trim(
                (string) $this->request->getPost('message')
            ),

            'status' => 'unread',

            'source_ip' => $this->request->getIPAddress(),

            'user_agent' => mb_substr(
                (string) $this->request->getUserAgent(),
                0,
                255
            ),
        ]);

        session()->set(
            'contact_last_submission',
            time()
        );

        return redirect()->to(public_url('/kontak'))
            ->with(
                'success',
                public_t(
                    'contact.success',
                    'Pesan berhasil dikirim. Tim GARDA 01 akan menindaklanjutinya.'
                )
            );
    }

    private function allowSubmission(): bool
    {
        $key = 'public-contact-'
            . hash(
                'sha256',
                $this->request->getIPAddress()
            );

        return service('throttler')->check(
            $key,
            self::CONTACT_IP_CAPACITY,
            self::CONTACT_WINDOW_SECONDS
        );
    }
}
