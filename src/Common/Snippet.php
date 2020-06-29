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

use Snippetify\ProgrammingLanguages\Facades\Languages;

/**
 * The snippet's class.
 */
class Snippet
{
    const ROBOT_TYPE = 'robot';

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $language;

    /**
     * @var array
     */
    public $meta;

    /**
     * @var array
     */
    public $tags;

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

        $this->setLanguageFromTags($attributes);
    }

    /**
     * Set language from tags
     *
     * @return void
     */
    private function setLanguageFromTags(array $attributes)
    {
        if (!empty($attributes['tags'])) {
            foreach ($attributes['tags'] as $tag) {
                if (Languages::exists($tag)) {
                    $this->language = ucfirst($tag);
                }
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
