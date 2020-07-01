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

interface ScraperInterface
{
    public function fetch(UriInterface $uri, array $options = []): array;

    public function fetchFromDocument($document, array $options = [], ?UriInterface $uri = null): array;
}
