<?php
/**
 * Base Grabber class providing HTTP fetching, DOM parsing, and sanitization
 */

namespace AiWriter\Core;

use DOMDocument;
use DOMXPath;

abstract class BaseGrabber implements GrabberInterface {
    protected int $timeout = 15;
    protected string $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    protected int $lastHttpCode = 0;
    protected ?string $lastError = null;
    protected bool $lastIsCloudflare = false;

    protected int $mainHttpCode = 0;
    protected ?string $mainError = null;
    protected bool $mainIsCloudflare = false;

    /**
     * Reset grab state before running
     */
    public function resetGrabState(): void {
        $this->lastHttpCode = 0;
        $this->lastError = null;
        $this->lastIsCloudflare = false;
        $this->mainHttpCode = 0;
        $this->mainError = null;
        $this->mainIsCloudflare = false;
    }

    public function getLastHttpCode(): int {
        return $this->lastHttpCode;
    }

    public function getLastError(): ?string {
        return $this->lastError;
    }

    public function getMainHttpCode(): int {
        return $this->mainHttpCode;
    }

    public function getMainError(): ?string {
        return $this->mainError;
    }

    public function isCloudflareBlocked(): bool {
        return $this->mainIsCloudflare || $this->lastIsCloudflare;
    }

    /**
     * Get configured source URL from database or default URL
     */
    protected function getSourceUrl(): string {
        try {
            $db = Database::getAiDB();
            $stmt = $db->prepare("SELECT url FROM sources WHERE id = :id");
            $stmt->execute(['id' => $this->getId()]);
            $url = $stmt->fetchColumn();
            if (!empty($url)) {
                return trim($url);
            }
        } catch (\Throwable $e) {
            // fallback
        }
        return $this->getUrl();
    }

    /**
     * Perform HTTP GET request with realistic headers, cookies and Cloudflare handling
     */
    protected function fetchUrl(string $url, array $customHeaders = []): ?string {
        $ch = curl_init();
        $cookieFile = sys_get_temp_dir() . '/aiwriter_cookies_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->getId()) . '.txt';

        $defaultHeaders = [
            'User-Agent: ' . $this->userAgent,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language: az,tr-TR;q=0.9,tr;q=0.8,en-US;q=0.7,en;q=0.6',
            'Accept-Encoding: gzip, deflate',
            'sec-ch-ua: "Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "macOS"',
            'sec-fetch-dest: document',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: none',
            'sec-fetch-user: ?1',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: max-age=0',
        ];

        $headers = array_merge($defaultHeaders, $customHeaders);

        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
        ];

        try {
            $proxy = Database::getSetting('grabber_proxy', '');
            if (!empty($proxy)) {
                $curlOptions[CURLOPT_PROXY] = $proxy;
            }
        } catch (\Throwable $e) {}

        curl_setopt_array($ch, $curlOptions);

        $rawResponse = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        @curl_close($ch);

        $this->lastHttpCode = $httpCode;
        if ($this->mainHttpCode === 0) {
            $this->mainHttpCode = $httpCode;
        }

        if ($rawResponse === false) {
            $this->lastError = "Bağlantı xətası: " . ($curlErr ?: 'Serverə qoşulmaq mümkün olmadı');
            if ($this->mainError === null) {
                $this->mainError = $this->lastError;
            }
            return null;
        }

        $headerText = substr($rawResponse, 0, $headerSize);
        $body = substr($rawResponse, $headerSize);

        // Detect Cloudflare
        $isCloudflare = (
            stripos($headerText, 'server: cloudflare') !== false ||
            stripos($headerText, 'cf-mitigated: challenge') !== false ||
            stripos($headerText, 'cf-ray:') !== false ||
            stripos($body, 'challenges.cloudflare.com') !== false ||
            stripos($body, 'Attention Required! | Cloudflare') !== false ||
            stripos($body, 'Just a moment...') !== false ||
            stripos($body, 'cf-turnstile') !== false
        );

        $isCfChallenge = $isCloudflare && (
            $httpCode === 403 || 
            $httpCode === 503 || 
            stripos($body, 'Just a moment...') !== false || 
            stripos($headerText, 'cf-mitigated: challenge') !== false ||
            stripos($body, 'challenges.cloudflare.com') !== false
        );

        if ($isCfChallenge) {
            $this->lastIsCloudflare = true;
            if ($this->mainHttpCode === $httpCode || $this->mainHttpCode === 0) {
                $this->mainIsCloudflare = true;
            }

            // Attempt FlareSolverr fallback if configured
            try {
                $flaresolverrUrl = Database::getSetting('flaresolverr_url', '');
                if (!empty($flaresolverrUrl)) {
                    $solvedBody = $this->fetchWithFlareSolverr($flaresolverrUrl, $url);
                    if ($solvedBody) {
                        $this->lastHttpCode = 200;
                        $this->lastError = null;
                        $this->lastIsCloudflare = false;
                        if ($this->mainHttpCode === $httpCode) {
                            $this->mainHttpCode = 200;
                            $this->mainError = null;
                            $this->mainIsCloudflare = false;
                        }
                        return $solvedBody;
                    }
                }
            } catch (\Throwable $e) {}

            $this->lastError = "Cloudflare mühafizəsi aktivdir (HTTP {$httpCode} / Bot Challenge)";
            if ($this->mainError === null) {
                $this->mainError = $this->lastError;
            }
            return null;
        }

        if ($httpCode !== 200) {
            $this->lastError = "HTTP {$httpCode} xətası qayıtdı (200 gözlənilirdi)";
            if ($this->mainError === null) {
                $this->mainError = $this->lastError;
            }
            return null;
        }

        $this->lastError = null;
        return $body;
    }

    /**
     * Optional FlareSolverr request to solve Cloudflare challenge
     */
    protected function fetchWithFlareSolverr(string $flareUrl, string $targetUrl): ?string {
        $ch = curl_init(rtrim($flareUrl, '/') . '/v1');
        $payload = json_encode([
            'cmd' => 'request.get',
            'url' => $targetUrl,
            'maxTimeout' => 60000
        ]);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 65,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $res = curl_exec($ch);
        @curl_close($ch);

        if (!$res) return null;
        $data = json_decode($res, true);
        if (($data['status'] ?? '') === 'ok' && !empty($data['solution']['response'])) {
            return $data['solution']['response'];
        }
        return null;
    }

    /**
     * Load HTML into DOMDocument and DOMXPath
     */
    protected function parseHtml(string $html): ?DOMXPath {
        if (empty($html)) return null;

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        // Prefix with UTF-8 meta to avoid character encoding issues
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        return new DOMXPath($doc);
    }

    /**
     * Helper to query single XPath text value
     */
    protected function xpathText(DOMXPath $xpath, string $query, ?\DOMNode $context = null): string {
        $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);
        if ($nodes && $nodes->length > 0) {
            return trim(preg_replace('/\s+/', ' ', $nodes->item(0)->textContent));
        }
        return '';
    }

    /**
     * Helper to query single XPath attribute value
     */
    protected function xpathAttr(DOMXPath $xpath, string $query, string $attr, ?\DOMNode $context = null): string {
        $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);
        if ($nodes && $nodes->length > 0) {
            $node = $nodes->item(0);
            if ($node instanceof \DOMElement && $node->hasAttribute($attr)) {
                return trim($node->getAttribute($attr));
            }
        }
        return '';
    }

    /**
     * Safely get attribute from any DOMNode if it is a DOMElement
     */
    protected function nodeAttr(\DOMNode $node, string $attr): string {
        if ($node instanceof \DOMElement && $node->hasAttribute($attr)) {
            return trim($node->getAttribute($attr));
        }
        return '';
    }

    /**
     * Extract OpenGraph meta property
     */
    protected function extractMeta(DOMXPath $xpath, string $property): string {
        $val = $this->xpathAttr($xpath, "//meta[@property='{$property}']", 'content');
        if (!$val) {
            $val = $this->xpathAttr($xpath, "//meta[@name='{$property}']", 'content');
        }
        return $val;
    }

    /**
     * Resolve relative URL into absolute URL
     */
    protected function makeAbsoluteUrl(string $rel, string $base): string {
        if (empty($rel)) return '';
        if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;
        if (str_starts_with($rel, '//')) return 'https:' . $rel;

        $parts = parse_url($base);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        if (str_starts_with($rel, '/')) {
            return "{$scheme}://{$host}{$port}{$rel}";
        }

        $path = $parts['path'] ?? '/';
        $dir = dirname($path);
        if ($dir === '\\' || $dir === '.') $dir = '';
        return "{$scheme}://{$host}{$port}{$dir}/{$rel}";
    }

    /**
     * Clean article HTML by stripping scripts, styles, trackers, keeping p, h2, h3, ul, ol, li, img
     */
    protected function cleanArticleHtml(string $html): string {
        // Strip scripts, styles, iframes, and ads
        $html = preg_replace('/<(script|style|iframe|noscript|svg)[^>]*>.*?<\/\1>/si', '', $html);
        $html = strip_tags($html, '<p><br><h2><h3><h4><ul><ol><li><strong><b><em><i><blockquote><img><a>');
        // Clean empty tags
        $html = preg_replace('/<p>\s*(&nbsp;|\s)*<\/p>/i', '', $html);
        return trim($html);
    }
}
