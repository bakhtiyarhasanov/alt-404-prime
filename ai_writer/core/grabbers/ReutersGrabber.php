<?php
/**
 * Reuters Technology Grabber
 * Source: https://www.reuters.com/technology/
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class ReutersGrabber extends BaseGrabber {
    public function getId(): string { return 'reuters'; }
    public function getName(): string { return 'Reuters Technology'; }
    public function getUrl(): string { return 'https://www.reuters.com/technology/'; }
    public function getDefaultCategory(): string { return 'texnologiya'; }
    public function isRewriteEnabledByDefault(): bool { return true; }
    public function getDefaultRetryMinutes(): int { return 60; }

    public function grab(): array {
        $sourceUrl = $this->getSourceUrl();
        $html = $this->fetchUrl($sourceUrl);
        if (!$html) return [];

        $xpath = $this->parseHtml($html);
        if (!$xpath) return [];

        $items = [];
        $links = $xpath->query("//a[contains(@href, '/technology/') and contains(@data-testid, 'Heading')] | //h2/a | //h3/a | //article//a");
        $seen = [];

        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href) || str_contains($href, '/authors/') || str_contains($href, '/tag/')) continue;

            $title = trim($node->textContent);
            if (strlen($title) < 15) continue;

            if (isset($seen[$href])) continue;
            $seen[$href] = true;

            $absUrl = $this->makeAbsoluteUrl($href, 'https://www.reuters.com');
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
        $excerpt = $this->extractMeta($xpath, 'og:description');
        $img = $this->extractMeta($xpath, 'og:image');

        $bodyNodes = $xpath->query(
            "//div[contains(@class, 'article-body__content') or contains(@class, 'article-body')]//p | " .
            "//div[contains(@class, 'article-body__content') or contains(@class, 'article-body')]//h2 | " .
            "//div[contains(@class, 'article-body__content') or contains(@class, 'article-body')]//h3 | " .
            "//div[contains(@data-testid, 'paragraph-')]//p | " .
            "//article//p"
        );
        $paragraphs = [];
        foreach ($bodyNodes as $node) {
            $cls = $node instanceof \DOMElement ? $node->getAttribute('class') : '';
            if (str_contains($cls, 'disclaimer') || str_contains($cls, 'byline')) {
                continue;
            }

            $text = trim($node->textContent);
            if (strlen($text) < 20) continue;
            if (str_contains($text, 'Reuters, the news and media division') ||
                str_contains($text, 'Our Standards: The Thomson Reuters Trust Principles') ||
                str_contains($text, 'Sign up here.')) {
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
            'tags' => ['Texnologiya', 'Reuters', 'Qlobal'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
