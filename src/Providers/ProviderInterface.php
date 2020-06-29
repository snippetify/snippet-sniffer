<?php

/*
 * This file is part of the snippetify package.
 *
 * (c) Evens Pierre <evenspierre@snippetify.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snippetify\SnippetSniffer\Providers;

interface ProviderInterface
{
    public static function create(array $config): self;

    public function __construct(array $config);

    public function fetch(string $query, array $meta): array;
}
