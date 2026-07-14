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
            'event_title' => 'required',
            'event_date' => 'required',
            'event_time' => 'required',
            'event_location' => 'required',
            'activity_description' => 'required',
            'content_images' => 'uploaded[content_images.0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Mohon lengkapi seluruh data utama konten.');
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
            'category' => $this->request->getPost('category'),
            'template_type' => 'feed_portrait_permanent',
            'event_title' => $this->request->getPost('event_title'),
            'event_date' => $this->request->getPost('event_date'),
            'event_time' => $this->request->getPost('event_time'),
            'event_location' => $this->request->getPost('event_location'),
            'activity_description' => $this->request->getPost('activity_description'),
            'notes' => $this->request->getPost('notes'),
            'status' => 'draft',
            'created_by' => session()->get('user_name') ?? 'Admin',
        ]);

        $sortOrder = 1;

        foreach ($validFiles as $file) {
            $fileName = $file->getRandomName();
            $uploadDir = FCPATH . 'uploads/content_studio';

            $file->move($uploadDir, $fileName);

            $savedPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

            $this->optimizeImageForTemplate($savedPath);

            $this->assetModel->insert([
                'content_post_id' => $postId,
                'image_path' => 'uploads/content_studio/' . $fileName,
                'original_name' => $file->getClientName(),
                'sort_order' => $sortOrder++,
            ]);
        }

        return redirect()->to('/content-studio/show/' . $postId)
            ->with('success', 'Draft konten berhasil dibuat. Silakan generate teks AI dan feed visual.');
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
            $generatedImage = $templateService->renderFeedPortraitPermanent($post, $assets);

            $this->postModel->update($id, [
                'generated_image' => $generatedImage,
                'status' => 'rendered',
            ]);

            return redirect()->to('/content-studio/show/' . $id)
                ->with('success', 'Feed visual 4:5 berhasil dibuat.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function optimizeImageForTemplate(string $path, int $maxWidth = 2000, int $maxHeight = 2000): void
    {
        if (!file_exists($path)) {
            return;
        }

        $info = getimagesize($path);

        if (!$info) {
            return;
        }

        [$width, $height] = $info;
        $mime = $info['mime'] ?? '';

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);

        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $source = imagecreatefromjpeg($path);
        } elseif ($mime === 'image/png') {
            $source = imagecreatefrompng($path);
        } elseif ($mime === 'image/webp') {
            $source = imagecreatefromwebp($path);
        } else {
            return;
        }

        if (!$source) {
            return;
        }

        $target = imagecreatetruecolor($newWidth, $newHeight);

        // Background putih agar PNG transparan tetap aman
        $white = imagecolorallocate($target, 255, 255, 255);
        imagefilledrectangle($target, 0, 0, $newWidth, $newHeight, $white);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            imagejpeg($target, $path, 85);
        } elseif ($mime === 'image/png') {
            imagepng($target, $path, 7);
        } elseif ($mime === 'image/webp') {
            imagewebp($target, $path, 85);
        }

        imagedestroy($source);
        imagedestroy($target);
    }
}