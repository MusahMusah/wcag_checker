<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

class MissingLabelRule extends BaseWCAGRule
{
    public function handle($content, \Closure $next)
    {
        $this->parser->filter('input, select, textarea')->each(function ($node) {
            if (!$this->parser->attr('aria-label', $node) || !$this->parser->attr('id', $node)) {
                $this->addIssue(
                    element: $this->parser->getNodeName($node),
                    issue: 'Missing accessible label',
                    suggestion: 'Provide an aria-label or associate input with a <label>.',
                    severity: 'high'
                );
            }
        });

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}