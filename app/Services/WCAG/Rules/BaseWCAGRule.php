<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

use App\Enums\SeverityLevelEnum;
use App\Interfaces\HtmlParserInterface;

abstract class BaseWCAGRule
{
    protected HtmlParserInterface $parser;
    protected array $issues = [];

    public function __construct(HtmlParserInterface $parser)
    {
        $this->parser = $parser;
    }

    abstract public function handle($content, \Closure $next);

    protected function addIssue(string $element, string $issue, SeverityLevelEnum $severity): void
    {
        $suggestion = '';
        $this->issues[] = compact('element', 'issue', 'suggestion', 'severity');
    }

    public function getIssues(): array
    {
        return $this->issues;
    }
}