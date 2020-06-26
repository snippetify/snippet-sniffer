<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer\Tests\Scrapers;

use GuzzleHttp\Psr7\Uri;
use Snippetify\SnippetSniffer\Scrapers\DefaultScraper;

class DefaultScraperTest extends AbstractScraperTest 
{
    protected function setUp(): void
    {
        $this->snippets = (new DefaultScraper)->fetch(new Uri('https://code.visualstudio.com/api/references/vscode-api'));
    }
}
