<?php

namespace App\Libraries;

class OpenAIContentService
{
    public function generateSocialContent(array $post, array $assets): array
    {
        $apiKey = env('OPENAI_API_KEY') ?: getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? null);
        $model  = env('OPENAI_MODEL') ?: getenv('OPENAI_MODEL') ?: ($_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini');

        if (empty($apiKey) || $apiKey === 'your_openai_api_key') {
            return $this->generateDemoContent($post);
        }

        $content = [];

        $content[] = [
            'type' => 'input_text',
            'text' => $this->buildPrompt($post),
        ];

        foreach ($assets as $asset) {
            $fullPath = FCPATH . $asset['image_path'];

            if (!file_exists($fullPath)) {
                continue;
            }

            $mime = mime_content_type($fullPath);
            $base64 = base64_encode(file_get_contents($fullPath));

            $content[] = [
                'type' => 'input_image',
                'image_url' => 'data:' . $mime . ';base64,' . $base64,
            ];
        }

        $payload = [
            'model' => $model,
            'input' => [
                [
                    'role' => 'user',
                    'content' => $content,
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'social_media_content',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'category' => [
                                'type' => 'string',
                                'enum' => [
                                    'dokumentasi_kegiatan',
                                    'pengumuman',
                                    'undangan',
                                    'hari_besar',
                                    'edukasi',
                                    'apresiasi',
                                    'laporan_singkat',
                                    'umum',
                                ],
                            ],
                            'title' => [
                                'type' => 'string',
                            ],
                            'caption' => [
                                'type' => 'string',
                            ],
                            'hashtags' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                            'mentions' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                            'alt_text' => [
                                'type' => 'string',
                            ],
                            'ai_summary' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => [
                            'category',
                            'title',
                            'caption',
                            'hashtags',
                            'mentions',
                            'alt_text',
                            'ai_summary',
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = $this->sendRequest($apiKey, $payload);

            $text = $response['output_text'] ?? null;

            if (!$text && isset($response['output'])) {
                foreach ($response['output'] as $output) {
                    if (!empty($output['content'])) {
                        foreach ($output['content'] as $item) {
                            if (($item['type'] ?? '') === 'output_text') {
                                $text = $item['text'] ?? null;
                                break 2;
                            }
                        }
                    }
                }
            }

            if (!$text) {
                return $this->generateDemoContent($post);
            }

            $data = json_decode($text, true);

            if (!is_array($data)) {
                return $this->generateDemoContent($post);
            }

            return $data;
        } catch (\Throwable $e) {
            return $this->generateDemoContent($post, $e->getMessage());
        }
    }

    private function buildPrompt(array $post): string
    {
        $category = $post['category'] ?? 'auto_detect';
        $notes    = $post['notes'] ?? '';

        return "
Anda adalah asisten media sosial resmi Karang Taruna RW 01 Kelurahan Randugarut.

Tugas Anda:
1. Analisis gambar yang dikirim.
2. Jika kategori bernilai auto_detect, klasifikasikan jenis postingan.
3. Buat judul/headline feed Instagram.
4. Buat caption Instagram dalam Bahasa Indonesia.
5. Buat hashtag yang konsisten dan relevan.
6. Buat mention/tag rekomendasi jika ada, jika tidak ada kosongkan array.
7. Buat alt text gambar.
8. Buat ringkasan singkat konteks gambar.

Brand voice:
- Bahasa Indonesia
- sopan
- hangat
- semangat pemuda
- tidak kaku berlebihan
- tidak alay
- tidak terlalu panjang
- ada unsur kebersamaan
- ada ajakan positif

Identitas organisasi:
Karang Taruna RW 01 Kelurahan Randugarut.

Hashtag wajib dipertimbangkan:
#KarangTarunaRW01
#Randugarut
#RW01Randugarut
#PemudaBergerak
#KarangTaruna

Kategori yang diminta user:
{$category}

Catatan tambahan dari user:
{$notes}

Output harus JSON sesuai schema.
";
    }

    private function sendRequest(string $apiKey, array $payload): array
    {
        $ch = curl_init('https://api.openai.com/v1/responses');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 120,
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new \RuntimeException('Gagal menghubungi OpenAI API: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($result, true);

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $decoded['error']['message'] ?? $result;
            throw new \RuntimeException('OpenAI API error: ' . $message);
        }

        return $decoded;
    }

    private function generateDemoContent(array $post, ?string $reason = null): array
    {
        $category = $post['category'] ?? 'auto_detect';

        if ($category === 'auto_detect') {
            $category = 'dokumentasi_kegiatan';
        }

        $notes = trim($post['notes'] ?? '');

        $title = 'Semangat Kebersamaan Pemuda RW 01';

        if ($category === 'pengumuman') {
            $title = 'Informasi Resmi Karang Taruna RW 01';
        } elseif ($category === 'undangan') {
            $title = 'Undangan Kegiatan Karang Taruna RW 01';
        } elseif ($category === 'hari_besar') {
            $title = 'Selamat Memperingati Hari Besar';
        } elseif ($category === 'edukasi') {
            $title = 'Edukasi Pemuda RW 01';
        } elseif ($category === 'apresiasi') {
            $title = 'Apresiasi untuk Pemuda RW 01';
        } elseif ($category === 'laporan_singkat') {
            $title = 'Laporan Singkat Kegiatan RW 01';
        }

        $caption = "Alhamdulillah, Karang Taruna RW 01 Kelurahan Randugarut terus berupaya menghadirkan kegiatan yang positif, bermanfaat, dan mempererat kebersamaan antar pemuda serta warga.\n\n";

        if (!empty($notes)) {
            $caption .= "Catatan kegiatan: " . $notes . "\n\n";
        }

        $caption .= "Semoga langkah kecil ini menjadi bagian dari ikhtiar bersama untuk membangun lingkungan yang lebih aktif, peduli, dan solid.\n\n";
        $caption .= "Terima kasih kepada seluruh pihak yang telah mendukung kegiatan Karang Taruna RW 01.";

        $summary = 'Konten demo otomatis dibuat karena layanan AI asli belum tersedia.';

        if (!empty($reason)) {
            $summary .= ' Alasan teknis: ' . $reason;
        }

        return [
            'category' => $category,
            'title' => $title,
            'caption' => $caption,
            'hashtags' => [
                '#KarangTarunaRW01',
                '#Randugarut',
                '#RW01Randugarut',
                '#PemudaBergerak',
                '#KarangTaruna',
                '#KegiatanPemuda',
                '#GotongRoyong',
            ],
            'mentions' => [],
            'alt_text' => 'Dokumentasi kegiatan Karang Taruna RW 01 Kelurahan Randugarut.',
            'ai_summary' => $summary,
        ];
    }
}