<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer\Scrapers;

use Goutte\Client;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Snippetify\SnippetSniffer\Core;
use Symfony\Component\DomCrawler\Crawler;
use Snippetify\SnippetSniffer\Common\Logger;
use Snippetify\SnippetSniffer\Common\Snippet;
use Snippetify\ProgrammingLanguages\Facades\Languages;

abstract class AbstractScraper implements ScraperInterface
{
    /**
     * The config.
     *
     * @var string
     */
    protected $config;

    /**
     * Logger.
     *
     * @var Snippetify\SnippetSniffer\Common\Logger
     */
    protected $logger;

    /**
     * Snippets.
     *
     * @var Snippetify\SnippetSniffer\Common\Snippet[]
     */
    protected $snippets = [];

    /**
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        if (empty($this->config['logger'])) $this->config['logger'] = [];

        if (empty($config['app'])) {
            $this->config['app']['name']        = Core::APP_NAME;
            $this->config['app']['type']        = Core::APP_TYPE;
            $this->config['app']['version']     = Core::APP_VERSION;
        }

        if (empty($config['crawler'])) {
            $this->config['crawler']['user_agent']  = Core::CRAWLER_USER_AGENT;
        }

        if (empty($config['html_tags'])) {
            $this->config['html_tags']['snippet']   = Core::HTML_SNIPPET_TAGS;
            $this->config['html_tags']['index']     = Core::HTML_TAGS_TO_INDEX;
        }

        $this->logger = Logger::create($this->config['logger']);
    }

    /**
     * Fetch snippets.
     *
     * @param  Psr\Http\Message\UriInterface  $uri
     * @param  array  $options
     * @return Snippetify\SnippetSniffer\Common\Snippet[]
     */
    public function fetch(UriInterface $uri, array $options = []): array
    {
        $this->fetchFromDocument($this->getCrawler($uri), $options);
        
        return $this->snippets;
    }

    /**
     * Fetch fom document.
     *
     * @param  string|Symfony\Component\DomCrawler\Crawler  $document
     * @param  array  $options
     * @return Snippetify\SnippetSniffer\Common\Snippet[]
     */
    public function fetchFromDocument($document, array $options = []): array
    {
        $crawler = $document instanceof Crawler ? $document : new Crawler($document);

        try {
            
            $htmlTags = explode(',', $this->config['html_tags']['snippet']);

            foreach ($htmlTags as $value) {
                $crawler->filter($value)->each(function ($node) use ($crawler) {
                    $this->hydrateSnippets($node, $crawler);
                });
            }

        } catch (\Exception $e) {
            $this->logError($e);
        }

        return $this->snippets;
    }

    /**
     * Get crawler.
     *
     * @param  Psr\Http\Message\UriInterface  $uri
     * @return Symfony\Component\DomCrawler\Crawler
     */
    protected function getCrawler(UriInterface $uri): Crawler
    {
        return (new Client)->request('GET', $uri, ['headers' => ['User-Agent' => $this->config['crawler']['user_agent']]]);
    }

    /**
     * Hydrate snippets.
     *
     * @param  Symfony\Component\DomCrawler\Crawler  $node
     * @param  Symfony\Component\DomCrawler\Crawler  $crawler
     * @param  array  $meta
     * @return void
     */
    protected function hydrateSnippets(Crawler $node, Crawler $crawler, array $meta = []): void
    {
    	if ($this->containsSnippet($this->snippets, $node)) return;

        if ($snippet = $this->fetchSnippet($node, $crawler, $meta)) $this->snippets[] = $snippet;
    }

    /**
     * Contains snippet.
     *
     * @param  Snippetify\SnippetSniffer\Common\Snippet[]  $snippets
     * @param  Symfony\Component\DomCrawler\Crawler  $node
     * @return bool
     */
    protected function containsSnippet(array $snippets, Crawler $node): bool
    {
        $has = false;

        try {
            foreach ($snippets as $snippet) {
                if ($snippet->code == $node->text()) {
                    $has = true;
                    break;
                }
            }
        } catch(\Exception $e) {
            $this->logError($e);
        }

        return $has;
    }

    /**
     * Fetch snippet.
     *
     * @param  Symfony\Component\DomCrawler\Crawler  $node
     * @param  Symfony\Component\DomCrawler\Crawler  $crawler
     * @param  array  $meta
     * @return ?Snippetify\SnippetSniffer\Common\Snippet
     */
    protected function fetchSnippet(Crawler $node, Crawler $crawler, array $meta = []): ?Snippet
    {
         // When there is no snippets
         // When there is no tags
        if (0 === $node->count() || 0 === count($tags = $this->fetchTags($node))) return null;

        // Desc
        $descTag = 'meta[name="description"], meta[property="og:description"]';
        $desc = 0 === $crawler->filter($descTag)->count() ? '' : $crawler->filter($descTag)->attr('content');

        return new Snippet([
            'tags'          => $tags,
            'code'          => $node->text(),
            'type'          => Snippet::ROBOT_TYPE,
            'title'         => $crawler->filter('title')->text(),
            'description'   => $desc,
            'meta'          => [
                'url'       => $crawler->getUri(),
                'target'    => $this->config['app'],
                'website'   => $this->fetchWebsiteMetadata($crawler)
            ]
        ]);
    }

    /**
     * Fetch tags.
     *
     * @param  Symfony\Component\DomCrawler\Crawler  $node
     * @return array
     */
    protected function fetchTags(Crawler $node): array
    {
        $tags 			= [];
        $nodeClasses 	= $node->attr('class');
        $parentClasses 	= implode(" ", $node->parents()->each(function ($v) { return $v->attr('class'); }));
        $classes 		= preg_split("/\s|\-/", strtolower(trim("$nodeClasses $parentClasses")));

        foreach ($classes as $class) {
        	if (Languages::exists($class)) $tags[] = ucfirst($class);
        }

        return array_unique($tags);
    }

    /**
     * Fetch website.
     *
     * @param  Symfony\Component\DomCrawler\Crawler  $crawler
     * @return array
     */
    protected function fetchWebsiteMetadata(Crawler $crawler): array
    {
        $title      = $crawler->filter('title')->text();
        $siteIcon   = $crawler->filter('link[rel="icon"]');
        $ogImage    = $crawler->filter('meta[property="og:image"]');
        $appleIcon  = $crawler->filter('link[rel="apple-touch-icon"]');
        $ogSiteName = $crawler->filter('meta[property="og:site_name"]');

        if ($ogSiteName->count() > 0) {
            $name = $ogSiteName->attr('content');
        } else {
            $words = preg_split("/\||\-/", $title);
            if (count($words) > 0) $name = trim($words[count($words) - 1]);
            else $name = (new Uri($crawler->getUri()))->getHost();
        }

        if ($ogImage->count() > 0) {
            $brand = $ogImage->attr('content');
        } else if ($appleIcon->count() > 0) {
            $brand = $appleIcon->attr('href');
        } else if ($siteIcon->count() > 0) {
            $brand = $siteIcon->attr('href');
        } else {
            $brand = '';
        }
        
        return [
            'name'  => $name,
            'brand' => $brand,
            'url'   => (new Uri($crawler->getUri()))->getHost(),
        ];
    }

    /**
     * Log error.
     *
     * @param  string  $message
     * @return  void
     */
    protected function logError(string $message): void
    {
        $this->logger->log($message, Logger::ERROR);
    }
}
