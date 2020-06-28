<?php

namespace Snippetify\SnippetSniffer\Scrapers;

use Goutte\Client;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler;
use Snippetify\SnippetSniffer\Common\Logger;
use Snippetify\SnippetSniffer\Common\Snippet;

final class StackoverflowScraper extends AbstractScraper
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
            $crawler->filter('#answers .answer')->each(function ($node) use ($crawler, $options) {
                
                if (($accepted = strpos($node->attr('class'), 'accepted') !== false) === false && // Only accepted snippets
                    isset($options['only_accepted']) && $options['only_accepted'] === true) return;
                
                if ($node->filter('pre code')->count() === 0 || // When there is no snippets
                    count($tags = $this->fetchTags($crawler)) == 0 || // When there is no tags
                    $this->hasSnippet($node->filter('code'))) return; // If snippet is already saved

                $node->filter('pre')->each(function ($node) use ($crawler, $tags, $accepted) {
                    
                    $title = 0 === $crawler->filter('#question-header')->count() ? 
                                        $crawler->filter('title')->text() : 
                                        $crawler->filter('#question-header')->text();

                    $desc = 0 === $crawler->filter('meta[name="description"], meta[property="og:description"]')->count() ? 
                                        '' : 
                                        $crawler->filter('meta[name="description"], meta[property="og:description"]')
                                                ->attr('content');

                    $this->snippets[] = new Snippet([
                        'tags'          => $tags,
                        'title'         => $title,
                        'type'          => Snippet::WIKI_TYPE,
                        'code'          => $node->filter('code')->text(),
                        'description'   => $desc,
                        'meta'          => [
                            'accepted'  => $accepted,
                            'url'       => $node->getUri(),
                            'target'    => $this->config['app'],
                            'website'   => $this->fetchWebsiteMetadata($node, $crawler)
                        ]
                    ]);
                });
                
            });
        } catch (\Exception $e) {
            Logger::create($this->config['logger'])->log($e, Logger::ERROR);
        }
        
        return $this->snippets;
    }

    /**
     * Fetch tags.
     *
     * @param  Crawler  $node
     * @return  Snippet[]
     */
    protected function fetchTags(Crawler $node): array
    {
        return array_unique($node->filter('.post-tag')->each(function ($v) { return $v->text(); }));
    }
}
