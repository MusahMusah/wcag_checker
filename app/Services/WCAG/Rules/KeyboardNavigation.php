<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

class KeyboardNavigation extends BaseWCAGRule
{
    public function handle($content, \Closure $next)
    {
        $focusableElements = $this->parser->filter('a, button, input, textarea, select, [tabindex]');

        if (count($focusableElements) === 0) {
            $this->addIssue(
                element: 'document',
                issue: 'Keyboard navigation issue',
                suggestion: 'No interactive elements detected. Ensure users can navigate via keyboard.',
                severity: 'high'
            );
        }

        foreach ($focusableElements as $node) {
            $nodeName = $this->parser->getNodeName($node);
            $tabIndex = $this->parser->attr('tabindex', $node);

            if ($nodeName === 'a' && !$this->parser->attr('href', $node)) {
                $this->addIssue(
                    element: 'a',
                    issue: 'Anchor tag missing href',
                    suggestion: 'Ensure anchor tags (`<a>`) have an `href` attribute to be keyboard-accessible.',
                    severity: 'high'
                );
            }

            // Check if tabindex is set to -1, making it unreachable via tab navigation
            if ($tabIndex === '-1' || $tabIndex === null) {
                $this->addIssue(
                    element: $nodeName,
                    issue: 'Tabindex issue',
                    suggestion: "Element <$nodeName> is missing tabindex, set tabindex to a positive value making it inaccessible via keyboard.",
                    severity: 'high'
                );
            }
        }

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}
