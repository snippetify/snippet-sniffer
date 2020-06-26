<?php

namespace Snippetify\SnippetSniffer;

use Snippetify\SnippetSniffer\Scrapers\ScraperInterface;
use Snippetify\SnippetSniffer\Providers\ProviderInterface;

class SnippetSniffer
{
    /**
     * The config.
     *
     * @var string
     */
    protected $config;

    /**
     * Singletion.
     *
     * @var self
     */
    private static $instance;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct(array $config)
    {
        if (empty($config['provider']) || 
            empty($config['provider']['name'])) {
            throw new \InvalidArgumentException("Invalid arguments");
        }

        $this->config = $config;
    }

    /**
     * Create an instance.
     *
     * @param  array  $config
     * @return  self
     */
    public static function create(array $config): self
    {
        if (is_null(self::$instance)) self::$instance = new self($config);

        return self::$instance;
    }

    /**
     * Fetch snippets.
     *
     * @param  string  $query
     * @param  array  $meta
     * @return  Snippet[]
     */
    public function fetch(string $query, array $meta = []): array
    {
        $snippets   = [];
        $urls       = $this->provider()->fetch($query, $meta);
        
        foreach ($urls as $url) {
            $snippets = array_merge($snippets, $this->scraper($url->getHost())->fetch($url));
        }

        return $snippets;
    }

    /**
     * Get provider.
     *
     * @return  ProviderInterface
     */
    protected function provider(): ProviderInterface
    {
        if (!array_key_exists($this->config['provider']['name'], Core::$providers)) {
            throw new \RuntimeException("Provider not exists");
        }

        return Core::$providers[$this->config['provider']['name']]::create($this->config['provider']);
    }

    /**
     * Get scraper.
     *
     * @return  ScraperInterface
     */
    protected function scraper(string $name): ScraperInterface
    {
        $name = array_key_exists($name, Core::$scrapers) ? $name : 'default';

        return new Core::$scrapers[$name]($this->config);
    }
}
