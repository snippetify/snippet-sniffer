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
 * The snippet's class.
 */
class MetaSnippetCollection
{
    /**
     * @var Psr\Http\Message\UriInterface
     */
    public $uri;

    /**
     * @var Snippetify\SnippetSniffer\Common\WebPage
     */
    public $page;

    /**
     * @var Snippetify\SnippetSniffer\Common\Snippet[]
     */
    public $snippets;

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
        return "Meta Snippet collection: ".json_encode($this->toArray());
    }
}
