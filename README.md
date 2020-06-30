# Snippet sniffer

**Snippet sniffer** allows you to extract code snippets from any websites.

## What it does

This library allows you

1. To get code snippets using search engine api (Google)
2. To get code snippets from any web page by crawling url seeds.

## How to use it

```bash
$ composer require snippetify/snippet-sniffer
```

### Snippet Sniffer

```php
use Snippetify\SnippetSniffer\SnippetSniffer;

// Configurations
$config = [
  // Required
  // Search engine api configuration keys
  'provider' => [
    "cx" => "your google Search engine ID",
    "key" => "your google API key"
    'name' => 'provider name (google)',
  ],
  // Optional
  // Useful for adding meta information to each snippet
  'app' => [
    "name" => "your App name",
    'version' => 'your App version',
  ],
  // Optional
  // Useful for logging
  'logger' => [
    "name" => "logger name",
    'file' => 'logger file path',
  ]
];

// Required
// Your query
$query = "your query";

// Optional
// Meta params
$meta = [
  "page" => 1,
  "limit" => 10,
];

// Fetch snippets
// @return Snippetify\SnippetSniffer\Common\Snippet[]
$snippets = SnippetSniffer::create($config)->fetch($query, $meta);
/*
* Snippet object public attributes [
*		title: string, 
* 	code: string, 
* 	description: string, 
* 	tags: array, // Array of string, also contains the snippet language
* 	meta: array
*]
*/
```

#### Providers

Providers allow you to get a **stack of seeds**(urls to scrape) from search engine API. Only Google search engine API is supported at this time, but you can create your own.

```php
use Snippetify\SnippetSniffer\Providers\GoogleProvider;

// Search engine api configuration keys
$config = [
  "cx" => "your google Search engine ID",
  "key" => "your google API key"
];

// Your query
$query = "your query";

// Meta params
$meta = [
  "page" => 1,
  "limit" => 10,
];

// url seeds
// @return GuzzleHttp\Psr7\Uri[]
$urlSeeds = GoogleProvider::create($config)->fetch($query, $meta);
```

##### Add new providers to package

1. Git clone the project
2. Create your new class in the `Snippetify\SnippetSniffer\Providers` folder
3. Each provider implements `Snippetify\SnippetSniffer\Providers\ProviderInterface` 
4. Take a look at `Snippetify\SnippetSniffer\Providers\GoogleProvider` to get you helped
5. Your fetch method must return an array of `Psr\Http\Message\UriInterface`
6. Add it in the providers stacks in the `Snippetify\SnippetSniffer\Core.php`
7. Write tests. Take a look at `Snippetify\SnippetSniffer\Tests\Providers\GoogleProviderTest` to get you helped
8. Send a pull request to us

##### Use your own providers

1. Your provider must implement `Snippetify\SnippetSniffer\Providers\ProviderInterface` 
2. Take a look at `Snippetify\SnippetSniffer\Providers\GoogleProvider` to get you helped
3. Your fetch method must return an array of `Psr\Http\Message\UriInterface`
4. Pass your new provider in the configuration parameter or use the `addProvider` method

```php
use Snippetify\SnippetSniffer\SnippetSniffer;

// Use Configurations
$config = [
  "providers" => [
    "provider_name" => ProviderClass::class,
    "provider_2_name" => Provider2Class::class // You can add as many as you want
  ]
];

// Or use addProvider method as follow
SnippetSniffer::create(...)
  ->addProvider('provider_name', ProviderClass::class)
  ->addProvider('provider_2_name', Provider2Class::class) // You can add as many as you want
  ...
```

#### Scrapers

Scrappers allow you to scrape html page and extract the snippets.

```php
use GuzzleHttp\Psr7\Uri;
use Snippetify\SnippetSniffer\Scrapers\DefaultScraper;

// Configurations
$config = [
  // Optional
  // Useful for adding meta information to each snippet
  'app' => [
    "name" => "your App name",
    'version' => 'your App version',
  ],
  // Optional
  // Useful for logging
  'logger' => [
    "name" => "logger name",
    'file' => 'logger file path',
  ]
];

// Your url
$urlSeed = "website url to scrape";

// Fetch snippets
// @return Snippetify\SnippetSniffer\Common\Snippet[]
$snippets = (new DefaultScraper($config))->fetch(new Uri($urlSeed));
```

##### Add new scrapers to package

1. Git clone the project
2. Create your new class in the `Snippetify\SnippetSniffer\Scrapers` folder
3. Each scraper implements `Snippetify\SnippetSniffer\Scrapers\ScraperInterface` 
4. Take a look at `Snippetify\SnippetSniffer\Scrapers\StackoverflowScraper` to get you helped
5. Your fetch method must return an array of `Snippetify\SnippetSniffer\Common\Snippet`
6. Add it in the scrapers stacks in the `Snippetify\SnippetSniffer\Core.php`
7. Write tests. Take a look at `Snippetify\SnippetSniffer\Tests\Scrapers\StackoverflowScraperTest` to get you helped
8. Send a pull request to us

##### Use your own scrapers

1. Your scraper must implement `Snippetify\SnippetSniffer\Scrapers\ScraperInterface` 
2. Take a look at `Snippetify\SnippetSniffer\Scrapers\StackoverflowScraper` to get you helped
3. Your fetch method must return an array of `Snippetify\SnippetSniffer\Common\Snippet`
4. Pass your new scraper in the configuration parameter or use the `addScraper` method

```php
use Snippetify\SnippetSniffer\SnippetSniffer;

// Important: Scrapper's name must be the website uri without the scheme. i.e. vuejs.org

// Configurations
$config = [
  "scrapers" => [
    "scraper_name" => ScraperClass::class,
    "scraper_2_name" => Scraper2Class::class // You can add as many as you want
  ]
];

// Or use addProvider method as follow
SnippetSniffer::create(...)
  ->addScraper('scraper_name', ScraperClass::class)
  ->addScraper('scraper_2_name', Scraper2Class::class) // You can add as many as you want
  ...
```

### Snippet crawler

Snippet crawler allows you to extract all snippets from a website by crawling it.

```php
use Snippetify\SnippetSniffer\WebCrawler;

// Optional
$config = [...];

// @return Snippetify\SnippetSniffer\Common\MetaSnippetCollection[]
$snippets = WebCrawler::create($config)->fetch(['your uri']);
```

#### Configuration reference

```php
$config = [
  // Required 
  // Search engine api configuration keys
  // https://developers.google.com/custom-search/v1/introduction
  'provider' => [
    "cx" => "your google Search engine ID",
    "key" => "your google API key"
    'name' => 'provider name (google)',
  ],
  // Optional
  // Useful for adding meta information to each snippet
  'app' => [
    "name" => "your App name",
    'version' => 'your App version',
  ],
  // Optional
  // Useful for logging
  'logger' => [
    "name" => "logger name",
    'file' => 'logger file path',
  ],
  // Optional
  // Useful for scraping
  "html_tags" => [
    "snippet" => "pre[class] code, div[class] code, .highlight pre, code[class]", // Tags to fetch snippets
    "index" => "h1, h2, h3, h4, h5, h6, p, li" // Tags to index
  ],
  // Optional
  // Useful for adding new scrapers
  // The name must be the website host without the scheme i.e. not https://foo.com but foo.com
  "scrapers" => [
    "scraper_name" => ScraperClass::class,
    "scraper_2_name" => Scraper2Class::class // You can add as many as you want
  ],
  // Optional
  // Useful for adding new providers
  "providers" => [
    "provider_name" => ProviderClass::class,
    "provider_2_name" => Provider2Class::class // You can add as many as you want
  ],
  // Optional
  // Useful for web crawling
  // Please follow the link below for more information as we use Spatie crawler
  // https://github.com/spatie/crawler
  "crawler" => [
    "langs" => ['en'],
    "profile" => CrawlSubdomainsAndUniqueUri::class,
    "user_agent" => 'your user agent',
    "concurrency" => 10,
    "ignore_robots" => false,
    "maximum_depth" => null,
    "execute_javascript" => false,
    "maximum_crawl_count" => null,
    "parseable_mime_types" => 'text/html',
    "maximum_response_size" => 1024 * 1024 * 3,
    "delay_between_requests" => 250,
  ]
];
```

## Changelog

Please see [CHANGELOG](https://github.com/snippetify/snippet-sniffer/blob/master/CHANGELOG.md) for more information what has changed recently.

## Testing

 You must set the **PROVIDER_NAME**, **PROVIDER_CX**, **PROVIDER_KEY**, **CRAWLER_URI**, **DEFAULT_SCRAPER_URI**, **STACKOVERFLOW_SCRAPER_URI** keys in phpunit.xml file before running tests.

**Important:** Those links must contains at least one snippet otherwise the tests will failed. The **Stackoverflow** uri must be a question link with an accepted answer otherwise the tests will failed.

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/snippetify/snippet-sniffer/blob/master/CONTRIBUTING.md) for details.

## Credits

1. [Evens Pierre](https://github.com/pierrevensy)

## License

The MIT License (MIT). Please see [License File](https://github.com/snippetify/snippet-sniffer/blob/master/LICENSE.md) for more information.

