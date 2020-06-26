<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Snippetify\SnippetSniffer\Providers\GoogleProvider;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;

class GoogleProviderTest extends TestCase
{
    /**
     * The provider.
     *
     * @var string
     */
    protected $provider;

    
    protected function setUp(): void
    {
        $config = [
            'cx' => $_SERVER['PROVIDER_CX'],
            'key' => $_SERVER['PROVIDER_KEY'],
        ];

        $this->provider = GoogleProvider::create($config);
    }


    public function testMissingConfigArgument()
    {
        try {
            $config = [
                'cx' => 'bidon',
            ];
            new GoogleProvider($config);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testInvalidConfigArgument()
    {
        try {
            $config = [
                'cx' => '00',
                'key' => '00',
            ];
            $provider = new GoogleProvider($config);
            $provider->fetch('js array contains');
        } catch (\Exception $e) {
            $this->assertInstanceOf(ClientException::class, $e);
        }
    }

    public function testInvalidPageArgument()
    {
        try {
            $data = $this->provider->fetch('js array contains', [ 'page' => 12, 'limit' => 10 ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    public function testInvalidLimitArgument()
    {
        try {
            $data = $this->provider->fetch('js array contains', [ 'page' => 1, 'limit' => 11 ]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    public function testInvalidQueryArgument()
    {
        try {
            $data = $this->provider->fetch('   ');
        } catch (\Exception $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
    }

    public function testContainsResults()
    {
        $data = $this->provider->fetch('js array contains', [ 'page' => 1, 'limit' => 10 ]);

        $this->assertGreaterThan(0, count($data));
    }
}
