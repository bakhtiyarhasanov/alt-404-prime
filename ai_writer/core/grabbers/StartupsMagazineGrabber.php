<?php
namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class StartupsMagazineGrabber extends BaseGrabber {
    public function getId(): string { return 'startups_magazine'; }
    public function getName(): string { return 'Startups Magazine'; }
    public function getUrl(): string { return 'https://startupsmagazine.co.uk/articles/news'; }
    public function getDefaultCategory(): string { return 'biznes'; }
    public function isRewriteEnabledByDefault(): bool { return true; }
    public function getDefaultRetryMinutes(): int { return 45; }

    public function grab(): array {
        $html = $this->fetchUrl($this->getSourceUrl());
        if (!$html) return [];

        $xpath = $this->parseHtml($html);
        if (!$xpath) return [];

        $items = [];
        $links = $xpath->query("//h2/a | //h3/a | //article//h2/a | //article//h3/a | //div[contains(@class, 'post-title')]/a | //h1/a | //a[contains(@class, 'title')]");

        $seen = [];
        foreach ($links as $node) {
            $href = $this->nodeAttr($node, 'href');
            if (empty($href)) continue;

            $title = trim($node->textContent);
            if (strlen($title) < 15) continue;

            if (isset($seen[$href])) continue;
            $seen[$href] = true;

            $absUrl = $this->makeAbsoluteUrl($href, $this->getSourceUrl());
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
        $excerpt = $this->extractMeta($xpath, 'og:description') ?: $this->xpathText($xpath, "//meta[@name='description']/@content");
        $img = $this->extractMeta($xpath, 'og:image');

        $bodyNodes = $xpath->query(
            "//article//p | //article//h2 | //article//h3 | " .
            "//div[contains(@class, 'article-content') or contains(@class, 'entry-content') or contains(@class, 'post-content')]//p | " .
            "//div[contains(@class, 'field--name-body')]//p"
        );

        $paragraphs = [];
        if ($bodyNodes) {
            foreach ($bodyNodes as $node) {
                $text = trim($node->textContent);
                if (strlen($text) < 15) continue;
                
                $tag = $node->nodeName;
                if ($tag === 'h2' || $tag === 'h3') {
                    $paragraphs[] = "<$tag>" . htmlspecialchars($text) . "</$tag>";
                } else {
                    $paragraphs[] = "<p>" . htmlspecialchars($text) . "</p>";
                }
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
            'image_url' => $img ? $this->makeAbsoluteUrl($img, $url) : '',
            'tags' => ['Startups Magazine', 'Startups', 'News'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
