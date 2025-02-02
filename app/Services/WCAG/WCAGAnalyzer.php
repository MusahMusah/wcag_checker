<?php

declare(strict_types=1);

namespace App\Services\WCAG;

use App\Enums\SeverityLevelEnum;
use App\Exceptions\GeminiAISuggestionException;
use App\Interfaces\HtmlParserInterface;
use App\Services\Parsers\HtmlParserFactory;
use App\Services\WCAG\Rules\HeadingHierarchyRule;
use App\Services\WCAG\Rules\KeyboardNavigation;
use App\Services\WCAG\Rules\MetaViewportRule;
use App\Services\WCAG\Rules\MissingAltRule;
use App\Services\WCAG\Rules\MissingLabelRule;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Pipeline;

final class WCAGAnalyzer
{
    protected HtmlParserInterface $parser;

    protected array $issues = [];

    protected array $rules = [];

    private const ESSENTIAL_ELEMENTS = [
        'img',
        'a',
        'input',
        'button',
        'h1, h2, h3, h4, h5, h6',
        'form',
        'table',
    ];

    private array $severityWeights;

    public function __construct(string $htmlContent, string $parserType = 'symfony')
    {
        $this->parser = HtmlParserFactory::create($htmlContent, $parserType);
        app()->bind(HtmlParserInterface::class, fn () => $this->parser);
        $this->registerRules();

        $this->severityWeights = [
            SeverityLevelEnum::High->value => 20,
            SeverityLevelEnum::Medium->value => 10,
            SeverityLevelEnum::Low->value => 5,
        ];
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

    /**
     * @throws GeminiAISuggestionException
     */
    public function analyze(): array
    {
        $this->issues = Pipeline::send($this)->through($this->rules)->thenReturn();

        $issuesWithAISuggestion = $this->fetchAISuggestions();

        return [
            'accessibility_score' => $this->calculateScore(),
            'issues' => $issuesWithAISuggestion,
        ];
    }

    /**
     * @throws GeminiAISuggestionException
     */
    protected function fetchAISuggestions(): array
    {
        try {
            $response = Gemini::geminiPro()->generateContent(
                sprintf(
                    'You are an AI assistant specializing in web accessibility. Given the following accessibility issues in an HTML document: %s, improve the issue descriptions and suggestions to be clearer and more actionable. Ensure that the output remains an array of updated issues in JSON format, without any additional explanations or formatting.',
                    json_encode($this->issues)
                )
            );

            return json_decode($response->text(), true);
        } catch (\Exception $e) {
            Log::error('Error fetching AI suggestions: '.$e->getMessage());

            throw new GeminiAISuggestionException($e->getMessage(), 400);
        }
    }

    protected function calculateScore(): float
    {
        $baseScore = $this->calculateBaseScore();
        $issueImpact = $this->calculateIssueImpact();
        $score = $this->applyPenalties($baseScore - $issueImpact);

        return max(0, min(100, round($score, 2)));
    }

    protected function getElementCounts(): array
    {
        $totalElements = count($this->parser->getAllElements());
        $essentialElementsCount = 0;

        foreach (self::ESSENTIAL_ELEMENTS as $selector) {
            $essentialElementsCount += count($this->parser->filter($selector));
        }

        return [
            'total' => $totalElements,
            'essential' => $essentialElementsCount,
        ];
    }

    protected function calculateBaseScore(): float
    {
        $elementCounts = $this->getElementCounts();

        return match (true) {
            $elementCounts['essential'] === 0 => 40,  // No essential elements
            $elementCounts['essential'] < 3 => 50,    // Very few essential elements
            $elementCounts['essential'] < 5 => 60,    // Some essential elements
            default => 100                            // Normal content amount
        };
    }

    protected function countIssuesBySeverity(): array
    {
        $issueCountByType = array_fill_keys(array_keys($this->severityWeights), 0);

        foreach ($this->issues as $issue) {
            if (isset($issue['severity']->value) && isset($issueCountByType[$issue['severity']->value])) {
                $issueCountByType[$issue['severity']->value]++;
            }
        }

        return $issueCountByType;
    }

    protected function calculateIssueImpact(): float
    {
        $issueCountByType = $this->countIssuesBySeverity();
        $impact = 0;

        foreach ($issueCountByType as $severity => $count) {
            $impact += $count * $this->severityWeights[$severity];
        }

        // Apply extra impact for issues on minimal pages
        $elementCounts = $this->getElementCounts();
        if ($elementCounts['total'] < 5 && ! empty($this->issues)) {
            $impact *= 1.5; // 50% more impact for issues on minimal pages
        }

        return $impact;
    }

    protected function applyPenalties(float $score): float
    {
        $elementCounts = $this->getElementCounts();

        // Apply penalty for minimal content with no accessibility features
        if ($elementCounts['total'] < 5 && $elementCounts['essential'] === 0) {
            return min($score, 40);
        }

        return $score;
    }
}
