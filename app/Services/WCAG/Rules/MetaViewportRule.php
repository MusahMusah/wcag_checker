<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

class MetaViewportRule extends BaseWCAGRule
{

    public function handle($content, \Closure $next)
    {
        if (!$this->parser->filter('meta[name="viewport"]')->count()) {
            $this->addIssue(
                element: 'meta',
                issue: 'Missing viewport meta tag',
                suggestion: 'Ensure the document has a viewport meta tag for responsive design.',
                severity: 'medium'
            );
        }

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}