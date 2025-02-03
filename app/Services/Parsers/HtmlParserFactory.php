<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\Interfaces\HtmlParserInterface;

class HtmlParserFactory
{
    public static function create(string $htmlContent, string $parserType): HtmlParserInterface
    {
        return match ($parserType) {
            'custom' => new CustomHtmlParser($htmlContent),
            default => new SymfonyHtmlParser($htmlContent),
        };
    }
}