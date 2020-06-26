<?php

namespace Snippetify\SnippetSniffer\Providers;

interface ProviderInterface
{
    public static function create(array $config): self;

    public function __construct(array $config);

    public function fetch(string $query, array $meta): array;
}
