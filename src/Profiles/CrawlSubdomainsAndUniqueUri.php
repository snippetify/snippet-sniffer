<?php

namespace Snippetify\SnippetSniffer\Profiles;

use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlSubdomains;
use Snippetify\SnippetSniffer\WebCrawler;

class CrawlSubdomainsAndUniqueUri extends CrawlSubdomains
{
    /**
     * @var Snippetify\SnippetSniffer\WebCrawler
     */
    private $webCrawler;

    public function __construct($baseUrl, WebCrawler $webCrawler)
    {
        parent::__construct($baseUrl);

        $this->webCrawler = $webCrawler;
    }

    public function shouldCrawl(UriInterface $url): bool
    {
        return $this->isSubdomainOfHost($url) && !$this->webCrawler->isCrawled($url);
    }
}
