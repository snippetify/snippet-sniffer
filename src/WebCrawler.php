<?php

namespace Snippetify\SnippetSniffer;

use GuzzleHttp\Psr7\Uri;

class WebCrawler
{
    /**
     * Singletion.
     *
     * @var self
     */
    private static $instance;

    /**
     * Create an instance.
     *
     * @param  array  $config
     * @return  self
     */
    public static function create(): self
    {
        if (is_null(self::$instance)) self::$instance = new self;

        return self::$instance;
    }

    /**
     * Fetch snippets.
     *
     * @param  string  $query
     * @param  array  $uris
     * @param  array  $meta
     * @return  Snippet[]
     */
    public function fetch(string $query, array $uris, array $meta = []): array
    {
        $urls       = [];
        $snippets   = [];

        foreach ($uris as $uri) {
            $urls[] = new Uri($uri);
        }
        
        foreach ($urls as $url) {
            $snippets = array_merge($snippets, $this->scraper($url->getHost())->fetch($url));
        }

        return $snippets;
    }

    /**
     * Get scraper.
     *
     * @return  ScraperInterface
     */
    protected function scraper(string $name): ScraperInterface
    {
        $name = in_array($name, Core::$scrapers) ? $name : 'default';

        return Core::$scrapers[$name]::create();
    }
}
