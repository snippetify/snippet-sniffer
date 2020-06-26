<?php

namespace Snippetify\SnippetSniffer\Scrapers;

use Goutte\Client;
use GuzzleHttp\Psr7\Uri;
use Snippetify\SnippetSniffer\Core;
use Snippetify\SnippetSniffer\Common\Snippet;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractScraper implements ScraperInterface
{
    /**
     * The config.
     *
     * @var string
     */
    protected $config;

    /**
     * Snippets.
     *
     * @var Snippetify\SnippetSniffer\Common\Snippet[]
     */
    protected $snippets = [];

    /**
     * Create new instance.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        if (empty($this->config['logger'])) $this->config['logger'] = [];

        if (empty($config['app'])) {
            $this->config['app']['name'] = Core::APP_NAME;
            $this->config['app']['type'] = Core::APP_TYPE;
            $this->config['app']['version'] = Core::APP_VERSION;
        }
    }

    /**
     * Get crawler.
     *
     * @param  Psr\Http\Message\UriInterface\Uri  $uri
     * @return  Symfony\Component\DomCrawler\Crawler
     */
    protected function getCrawler(Uri $uri): Crawler
    {
        return (new Client)->request('GET', $uri, ['headers' => ['User-Agent' => Core::USER_AGENT]]);
    }

    /**
     * Has snippet.
     *
     * @param  Crawler  $node
     * @return  Snippet[]
     */
    protected function hasSnippet(Crawler $node): bool
    {
    	$has = false;

    	foreach ($this->snippets as $snippet) {
    		if ($snippet->code == $node->text()) {
    			$has = true;
    			break;
    		}
    	}

    	return $has;
    }

    /**
     * Hydrate snippets.
     *
     * @param  Crawler  $node
     * @param  Uri  $uri
     * @return  Snippet[]
     */
    protected function hydrateSnippets(Crawler $node, Crawler $crawler, Uri $uri): void
    {
    	if ($node->count() === 0 || // When there is no snippets
            count($tags = $this->fetchTags($node)) === 0 || // When there is no tags
            $this->hasSnippet($node)) return; // If snippet is already saved

        $desc = 0 === $crawler->filter('meta[name="description"], meta[property="og:description"]')->count() ? 
            '' : $crawler->filter('meta[name="description"], meta[property="og:description"]')->attr('content');

        $this->snippets[] = new Snippet([
            'tags' 			=> $tags,
            'code' 			=> $node->text(),
            'type' 			=> Snippet::WIKI_TYPE,
            'title' 		=> $crawler->filter('title')->text(),
            'description'   => $desc,
            'meta' 			=> [
                'url'       => $node->getUri(),
                'target' 	=> $this->config['app'],
                'website'   => $this->fetchWebsiteMetadata($node, $crawler)
            ]
        ]);
    }

    /**
     * Fetch tags.
     *
     * @param  Crawler  $node
     * @return  Snippet[]
     */
    protected function fetchTags(Crawler $node): array
    {
        $tags 			= [];
        $nodeClasses 	= $node->attr('class');
        $parentClasses 	= implode(" ", $node->parents()->each(function ($v) { return $v->attr('class'); }));
        $classes 		= preg_split("/\s|\-/", strtolower(trim("$nodeClasses $parentClasses")));
        $languages 		= array_map(function ($v) { return strtolower(trim($v)); }, Core::getLanguages());

        foreach ($classes as $class) {
        	if (in_array($class, $languages)) $tags[] = ucfirst($class);
        }

        return array_unique($tags);
    }

    /**
     * Fetch website.
     *
     * @param  Crawler  $node
     * @return  Snippet[]
     */
    protected function fetchWebsiteMetadata(Crawler $node, Crawler $crawler): array
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
            else $name = (new Uri($node->getUri()))->getHost();
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
            'url'   => (new Uri($node->getUri()))->getHost(),
        ];
    }
}
