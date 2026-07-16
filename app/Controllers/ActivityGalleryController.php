<?php

namespace App\Controllers;

use App\Models\ActivityImageModel;
use App\Models\ActivityModel;

class ActivityGalleryController extends BaseController
{
    protected ActivityModel $activityModel;
    protected ActivityImageModel $imageModel;

    public function __construct()
    {
        $this->activityModel = new ActivityModel();
        $this->imageModel    = new ActivityImageModel();
    }

    public function index(int $activityId)
    {
        $activity = $this->activityModel->find($activityId);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with('error', 'Data kegiatan tidak ditemukan.');
        }

        return view('activities/gallery', [
            'title'    => 'Galeri Kegiatan',
            'activity' => $activity,
            'images'   => $this->imageModel
                ->getActivityImages($activityId),
        ]);
    }

    public function upload(int $activityId)
    {
        $activity = $this->activityModel->find($activityId);

        if (!$activity) {
            return redirect()->to('/activities')
                ->with('error', 'Data kegiatan tidak ditemukan.');
        }

        $files = $this->request->getFileMultiple('images');

        if (empty($files)) {
            return redirect()->back()
                ->with('error', 'Pilih minimal satu foto.');
        }

        $validFiles = array_filter(
            $files,
            static fn ($file) =>
                $file
                && $file->getError() !== UPLOAD_ERR_NO_FILE
        );

        if (empty($validFiles)) {
            return redirect()->back()
                ->with('error', 'Pilih minimal satu foto.');
        }

        if (count($validFiles) > 12) {
            return redirect()->back()
                ->with(
                    'error',
                    'Maksimal 12 foto dalam satu kali upload.'
                );
        }

        $existingCount = $this->imageModel
            ->where('activity_id', $activityId)
            ->countAllResults();

        if (($existingCount + count($validFiles)) > 30) {
            return redirect()->back()
                ->with(
                    'error',
                    'Maksimal 30 foto untuk setiap kegiatan.'
                );
        }

        $maxOrderResult = $this->imageModel
            ->selectMax('display_order')
            ->where('activity_id', $activityId)
            ->first();

        $nextOrder = (int) (
            $maxOrderResult['display_order'] ?? 0
        );

        $existingCover = $this->imageModel
            ->getCoverImage($activityId);

        $directory = FCPATH . 'uploads/activities';

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $uploadedFiles = [];
        $firstNewFile  = null;

        try {
            foreach ($validFiles as $file) {
                if (!$file->isValid()) {
                    throw new \RuntimeException(
                        'Salah satu foto gagal diunggah.'
                    );
                }

                if ($file->getSize() > (4 * 1024 * 1024)) {
                    throw new \RuntimeException(
                        'Ukuran setiap foto maksimal 4 MB.'
                    );
                }

                $allowedMimes = [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/webp',
                ];

                if (
                    !in_array(
                        $file->getMimeType(),
                        $allowedMimes,
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'Foto harus berformat JPG, PNG, atau WEBP.'
                    );
                }

                $newName = $file->getRandomName();
                $file->move($directory, $newName);

                $uploadedFiles[] = $newName;
                $firstNewFile ??= $newName;

                $nextOrder++;

                $isCover = empty($existingCover)
                    && $firstNewFile === $newName;

                $this->imageModel->insert([
                    'activity_id'  => $activityId,
                    'image_file'   => $newName,
                    'caption'      => null,
                    'is_cover'     => $isCover ? 1 : 0,
                    'display_order'=> $nextOrder,
                ]);

                if ($isCover) {
                    $existingCover = [
                        'image_file' => $newName,
                    ];

                    $this->activityModel->update(
                        $activityId,
                        [
                            'documentation_file' => $newName,
                        ]
                    );
                }
            }

            return redirect()
                ->to('/activities/gallery/' . $activityId)
                ->with(
                    'success',
                    count($uploadedFiles)
                    . ' foto berhasil ditambahkan.'
                );
        } catch (\RuntimeException $e) {
            foreach ($uploadedFiles as $uploadedFile) {
                $filePath = $directory
                    . DIRECTORY_SEPARATOR
                    . $uploadedFile;

                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }

            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function update(int $activityId, int $imageId)
    {
        $image = $this->findOwnedImage(
            $activityId,
            $imageId
        );

        if (!$image) {
            return redirect()
                ->to('/activities/gallery/' . $activityId)
                ->with('error', 'Foto tidak ditemukan.');
        }

        $caption = trim(
            (string) $this->request->getPost('caption')
        );

        $displayOrder = (int) $this->request
            ->getPost('display_order');

        if (mb_strlen($caption) > 255) {
            return redirect()->back()
                ->with(
                    'error',
                    'Caption maksimal 255 karakter.'
                );
        }

        $this->imageModel->update($imageId, [
            'caption'       => $caption,
            'display_order' => max(0, $displayOrder),
        ]);

        return redirect()
            ->to('/activities/gallery/' . $activityId)
            ->with(
                'success',
                'Informasi foto berhasil diperbarui.'
            );
    }

    public function setCover(int $activityId, int $imageId)
    {
        $image = $this->findOwnedImage(
            $activityId,
            $imageId
        );

        if (!$image) {
            return redirect()
                ->to('/activities/gallery/' . $activityId)
                ->with('error', 'Foto tidak ditemukan.');
        }

        $db = db_connect();
        $db->transStart();

        $this->imageModel
            ->where('activity_id', $activityId)
            ->set(['is_cover' => 0])
            ->update();

        $this->imageModel->update($imageId, [
            'is_cover' => 1,
        ]);

        /*
         * Sinkronisasi dengan field lama.
         * Beranda dan kartu kegiatan otomatis memakai cover ini.
         */
        $this->activityModel->update($activityId, [
            'documentation_file' => $image['image_file'],
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->to('/activities/gallery/' . $activityId)
                ->with(
                    'error',
                    'Foto utama gagal diperbarui.'
                );
        }

        return redirect()
            ->to('/activities/gallery/' . $activityId)
            ->with(
                'success',
                'Foto utama kegiatan berhasil diperbarui.'
            );
    }

    public function delete(int $activityId, int $imageId)
    {
        $image = $this->findOwnedImage(
            $activityId,
            $imageId
        );

        if (!$image) {
            return redirect()
                ->to('/activities/gallery/' . $activityId)
                ->with('error', 'Foto tidak ditemukan.');
        }

        $wasCover = (int) $image['is_cover'] === 1;

        $this->imageModel->delete($imageId);

        $filePath = FCPATH
            . 'uploads/activities/'
            . $image['image_file'];

        if (is_file($filePath)) {
            unlink($filePath);
        }

        if ($wasCover) {
            $replacement = $this->imageModel
                ->where('activity_id', $activityId)
                ->orderBy('display_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->first();

            if ($replacement) {
                $this->imageModel->update(
                    $replacement['id'],
                    ['is_cover' => 1]
                );

                $this->activityModel->update(
                    $activityId,
                    [
                        'documentation_file' =>
                            $replacement['image_file'],
                    ]
                );
            } else {
                $this->activityModel->update(
                    $activityId,
                    ['documentation_file' => null]
                );
            }
        }

        return redirect()
            ->to('/activities/gallery/' . $activityId)
            ->with('success', 'Foto berhasil dihapus.');
    }

    private function findOwnedImage(
        int $activityId,
        int $imageId
    ): ?array {
        return $this->imageModel
            ->where('id', $imageId)
            ->where('activity_id', $activityId)
            ->first();
    }
}