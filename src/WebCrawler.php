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

use GuzzleHttp\Psr7\Uri;
use Spatie\Crawler\Crawler;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlSubdomains;
use Snippetify\SnippetSniffer\Common\Logger;
use Snippetify\SnippetSniffer\Common\MetaSnippetCollection;
use Snippetify\SnippetSniffer\Observers\SnippetCrawlObserver;

class WebCrawler
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Snippetify\SnippetSniffer\Common\Logger
     */
    protected $logger;

    /**
     * @var Spatie\Crawler\Crawler
     */
    private $crawler;

    /**
     * @var Psr\Http\Message\UriInterface[]
     */
    private $urlSeeds;

    /**
     * @var Snippetify\SnippetSniffer\Scrapers\ScraperInterface[]
     */
    private $scrapers;

    /**
     * @var Snippetify\SnippetSniffer\Common\MetaSnippetCollection[]
     */
    private $snippets = [];

    /**
     * @var self
     */
    private static $instance;

    /**
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->config   = $config;
        $this->scrapers = Core::$scrapers;

        if (!empty($config['scrapers'])) {
            $this->scrapers = array_merge($this->scrapers, $config['scrapers']);
        }
        
        if (empty($config['logger'])) {
            $this->config['logger'] = [];
        }

        if (empty($config['crawler'])) {
            $this->config['crawler']['profile']                 = Core::CRAWLER_PROFILE;
            $this->config['crawler']['user_agent']              = Core::CRAWLER_USER_AGENT;
            $this->config['crawler']['concurrency']             = Core::CRAWLER_CONCURENCY;
            $this->config['crawler']['ignore_robots']           = Core::CRAWLER_IGNORE_ROBOTS;
            $this->config['crawler']['maximum_depth']           = Core::CRAWLER_MAXIMUM_DEPTH;
            $this->config['crawler']['execute_javascript']      = Core::CRAWLER_EXECUTE_JAVASCRIPT;
            $this->config['crawler']['maximum_crawl_count']     = Core::CRAWLER_MAXIMUM_CRAWL_COUNT;
            $this->config['crawler']['parseable_mime_types']    = [Core::CRAWLER_PARSEABLE_MIME_TYPES];
            $this->config['crawler']['maximum_response_size']   = Core::CRAWLER_MAXIMUM_RESPONSE_SIZE;
            $this->config['crawler']['delay_between_requests']  = Core::CRAWLER_DELAY_BETWEEN_REQUESTS;
        }

        if (empty($config['html_tags'])) {
            $this->config['html_tags']['snippet'] = Core::HTML_SNIPPET_TAGS;
            $this->config['html_tags']['index']   = Core::HTML_TAGS_TO_INDEX;
        }

        $this->logger  = Logger::create($this->config['logger']);

        $this->crawler = Crawler::create()
            ->setUserAgent($this->config['crawler']['user_agent'])
            ->setConcurrency($this->config['crawler']['concurrency'])
            ->setMaximumDepth($this->config['crawler']['maximum_depth'])
            ->setMaximumCrawlCount($this->config['crawler']['maximum_crawl_count'])
            ->setParseableMimeTypes($this->config['crawler']['parseable_mime_types'])
            ->setMaximumResponseSize($this->config['crawler']['maximum_response_size'])
            ->setDelayBetweenRequests($this->config['crawler']['delay_between_requests'])
        ;

        if ($this->config['crawler']['ignore_robots']) $this->crawler->ignoreRobots();
        if ($this->config['crawler']['execute_javascript']) $this->crawler->executeJavaScript();
    }

    /**
     * @param  array  $config
     * @return  self
     */
    public static function create(array $config = []): self
    {
        if (is_null(self::$instance)) self::$instance = new self($config);

        return self::$instance;
    }

    /**
     * @return  array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return  Snippetify\SnippetSniffer\Scrapers\ScraperInterface[]
     */
    public function getScrapers(): array
    {
        return $this->scrapers;
    }

    /**
     * @param  Snippetify\SnippetSniffer\Common\MetaSnippetCollection[] $snippets
     * @return void
     */
    public function setSnippets(array $snippets): void
    {
        $this->snippets = $snippets;
    }

    /**
     * @param  Snippetify\SnippetSniffer\Common\MetaSnippetCollection $snippet
     * @return self
     */
    public function addSnippet(MetaSnippetCollection $snippet): self
    {
        $this->snippets[] = $snippet;

        return $this;
    }

    /**
     * Add scraper.
     *
     * @param  string  $name
     * @param  string  $class
     * @return self
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
     * Get scraper.
     *
     * @return  Snippetify\SnippetSniffer\Scrapers\ScraperInterface
     */
    public function getScraper(string $name): ScraperInterface
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

    /**
     * Fetch snippets.
     *
     * @param  Psr\Http\Message\UriInterface[]  $uris
     * @param  array  $meta
     * @return Snippetify\SnippetSniffer\Common\MetaSnippetCollection[]
     */
    public function fetch(array $uris, array $meta = []): array
    {
        foreach ($uris as $key => $uri) {
            if (! $uri instanceof UriInterface ) {
                $uris[$key] = new Uri($uri);
            }
        }

        $this->crawler->setCrawlObserver(new SnippetCrawlObserver($this));
        
        foreach ($uris as $uri) {
            $this->crawler
                ->setCrawlProfile(new $this->config['crawler']['profile']($uri))
                ->startCrawling($uri);
        }
        $this->logger->log(json_encode($this->snippets));
        return $this->snippets;
    }

    /**
     * Log error.
     *
     * @param  string  $message
     * @return  void
     */
    public function logError(string $message): void
    {
        $this->logger->log($message, Logger::ERROR);
    }
}
