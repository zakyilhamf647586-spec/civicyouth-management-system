<?php

namespace App\Controllers;

use App\Models\ContactMessageModel;

class ContactMessageController extends BaseController
{
    protected ContactMessageModel $messageModel;

    public function __construct()
    {
        $this->messageModel = new ContactMessageModel();
    }

    public function index()
    {
        $keyword  = trim(
            (string) $this->request->getGet('keyword')
        );

        $category = trim(
            (string) $this->request->getGet('category')
        );

        $status = trim(
            (string) $this->request->getGet('status')
        );

        $builder = $this->messageModel
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC');

        if ($keyword !== '') {
            $builder
                ->groupStart()
                ->like('name', $keyword)
                ->orLike('email', $keyword)
                ->orLike('phone', $keyword)
                ->orLike('subject', $keyword)
                ->orLike('message', $keyword)
                ->groupEnd();
        }

        $allowedCategories = [
            'collaboration',
            'activity',
            'social',
            'business',
            'media',
            'general',
        ];

        if (in_array($category, $allowedCategories, true)) {
            $builder->where('category', $category);
        }

        $allowedStatuses = [
            'unread',
            'read',
            'replied',
            'archived',
        ];

        if (in_array($status, $allowedStatuses, true)) {
            $builder->where('status', $status);
        }

        $messages = $builder->paginate(
            15,
            'contact_messages'
        );

        return view('contact_messages/index', [
            'title'    => 'Pesan Masuk',
            'messages' => $messages,
            'pager'    => $this->messageModel->pager,
            'keyword'  => $keyword,
            'category' => $category,
            'status'   => $status,
        ]);
    }

    public function show(int $id)
    {
        $message = $this->messageModel->find($id);

        if (!$message) {
            return redirect()->to('/messages')
                ->with(
                    'error',
                    'Pesan tidak ditemukan.'
                );
        }

        if ($message['status'] === 'unread') {
            $this->messageModel->update($id, [
                'status' => 'read',
            ]);

            $message['status'] = 'read';
        }

        return view('contact_messages/show', [
            'title'   => 'Detail Pesan',
            'message' => $message,
        ]);
    }

    public function updateStatus(int $id)
    {
        $message = $this->messageModel->find($id);

        if (!$message) {
            return redirect()->to('/messages')
                ->with(
                    'error',
                    'Pesan tidak ditemukan.'
                );
        }

        $status = trim(
            (string) $this->request->getPost('status')
        );

        $allowedStatuses = [
            'unread',
            'read',
            'replied',
            'archived',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            return redirect()->back()
                ->with(
                    'error',
                    'Status pesan tidak valid.'
                );
        }

        $this->messageModel->update($id, [
            'status' => $status,
        ]);

        return redirect()
            ->to('/messages/' . $id)
            ->with(
                'success',
                'Status pesan berhasil diperbarui.'
            );
    }
}