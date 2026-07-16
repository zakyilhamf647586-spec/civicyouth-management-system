<?php

namespace App\Controllers;

use App\Models\ContactMessageModel;

class PublicContactController extends BaseController
{
    protected ContactMessageModel $messageModel;

    public function __construct()
    {
        $this->messageModel = new ContactMessageModel();
    }

    public function index()
    {
        return view('public/contact', [
            'title' => 'Kontak dan Kolaborasi | GARDA 01',

            'metaDescription' =>
                'Hubungi GARDA 01 untuk kolaborasi kegiatan, program sosial, lingkungan, kepemudaan, usaha, media, dan pemberdayaan masyarakat.',

            'activePage' => 'contact',
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
            return redirect()->to('/kontak')
                ->with(
                    'success',
                    'Pesan Anda berhasil dikirim.'
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
                    'Mohon tunggu sebentar sebelum mengirim pesan berikutnya.'
                );
        }

        $rules = [
            'name' => [
                'label' => 'Nama lengkap',
                'rules' => 'required|min_length[3]|max_length[120]',
            ],

            'email' => [
                'label' => 'Alamat email',
                'rules' => 'permit_empty|valid_email|max_length[150]',
            ],

            'phone' => [
                'label' => 'Nomor WhatsApp',
                'rules' => 'required|min_length[8]|max_length[30]',
            ],

            'category' => [
                'label' => 'Kategori pesan',
                'rules' =>
                    'required|in_list[collaboration,activity,social,business,media,general]',
            ],

            'subject' => [
                'label' => 'Subjek',
                'rules' => 'required|min_length[4]|max_length[180]',
            ],

            'message' => [
                'label' => 'Isi pesan',
                'rules' => 'required|min_length[10]|max_length[2000]',
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

        return redirect()->to('/kontak')
            ->with(
                'success',
                'Pesan berhasil dikirim. Tim GARDA 01 akan menindaklanjutinya.'
            );
    }
}