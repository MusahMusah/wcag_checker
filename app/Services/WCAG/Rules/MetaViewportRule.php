<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

class MetaViewportRule extends BaseWCAGRule
{

    public function handle($content, \Closure $next)
    {
        if (!$this->parser->filter('meta[name="viewport"]')->count()) {
            $this->addIssue('meta', 'Missing viewport meta tag', 'Ensure the document has a viewport meta tag for responsive design.');
        }

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}