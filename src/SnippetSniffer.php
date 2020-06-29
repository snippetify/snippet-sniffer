<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer;

use Snippetify\SnippetSniffer\Scrapers\ScraperInterface;
use Snippetify\SnippetSniffer\Providers\ProviderInterface;

final class SnippetSniffer
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Snippetify\SnippetSniffer\Scrapers\ScraperInterface[]
     */
    private $scrapers;

    /**
     * @var Snippetify\SnippetSniffer\Providers\ProviderInterface[]
     */
    private $providers;

    /**
     * @var self
     */
    private static $instance;

    /**
     * @param  array  $config
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct(array $config)
    {
        if (empty($config['provider']) || 
            empty($config['provider']['name'])) {
            throw new \InvalidArgumentException("Invalid arguments");
        }

        $this->config       = $config;
        $this->scrapers     = Core::$scrapers;
        $this->providers    = Core::$providers;

        if (!empty($config['scrapers'])) {
             $this->scrapers = array_merge($this->scrapers, $config['scrapers']);
        }

        if (!empty($config['providers'])) {
             $this->providers = array_merge($this->providers, $config['providers']);
        }

    }

    /**
     * @param  array  $config
     * @return  self
     */
    public static function create(array $config): self
    {
        if (is_null(self::$instance)) self::$instance = new self($config);

        return self::$instance;
    }

    /**
     * Add scraper.
     *
     * @param  string  $name
     * @param  string  $class
     * @return self
     * @throws InvalidArgumentException
     */
    public function addScraper(string $name, string $class): self
    {
        if (empty(trim($name)) || empty(trim($class))) {
            throw new \InvalidArgumentException("Arguments cannot be empty.");
        }

        $this->scrapers[$name] = $class;

        return $this;
    }

    /**
     * Add provider.
     *
     * @param  string  $name
     * @param  string  $class
     * @return self
     * @throws InvalidArgumentException
     */
    public function addProvider(string $name, string $class): self
    {
        if (empty(trim($name)) || empty(trim($class))) {
            throw new \InvalidArgumentException("Arguments cannot be empty.");
        }

        $this->providers[$name] = $class;

        return $this;
    }

    /**
     * Fetch snippets.
     *
     * @param  string  $query
     * @param  array  $meta
     * @return Snippetify\SnippetSniffer\Common\Snippet[]
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
     * @return Snippetify\SnippetSniffer\Providers\ProviderInterface
     * @throws RuntimeException
     */
    private function provider(): ProviderInterface
    {
        if (empty($this->providers)) {
            throw new \RuntimeException("Providers cannot be empty.");
        }

        foreach ($this->providers as $key => $value) {
            if (!class_exists($value)) {
                throw new \RuntimeException("Provider class not exists");
            }
        }

        if (!array_key_exists($this->config['provider']['name'], $this->providers)) {
            throw new \RuntimeException("Provider not exists");
        }

        $provider = $this->providers[$this->config['provider']['name']]::create($this->config['provider']);

        if (!$provider instanceof ProviderInterface) {
            throw new \RuntimeException("Provider class must implement the ProviderInterface");
        }

        return $provider;
    }

    /**
     * Get scraper.
     *
     * @param  string  $name
     * @return Snippetify\SnippetSniffer\Scrapers\ScraperInterface
     * @throws RuntimeException
     */
    private function scraper(string $name): ScraperInterface
    {
        if (empty($this->scrapers)) {
            throw new \RuntimeException("Scrapers cannot be empty.");
        }

        foreach ($this->scrapers as $key => $value) {
            if (!class_exists($value)) {
                throw new \RuntimeException("Scraper class not exists");
            }
        }

        $name    = array_key_exists($name, $this->scrapers) ? $name : 'default';
        $scraper = new $this->scrapers[$name]($this->config);

        if (!$scraper instanceof ScraperInterface) {
            throw new \RuntimeException("Scraper class must implement the ScraperInterface");
        }

        return $scraper;
    }
}
