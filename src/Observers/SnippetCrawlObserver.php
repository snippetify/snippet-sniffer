<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer\Observers;

use Spatie\Crawler\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Snippetify\SnippetSniffer\Core;
use Psr\Http\Message\ResponseInterface;
use Snippetify\SnippetSniffer\WebCrawler;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\RequestException;
use Snippetify\SnippetSniffer\Common\WebPage;
use Snippetify\SnippetSniffer\Scrapers\ScraperInterface;
use Snippetify\SnippetSniffer\Common\MetaSnippetCollection;

class SnippetCrawlObserver extends CrawlObserver
{
    /**
     * @var Snippetify\SnippetSniffer\WebCrawler
     */
    private $webCrawler;

    /**
     * @param  Snippetify\SnippetSniffer\WebCrawler  $parent
     * @param  array  $config
     * @return void
     */
    public function __construct(WebCrawler $webCrawler)
    {
        $this->webCrawler = $webCrawler;
    }

    /**
     * Called when the crawler has crawled the given url successfully.
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        // Must crawl uri once
        if ($this->webCrawler->isCrawled($url)) return;

        // Create crawler from reponse body
        $crawler = new Crawler((string) $response->getBody(), (string) $url);

        // Must contains snippets
        if (0 === $crawler->filter($this->webCrawler->getConfig()['html_tags']['snippet'])->count()) return;

        // Only crawl specified langs
        if (!$this->hasLang($crawler)) return;

        // New meta snippet
        $metaSnippet = new MetaSnippetCollection([
            'uri' => (string) $url,
            'snippets' => $this->webCrawler->getScraper($url->getHost())
                ->fetchFromDocument($crawler, $this->webCrawler->getMeta(), $url)
        ]);

        // Must contains snippets
        if (0 === count($metaSnippet->snippets)) return;

        // Get page description
        $summTags = 'meta[name="description"], meta[property="og:description"]';
        $summary  = 0 === $crawler->filter($summTags)->count() ? '' : $crawler->filter($summTags)->attr('content');

        try {
            $metaSnippet->page = new WebPage([
                'summary'   => $summary,
                'link'      => (string) $url,
                'title'     => $crawler->filter('title')->text(),
                'lang'      => $crawler->filter('html')->attr('lang'),
                'metaTags'  => $crawler->filter('meta')
                                ->each(function ($v) { return [$v->attr('name') => $v->attr('content')]; }),
                'plainText' => implode(' ', $crawler->filter($this->webCrawler->getConfig()['html_tags']['index'])
                                        ->each(function ($v) { return $v->text(); })),
            ]);
        } catch(\Exception $e) {
            $this->webCrawler->logError($requestException);
        }

        // Save meta snippet
        $this->webCrawler->addUniqueSnippet($metaSnippet);

        // Save crawled uri
        $this->webCrawler->addToCrawledUris($url);
    }

    /**
     * Called when the crawler had a problem crawling the given url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \GuzzleHttp\Exception\RequestException $requestException
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        $this->webCrawler->logError($requestException);
    }

    /**
     * @param Symfony\Component\DomCrawler\Crawler  $crawler
     * @return bool
     */
    private function hasLang(Crawler $crawler): bool
    {
        $has = false;

        foreach ($this->webCrawler->getConfig()['crawler']['langs'] as $value) {
            if (false !== stripos($crawler->filter('html')->attr('lang'), $value)) {
                $has = true;
            }
        }

        return $has;
    }
}
