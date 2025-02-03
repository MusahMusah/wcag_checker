<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

interface HtmlParserInterface
{
    public function filter(string $selector): Collection;

    public function getAllElements(): array;

    public function attr(string $attribute, \DOMNode|Crawler|null $node): ?string;

    public function getNodeName(\DOMNode|Crawler $node): ?string;
}
