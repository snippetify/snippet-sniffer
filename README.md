# Snippet sniffer

**Snippet sniffer** allows you to extract code snippets from any websites.

## What it does

This library allows you 

1. To get url seeds from search engine api (Google)
2. Get code snippets from any web page by crawling url seeds.

## How to use it

```bash
$ composer require snippetify/snippet-sniffer
```

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
2. Create your new class in the ``Snippetify\SnippetSniffer\Providers` folder
3. Each provider implements `Snippetify\SnippetSniffer\Providers\ProviderInterface` 
4. Take a look at `Snippetify\SnippetSniffer\Providers\GoogleProvider` to get you helped
5. Your fetch method must return an array of `GuzzleHttp\Psr7\Uri`
6. Add it in the providers stacks in the `Snippetify\SnippetSniffer\Core.php`
7. Write tests. Take a look at `Snippetify\SnippetSniffer\Tests\Providers\GoogleProviderTest` to get you helped
8. Send a pull request to us

##### Use your own providers

1. Your provider must implement `Snippetify\SnippetSniffer\Providers\ProviderInterface` 
2. Take a look at `Snippetify\SnippetSniffer\Providers\GoogleProvider` to get you helped
3. Your fetch method must return an array of `GuzzleHttp\Psr7\Uri`
4. Pass your new provider in the configuration parameter as follow

```php
// Configurations
$config = [
  "providers" => [
    "provider_name" => "ProviderClass::class"
  ]
];
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
2. Create your new class in the ``Snippetify\SnippetSniffer\Scrapers` folder
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
4. Pass your new scraper in the configuration parameter as follow

```php
// Configurations
// Important: Scrapper's name must be the website uri without the scheme. i.e. vuejs.org
$config = [
  "scrapers" => [
    "scraper_name" => "ScraperClass::class"
  ]
];
```

## Changelog

Please see [CHANGELOG](https://github.com/snippetify/snippet-sniffer/blob/master/CHANGELOG.md) for more information what has changed recently.

## Testing

 You must set the **PROVIDER_NAME**, **PROVIDER_CX**, **PROVIDER_KEY** keys in phpunit.xml file before running tests.

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/snippetify/snippet-sniffer/blob/master/CONTRIBUTING.md) for details.

## Credits

1. [Evens Pierre](https://github.com/pierrevensy)

## License

The MIT License (MIT). Please see [License File](https://github.com/snippetify/snippet-sniffer/blob/master/LICENSE.md) for more information.

