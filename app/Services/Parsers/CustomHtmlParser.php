<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\Interfaces\HtmlParserInterface;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class CustomHtmlParser implements HtmlParserInterface
{
    protected \DOMDocument $dom;
    protected \DOMXPath $xpath;

    public function __construct(string $htmlContent)
    {
        $this->dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($htmlContent);
        libxml_clear_errors();
        $this->xpath = new \DOMXPath($this->dom);
    }

    public function filter(string $selector): Collection
    {
        // Split multiple selectors
        $selectors = array_map('trim', explode(',', $selector));
        $results = new Collection();

        foreach ($selectors as $selector) {
            // Handle attribute selectors
            if (preg_match('/^(\w+)\[([^=]+)="([^"]+)"\]$/', $selector, $matches)) {
                $tag = $matches[1];
                $attribute = $matches[2];
                $value = $matches[3];
                $query = "//{$tag}[@{$attribute}='{$value}']";
            } else {
                $query = "//{$selector}";
            }

            $nodes = $this->xpath->query($query);

            if ($nodes !== false) {
                $results = $results->concat(iterator_to_array($nodes));
            }
        }

        return $results;
    }

    public function attr(string $attribute, \DOMNode|Crawler|null $node): ?string
    {
        if ($node instanceof \DOMElement && $node->hasAttribute($attribute)) {
            return $node->getAttribute($attribute);
        }

        return null;
    }

    public function getNodeName(\DOMNode|Crawler $node): ?string
    {
        return $node->nodeName;
    }

    public function getAllElements(): array
    {
        return iterator_to_array($this->dom->getElementsByTagName('*'));
    }
}