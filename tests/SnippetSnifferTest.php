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
use Snippetify\SnippetSniffer\SnippetSniffer;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;

class SnippetSnifferTest extends TestCase
{
    /**
     * The sniffer.
     *
     * @var string
     */
    protected $sniffer;


    protected function setUp(): void
    {
        $config = [
            'provider' => [
                'cx' => $_SERVER['PROVIDER_CX'],
                'key' => $_SERVER['PROVIDER_KEY'],
                'name' => $_SERVER['PROVIDER_NAME'],
            ],
        ];

        $this->sniffer = SnippetSniffer::create($config);
    }


    public function testMissingConfigArgument()
    {
        try {
            $config = [
                'provider' => [],
            ];
            new SnippetSniffer($config);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testInvalidProvider()
    {
        try {
            $config = [
                'provider' => [
                    'name' => 'poople',
                    'cx' => 'a',
                    'key' => 'a',
                ],
            ];
            $sniffer = new SnippetSniffer($config);
            $sniffer->fetch('js array contains', [ 'page' => 1, 'limit' => 10 ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\RuntimeException::class, $e);
        }
    }

    public function testContainsResults()
    {
        $data = $this->sniffer->fetch('js array contains', [ 'page' => 1, 'limit' => 10 ]);

        $this->assertGreaterThan(0, count($data));
    }
}
