<?php
/**
 * CERT.az Grabber (Cybersecurity News - Copy as-is, No AI Rewrite)
 * Source: https://www.cert.az/news/23
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class CertGrabber extends BaseGrabber {
    public function getId(): string { return 'cert'; }
    public function getName(): string { return 'CERT.az (Kibertəhlükəsizlik Mərkəzi)'; }
    public function getUrl(): string { return 'https://www.cert.az/news/23'; }
    public function getDefaultCategory(): string { return 'texnologiya'; }
    public function isRewriteEnabledByDefault(): bool { return false; }
    public function getDefaultRetryMinutes(): int { return 60; }

    public function grab(): array {
        $html = $this->fetchUrl($this->getUrl());
        if (!$html) return [];

        $xpath = $this->parseHtml($html);
        if (!$xpath) return [];

        $items = [];
        $links = $xpath->query("//a[contains(@href, '/news/') and not(contains(@href, '/news/23'))] | //div[contains(@class, 'news')]//a");
        $seen = [];

        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href) || isset($seen[$href])) continue;
            $seen[$href] = true;

            $title = trim($node->textContent);
            if (strlen($title) < 10) continue;

            $absUrl = $this->makeAbsoluteUrl($href, 'https://www.cert.az');
            $item = $this->grabArticle($absUrl, $title);
            if ($item) {
                $items[] = $item;
            }

            if (count($items) >= 8) break;
        }

        return $items;
    }

    private function grabArticle(string $url, string $fallbackTitle): ?array {
        $html = $this->fetchUrl($url);
        if (!$html) return null;

        $xpath = $this->parseHtml($html);
        if (!$xpath) return null;

        $title = $this->xpathText($xpath, "//h1") ?: $this->extractMeta($xpath, 'og:title') ?: $fallbackTitle;
        $excerpt = $this->extractMeta($xpath, 'og:description');
        $img = $this->extractMeta($xpath, 'og:image');

        $bodyNodes = $xpath->query("//div[contains(@class, 'content')]//p | //div[contains(@class, 'news-body')]//p | //article//p");
        $paragraphs = [];
        foreach ($bodyNodes as $p) {
            $text = trim($p->textContent);
            if (strlen($text) > 15) {
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
            'tags' => ['CERT.az', 'Kibertəhlükəsizlik', 'Təhlükəsizlik'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
