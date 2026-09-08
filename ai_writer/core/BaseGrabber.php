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
     * Perform HTTP GET request with realistic headers
     */
    protected function fetchUrl(string $url, array $customHeaders = []): ?string {
        $ch = curl_init();
        $headers = array_merge([
            'User-Agent: ' . $this->userAgent,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: az,tr,en-US,en;q=0.9',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        ], $customHeaders);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => 'gzip,deflate',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || $httpCode >= 400) {
            return null;
        }

        return $response;
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
