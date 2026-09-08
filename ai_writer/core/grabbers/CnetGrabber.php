<?php
/**
 * CNET Grabber
 * Source: https://www.cnet.com/news/
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class CnetGrabber extends BaseGrabber {
    public function getId(): string { return 'cnet'; }
    public function getName(): string { return 'CNET'; }
    public function getUrl(): string { return 'https://www.cnet.com/news/'; }
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
        $links = $xpath->query("//a[contains(@class, 'c-assetCard_link') or contains(@class, 'c-storyCard_link') or (contains(@href, '/tech/') and string-length(text()) > 15)]");
        if (!$links || $links->length === 0) {
            $links = $xpath->query("//h2/a | //h3/a | //article//h2/a | //article//h3/a");
        }

        $seen = [];
        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href) || str_contains($href, '/author/') || str_contains($href, '/deals/') || str_contains($href, '/user/')) continue;

            $title = trim($node->textContent);
            if (strlen($title) < 15) continue;

            if (isset($seen[$href])) continue;
            $seen[$href] = true;

            $absUrl = $this->makeAbsoluteUrl($href, 'https://www.cnet.com');
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
        $excerpt = $this->extractMeta($xpath, 'og:description') ?: $this->xpathText($xpath, "//p[contains(@class, 'c-pageArticle_lead')]");
        $img = $this->extractMeta($xpath, 'og:image');

        $bodyNodes = $xpath->query(
            "//article//div[contains(@class, 'entry-content') or contains(@class, 'c-pageArticle_content') or contains(@class, 'article-main-body') or contains(@class, 'article-body')]//p | " .
            "//article//div[contains(@class, 'entry-content') or contains(@class, 'c-pageArticle_content') or contains(@class, 'article-main-body') or contains(@class, 'article-body')]//h2 | " .
            "//article//div[contains(@class, 'entry-content') or contains(@class, 'c-pageArticle_content') or contains(@class, 'article-main-body') or contains(@class, 'article-body')]//h3"
        );
        if (!$bodyNodes || $bodyNodes->length === 0) {
            $bodyNodes = $xpath->query("//article//p | //article//h2 | //article//h3");
        }

        $paragraphs = [];
        foreach ($bodyNodes as $node) {
            $cls = $node instanceof \DOMElement ? $node->getAttribute('class') : '';
            if (str_contains($cls, 'disclaimer') || str_contains($cls, 'byline') || str_contains($cls, 'zd-best-list-precap')) {
                continue;
            }

            $text = trim($node->textContent);
            if (strlen($text) < 15) continue;
            if (str_contains($text, 'Subscribe to') ||
                str_contains($text, 'Read more:') ||
                str_contains($text, 'Jump To See More Details') ||
                str_contains($text, 'We may receive a commission') ||
                str_contains($text, "Editors' note:")) {
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
            'tags' => ['CNET', 'Texnologiya', 'Smartfon'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
