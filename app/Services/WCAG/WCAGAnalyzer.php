<?php

declare(strict_types=1);

namespace App\Services\WCAG;

use App\interfaces\HtmlParserInterface;
use App\Services\Parsers\HtmlParserFactory;
use App\Services\WCAG\Rules\HeadingHierarchyRule;
use App\Services\WCAG\Rules\KeyboardNavigation;
use App\Services\WCAG\Rules\MetaViewportRule;
use App\Services\WCAG\Rules\MissingAltRule;
use App\Services\WCAG\Rules\MissingLabelRule;
use Illuminate\Support\Facades\Pipeline;

class WCAGAnalyzer
{
    protected HtmlParserInterface $parser;
    protected array $issues = [];
    protected array $rules = [];

    public function __construct(string $htmlContent, string $parserType = 'symfony')
    {
        $this->parser = HtmlParserFactory::create($htmlContent, $parserType);
        app()->bind(HtmlParserInterface::class, fn () => $this->parser);
        $this->registerRules();
    }

    protected function registerRules(): void
    {
        $this->rules = [
            MissingAltRule::class,
            MissingLabelRule::class,
            HeadingHierarchyRule::class,
            MetaViewportRule::class,
            KeyboardNavigation::class,
        ];
    }

    public function analyze(): array
    {
        $this->issues = Pipeline::send($this)->through($this->rules)->thenReturn();

        return [
            'accessibility_score' => $this->calculateScore(),
            'issues' => $this->issues,
        ];
    }

    protected function calculateScore(): float
    {
        $totalElements = count($this->parser->getAllElements());
        $issueCount = count($this->issues);
        return $totalElements ? round((1 - $issueCount / $totalElements) * 100, 2) : 100;
    }
}