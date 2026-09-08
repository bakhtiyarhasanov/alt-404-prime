<?php
/**
 * Interface for all Website Grabbers
 */

namespace AiWriter\Core;

interface GrabberInterface {
    /**
     * Unique source ID (e.g. 'donanimhaber', 'cnet')
     */
    public function getId(): string;

    /**
     * Human-readable source name
     */
    public function getName(): string;

    /**
     * Target source entry URL
     */
    public function getUrl(): string;

    /**
     * Default category in alt404
     */
    public function getDefaultCategory(): string;

    /**
     * Whether news from this source should be rewritten by OpenAI
     */
    public function isRewriteEnabledByDefault(): bool;

    /**
     * Default retry interval in minutes
     */
    public function getDefaultRetryMinutes(): int;

    /**
     * Execute grabber logic and return an array of news items:
     * [
     *   [
     *     'external_id' => string,
     *     'url' => string,
     *     'title' => string,
     *     'excerpt' => string,
     *     'content' => string,
     *     'image_url' => string,
     *     'tags' => array,
     *     'category' => string
     *   ],
     *   ...
     * ]
     */
    public function grab(): array;
}
