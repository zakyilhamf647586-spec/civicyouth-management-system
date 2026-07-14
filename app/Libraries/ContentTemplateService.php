<?php

namespace App\Libraries;

class ContentTemplateService
{
    public function renderFeedPortraitPermanent(array $post, array $assets): string
    {
        @ini_set('memory_limit', '1024M');

        if (empty($assets)) {
            throw new \RuntimeException('Tidak ada gambar untuk dirender.');
        }

        $width = 1080;
        $height = 1350;

        $canvas = imagecreatetruecolor($width, $height);
        imageantialias($canvas, true);

        $cream = $this->color($canvas, '#F8F3E8');
        $navy = $this->color($canvas, '#07264B');
        $navy2 = $this->color($canvas, '#0B1D38');
        $gold = $this->color($canvas, '#D6A437');
        $goldSoft = $this->color($canvas, '#E5C16B');
        $white = $this->color($canvas, '#FFFFFF');
        $black = $this->color($canvas, '#111827');
        $line = $this->color($canvas, '#D3B06A');

        imagefilledrectangle($canvas, 0, 0, $width, $height, $cream);

        // Background lower navy block
        imagefilledrectangle($canvas, 0, 760, $width, $height, $navy2);

        // Decorative arcs similar to the permanent template
        imagefilledellipse($canvas, -70, -20, 430, 430, $navy);
        imagefilledellipse($canvas, $width + 70, -20, 430, 430, $navy);

        imagesetthickness($canvas, 4);
        imagearc($canvas, -70, -20, 470, 470, 290, 110, $gold);
        imagearc($canvas, $width + 70, -20, 470, 470, 70, 250, $gold);
        imagesetthickness($canvas, 1);

        // Thin outer lines
        imageline($canvas, 28, 32, 28, $height - 90, $line);
        imageline($canvas, $width - 28, 32, $width - 28, $height - 90, $line);
        imageline($canvas, 30, 32, $width - 30, 32, $line);

        $fontRegular = $this->getSansFont(false);
        $fontBold = $this->getSansFont(true);
        $fontSerif = $this->getSerifFont(true);

        // Logo
        $logoPath = FCPATH . 'assets/img/logo-rw01.png';
        if (file_exists($logoPath)) {
            $this->drawImageContain($canvas, $logoPath, 455, 20, 170, 135);
        }

        // Organization line
        $orgText = 'KARANG TARUNA RW 01 RANDUGARUT';
        $this->drawCenteredText($canvas, $orgText, $width / 2, 167, 16, $navy, $fontRegular);

        imageline($canvas, 120, 166, 270, 166, $line);
        imageline($canvas, 810, 166, 960, 166, $line);

        // Title
        $title = trim($post['event_title'] ?? '');
        if ($title === '') {
            $title = trim($post['title'] ?? '');
        }
        if ($title === '') {
            $title = 'GERAK BERSAMA PEMUDA RW 01';
        }

        $title = strtoupper($title);
        $titleLines = $this->wrapTextByWidth($title, $fontSerif, 68, 900, 3);

        $titleStartY = 262;
        foreach ($titleLines as $index => $lineText) {
            $color = (stripos($lineText, 'RW 01') !== false) ? $gold : $navy;
            $this->drawCenteredText(
                $canvas,
                $lineText,
                $width / 2,
                $titleStartY + ($index * 78),
                68,
                $color,
                $fontSerif
            );
        }

        // Meta data from user input
        $meta = $this->extractMetaFromNotes($post['notes'] ?? '', $post);

        // Date pill
        $pillText = $meta['date_line'];
        $this->drawRoundedBorderBox($canvas, 250, 405, 580, 52, $cream, $gold, 26, 3);
        $this->drawCenteredText($canvas, strtoupper($pillText), $width / 2, 440, 17, $navy, $fontBold);
        // Determine image set
        $image1 = FCPATH . $assets[0]['image_path'];
        $image2 = FCPATH . ($assets[1]['image_path'] ?? $assets[0]['image_path']);
        $image3 = FCPATH . ($assets[2]['image_path'] ?? ($assets[1]['image_path'] ?? $assets[0]['image_path']));

        // Main large image frame
        $this->drawPhotoCard($canvas, $image1, 58, 500, 932, 275, $navy, $gold, 18);

        // Left two stacked small images
        $this->drawPhotoCard($canvas, $image2, 58, 820, 430, 162, $navy, $gold, 18);
        $this->drawPhotoCard($canvas, $image3, 58, 1018, 430, 162, $navy, $gold, 18);

        // Right info block
        $infoX = 530;
        $infoY = 810;

        $sectionTitle1 = 'LOKASI KEGIATAN';
        $sectionTitle2 = 'BENTUK KEGIATAN';

        $this->drawText($canvas, $sectionTitle1, $infoX, $infoY + 24, 15, $gold, $fontBold);
        $this->drawWrappedText($canvas, $meta['location'], $infoX, $infoY + 60, 410, 18, $white, $fontBold, 3, 30);

        imageline($canvas, $infoX, $infoY + 106, $infoX + 392, $infoY + 106, $line);

        $this->drawText($canvas, $sectionTitle2, $infoX, $infoY + 154, 15, $gold, $fontBold);
        $this->drawWrappedText($canvas, $meta['activity_description'], $infoX, $infoY + 192, 410, 18, $white, $fontRegular, 8, 29);

        // Footer handle
        imageline($canvas, 68, 1230, 340, 1230, $line);
        imageline($canvas, 742, 1230, 1012, 1230, $line);

        $this->drawCenteredText($canvas, $meta['instagram'], $width / 2, 1242, 17, $gold, $fontBold);

        // Decorative bottom notch
        imageline($canvas, 388, 1270, 518, 1270, $line);
        imageline($canvas, 562, 1270, 692, 1270, $line);
        imageline($canvas, 518, 1270, 540, 1288, $line);
        imageline($canvas, 540, 1288, 562, 1270, $line);

        $fileName = 'feed-portrait-' . date('YmdHis') . '-' . uniqid() . '.png';
        $relativePath = 'uploads/content_studio/generated/' . $fileName;
        $savePath = FCPATH . $relativePath;

        if (!is_dir(dirname($savePath))) {
            mkdir(dirname($savePath), 0775, true);
        }

        imagepng($canvas, $savePath);
        imagedestroy($canvas);

        return $relativePath;
    }

private function extractMetaFromNotes(string $notes, array $post): array
{
    $location = trim($post['event_location'] ?? '');
    if ($location === '') {
        $location = 'Lokasi kegiatan belum diisi';
    }

    $tanggalRaw = trim($post['event_date'] ?? '');
    $waktuRaw = trim($post['event_time'] ?? '');

    $tanggal = 'Tanggal kegiatan';
    if ($tanggalRaw !== '') {
        $timestamp = strtotime($tanggalRaw);
        if ($timestamp) {
            $tanggal = $this->formatIndonesianDate($timestamp);
        }
    }

    $waktu = 'Waktu kegiatan';
    if ($waktuRaw !== '') {
        $waktu = date('H.i', strtotime($waktuRaw)) . ' WIB';
    }

    $dateLine = $tanggal . ' • ' . $waktu;

    $bentuk = trim($post['activity_description'] ?? '');
    if ($bentuk === '') {
        $bentuk = 'Deskripsi kegiatan belum diisi.';
    }

    return [
        'location' => $this->limitText($location, 90),
        'date_line' => $this->limitText($dateLine, 60),
        'activity_description' => $this->limitText($bentuk, 280),
        'instagram' => '@kartar.rw01.randugarut',
    ];
}

    private function extractLabeledValue(string $text, string $label): string
    {
        $pattern = '/^' . preg_quote($label, '/') . '\s*:\s*(.+)$/im';

        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private function formatIndonesianDate(int $timestamp): string
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $dayName = $days[date('l', $timestamp)] ?? date('l', $timestamp);
        $day = date('j', $timestamp);
        $month = $months[(int) date('n', $timestamp)] ?? date('F', $timestamp);
        $year = date('Y', $timestamp);

        return $dayName . ', ' . $day . ' ' . $month . ' ' . $year;
    }

    private function limitText(string $text, int $limit): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 3) . '...';
    }

    private function drawPhotoCard($canvas, string $imagePath, int $x, int $y, int $w, int $h, int $borderColor, int $accentColor, int $radius): void
    {
        // Gold shadow/accent
        $this->drawRoundedFilledRectangle($canvas, $x + 6, $y + 6, $w, $h, $radius, $accentColor);

        // Navy outer card
        $this->drawRoundedFilledRectangle($canvas, $x, $y, $w, $h, $radius, $borderColor);

        // White inner margin
        $innerX = $x + 8;
        $innerY = $y + 8;
        $innerW = $w - 16;
        $innerH = $h - 16;

        $white = $this->color($canvas, '#FFFFFF');
        $this->drawRoundedFilledRectangle($canvas, $innerX, $innerY, $innerW, $innerH, max(6, $radius - 4), $white);

        // Image
        $imgX = $x + 10;
        $imgY = $y + 10;
        $imgW = $w - 20;
        $imgH = $h - 20;

        if (file_exists($imagePath)) {
            $this->drawImageCover($canvas, $imagePath, $imgX, $imgY, $imgW, $imgH);
        }
    }

    private function drawRoundedBorderBox($canvas, int $x, int $y, int $w, int $h, int $bgColor, int $borderColor, int $radius, int $thickness = 2): void
    {
        $this->drawRoundedFilledRectangle($canvas, $x, $y, $w, $h, $radius, $borderColor);
        $this->drawRoundedFilledRectangle($canvas, $x + $thickness, $y + $thickness, $w - ($thickness * 2), $h - ($thickness * 2), max(2, $radius - $thickness), $bgColor);
    }

    private function drawRoundedFilledRectangle($canvas, int $x, int $y, int $w, int $h, int $r, int $color): void
    {
        imagefilledrectangle($canvas, $x + $r, $y, $x + $w - $r, $y + $h, $color);
        imagefilledrectangle($canvas, $x, $y + $r, $x + $w, $y + $h - $r, $color);

        imagefilledellipse($canvas, $x + $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    }

    private function drawText($canvas, string $text, int $x, int $y, int $size, int $color, string $font): void
    {
        imagettftext($canvas, $size, 0, $x, $y, $color, $font, $text);
    }

    private function drawCenteredText($canvas, string $text, int $centerX, int $baselineY, int $size, int $color, string $font): void
    {
        $bbox = imagettfbbox($size, 0, $font, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $x = (int) ($centerX - ($textWidth / 2));

        imagettftext($canvas, $size, 0, $x, $baselineY, $color, $font, $text);
    }

    private function drawWrappedText($canvas, string $text, int $x, int $y, int $maxWidth, int $size, int $color, string $font, int $maxLines = 3, int $lineHeight = 34): void
    {
        $lines = $this->wrapTextByWidth($text, $font, $size, $maxWidth, $maxLines);

        foreach ($lines as $index => $lineText) {
            $this->drawText($canvas, $lineText, $x, $y + ($index * $lineHeight), $size, $color, $font);
        }
    }

    private function wrapTextByWidth(string $text, string $font, int $size, int $maxWidth, int $maxLines = 3): array
    {
        $words = preg_split('/\s+/', trim($text));
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $testLine = trim($line . ' ' . $word);
            $box = imagettfbbox($size, 0, $font, $testLine);
            $lineWidth = $box[2] - $box[0];

            if ($lineWidth > $maxWidth && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $testLine;
            }

            if (count($lines) >= ($maxLines - 1)) {
                continue;
            }
        }

        if ($line !== '' && count($lines) < $maxLines) {
            $lines[] = $line;
        }

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
        }

        if (!empty($lines)) {
            $last = end($lines);
            if (mb_strlen($last) > 60) {
                $last = mb_substr($last, 0, 57) . '...';
                $lines[key($lines)] = $last;
            }
        }

        return $lines;
    }

    private function drawImageCover($canvas, string $imagePath, int $x, int $y, int $targetWidth, int $targetHeight): void
    {
        $source = $this->createImageFromFile($imagePath);

        if (!$source) {
            throw new \RuntimeException('Gambar tidak dapat dibaca.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) ($sourceHeight * $targetRatio);
            $srcX = (int) (($sourceWidth - $cropWidth) / 2);
            $srcY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) ($sourceWidth / $targetRatio);
            $srcX = 0;
            $srcY = (int) (($sourceHeight - $cropHeight) / 2);
        }

        imagecopyresampled(
            $canvas,
            $source,
            $x,
            $y,
            $srcX,
            $srcY,
            $targetWidth,
            $targetHeight,
            $cropWidth,
            $cropHeight
        );

        imagedestroy($source);
    }

    private function drawImageContain($canvas, string $imagePath, int $x, int $y, int $targetWidth, int $targetHeight): void
    {
        $source = $this->createImageFromFile($imagePath);

        if (!$source) {
            return;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $ratio = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
        $newWidth = (int) ($sourceWidth * $ratio);
        $newHeight = (int) ($sourceHeight * $ratio);

        $dstX = $x + (int) (($targetWidth - $newWidth) / 2);
        $dstY = $y + (int) (($targetHeight - $newHeight) / 2);

        imagecopyresampled(
            $canvas,
            $source,
            $dstX,
            $dstY,
            0,
            0,
            $newWidth,
            $newHeight,
            $sourceWidth,
            $sourceHeight
        );

        imagedestroy($source);
    }

    private function createImageFromFile(string $path)
    {
        $mime = mime_content_type($path);

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            return imagecreatefromjpeg($path);
        }

        if ($mime === 'image/png') {
            return imagecreatefrompng($path);
        }

        if ($mime === 'image/webp') {
            return imagecreatefromwebp($path);
        }

        return null;
    }

    private function color($canvas, string $hex): int
    {
        $hex = ltrim($hex, '#');

        return imagecolorallocate(
            $canvas,
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }

    private function getSansFont(bool $bold = false): string
    {
        $candidates = $bold
            ? [
                FCPATH . 'assets/fonts/arialbd.ttf',
                'C:\Windows\Fonts\arialbd.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            ]
            : [
                FCPATH . 'assets/fonts/arial.ttf',
                'C:\Windows\Fonts\arial.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('Font sans TTF tidak ditemukan.');
    }

    private function getSerifFont(bool $bold = false): string
    {
        $candidates = $bold
            ? [
                FCPATH . 'assets/fonts/Cinzel-Bold.ttf',
                FCPATH . 'assets/fonts/cinzel-bold.ttf',
                FCPATH . 'assets/fonts/georgiab.ttf',
                'C:\Windows\Fonts\georgiab.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf',
            ]
            : [
                FCPATH . 'assets/fonts/Cinzel-Regular.ttf',
                FCPATH . 'assets/fonts/cinzel-regular.ttf',
                FCPATH . 'assets/fonts/georgia.ttf',
                'C:\Windows\Fonts\georgia.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
            ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return $this->getSansFont($bold);
    }
}