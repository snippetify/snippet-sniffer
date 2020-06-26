<?php

namespace Snippetify\SnippetSniffer\Common;

/**
 * The snippet's class.
 */
class Snippet
{
    const WIKI_TYPE = 'wiki';

    /**
     * The snippet's title.
     * @var string
     */
    public $title;

    /**
     * The snippet's code.
     * @var string
     */
    public $code;

    /**
     * The snippet's description.
     * @var string
     */
    public $description;

    /**
     * The snippet's type.
     * @var string
     */
    public $type;

    /**
     * The snippet's meta.
     * @var array
     */
    public $meta;

    /**
     * The snippet's tags.
     * @var array
     */
    public $tags;

    /**
     * Create a new Snippet instance.
     *
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
        return "Snippet: ".json_encode($this->toArray());
    }
}
