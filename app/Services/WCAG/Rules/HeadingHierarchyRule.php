<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

use App\Enums\SeverityLevelEnum;

class HeadingHierarchyRule extends BaseWCAGRule
{
    public function handle($content, \Closure $next)
    {
        $lastLevel = 0;

        foreach ($this->parser->filter('h1, h2, h3, h4, h5, h6') as $node) {
            // Map node name (h1, h2, ...) to level number (1, 2, ...)
            $currentLevel = (int) filter_var($this->parser->getNodeName($node), FILTER_SANITIZE_NUMBER_INT);

            // Check if the current level skips more than one level from the previous one
            if ($lastLevel && ($currentLevel > $lastLevel + 1)) {
                $this->addIssue(
                    element: $this->parser->getNodeName($node),
                    issue: 'Skipped heading level',
                    severity: SeverityLevelEnum::High
                );
            }

            // Update lastLevel only after checking the skipped levels
            $lastLevel = $currentLevel;
        }

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}
