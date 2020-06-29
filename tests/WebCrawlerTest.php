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
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;

class WebCrawlerTest extends TestCase
{
    /**
     * @var Snippetify\SnippetSniffer\WebCrawler
     */
    protected $webCrawler;


    protected function setUp(): void
    {
        $this->webCrawler = WebCrawler::create();
    }


    public function testCustomScraperClassNotExists()
    {
        try {
            $config = [
                'scrapers' => [
                    'gigbyte' => 'lormemdm'
                ]
            ];
            $webCrawler = new WebCrawler($config);
            $webCrawler->fetch(['http://localhost:3000']);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\RuntimeException::class, $e);
        }
    }

    public function testCustomScraperClassNotImpementsScraperInterface()
    {
        try {
            $config = [
                'scrapers' => [
                    'default' => WebCrawler::class
                ]
            ];
            $webCrawler = new WebCrawler($config);
            $webCrawler->fetch(['http://localhost:3000']);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\RuntimeException::class, $e);
        }
    }

    public function testAddScraperArgumentsCannotBeEmpty()
    {
        try {
            $webCrawler = new WebCrawler();
            $webCrawler->addScraper('', '');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testContainsResults()
    {
        $data = $this->webCrawler->fetch(['http://localhost:3000']);

        $this->assertGreaterThan(0, count($data));
    }

    public function testAddScraper()
    {
        $data = $this->webCrawler
            ->addScraper('stackoverflow.com', \Snippetify\SnippetSniffer\Scrapers\StackoverflowScraper::class)
            ->fetch(['http://localhost:3000']);

        $this->assertGreaterThan(0, count($data));
    }
}
