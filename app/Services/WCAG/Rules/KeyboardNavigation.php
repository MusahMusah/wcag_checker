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
                'document',
                'Keyboard navigation issue',
                'No interactive elements detected. Ensure users can navigate via keyboard.'
            );
        }

        foreach ($focusableElements as $node) {
            $nodeName = $this->parser->getNodeName($node);
            $tabIndex = $this->parser->attr('tabindex', $node);

            if ($nodeName === 'a' && !$this->parser->attr('href', $node)) {
                $this->addIssue(
                    'a',
                    'Anchor tag missing href',
                    'Ensure anchor tags (`<a>`) have an `href` attribute to be keyboard-accessible.'
                );
            }

            // Check if tabindex is set to -1, making it unreachable via tab navigation
            if ($tabIndex === '-1' || $tabIndex === null) {
                $this->addIssue(
                    $nodeName,
                    'Tabindex issue',
                    "Element <$nodeName> is missing tabindex, set tabindex to a positive value making it inaccessible via keyboard."
                );
            }
        }

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}
