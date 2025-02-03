<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

use App\Enums\SeverityLevelEnum;

class MetaViewportRule extends BaseWCAGRule
{

    public function handle($content, \Closure $next)
    {
        if (!$this->parser->filter('meta[name="viewport"]')->count()) {
            $this->addIssue(
                element: 'meta',
                issue: 'Missing viewport meta tag',
                severity: SeverityLevelEnum::Medium
            );
        }

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}