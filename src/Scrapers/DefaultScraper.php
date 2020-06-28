<?php

namespace Snippetify\SnippetSniffer\Scrapers;

use Psr\Http\Message\UriInterface;
use Snippetify\SnippetSniffer\Common\Logger;

final class DefaultScraper extends AbstractScraper
{
    /**
     * Fetch snippets.
     *
     * @param  UriInterface  $uri
     * @param  array  $options
     * @return  Snippet[]
     */
    public function fetch(UriInterface $uri, array $options = []): array
    {
        $crawler = $this->getCrawler($uri);

        try {
            $crawler->filter('pre[class] code')->each(function ($node) use ($crawler, $uri) {
                $this->hydrateSnippets($node, $crawler, $uri);
            });

            $crawler->filter('div[class] code')->each(function ($node) use ($crawler, $uri) {
                $this->hydrateSnippets($node, $crawler, $uri);
            });

            $crawler->filter('.highlight pre')->each(function ($node) use ($crawler, $uri) {
                $this->hydrateSnippets($node, $crawler, $uri);
            });

            $crawler->filter('code[class]')->each(function ($node) use ($crawler, $uri) {
                $this->hydrateSnippets($node, $crawler, $uri);
            });
        } catch (\Exception $e) {
            Logger::create($this->config['logger'])->log($e, Logger::ERROR);
        }

        return $this->snippets;
    }
}
