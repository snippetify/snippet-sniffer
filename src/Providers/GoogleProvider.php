<?php

namespace Snippetify\SnippetSniffer\Providers;

use GuzzleHttp\Psr7\Uri;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;

class GoogleProvider implements ProviderInterface
{
    const API_URI = 'https://www.googleapis.com/customsearch/v1';

    /**
     * The config.
     *
     * @var string
     */
    protected $config;

    /**
     * Singletion.
     *
     * @var self
     */
    private static $instance;

    /**
     * Create a new instance.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        if (empty($config['cx']) || 
            empty($config['key'])) {
            throw new \InvalidArgumentException("Invalid arguments");
        }

        $this->config = $config;
    }

    /**
     * Create an instance.
     *
     * @param  array  $config
     * @return  self
     */
    public static function create(array $config): self
    {
        if (is_null(self::$instance)) self::$instance = new self($config);

        return self::$instance;
    }

    /**
     * Fetch data.
     *
     * @param  string  $query
     * @param  array  $meta
     * @return  Uri[]
     */
    public function fetch(string $query, array $meta = []): array
    {
        if (empty(trim($query))) {
            throw new InvalidArgumentException("The query cannot be empty");
        }

        if (!empty($meta['limit']) && (!is_numeric($meta['limit']) || ($meta['limit'] < 1 || $meta['limit'] > 10))) {
            throw new InvalidArgumentException("The limit argument must be must numeric and be between 0 and 10");
        }

        if (!empty($meta['page']) && (!is_numeric($meta['page']) ||  
            ($meta['page'] < 1 || ($meta['page'] * $meta['limit']) > 100))) {
            throw new InvalidArgumentException("The product of the page and the limit must be must be between 0 and 100");
        }

        $urls       = [];
        $query      = http_build_query([
            'q'     => trim($query),
            'cx'    => trim($this->config['cx']),
            'key'   => trim($this->config['key']),
            'num'   => empty($meta['limit']) ? 10 : $meta['limit'],
            'start' => empty($meta['page']) || empty($meta['limit']) ? 1 : 
                (($meta['page'] * $meta['limit']) - $meta['limit']) + 1
        ]);
        $response   = HttpClient::create()->request('GET', self::API_URI."?$query");

        if (200 !== $response->getStatusCode()) {
            throw new ClientException($response);
        }
        
        foreach ($response->toArray()['items'] as $item) {
            $urls[] = new Uri($item['link']);
        }

        return $urls;
    }
}
