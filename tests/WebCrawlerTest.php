<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer\Tests;

use PHPUnit\Framework\TestCase;
use Snippetify\SnippetSniffer\WebCrawler;

final class WebCrawlerTest extends TestCase
{
    /**
     * @var Snippetify\SnippetSniffer\WebCrawler
     */
    private $webCrawler;

    /**
     * @var Snippetify\SnippetSniffer\Common\MetaSnippetCollection[]
     */
    private $snippets = [];


    protected function setUp(): void
    {
        $this->webCrawler = WebCrawler::create();
        $this->snippets   = $this->webCrawler->fetch([$_SERVER['CRAWLER_URI']]);
    }


    public function testContainsResults()
    {
        $this->assertGreaterThan(0, count($this->snippets));
    }

    public function testContainsUniqueResults()
    {
        $has = true;
        
        foreach ($this->snippets as $i => $snippet1) {
            foreach ($this->snippets as $j => $snippet2) {
                if ($i !== $j && (string) $snippet1->uri === (string) $snippet2->uri) {
                    $has = false;
                    break;
                }
            }
        }

        $this->assertTrue($has);
    }

    public function testContainsSnippets()
    {
        $has = true;

        foreach ($this->snippets as $snippet) {
            if (0 === count($snippet->snippets)) {
                $has = false;
                break;
            }
        }

        $this->assertTrue($has);
    }
}
