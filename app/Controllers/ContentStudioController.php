<?php

namespace App\Controllers;

use App\Models\ContentPostModel;
use App\Models\ContentAssetModel;
use App\Libraries\OpenAIContentService;
use App\Libraries\ContentTemplateService;

class ContentStudioController extends BaseController
{
    protected $postModel;
    protected $assetModel;

    public function __construct()
    {
        $this->postModel  = new ContentPostModel();
        $this->assetModel = new ContentAssetModel();
    }

    public function index()
    {
        $data = [
            'title' => 'AI Content Studio',
            'posts' => $this->postModel
                ->orderBy('id', 'DESC')
                ->paginate(10, 'content_posts'),
            'pager' => $this->postModel->pager,
        ];

        return view('content_studio/index', $data);
    }

    public function create()
    {
        return view('content_studio/create', [
            'title' => 'Buat Konten AI',
        ]);
    }

    public function store()
    {
        $rules = [
            'category' => 'required',
            'template_type' => 'required',
            'content_images' => 'uploaded[content_images.0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Minimal upload 1 gambar untuk membuat konten.');
        }

        $files = $this->request->getFiles();

        if (empty($files['content_images'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'File gambar belum dipilih.');
        }

        $validFiles = [];

        foreach ($files['content_images'] as $file) {
            if (!$file->isValid()) {
                continue;
            }

            if (!in_array($file->getMimeType(), ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Format gambar harus JPG, JPEG, PNG, atau WEBP.');
            }

            if ($file->getSizeByUnit('mb') > 4) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ukuran tiap gambar maksimal 4MB.');
            }

            $validFiles[] = $file;
        }

        if (count($validFiles) < 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Minimal upload 1 gambar valid.');
        }

        if (count($validFiles) > 5) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Maksimal upload 5 gambar.');
        }

        $postId = $this->postModel->insert([
            'category'      => $this->request->getPost('category'),
            'template_type' => 'feed_portrait_permanent',
            'notes'         => $this->request->getPost('notes'),
            'status'        => 'draft',
            'created_by'    => session()->get('user_name') ?? 'Admin',
        ]);

        $sortOrder = 1;

        foreach ($validFiles as $file) {
            $fileName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/content_studio', $fileName);

            $this->assetModel->insert([
                'content_post_id' => $postId,
                'image_path'      => 'uploads/content_studio/' . $fileName,
                'original_name'   => $file->getClientName(),
                'sort_order'      => $sortOrder++,
            ]);
        }

        return redirect()->to('/content-studio/show/' . $postId)
            ->with('success', 'Draft konten berhasil dibuat. Silakan generate konten AI.');
    }

    public function show($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/content-studio')->with('error', 'Konten tidak ditemukan.');
        }

        $assets = $this->assetModel
            ->where('content_post_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        return view('content_studio/show', [
            'title'  => 'Detail Konten AI',
            'post'   => $post,
            'assets' => $assets,
        ]);
    }

    public function generate($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/content-studio')->with('error', 'Konten tidak ditemukan.');
        }

        $assets = $this->assetModel
            ->where('content_post_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        if (empty($assets)) {
            return redirect()->back()->with('error', 'Konten belum memiliki gambar.');
        }

        try {
            $service = new OpenAIContentService();
            $result = $service->generateSocialContent($post, $assets);

            $this->postModel->update($id, [
                'category'   => $result['category'] ?? $post['category'],
                'title'      => $result['title'] ?? null,
                'caption'    => $result['caption'] ?? null,
                'hashtags'   => implode(' ', $result['hashtags'] ?? []),
                'mentions'   => implode(' ', $result['mentions'] ?? []),
                'alt_text'   => $result['alt_text'] ?? null,
                'ai_summary' => $result['ai_summary'] ?? null,
                'status'     => 'generated',
            ]);

            return redirect()->to('/content-studio/show/' . $id)
                ->with('success', 'Konten berhasil digenerate oleh AI.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function updateText($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/content-studio')->with('error', 'Konten tidak ditemukan.');
        }

        $this->postModel->update($id, [
            'title'    => $this->request->getPost('title'),
            'caption'  => $this->request->getPost('caption'),
            'hashtags' => $this->request->getPost('hashtags'),
            'mentions' => $this->request->getPost('mentions'),
            'alt_text' => $this->request->getPost('alt_text'),
            'status'   => 'edited',
        ]);

        return redirect()->to('/content-studio/show/' . $id)
            ->with('success', 'Konten berhasil diperbarui.');
    }

    public function delete($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/content-studio')->with('error', 'Konten tidak ditemukan.');
        }

        $assets = $this->assetModel
            ->where('content_post_id', $id)
            ->findAll();

        foreach ($assets as $asset) {
            $path = FCPATH . $asset['image_path'];

            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->assetModel->where('content_post_id', $id)->delete();
        $this->postModel->delete($id);

        return redirect()->to('/content-studio')
            ->with('success', 'Konten berhasil dihapus.');
    }

    public function renderFeed($id)
    {
        $post = $this->postModel->find($id);

        if (!$post) {
            return redirect()->to('/content-studio')->with('error', 'Konten tidak ditemukan.');
        }

        $assets = $this->assetModel
            ->where('content_post_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        if (empty($assets)) {
            return redirect()->back()->with('error', 'Konten belum memiliki gambar.');
        }

        try {
            $templateService = new ContentTemplateService();
            $generatedImage = $templateService->renderFeedSquare($post, $assets);

            $this->postModel->update($id, [
                'generated_image' => $generatedImage,
                'status' => 'rendered',
            ]);

            return redirect()->to('/content-studio/show/' . $id)
                ->with('success', 'Feed visual berhasil dibuat.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}