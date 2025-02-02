<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

use App\interfaces\HtmlParserInterface;

abstract class BaseWCAGRule
{
    protected HtmlParserInterface $parser;
    protected array $issues = [];

    public function __construct(HtmlParserInterface $parser)
    {
        $this->parser = $parser;
    }

    abstract public function handle($content, \Closure $next);

    protected function addIssue(string $element, string $issue, string $suggestion): void
    {
        $this->issues[] = compact('element', 'issue', 'suggestion');
    }

    public function getIssues(): array
    {
        return $this->issues;
    }
}