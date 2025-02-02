<?php

declare(strict_types=1);

namespace App\Services\WCAG\Rules;

class MissingLabelRule extends BaseWCAGRule
{
    public function handle($content, \Closure $next)
    {
//        foreach ($this->parser->filter('input, select, textarea') as $node) {
//            if (!$this->parser->attr('aria-label', $node) || !$this->parser->attr('id', $node)) {
//                $this->addIssue($node->nodeName(), 'Missing accessible label', 'Provide an aria-label or associate input with a <label>.');
//            }
//        }

        $this->parser->filter('input, select, textarea')->each(function ($node) {
            if (!$this->parser->attr('aria-label', $node) || !$this->parser->attr('id', $node)) {
                $this->addIssue($this->parser->getNodeName($node), 'Missing accessible label', 'Provide an aria-label or associate input with a <label>.');
            }
        });

        return $next(collect($this->getIssues())->merge($content)->toArray());
    }
}