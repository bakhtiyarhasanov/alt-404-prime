<?php
/**
 * AI Rewriter Engine
 * Uses OpenAI API to translate and rewrite news into journalistic Azerbaijani
 */

namespace AiWriter\Core;

require_once __DIR__ . '/Database.php';

use Exception;

class AiRewriter {
    public const DEFAULT_SYSTEM_PROMPT = 'Sən peşəkar xəbər jurnalistisən. Xarici dildəki texnoloji və ictimai xəbərləri araşdıraraq səlis, anlaşıqlı, bitərəf və peşəkar Azərbaycan dilinə adaptasiya edib yenidən yazırsan.';

    public const DEFAULT_REGENERATE_PROMPT = "Linkdəki xəbəri diqqətlə oxu, həqiqiliyini digər mənbələrdə araşdırdıqdan sonra Azərbaycan dilində yaz. Mətnlə bağlı tələblər belədir -\nMətnə uyğun ən uyğun, təsirli başlıq yaz.\nBütün xüsusi isimlər Azərbaycan dilində yazılması, düzgün yazılış forması saytlarda yoxlanılmalıdır.\nSəliqəli, abzaslara bölünmüş şəkildə aydın dildə yaz. Xəbərə və yaşanan hadisələrə münasibət bildirmə, sadəcə, xəbəri çatdır.\nMətndə xüsusi vurğulamaq istədiyin hissələri boldla və ya kursivlə vermə, sadə fontla ver hamısını.\nSonda xəbəri yazarkən istifadə etdiyin bütün mənbələri bu şəkildə qeyd et -\nMƏNBƏ:\nmənbə 1 link şəklində\nmənbə 2 link şəklində\nmənbə 3 link şəklində və s.\nİstinad etdiyin mənbə sayı 5-dən çox olmasın. Link uzun olduqda ixtisar et və … nöqtə ilə tamamla. Məsələn, [ https://www.kaldata.com/it-%d0%bd%d0%be%](https://www.kaldata.com/it-%25d0%25bd%25d0%25be%25)…";

    private string $apiKey;
    private string $model;
    private float $temperature;
    private string $systemPrompt;
    private string $regeneratePrompt;

    public function __construct() {
        $this->apiKey = Database::getSetting('openai_api_key', '');
        $this->model = Database::getSetting('openai_model', 'gpt-4o-mini');
        $this->temperature = (float)Database::getSetting('openai_temperature', '0.7');
        $this->systemPrompt = Database::getSetting('system_prompt', self::DEFAULT_SYSTEM_PROMPT);
        $this->regeneratePrompt = Database::getSetting('regenerate_prompt', self::DEFAULT_REGENERATE_PROMPT);
        if (trim($this->regeneratePrompt) === '') {
            $this->regeneratePrompt = self::DEFAULT_REGENERATE_PROMPT;
        }
    }

    /**
     * Rewrite article into Azerbaijani or pass through if rewrite is disabled
     * 
     * @param array $newsItem Row from grabbed_news or grabber output
     * @param bool $isRewriteEnabled
     * @return array ['title' => string, 'excerpt' => string, 'content' => string, 'tags' => array]
     */
    public function rewrite(array $newsItem, bool $isRewriteEnabled = true): array {
        // If rewrite is disabled for this source (e.g. official gov websites), return as-is
        if (!$isRewriteEnabled) {
            $tags = is_string($newsItem['tags']) ? json_decode($newsItem['tags'], true) : ($newsItem['tags'] ?? []);
            return [
                'title' => $newsItem['source_title'],
                'excerpt' => $newsItem['source_excerpt'] ?: mb_substr(strip_tags($newsItem['source_content']), 0, 200),
                'content' => $newsItem['source_content'],
                'tags' => $tags ?: ['Texnologiya', 'Rəsmi']
            ];
        }

        // Fetch fresh settings in runtime in case updated in Settings page
        $apiKey = Database::getSetting('openai_api_key', $this->apiKey);
        if (empty($apiKey)) {
            throw new Exception("OpenAI API açarı tapılmadı. Zəhmət olmasa Manager -> Settings bölməsindən OpenAI API açarını qeyd edin.");
        }

        $model = Database::getSetting('openai_model', $this->model);
        $temperature = (float)Database::getSetting('openai_temperature', (string)$this->temperature);
        $systemPrompt = Database::getSetting('system_prompt', $this->systemPrompt);
        $regeneratePrompt = Database::getSetting('regenerate_prompt', $this->regeneratePrompt);
        if (trim($regeneratePrompt) === '') {
            $regeneratePrompt = self::DEFAULT_REGENERATE_PROMPT;
        }

        $sourceUrl = $newsItem['source_url'] ?? '';
        $sourceName = $newsItem['source_name'] ?? $newsItem['source_id'] ?? '';
        $sourceTitle = $newsItem['source_title'] ?? '';
        $sourceExcerpt = $newsItem['source_excerpt'] ?? '';
        $sourceContent = strip_tags($newsItem['source_content'] ?? '');

        $prompt = $regeneratePrompt . "\n\n"
                . "==============================\n"
                . "İŞLƏNƏCƏK XƏBƏR VƏ MƏNBƏ MƏLUMATLARI:\n"
                . "==============================\n"
                . "Xəbərin Linki (URL): " . $sourceUrl . "\n"
                . "Mənbə Adı: " . $sourceName . "\n"
                . "Orijinal Başlıq: " . $sourceTitle . "\n"
                . "Orijinal Xülasə: " . $sourceExcerpt . "\n"
                . "Orijinal Mətn Məzmunu:\n" . $sourceContent . "\n\n"
                . "==============================\n"
                . "TƏLƏB EDİLƏN CAVAB STRUKTURU (YALNIZ VALID JSON):\n"
                . "==============================\n"
                . "Cavabı mütləq və istisnasız olaraq aşağıdakı JSON strukturunda təqdim et:\n"
                . "{\n"
                . '  "title": "Tələblərə uyğun ən uyğun, təsirli başlıq",' . "\n"
                . '  "excerpt": "Xəbərin qısa, lakonik xülasəsi (1-2 cümlə)",' . "\n"
                . '  "content": "<p>Səliqəli, abzaslara bölünmüş birinci abzas...</p><p>İkinci abzas...</p><p>MƏNBƏ:<br><a href=\"' . $sourceUrl . '\" target=\"_blank\">' . $sourceUrl . '</a></p>",' . "\n"
                . '  "tags": ["Etiket 1", "Etiket 2", "Etiket 3"]' . "\n"
                . "}\n\n"
                . "ƏLAVƏ FORMAT VƏ ÜSLUB QAYDALARI:\n"
                . "- Mətndə (content daxilində) xüsusi vurğulamaq istədiyin hissələri bold (<b>, <strong>) və ya kursivlə (<i>, <em>) VERMƏ, sadə fontla ver hamısını.\n"
                . "- Mətn abzaslarını təmiz <p> teqləri ilə formatla.\n"
                . "- Sonda xəbəri yazarkən istifadə etdiyin bütün mənbələri göstərilən formatda qeyd et (maksimum 5 mənbə).\n"
                . "- Yalnız valid JSON qaytar, əlavə izahat mətni və ya ```json kod bloku yazma.";

        $isReasoning = (bool)preg_match('/^(o1|o3)/i', $model);

        $payload = [
            'model' => $model,
        ];

        if ($isReasoning) {
            // Reasoning models (o1, o3-mini, etc.) do not support temperature (OpenAI returns 400 error)
            // Early reasoning models (o1-mini, o1-preview) do not support system or developer roles, nor response_format
            if (preg_match('/^(o1-mini|o1-preview)/i', $model)) {
                $payload['messages'] = [
                    ['role' => 'user', 'content' => $systemPrompt . "\n\n" . $prompt]
                ];
            } else {
                // o1 and o3-mini support developer role and response_format
                $payload['messages'] = [
                    ['role' => 'developer', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt]
                ];
                $payload['response_format'] = ['type' => 'json_object'];
            }
        } else {
            // Standard models (gpt-4o, gpt-4-turbo, gpt-3.5-turbo, etc.)
            $payload['messages'] = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt]
            ];
            $payload['temperature'] = $temperature;
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 90,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($curlError) {
            throw new Exception("OpenAI API cURL xətası: " . $curlError);
        }

        if ($httpCode !== 200) {
            $errObj = json_decode($response, true);
            $msg = $errObj['error']['message'] ?? "HTTP {$httpCode}: {$response}";
            throw new Exception("OpenAI API xətası: " . $msg);
        }

        $resData = json_decode($response, true);
        $contentRaw = trim($resData['choices'][0]['message']['content'] ?? '');

        // Clean any markdown code blocks defensively
        $contentClean = preg_replace('/^```(?:json)?\s*/i', '', $contentRaw);
        $contentClean = preg_replace('/\s*```$/', '', $contentClean);

        $parsed = json_decode($contentClean, true);
        if (!$parsed) {
            // Fallback: extract substring between first { and last }
            if (preg_match('/\{[\s\S]*\}/', $contentRaw, $m)) {
                $parsed = json_decode($m[0], true);
            }
        }
        if (!$parsed || empty($parsed['title']) || empty($parsed['content'])) {
            throw new Exception("OpenAI cavabı gözlənilən JSON formatında olmadı: " . mb_substr($contentRaw, 0, 150));
        }

        return [
            'title' => trim($parsed['title']),
            'excerpt' => trim($parsed['excerpt'] ?? ''),
            'content' => trim($parsed['content']),
            'tags' => is_array($parsed['tags'] ?? null) ? $parsed['tags'] : ['Texnologiya']
        ];
    }
}
