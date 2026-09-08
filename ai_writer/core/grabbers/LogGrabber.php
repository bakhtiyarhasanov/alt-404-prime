<?php
/**
 * Log.com.tr Grabber
 * Source: https://www.log.com.tr/teknoloji-haberleri/
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class LogGrabber extends BaseGrabber {
    public function getId(): string { return 'log'; }
    public function getName(): string { return 'Log.com.tr'; }
    public function getUrl(): string { return 'https://www.log.com.tr/teknoloji-haberleri/'; }
    public function getDefaultCategory(): string { return 'texnologiya'; }
    public function isRewriteEnabledByDefault(): bool { return true; }
    public function getDefaultRetryMinutes(): int { return 30; }

    public function grab(): array {
        $sourceUrl = $this->getSourceUrl();
        $html = $this->fetchUrl($sourceUrl);
        if (!$html) return [];

        $xpath = $this->parseHtml($html);
        if (!$xpath) return [];

        $items = [];
        $links = $xpath->query("//h2/a | //h3/a | //div[contains(@class, 'post-title')]//a");
        $seen = [];

        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href) || str_contains($href, '/kategori/') || str_contains($href, '/author/')) continue;

            $title = trim($node->textContent);
            if (strlen($title) < 10) continue;

            if (isset($seen[$href])) continue;
            $seen[$href] = true;

            $absUrl = $this->makeAbsoluteUrl($href, $this->getUrl());
            $item = $this->grabArticle($absUrl, $title);
            if ($item) {
                $items[] = $item;
            }

            if (count($items) >= 10) break;
        }

        return $items;
    }

    public function grabArticle(string $url, string $fallbackTitle = ''): ?array {
        $html = $this->fetchUrl($url);
        if (!$html) return null;

        $xpath = $this->parseHtml($html);
        if (!$xpath) return null;

        $title = $this->xpathText($xpath, "//h1") ?: $this->extractMeta($xpath, 'og:title') ?: $fallbackTitle;
        $excerpt = $this->extractMeta($xpath, 'og:description');
        $img = $this->extractMeta($xpath, 'og:image');

        $bodyNodes = $xpath->query(
            "//div[contains(@class, 'storycontent') or contains(@class, 'entry-content') or contains(@class, 'post-content')]//p | " .
            "//div[contains(@class, 'storycontent') or contains(@class, 'entry-content') or contains(@class, 'post-content')]//h2 | " .
            "//div[contains(@class, 'storycontent') or contains(@class, 'entry-content') or contains(@class, 'post-content')]//h3"
        );
        if (!$bodyNodes || $bodyNodes->length === 0) {
            $bodyNodes = $xpath->query("//article[contains(@class, 'boxPost')]//p | //article//p");
        }

        $paragraphs = [];
        foreach ($bodyNodes as $node) {
            $cls = $node instanceof \DOMElement ? $node->getAttribute('class') : '';
            if (str_contains($cls, 'disclaimer') || str_contains($cls, 'byline') || str_contains($cls, 'share')) {
                continue;
            }

            $text = trim($node->textContent);
            if (strlen($text) < 15) continue;
            if (str_contains($text, 'İlginizi Çekebilir') || 
                str_contains($text, 'googletag') || 
                str_contains($text, 'PAYLAŞ') ||
                str_contains($text, 'TWEETLE') ||
                str_contains($text, 'GÖNDER')) {
                continue;
            }

            $tag = $node->nodeName;
            if ($tag === 'h2' || $tag === 'h3') {
                $paragraphs[] = "<{$tag}>" . htmlspecialchars($text) . "</{$tag}>";
            } else {
                $paragraphs[] = "<p>" . htmlspecialchars($text) . "</p>";
            }
        }

        $content = implode("\n", $paragraphs);
        if (empty($content)) {
            $content = "<p>" . htmlspecialchars($excerpt ?: $title) . "</p>";
        }

        return [
            'external_id' => md5($url),
            'url' => $url,
            'title' => $title,
            'excerpt' => $excerpt ?: mb_substr(strip_tags($content), 0, 200),
            'content' => $content,
            'image_url' => $this->makeAbsoluteUrl($img, $url),
            'tags' => ['Texnologiya', 'Qadcet'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
