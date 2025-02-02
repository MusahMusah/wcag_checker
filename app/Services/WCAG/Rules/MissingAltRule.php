<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

class MissingAltRule extends BaseWCAGRule
{
    public function handle($content, \Closure $next)
    {
        $this->parser->filter('img')->each(function ($node) {
            if (!$this->parser->attr('alt', $node)) {
                $this->addIssue('img', 'Missing alt attribute', 'Provide a meaningful alt attribute for images.');
            }
        });

        return $next($this->getIssues());
    }
}