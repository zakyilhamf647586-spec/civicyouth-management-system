<?php

namespace App\Libraries;

class OpenAIContentService
{
    public function generateSocialContent(array $post, array $assets): array
    {
        $provider = strtolower($this->getEnvValue('AI_PROVIDER', 'demo'));

        if ($provider === 'demo') {
            return $this->generateDemoContent($post, 'AI_PROVIDER=demo');
        }

        try {
            if ($provider === 'openai') {
                return $this->generateWithOpenAI($post, $assets);
            }

            if ($provider === 'github_models') {
                return $this->generateWithGitHubModels($post, $assets);
            }

            if ($provider === 'openrouter') {
                return $this->generateWithOpenRouter($post, $assets);
            }

            return $this->generateDemoContent($post, 'AI_PROVIDER tidak dikenali: ' . $provider);
        } catch (\Throwable $e) {
            return $this->generateDemoContent($post, $e->getMessage());
        }
    }

    private function generateWithOpenAI(array $post, array $assets): array
    {
        $apiKey = $this->getEnvValue('OPENAI_API_KEY');
        $model  = $this->getEnvValue('OPENAI_MODEL', 'gpt-4o-mini');

        if (empty($apiKey) || $apiKey === 'your_openai_api_key') {
            return $this->generateDemoContent($post, 'OPENAI_API_KEY belum diatur.');
        }

        $payload = $this->buildChatCompletionPayload($model, $post, $assets, true);

        $response = $this->sendJsonRequest(
            'https://api.openai.com/v1/chat/completions',
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            $payload
        );

        return $this->parseChatCompletionResponse($response);
    }

    private function generateWithGitHubModels(array $post, array $assets): array
    {
        $token = $this->getEnvValue('GITHUB_MODELS_TOKEN');
        $model = $this->getEnvValue('GITHUB_MODELS_MODEL', 'openai/gpt-4o-mini');

        if (empty($token) || $token === 'your_github_personal_access_token') {
            return $this->generateDemoContent($post, 'GITHUB_MODELS_TOKEN belum diatur.');
        }

        $payload = $this->buildChatCompletionPayload($model, $post, $assets, false);

        $response = $this->sendJsonRequest(
            'https://models.github.ai/inference/chat/completions',
            [
                'Accept: application/vnd.github+json',
                'Authorization: Bearer ' . $token,
                'X-GitHub-Api-Version: 2022-11-28',
                'Content-Type: application/json',
            ],
            $payload
        );

        return $this->parseChatCompletionResponse($response);
    }

    private function generateWithOpenRouter(array $post, array $assets): array
    {
        $apiKey = $this->getEnvValue('OPENROUTER_API_KEY');
        $model  = $this->getEnvValue('OPENROUTER_MODEL', 'openai/gpt-4o-mini');

        if (empty($apiKey) || $apiKey === 'your_openrouter_api_key') {
            return $this->generateDemoContent($post, 'OPENROUTER_API_KEY belum diatur.');
        }

        $payload = $this->buildChatCompletionPayload($model, $post, $assets, false);

        $response = $this->sendJsonRequest(
            'https://openrouter.ai/api/v1/chat/completions',
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'HTTP-Referer: ' . base_url(),
                'X-OpenRouter-Title: CivicYouth Management System',
            ],
            $payload
        );

        return $this->parseChatCompletionResponse($response);
    }

    private function buildChatCompletionPayload(string $model, array $post, array $assets, bool $useJsonSchema): array
    {
        $content = [];

        $content[] = [
            'type' => 'text',
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
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:' . $mime . ';base64,' . $base64,
                ],
            ];
        }

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah asisten media sosial resmi Karang Taruna RW 01 Kelurahan Randugarut.',
                ],
                [
                    'role' => 'user',
                    'content' => $content,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 1200,
        ];

        if ($useJsonSchema) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'social_media_content',
                    'strict' => true,
                    'schema' => $this->getJsonSchema(),
                ],
            ];
        }

        return $payload;
    }

    private function buildPrompt(array $post): string
    {
        $category = $post['category'] ?? 'auto_detect';
        $notes    = $post['notes'] ?? '';

        return "
Anda adalah asisten media sosial resmi Karang Taruna RW 01 Kelurahan Randugarut.

Tugas:
1. Analisis gambar yang dikirim.
2. Jika kategori bernilai auto_detect, klasifikasikan jenis postingan.
3. Buat judul/headline feed Instagram.
4. Buat caption Instagram dalam Bahasa Indonesia.
5. Buat hashtag yang konsisten dan relevan.
6. Buat mention/tag rekomendasi jika ada. Jika tidak ada, kosongkan array.
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

Balas hanya JSON valid, tanpa markdown, tanpa penjelasan tambahan, dengan format:

{
  \"category\": \"dokumentasi_kegiatan\",
  \"title\": \"...\",
  \"caption\": \"...\",
  \"hashtags\": [\"#KarangTarunaRW01\"],
  \"mentions\": [],
  \"alt_text\": \"...\",
  \"ai_summary\": \"...\"
}

Kategori yang boleh dipakai:
dokumentasi_kegiatan, pengumuman, undangan, hari_besar, edukasi, apresiasi, laporan_singkat, umum.
";
    }

    private function getJsonSchema(): array
    {
        return [
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
                    'items' => [
                        'type' => 'string',
                    ],
                ],
                'mentions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
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
        ];
    }

    private function sendJsonRequest(string $url, array $headers, array $payload): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 120,
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new \RuntimeException('Gagal menghubungi AI Provider: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($result, true);

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $decoded['error']['message']
                ?? $decoded['message']
                ?? $result;

            throw new \RuntimeException('AI Provider error: ' . $message);
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('Respons AI Provider tidak valid.');
        }

        return $decoded;
    }

    private function parseChatCompletionResponse(array $response): array
    {
        $text = $response['choices'][0]['message']['content'] ?? null;

        if (!$text) {
            throw new \RuntimeException('Respons AI tidak berisi teks.');
        }

        $data = $this->parseJsonText($text);

        return $this->normalizeAiResult($data);
    }

    private function parseJsonText(string $text): array
    {
        $text = trim($text);

        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $data = json_decode($text, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Respons AI bukan JSON valid.');
        }

        return $data;
    }

    private function normalizeAiResult(array $data): array
    {
        $hashtags = $data['hashtags'] ?? [];
        $mentions = $data['mentions'] ?? [];

        if (is_string($hashtags)) {
            $hashtags = preg_split('/\s+/', trim($hashtags));
        }

        if (is_string($mentions)) {
            $mentions = preg_split('/\s+/', trim($mentions));
        }

        $hashtags = array_values(array_filter(array_map(function ($tag) {
            $tag = trim((string) $tag);

            if ($tag === '') {
                return null;
            }

            if (!str_starts_with($tag, '#')) {
                $tag = '#' . $tag;
            }

            return $tag;
        }, $hashtags)));

        $mentions = array_values(array_filter(array_map(function ($mention) {
            return trim((string) $mention);
        }, $mentions)));

        return [
            'category' => $data['category'] ?? 'umum',
            'title' => $data['title'] ?? 'Konten Karang Taruna RW 01',
            'caption' => $data['caption'] ?? '',
            'hashtags' => $hashtags,
            'mentions' => $mentions,
            'alt_text' => $data['alt_text'] ?? '',
            'ai_summary' => $data['ai_summary'] ?? '',
        ];
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

        $summary = 'Konten dibuat menggunakan Demo Mode / fallback otomatis.';

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

    private function getEnvValue(string $key, ?string $default = null): ?string
    {
        $value = env($key);

        if ($value === null || $value === '') {
            $value = getenv($key);
        }

        if (($value === false || $value === null || $value === '') && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return trim((string) $value);
    }
}