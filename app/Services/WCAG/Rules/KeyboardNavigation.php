<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

use App\Enums\SeverityLevelEnum;

class KeyboardNavigation extends BaseWCAGRule
{
    public function handle($content, \Closure $next)
    {
        $focusableElements = $this->parser->filter('a, button, input, textarea, select, [tabindex]');

        if (count($focusableElements) === 0) {
            $this->addIssue(
                element: 'document',
                issue: 'Keyboard navigation issue',
                severity: SeverityLevelEnum::High
            );
        }

        foreach ($focusableElements as $node) {
            $nodeName = $this->parser->getNodeName($node);
            $tabIndex = $this->parser->attr('tabindex', $node);

            if ($nodeName === 'a' && !$this->parser->attr('href', $node)) {
                $this->addIssue(
                    element: 'a',
                    issue: 'Anchor tag missing href',
                    severity: SeverityLevelEnum::High
                );
            }

            // Check if tabindex is set to -1, making it unreachable via tab navigation
            if ($tabIndex === '-1' || $tabIndex === null) {
                $this->addIssue(
                    element: $nodeName,
                    issue: 'Tabindex issue',
                    severity: SeverityLevelEnum::High
                );
            }
        }

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}
