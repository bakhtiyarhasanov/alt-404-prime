<?php
/**
 * IDDA Grabber (Innovation and Digital Development Agency - Copy as-is, No AI Rewrite)
 * Source: https://idda.az/az/xeberler
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class IddaGrabber extends BaseGrabber {
    public function getId(): string { return 'idda'; }
    public function getName(): string { return 'İnnovasiya və Rəqəmsal İnkişaf Agentliyi (IRIA / IDDA)'; }
    public function getUrl(): string { return 'https://idda.az/az/xeberler'; }
    public function getDefaultCategory(): string { return 'texnologiya'; }
    public function isRewriteEnabledByDefault(): bool { return false; }
    public function getDefaultRetryMinutes(): int { return 60; }

    public function grab(): array {
        $html = $this->fetchUrl($this->getUrl());
        if (!$html) return [];

        $xpath = $this->parseHtml($html);
        if (!$xpath) return [];

        $items = [];
        $links = $xpath->query("//a[contains(@href, '/xeberler/') and string-length(@href) > 16] | //div[contains(@class, 'news')]//a");
        $seen = [];

        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href) || isset($seen[$href])) continue;
            $seen[$href] = true;

            $title = trim($node->textContent);
            if (strlen($title) < 10) continue;

            $absUrl = $this->makeAbsoluteUrl($href, 'https://idda.az');
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

        $bodyNodes = $xpath->query("//div[contains(@class, 'content')]//p | //article//p");
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
            'tags' => ['İRİA', 'İnnovasiya', 'Rəqəmsal İnkişaf', 'Startap'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
