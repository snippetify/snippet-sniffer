<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer\Common;

/**
 * WebPage.
 */
class WebPage
{
    /**
     * @var string
     */
    public $siteName;

    /**
     * @var Psr\Http\Message\UriInterface
     */
    public $siteUri;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $summary;

    /**
     * @var Psr\Http\Message\UriInterface
     */
    public $link;

    /**
     * @var string
     */
    public $plainText;

    /**
     * @var array
     */
    public $metaTags;

    /**
     * @return void
     */
    public function __construct(array $attributes = [])
    {
    	foreach ($attributes as $key => $value) {
    		if (property_exists($this, $key)) {
    			$this->{$key} = $value;
    		}
    	}
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray() {
        return get_object_vars($this);
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString() {
        return "Web Page: ".json_encode($this->toArray());
    }
}
