<?php

namespace Snippetify\SnippetSniffer;

class Core
{
    const APP_NAME = 'Snippet sniffer';
    const APP_TYPE = 'snippetify-sniffer';
    const APP_VERSION = '1.0.0';
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:65.0) Gecko/20100101 Firefox/65.0';
    

    /**
     * The Provider stack.
     *
     * @var array
     */
    public static $providers = [
        'google' => \Snippetify\SnippetSniffer\Providers\GoogleProvider::class,
        // 'gigablast' => \Snippetify\SnippetSniffer\Providers\GigablastProvider::class,
    ];

    /**
     * The Scraper stack.
     *
     * @var array
     */
    public static $scrapers = [
        'default' => \Snippetify\SnippetSniffer\Scrapers\DefaultScraper::class,
        'stackoverflow.com' => \Snippetify\SnippetSniffer\Scrapers\StackoverflowScraper::class,
    ];
}