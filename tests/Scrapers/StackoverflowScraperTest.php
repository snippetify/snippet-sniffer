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
use Snippetify\SnippetSniffer\Scrapers\StackoverflowScraper;

class StackoverflowScraperTest extends AbstractScraperTest
{
    protected function setUp(): void
    {
        $this->snippets = (new StackoverflowScraper)->fetch(new Uri('https://stackoverflow.com/questions/218384/what-is-a-nullpointerexception-and-how-do-i-fix-it'));
    }


    public function testResultsContainOnlyAccepted()
    {
    	$snippets = (new StackoverflowScraper)->fetch(new Uri('https://stackoverflow.com/questions/218384/what-is-a-nullpointerexception-and-how-do-i-fix-it'), ['only_accepted' => true]);
    	$has = true;
    	
    	foreach ($snippets as $snippet) {
    		if (false === $snippet->meta['accepted']) {
    			$has = false;
    			break;
    		}
    	}

        $this->assertTrue($has);
    }
}
