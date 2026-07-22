<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

/**
 * Centralized upload validation for files accepted by the portal.
 *
 * Raster images are decoded and re-encoded before being stored. This removes
 * appended payloads and most metadata while ensuring the resulting extension
 * matches the real image format.
 */
final class SecureUploadService
{
    private const RASTER_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const ICO_MIMES = [
        'image/x-icon',
        'image/vnd.microsoft.icon',
    ];

    /**
     * @return array{
     *   file_name:string,
     *   relative_path:string,
     *   absolute_path:string,
     *   original_name:string,
     *   mime:string,
     *   width:int,
     *   height:int,
     *   size:int
     * }
     */
    public function storeImage(
        UploadedFile $file,
        string $relativeDirectory,
        array $options = []
    ): array {
        $maximumBytes = (int) ($options['max_bytes'] ?? 6 * 1024 * 1024);
        // Hard ceilings prevent memory exhaustion during GD decoding even when
        // a caller accidentally requests a larger allowance.
        $maximumPixels = min(
            16_000_000,
            (int) ($options['max_pixels'] ?? 16_000_000)
        );
        $maximumDimension = min(
            8_000,
            (int) ($options['max_dimension'] ?? 8_000)
        );
        $targetMaxWidth = (int) ($options['target_max_width'] ?? 2_400);
        $targetMaxHeight = (int) ($options['target_max_height'] ?? 2_400);
        $allowIco = (bool) ($options['allow_ico'] ?? false);

        $this->assertUploadIsUsable($file, $maximumBytes);

        $temporaryPath = $file->getTempName();
        $realMime = $this->detectMime($temporaryPath);
        $iconDimensions = $allowIco
            ? $this->readIcoDimensions($temporaryPath)
            : null;

        if ($iconDimensions !== null) {
            [$width, $height] = $iconDimensions;

            return $this->storeValidatedIcon(
                $file,
                $temporaryPath,
                $relativeDirectory = $this->normalizeUploadDirectory($relativeDirectory),
                $absoluteDirectory = $this->prepareUploadDirectory($relativeDirectory),
                $width,
                $height
            );
        }

        $imageInfo = @getimagesize($temporaryPath);

        if ($imageInfo === false) {
            throw new RuntimeException('File tidak dapat dibaca sebagai gambar yang valid.');
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);

        if ($width < 1 || $height < 1) {
            throw new RuntimeException('Dimensi gambar tidak valid.');
        }

        if ($width > $maximumDimension || $height > $maximumDimension) {
            throw new RuntimeException(
                'Dimensi gambar terlalu besar. Maksimal '
                . number_format($maximumDimension, 0, ',', '.')
                . ' piksel pada setiap sisi.'
            );
        }

        if (($width * $height) > $maximumPixels) {
            throw new RuntimeException('Resolusi gambar terlalu besar untuk diproses dengan aman.');
        }

        $this->assertImageMemoryBudget(
            $width,
            $height,
            $targetMaxWidth,
            $targetMaxHeight
        );

        $relativeDirectory = $this->normalizeUploadDirectory($relativeDirectory);
        $absoluteDirectory = $this->prepareUploadDirectory($relativeDirectory);

        if (isset(self::RASTER_MIMES[$realMime])) {
            return $this->reencodeRasterImage(
                $file,
                $temporaryPath,
                $realMime,
                $relativeDirectory,
                $absoluteDirectory,
                $width,
                $height,
                $targetMaxWidth,
                $targetMaxHeight
            );
        }

        throw new RuntimeException('Format gambar harus JPG, PNG, atau WEBP. ICO hanya diizinkan untuk favicon.');
    }

    /**
     * Validate an import spreadsheet without persisting it in public storage.
     *
     * @return array{extension:string,mime:string,size:int,temp_path:string}
     */
    public function validateSpreadsheet(
        UploadedFile $file,
        int $maximumBytes = 5_242_880
    ): array {
        $this->assertUploadIsUsable($file, $maximumBytes);

        $extension = strtolower((string) $file->getClientExtension());

        if (!in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            throw new RuntimeException('File import harus menggunakan format XLSX, XLS, atau CSV.');
        }

        $temporaryPath = $file->getTempName();
        $mime = $this->detectMime($temporaryPath);
        $header = (string) file_get_contents($temporaryPath, false, null, 0, 8);

        if ($extension === 'xlsx') {
            if (!str_starts_with($header, "PK")) {
                throw new RuntimeException('Struktur file XLSX tidak valid.');
            }

            if (class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive();
                $opened = $zip->open($temporaryPath);

                if (
                    $opened !== true
                    || $zip->locateName('[Content_Types].xml') === false
                    || $zip->locateName('xl/workbook.xml') === false
                ) {
                    if ($opened === true) {
                        $zip->close();
                    }

                    throw new RuntimeException('Arsip XLSX tidak memiliki struktur workbook yang valid.');
                }

                $entryCount = $zip->numFiles;
                $totalUncompressedBytes = 0;

                if ($entryCount > 5_000) {
                    $zip->close();
                    throw new RuntimeException('Arsip XLSX memiliki terlalu banyak entry.');
                }

                for ($index = 0; $index < $entryCount; $index++) {
                    $stat = $zip->statIndex($index);

                    if (!is_array($stat)) {
                        continue;
                    }

                    $entryName = str_replace('\\', '/', (string) ($stat['name'] ?? ''));
                    $entrySize = (int) ($stat['size'] ?? 0);

                    if (
                        str_starts_with($entryName, '/')
                        || str_contains($entryName, '../')
                    ) {
                        $zip->close();
                        throw new RuntimeException('Arsip XLSX mengandung path yang tidak aman.');
                    }

                    if ($entrySize > 50 * 1024 * 1024) {
                        $zip->close();
                        throw new RuntimeException('Salah satu entry XLSX terlalu besar.');
                    }

                    $totalUncompressedBytes += $entrySize;

                    if ($totalUncompressedBytes > 100 * 1024 * 1024) {
                        $zip->close();
                        throw new RuntimeException('Ukuran ekstraksi XLSX terlalu besar.');
                    }
                }

                $zip->close();
            }
        } elseif ($extension === 'xls') {
            $oleSignature = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";

            if ($header !== $oleSignature) {
                throw new RuntimeException('Struktur file XLS tidak valid.');
            }
        } else {
            $sample = (string) file_get_contents($temporaryPath, false, null, 0, 8192);

            if (str_contains($sample, "\0")) {
                throw new RuntimeException('File CSV mengandung data biner yang tidak valid.');
            }
        }

        $allowedMimes = [
            'application/zip',
            'application/octet-stream',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/x-ole-storage',
            'text/plain',
            'text/csv',
            'application/csv',
        ];

        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('MIME type file import tidak dikenali.');
        }

        return [
            'extension' => $extension,
            'mime' => $mime,
            'size' => (int) $file->getSize(),
            'temp_path' => $temporaryPath,
        ];
    }

    public function deleteManagedFile(
        ?string $relativePath,
        array $allowedDirectories = ['uploads']
    ): void {
        $relativePath = trim(str_replace('\\', '/', (string) $relativePath), '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return;
        }

        $allowed = false;

        foreach ($allowedDirectories as $directory) {
            $directory = trim(str_replace('\\', '/', $directory), '/');

            if ($relativePath === $directory || str_starts_with($relativePath, $directory . '/')) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return;
        }

        $absolutePath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function assertUploadIsUsable(UploadedFile $file, int $maximumBytes): void
    {
        if (!$file->isValid() || $file->hasMoved()) {
            throw new RuntimeException('File gagal diunggah atau sudah diproses sebelumnya.');
        }

        $size = (int) $file->getSize();

        if ($size < 1) {
            throw new RuntimeException('File kosong tidak dapat diunggah.');
        }

        if ($size > $maximumBytes) {
            throw new RuntimeException(
                'Ukuran file melebihi batas '
                . number_format($maximumBytes / 1024 / 1024, 1, ',', '.')
                . ' MB.'
            );
        }

        if (!is_uploaded_file($file->getTempName()) && PHP_SAPI !== 'cli') {
            throw new RuntimeException('Sumber upload tidak valid.');
        }
    }

    private function detectMime(string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);

        if (!is_string($mime) || $mime === '') {
            throw new RuntimeException('MIME type file tidak dapat dikenali.');
        }

        return strtolower($mime);
    }

    private function normalizeUploadDirectory(string $relativeDirectory): string
    {
        $relativeDirectory = trim(str_replace('\\', '/', $relativeDirectory), '/');

        if (
            $relativeDirectory === ''
            || str_contains($relativeDirectory, '..')
            || !str_starts_with($relativeDirectory, 'uploads/')
        ) {
            throw new RuntimeException('Direktori upload tidak diizinkan.');
        }

        return $relativeDirectory;
    }

    private function assertImageMemoryBudget(
        int $width,
        int $height,
        int $targetMaxWidth,
        int $targetMaxHeight
    ): void {
        $memoryLimit = $this->memoryLimitBytes();

        if ($memoryLimit === null) {
            return;
        }

        $scale = min(
            1,
            $targetMaxWidth > 0 ? $targetMaxWidth / $width : 1,
            $targetMaxHeight > 0 ? $targetMaxHeight / $height : 1
        );

        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $estimatedBytes = ($width * $height * 5)
            + ($targetWidth * $targetHeight * 5)
            + (16 * 1024 * 1024);

        $availableBytes = $memoryLimit - memory_get_usage(true);

        if ($estimatedBytes > max(0, $availableBytes)) {
            throw new RuntimeException(
                'Resolusi gambar terlalu besar untuk batas memori server. '
                . 'Kecilkan dimensinya sebelum mengunggah.'
            );
        }
    }

    private function memoryLimitBytes(): ?int
    {
        $value = trim((string) ini_get('memory_limit'));

        if ($value === '' || $value === '-1') {
            return null;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) round($number * 1024 * 1024 * 1024),
            'm' => (int) round($number * 1024 * 1024),
            'k' => (int) round($number * 1024),
            default => (int) $number,
        };
    }

    private function prepareUploadDirectory(string $relativeDirectory): string
    {
        $absoluteDirectory = FCPATH
            . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);

        $this->ensureDirectory($absoluteDirectory);

        return $absoluteDirectory;
    }

    /** @return array{0:int,1:int}|null */
    private function readIcoDimensions(string $path): ?array
    {
        $header = file_get_contents($path, false, null, 0, 8);

        if (!is_string($header) || strlen($header) < 8) {
            return null;
        }

        if (substr($header, 0, 4) !== "\x00\x00\x01\x00") {
            return null;
        }

        $width = ord($header[6]);
        $height = ord($header[7]);

        return [
            $width === 0 ? 256 : $width,
            $height === 0 ? 256 : $height,
        ];
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Direktori upload tidak dapat dibuat.');
        }

        if (!is_writable($directory)) {
            throw new RuntimeException('Direktori upload tidak dapat ditulis.');
        }
    }

    /** @return array<string,mixed> */
    private function reencodeRasterImage(
        UploadedFile $file,
        string $temporaryPath,
        string $mime,
        string $relativeDirectory,
        string $absoluteDirectory,
        int $width,
        int $height,
        int $targetMaxWidth,
        int $targetMaxHeight
    ): array {
        $source = $this->createImageResource($temporaryPath, $mime);
        $source = $this->applyJpegOrientation($source, $temporaryPath, $mime);

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(
            1,
            $targetMaxWidth > 0 ? $targetMaxWidth / $sourceWidth : 1,
            $targetMaxHeight > 0 ? $targetMaxHeight / $sourceHeight : 1
        );

        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($target === false) {
            imagedestroy($source);
            throw new RuntimeException('Gambar tidak dapat diproses.');
        }

        if (in_array($mime, ['image/png', 'image/webp'], true)) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        if (!imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        )) {
            imagedestroy($source);
            imagedestroy($target);
            throw new RuntimeException('Gambar gagal dinormalisasi.');
        }

        $extension = self::RASTER_MIMES[$mime];
        $fileName = bin2hex(random_bytes(18)) . '.' . $extension;
        $absolutePath = $absoluteDirectory . DIRECTORY_SEPARATOR . $fileName;
        $temporaryOutput = $absolutePath . '.uploading';

        $written = match ($mime) {
            'image/jpeg' => imagejpeg($target, $temporaryOutput, 88),
            'image/png'  => imagepng($target, $temporaryOutput, 7),
            'image/webp' => function_exists('imagewebp')
                ? imagewebp($target, $temporaryOutput, 86)
                : false,
            default => false,
        };

        imagedestroy($source);
        imagedestroy($target);

        if (!$written || !is_file($temporaryOutput)) {
            @unlink($temporaryOutput);
            throw new RuntimeException('Gambar gagal ditulis dalam format aman. Pastikan ekstensi GD mendukung format tersebut.');
        }

        if (!@rename($temporaryOutput, $absolutePath)) {
            @unlink($temporaryOutput);
            throw new RuntimeException('Gambar gagal dipindahkan ke penyimpanan akhir.');
        }

        @chmod($absolutePath, 0644);

        return [
            'file_name' => $fileName,
            'relative_path' => $relativeDirectory . '/' . $fileName,
            'absolute_path' => $absolutePath,
            'original_name' => basename((string) $file->getClientName()),
            'mime' => $mime,
            'width' => $targetWidth,
            'height' => $targetHeight,
            'size' => (int) filesize($absolutePath),
        ];
    }

    /** @return array<string,mixed> */
    private function storeValidatedIcon(
        UploadedFile $file,
        string $temporaryPath,
        string $relativeDirectory,
        string $absoluteDirectory,
        int $width,
        int $height
    ): array {
        $signature = (string) file_get_contents($temporaryPath, false, null, 0, 4);

        if ($signature !== "\x00\x00\x01\x00") {
            throw new RuntimeException('Struktur favicon ICO tidak valid.');
        }

        $fileName = bin2hex(random_bytes(18)) . '.ico';
        $absolutePath = $absoluteDirectory . DIRECTORY_SEPARATOR . $fileName;
        $contents = file_get_contents($temporaryPath);

        if ($contents === false || file_put_contents($absolutePath, $contents, LOCK_EX) === false) {
            throw new RuntimeException('Favicon gagal disimpan.');
        }

        @chmod($absolutePath, 0644);

        return [
            'file_name' => $fileName,
            'relative_path' => $relativeDirectory . '/' . $fileName,
            'absolute_path' => $absolutePath,
            'original_name' => basename((string) $file->getClientName()),
            'mime' => 'image/x-icon',
            'width' => $width,
            'height' => $height,
            'size' => (int) filesize($absolutePath),
        ];
    }

    /** @return \GdImage|resource */
    private function createImageResource(string $path, string $mime)
    {
        $function = match ($mime) {
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp',
            default => null,
        };

        if ($function === null || !function_exists($function)) {
            throw new RuntimeException('Ekstensi GD belum mendukung format gambar yang diunggah.');
        }

        $resource = @$function($path);

        if ($resource === false) {
            throw new RuntimeException('Isi gambar rusak atau tidak dapat didekode.');
        }

        return $resource;
    }

    /** @param \GdImage|resource $image @return \GdImage|resource */
    private function applyJpegOrientation($image, string $path, string $mime)
    {
        if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $transformed = $image;

        if (in_array($orientation, [3, 6, 8], true)) {
            $angle = match ($orientation) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };

            $rotated = imagerotate($transformed, $angle, 0);

            if ($rotated !== false) {
                imagedestroy($transformed);
                $transformed = $rotated;
            }
        }

        return $transformed;
    }
}
