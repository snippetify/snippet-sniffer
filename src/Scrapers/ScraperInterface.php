<?php

namespace Snippetify\SnippetSniffer\Scrapers;

use Psr\Http\Message\UriInterface;

interface ScraperInterface
{
    public function fetch(UriInterface $uri, array $options = []): array;
}
