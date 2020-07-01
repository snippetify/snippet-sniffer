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

use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler;
use Snippetify\SnippetSniffer\Common\Snippet;

final class StackoverflowScraper extends AbstractScraper
{
    /**
     * Fetch snippets.
     *
     * @param  Psr\Http\Message\UriInterface  $uri
     * @param  array  $options
     * @return Snippetify\SnippetSniffer\Common\Snippet[]
     */
    public function fetch(UriInterface $uri, array $options = []): array
    {
        $this->fetchFromDocument($this->getCrawler($uri), $options, $uri);
        
        return $this->snippets;
    }

    /**
     * Fetch fom document.
     *
     * @param  string|Symfony\Component\DomCrawler\Crawler  $document
     * @param  array  $options
     * @param  Psr\Http\Message\UriInterface  $uri
     * @return Snippetify\SnippetSniffer\Common\Snippet[]
     */
    public function fetchFromDocument($document, array $options = [], ?UriInterface $uri = null): array
    {
        $crawler = $document instanceof Crawler ? $document : new Crawler($document, $uri);

        try {
            $crawler->filter('#answers .answer')->each(function ($node) use ($crawler, $options) {
                
                if (($accepted = strpos($node->attr('class'), 'accepted') !== false) === false && // Only accepted snippets
                    isset($options['only_accepted']) && $options['only_accepted'] === true) return;
                
                $options['accepted'] = $accepted;

                $node->filter('pre')->each(function ($node) use ($crawler, $options) {
                    if ($this->containsSnippet($node->filter('code'))) return;
                    if ($this->hasMoreSnippetsPerPage($crawler, $options)) return;
                    if ($snippet = $this->fetchSnippet($node, $crawler, $options)) $this->snippets[] = $snippet;
                });
            });
        } catch (\Exception $e) {
            $this->logError($e);
        }

        return $this->snippets;
    }

    /**
     * Fetch snippet.
     *
     * @param  Crawler  $node
     * @param  Crawler  $crawler
     * @param  array    $meta
     * @return ?Snippetify\SnippetSniffer\Common\Snippet
     */
    protected function fetchSnippet(Crawler $node, Crawler $crawler, array $meta = []): ?Snippet
    {
         // When there is no snippets
         // When there is no tags
        if (0 === $node->filter('code')->count() || 0 === count($tags = $this->fetchTags($crawler))) return null;

        // Desc
        $descTag = 'meta[name="description"], meta[property="og:description"]';
        $desc    = 0 === $crawler->filter($descTag)->count() ? '' : $crawler->filter($descTag)->attr('content');
        $title   = 0 === $crawler->filter('#question-header')->count() ? 
                            $crawler->filter('title')->text() : 
                            $crawler->filter('#question-header')->text();

        return new Snippet([
            'tags'          => $tags,
            'title'         => $title,
            'type'          => Snippet::ROBOT_TYPE,
            'code'          => $node->filter('code')->text(),
            'description'   => $desc,
            'meta'          => [
                'accepted'  => $meta['accepted'],
                'url'       => $crawler->getUri(),
                'target'    => $this->config['app'],
                'website'   => $this->fetchWebsiteMetadata($crawler)
            ]
        ]);
    }

    /**
     * Fetch tags.
     *
     * @param  Crawler  $node
     * @return array
     */
    protected function fetchTags(Crawler $node): array
    {
        return array_unique($node->filter('.post-tag')->each(function ($v) { return $v->text(); }));
    }
}
