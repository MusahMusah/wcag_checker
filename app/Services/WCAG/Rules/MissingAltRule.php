<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

use App\Enums\SeverityLevelEnum;

class MissingAltRule extends BaseWCAGRule
{
    public function handle($content, \Closure $next)
    {
        $this->parser->filter('img')->each(function ($node) {
            if (! $this->parser->attr('alt', $node)) {
                $this->addIssue(
                    element: 'img',
                    issue: 'Missing alt attribute',
                    severity: SeverityLevelEnum::High
                );
            }
        });

        return $next($this->getIssues());
    }
}
