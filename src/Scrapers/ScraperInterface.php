<?php

namespace Snippetify\SnippetSniffer\Scrapers;

use GuzzleHttp\Psr7\Uri;

interface ScraperInterface
{
    public function fetch(Uri $uri, array $options = []): array;
}
