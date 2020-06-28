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
        $providers = Core::$providers;

        if (!empty($this->config['providers'])) {
            foreach ($this->config['providers'] as $key => $value) {
                if (!class_exists($value)) {
                    throw new \RuntimeException("Provider class not exists");
                }
                $providers[$key] = $value;
            }
        }

        if (!array_key_exists($this->config['provider']['name'], $providers)) {
            throw new \RuntimeException("Provider not exists");
        }

        $provider = $providers[$this->config['provider']['name']]::create($this->config['provider']);

        if (!$provider instanceof ProviderInterface) {
            throw new \RuntimeException("Provider class must implement the ProviderInterface");
        }

        return $provider;
    }

    /**
     * Get scraper.
     *
     * @return  ScraperInterface
     */
    protected function scraper(string $name): ScraperInterface
    {
        $scrapers = Core::$scrapers;

        if (!empty($this->config['scrapers'])) {
            foreach ($this->config['scrapers'] as $key => $value) {
                if (!class_exists($value)) {
                    throw new \RuntimeException("Scraper class not exists");
                }
                $scrapers[$key] = $value;
            }
        }

        $name = array_key_exists($name, $scrapers) ? $name : 'default';

        $scraper = new $scrapers[$name]($this->config);

        if (!$scraper instanceof ScraperInterface) {
            throw new \RuntimeException("Scraper class must implement the ScraperInterface");
        }

        return $scraper;
    }
}
