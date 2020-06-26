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
use PHPUnit\Framework\TestCase;
use Snippetify\SnippetSniffer\Core;
use Snippetify\SnippetSniffer\Scrapers\DefaultScraper;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;

abstract class AbstractScraperTest extends TestCase
{
    /**
     * Snippets.
     *
     * @var Snippet[]
     */
    protected $snippets;


    public function testContainsResults()
    {
        $this->assertGreaterThan(0, count($this->snippets));
    }

    public function testResultsContainTitle()
    {
    	$has = true;
    	
    	foreach ($this->snippets as $snippet) {
    		if (strlen(trim($snippet->title)) === 0) {
                $has = false;
                break;
            }
    	}

        $this->assertTrue($has);
    }

    public function testResultsContainCode()
    {
    	$has = true;
    	
    	foreach ($this->snippets as $snippet) {
    		if (strlen(trim($snippet->code)) === 0) {
                $has = false;
                break;
            }
    	}

        $this->assertTrue($has);
    }

    public function testResultsContainUrls()
    {
    	$has = true;
    	
    	foreach ($this->snippets as $snippet) {
    		if (strlen(trim($snippet->meta['url'])) === 0) {
                $has = false;
                break;
            }
    	}

        $this->assertTrue($has);
    }

    public function testResultsContainTags()
    {
    	$has = true;
    	
    	foreach ($this->snippets as $snippet) {
    		if (count($snippet->tags) === 0) {
                $has = false;
                break;
            }
    	}

        $this->assertTrue($has);
    }

    public function testResultsNotContainDuplicateTags()
    {
    	$has = false;
    	
    	foreach ($this->snippets as $snippet) {
    		foreach ($snippet->tags as $i => $tag1) {
    			foreach ($snippet->tags as $j => $tag2) {
    				if ($i !== $j && $tag1 === $tag2) {
                        $has = true;
                        break;
                    }
    			}
    		}
    	}

        $this->assertFalse($has);
    }

    public function testResultsContainLanguage()
    {
    	$has = true;
    	$languages = array_map(function ($v) { return strtolower(trim($v)); }, Core::getLanguages());
    	
    	foreach ($this->snippets as $snippet) {
            $hasLang = false;
    		foreach ($snippet->tags as $tag) {
	        	if (in_array(strtolower($tag), $languages)) {
                    $hasLang = true;
                    break;
                }
	        }
            $has = $hasLang;
    	}

        $this->assertTrue($has);
    }

    public function testResultsNotContainDuplicates()
    {
    	$has = false;
    	
    	foreach ($this->snippets as $i => $snip1) {
    		foreach ($this->snippets as $j => $snip2) {
    			if ($i !== $j && $snip1->code === $snip2->code) {
                    $has = true;
                    break;
                }
    		}
    	}

        $this->assertFalse($has);
    }
}
