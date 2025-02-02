<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\interfaces\HtmlParserInterface;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class SymfonyHtmlParser implements HtmlParserInterface
{
    protected Crawler $crawler;

    public function __construct(string $htmlContent)
    {
        $this->crawler = new Crawler($htmlContent);
    }

    public function filter(string $selector): Collection
    {
        return collect($this->crawler->filter($selector)->each(fn($node) => $node));
    }

    public function attr(string $attribute, \DOMNode|Crawler|null $node): ?string
    {
//        return $this->crawler->attr($attribute);
        return $node->attr($attribute);
    }

    public function getNodeName(\DOMNode|Crawler $node): ?string
    {
        return $node->nodeName();
    }

    public function getAllElements(): array
    {
        return $this->crawler->filter('*')->getIterator()->getArrayCopy();
    }
}