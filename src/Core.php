<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer;

use Snippetify\SnippetSniffer\Profiles\CrawlSubdomainsAndUniqueUri;

class Core
{
    public const APP_NAME      = 'Snippet sniffer';
    public const APP_TYPE      = 'snippetify-sniffer';
    public const APP_VERSION   = '1.1.0';

    // Crawler
    public const CRAWLER_LANG                   = 'en';
    public const CRAWLER_PROFILE                = CrawlSubdomainsAndUniqueUri::class;
    public const CRAWLER_CONCURENCY             = 10;
    public const CRAWLER_IGNORE_ROBOTS          = true;
    public const CRAWLER_MAXIMUM_DEPTH          = null;
    public const CRAWLER_EXECUTE_JAVASCRIPT     = false;
    public const CRAWLER_MAXIMUM_CRAWL_COUNT    = null;
    public const CRAWLER_PARSEABLE_MIME_TYPES   = 'text/html';
    public const CRAWLER_MAXIMUM_RESPONSE_SIZE  = 1024 * 1024 * 3;
    public const CRAWLER_DELAY_BETWEEN_REQUESTS = 250;
    public const CRAWLER_USER_AGENT = 'Mozilla/5.0 (compatible; Sniptbot/1.0; +http://www.snippetify.com/bot)';

    /**
     * Html Snippet tags
     * Add all html snippet tags here
     *
     * @var array
     */
    public const HTML_SNIPPET_TAGS = 'pre[class] code, div[class] code, .highlight pre, code[class]';

    /**
     * Html Snippet tags
     * Add all html snippet tags here
     *
     * @var array
     */
    public const HTML_TAGS_TO_INDEX = 'h1, h2, h3, h4, h5, h6, p, li';
    

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