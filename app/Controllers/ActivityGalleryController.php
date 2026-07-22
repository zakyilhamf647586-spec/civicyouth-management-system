<?php

namespace App\Controllers;

use App\Models\ActivityImageModel;
use App\Models\ActivityModel;
use App\Libraries\SecureUploadService;

class ActivityGalleryController extends BaseController
{
    protected ActivityModel $activityModel;
    protected ActivityImageModel $imageModel;
    protected SecureUploadService $uploadService;

    public function __construct()
    {
        $this->activityModel = new ActivityModel();
        $this->imageModel    = new ActivityImageModel();
        $this->uploadService = new SecureUploadService();
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


        $uploadedFiles = [];
        $firstNewFile  = null;
        $database      = db_connect();

        $database->transBegin();

        try {
            foreach ($validFiles as $file) {
                $stored = $this->uploadService->storeImage(
                    $file,
                    'uploads/activities',
                    [
                        'max_bytes' => 4 * 1024 * 1024,
                        'max_pixels' => 36_000_000,
                        'target_max_width' => 2400,
                        'target_max_height' => 1800,
                    ]
                );

                $newName = $stored['file_name'];

                $uploadedFiles[] = $newName;
                $firstNewFile ??= $newName;
                $nextOrder++;

                $isCover = empty($existingCover)
                    && $firstNewFile === $newName;

                $insertedId = $this->imageModel->insert([
                    'activity_id'   => $activityId,
                    'image_file'    => $newName,
                    'caption'       => null,
                    'is_cover'      => $isCover ? 1 : 0,
                    'display_order' => $nextOrder,
                ]);

                if ($insertedId === false) {
                    throw new \RuntimeException(
                        'Data salah satu foto gagal disimpan.'
                    );
                }

                if ($isCover) {
                    $existingCover = [
                        'image_file' => $newName,
                    ];

                    if (!$this->activityModel->update(
                        $activityId,
                        ['documentation_file' => $newName]
                    )) {
                        throw new \RuntimeException(
                            'Foto utama kegiatan gagal diperbarui.'
                        );
                    }
                }
            }

            if (!$database->transCommit()) {
                throw new \RuntimeException(
                    'Transaksi upload galeri gagal disimpan.'
                );
            }

            return redirect()
                ->to('/activities/gallery/' . $activityId)
                ->with(
                    'success',
                    count($uploadedFiles)
                    . ' foto berhasil ditambahkan.'
                );
        } catch (\Throwable $exception) {
            $database->transRollback();

            foreach ($uploadedFiles as $uploadedFile) {
                $this->uploadService->deleteManagedFile(
                    'uploads/activities/' . basename($uploadedFile),
                    ['uploads/activities']
                );
            }

            $message = $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Galeri gagal diunggah. Silakan coba kembali.';

            return redirect()->back()
                ->with('error', $message);
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
        $database = db_connect();
        $database->transBegin();

        try {
            if ($this->imageModel->delete($imageId) === false) {
                throw new \RuntimeException('Foto gagal dihapus.');
            }

            if ($wasCover) {
                $replacement = $this->imageModel
                    ->where('activity_id', $activityId)
                    ->orderBy('display_order', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->first();

                if ($replacement) {
                    if ($this->imageModel->update(
                        $replacement['id'],
                        ['is_cover' => 1]
                    ) === false) {
                        throw new \RuntimeException(
                            'Foto pengganti gagal ditetapkan.'
                        );
                    }

                    if ($this->activityModel->update(
                        $activityId,
                        [
                            'documentation_file' =>
                                $replacement['image_file'],
                        ]
                    ) === false) {
                        throw new \RuntimeException(
                            'Cover kegiatan gagal disinkronkan.'
                        );
                    }
                } elseif ($this->activityModel->update(
                    $activityId,
                    ['documentation_file' => null]
                ) === false) {
                    throw new \RuntimeException(
                        'Cover kegiatan gagal dikosongkan.'
                    );
                }
            }

            if ($database->transCommit() === false) {
                throw new \RuntimeException(
                    'Perubahan galeri gagal disimpan.'
                );
            }
        } catch (\Throwable $exception) {
            $database->transRollback();

            $message = $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Foto gagal dihapus.';

            return redirect()
                ->to('/activities/gallery/' . $activityId)
                ->with('error', $message);
        }

        $this->uploadService->deleteManagedFile(
            'uploads/activities/' . basename($image['image_file']),
            ['uploads/activities']
        );

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