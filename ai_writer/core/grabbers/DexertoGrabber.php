<?php
/**
 * Dexerto Grabber
 * Source: https://www.dexerto.com/ (Focus on "Latest news" block)
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class DexertoGrabber extends BaseGrabber {
    public function getId(): string { return 'dexerto'; }
    public function getName(): string { return 'Dexerto (Latest News)'; }
    public function getUrl(): string { return 'https://www.dexerto.com/'; }
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
        $links = $xpath->query("//h2/a | //h3/a | //article//h2/a | //article//h3/a | //a[contains(@href, '-') and contains(@class, 'title')]");
        if (!$links || $links->length === 0) {
            $links = $xpath->query("//a[contains(@href, '/gaming/') or contains(@href, '/tech/') or contains(@href, '/entertainment/')]");
        }

        $seen = [];
        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href) || str_contains($href, '/category/') || str_contains($href, '/author/') || str_contains($href, '/wikis/') || str_contains($href, '/tag/')) continue;

            $title = trim($node->textContent);
            if (strlen($title) < 15) continue;

            if (isset($seen[$href])) continue;
            $seen[$href] = true;

            $absUrl = $this->makeAbsoluteUrl($href, 'https://www.dexerto.com');
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
            "//div[@id='article-content']//p | " .
            "//div[@id='article-content']//h2 | " .
            "//div[@id='article-content']//h3 | " .
            "//article//div[contains(@class, 'article__content') or contains(@class, 'entry-content')]//p"
        );
        if (!$bodyNodes || $bodyNodes->length === 0) {
            $bodyNodes = $xpath->query("//article//p | //article//h2 | //article//h3");
        }

        $paragraphs = [];
        $stopRemaining = false;
        foreach ($bodyNodes as $node) {
            if ($stopRemaining) break;

            $cls = $node instanceof \DOMElement ? $node->getAttribute('class') : '';
            if (str_contains($cls, 'disclaimer') || str_contains($cls, 'byline')) {
                continue;
            }

            $text = trim($node->textContent);
            if (strlen($text) < 15) continue;

            $tag = $node->nodeName;
            // Stop at "Related" recommendation sections
            if (($tag === 'h2' || $tag === 'h3') && (strcasecmp($text, 'Related') === 0 || str_starts_with(strtolower($text), 'related:'))) {
                break;
            }

            if (str_contains($text, 'Sign up to our newsletter') ||
                str_contains($text, 'Follow all of our') ||
                str_contains($text, 'Related:') ||
                str_contains($text, 'A post shared by')) {
                continue;
            }

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
            'tags' => ['Texnologiya', 'Oyun', 'Dexerto'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
