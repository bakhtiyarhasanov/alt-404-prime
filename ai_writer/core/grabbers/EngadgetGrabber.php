<?php
/**
 * Engadget Grabber
 * Source: https://www.engadget.com/latest/
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class EngadgetGrabber extends BaseGrabber {
    public function getId(): string { return 'engadget'; }
    public function getName(): string { return 'Engadget'; }
    public function getUrl(): string { return 'https://www.engadget.com/latest/'; }
    public function getDefaultCategory(): string { return 'texnologiya'; }
    public function isRewriteEnabledByDefault(): bool { return true; }
    public function getDefaultRetryMinutes(): int { return 45; }

    public function grab(): array {
        $sourceUrl = $this->getSourceUrl();
        $html = $this->fetchUrl($sourceUrl);
        if (!$html) return [];

        $xpath = $this->parseHtml($html);
        if (!$xpath) return [];

        $items = [];
        $links = $xpath->query("//h2/a | //h3/a | //article//h2/a | //article//h3/a");
        $seen = [];

        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href) || str_contains($href, '/about/') || str_contains($href, '/tag/') || str_contains($href, '/category/') || str_contains($href, '/author/')) continue;

            $title = trim($node->textContent);
            if (strlen($title) < 15) continue;

            if (isset($seen[$href])) continue;
            $seen[$href] = true;

            $absUrl = $this->makeAbsoluteUrl($href, 'https://www.engadget.com');
            $item = $this->grabArticle($absUrl, $title);
            if ($item) {
                $items[] = $item;
            }

            if (count($items) >= 8) break;
        }

        return $items;
    }

    public function grabArticle(string $url, string $fallbackTitle = ''): ?array {
        $html = $this->fetchUrl($url);
        if (!$html) return null;

        $xpath = $this->parseHtml($html);
        if (!$xpath) return null;

        $title = $this->xpathText($xpath, "//h1") ?: $this->extractMeta($xpath, 'og:title') ?: $fallbackTitle;
        $excerpt = $this->extractMeta($xpath, 'og:description') ?: $this->xpathText($xpath, "//article//p[contains(@class, 'subtitle')]");
        $img = $this->extractMeta($xpath, 'og:image');

        $bodyNodes = $xpath->query(
            "//div[contains(@class, 'news-article')]//p | " .
            "//div[contains(@class, 'news-article')]//h2 | " .
            "//div[contains(@class, 'news-article')]//h3 | " .
            "//div[contains(@class, 'article-text') or contains(@class, 'caas-body') or contains(@class, 'article-body') or contains(@class, 'entry-content')]//p"
        );

        if (!$bodyNodes || $bodyNodes->length === 0) {
            $bodyNodes = $xpath->query("//article//p");
        }

        $paragraphs = [];
        foreach ($bodyNodes as $node) {
            $cls = $node instanceof \DOMElement ? $node->getAttribute('class') : '';
            if (str_contains($cls, 'disclaimer') || str_contains($cls, 'byline') || str_contains($cls, 'gallery-image-credit')) {
                continue;
            }

            $text = trim($node->textContent);
            if (strlen($text) < 15) continue;

            if (str_contains($text, 'Follow all of our') ||
                str_contains($text, 'We may receive a commission') ||
                str_contains($text, 'Subscribe to our') ||
                str_contains($text, 'Add Engadget on Google') ||
                str_contains($text, 'Billy Steele for Engadget') ||
                str_contains($text, 'for Engadget')) {
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
            'tags' => ['Engadget', 'Texnologiya', 'Cihazlar'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
