<?php
/**
 * DonanımHaber Grabber
 * Source: https://www.donanimhaber.com/teknoloji-haberleri
 */

namespace AiWriter\Core\Grabbers;

use AiWriter\Core\BaseGrabber;

class DonanimHaberGrabber extends BaseGrabber {
    public function getId(): string { return 'donanimhaber'; }
    public function getName(): string { return 'DonanımHaber'; }
    public function getUrl(): string { return 'https://www.donanimhaber.com/teknoloji-haberleri'; }
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
        // Query news cards
        $nodes = $xpath->query("//div[contains(@class, 'medya') or contains(@class, 'haber')]//a[contains(@href, '-haberleri--') or contains(@href, '--')] | //a[contains(@class, 'baslik') or contains(@href, 'haberleri--')]");
        
        $seenUrls = [];
        foreach ($nodes as $node) {
            $link = $this->nodeAttr($node, 'href');
            if (empty($link) || str_contains($link, '/video-') || str_contains($link, '/forum/')) continue;
            
            $absUrl = $this->makeAbsoluteUrl($link, $this->getUrl());
            if (isset($seenUrls[$absUrl])) continue;
            $seenUrls[$absUrl] = true;

            $title = trim($node->textContent);
            if (strlen($title) < 10) continue;

            // Extract image from card if available
            $img = '';
            $imgNode = $xpath->query(".//img", $node);
            if ($imgNode->length > 0) {
                $img = $this->nodeAttr($imgNode->item(0), 'data-src') ?: $this->nodeAttr($imgNode->item(0), 'src');
            }

            // Fetch article details
            $articleData = $this->grabArticle($absUrl, $title, $img);
            if ($articleData) {
                $items[] = $articleData;
            }

            if (count($items) >= 10) break; // Limit to 10 latest per run
        }

        return $items;
    }

    public function grabArticle(string $url, string $fallbackTitle = '', string $fallbackImg = ''): ?array {
        $html = $this->fetchUrl($url);
        if (!$html) {
            return [
                'external_id' => md5($url),
                'url' => $url,
                'title' => $fallbackTitle,
                'excerpt' => $fallbackTitle,
                'content' => "<p>" . htmlspecialchars($fallbackTitle) . "</p>",
                'image_url' => $this->makeAbsoluteUrl($fallbackImg, $url),
                'tags' => ['DonanimHaber', 'Texnologiya'],
                'category' => $this->getDefaultCategory()
            ];
        }

        $xpath = $this->parseHtml($html);
        if (!$xpath) return null;

        $title = $this->xpathText($xpath, "//h1") ?: $this->extractMeta($xpath, 'og:title') ?: $fallbackTitle;
        $excerpt = $this->xpathText($xpath, "//div[contains(@class, 'ozet')] | //h2[contains(@class, 'spot')]") 
                   ?: $this->extractMeta($xpath, 'og:description');
        $img = $this->extractMeta($xpath, 'og:image') ?: $fallbackImg;

        // Article body
        $content = '';
        $bodyNodes = $xpath->query(
            "//section[contains(@class, 'yazi') or contains(@class, 'kolon')]//p[not(descendant::aside) and not(contains(@class, 'content'))] | " .
            "//section[contains(@class, 'yazi') or contains(@class, 'kolon')]//h2 | " .
            "//section[contains(@class, 'yazi') or contains(@class, 'kolon')]//ul[contains(@class, 'liste')]/li | " .
            "//div[contains(@class, 'icerik') or contains(@class, 'haber-metni') or contains(@class, 'content-body')]//p"
        );
        if (!$bodyNodes || $bodyNodes->length === 0) {
            $bodyNodes = $xpath->query("//article//p | //article//h2");
        }

        if ($bodyNodes && $bodyNodes->length > 0) {
            $paragraphs = [];
            foreach ($bodyNodes as $node) {
                $text = trim($node->textContent);
                if (strlen($text) < 10) continue;
                if (str_contains($text, 'İlginizi Çekebilir') || 
                    str_contains($text, 'reklam') || 
                    str_contains($text, 'twitterwidget') ||
                    str_contains($text, '{float:none') ||
                    str_contains($text, 'Ayrıca Bakınız') ||
                    str_contains($text, 'Yorum Yaz') ||
                    str_contains($text, 'Editör Hakkında') ||
                    str_contains($text, 'Eposta ile Paylaşın') ||
                    str_contains($text, 'Bu haberi ve diğer DH') ||
                    str_contains($text, 'Tam Boyutta Gör')) {
                    continue;
                }

                $tag = $node->nodeName;
                if ($tag === 'h2') {
                    $paragraphs[] = "<h2>" . htmlspecialchars($text) . "</h2>";
                } elseif ($tag === 'li') {
                    $paragraphs[] = "<li>" . htmlspecialchars($text) . "</li>";
                } else {
                    $paragraphs[] = "<p>" . htmlspecialchars($text) . "</p>";
                }
            }
            $content = implode("\n", $paragraphs);
        }

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
            'tags' => ['Texnologiya', 'Donanım'],
            'category' => $this->getDefaultCategory()
        ];
    }
}
