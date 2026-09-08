<?php
/**
 * Azertag Grabber (Official News Agency - Copy as-is, No AI Rewrite)
 * Source: https://azertag.az/bolme/economy
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class AzertagGrabber extends BaseGrabber {
    public function getId(): string { return 'azertag'; }
    public function getName(): string { return 'Azertac (İqtisadiyyat & Texnologiya)'; }
    public function getUrl(): string { return 'https://azertag.az/bolme/economy'; }
    public function getDefaultCategory(): string { return 'texnologiya'; }
    public function isRewriteEnabledByDefault(): bool { return false; }
    public function getDefaultRetryMinutes(): int { return 30; }

    public function grab(): array {
        $html = $this->fetchUrl($this->getUrl());
        if (!$html) return [];

        $xpath = $this->parseHtml($html);
        if (!$xpath) return [];

        $items = [];
        $links = $xpath->query("//a[contains(@href, '/xeber/') or contains(@href, '/bolme/')]");
        $seen = [];

        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href) || isset($seen[$href]) || !str_contains($href, '/xeber/')) continue;
            $seen[$href] = true;

            $title = trim($node->textContent);
            if (strlen($title) < 15) continue;

            $absUrl = $this->makeAbsoluteUrl($href, 'https://azertag.az');
            $item = $this->grabArticle($absUrl, $title);
            if ($item) {
                $items[] = $item;
            }

            if (count($items) >= 10) break;
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

        $bodyNodes = $xpath->query("//div[contains(@class, 'news-text')]//p | //div[contains(@class, 'article-text')]//p | //div[contains(@class, 'post-content')]//p");
        $paragraphs = [];
        foreach ($bodyNodes as $p) {
            $text = trim($p->textContent);
            if (strlen($text) > 20 && !str_contains($text, 'AZƏRTAC')) {
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
            'tags' => ['Azərtac', 'İqtisadiyyat', 'Texnologiya'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
