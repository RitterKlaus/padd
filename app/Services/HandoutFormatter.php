<?php

namespace App\Services;

/**
 * Converts the simple notation of a handout into safe HTML:
 * "- " starts a list item and a line of four or more "-" is an LCARS bar.
 */
class HandoutFormatter
{
    /**
     * Convert the text of a handout to HTML. All text is escaped.
     */
    public function toHtml(string $body): string
    {
        $blocks = [];
        $paragraph = [];
        $items = [];

        $flushParagraph = function () use (&$blocks, &$paragraph): void {
            if ($paragraph !== []) {
                $blocks[] = '<p>'.implode("<br />\n", $paragraph).'</p>';
                $paragraph = [];
            }
        };

        $flushList = function () use (&$blocks, &$items): void {
            if ($items !== []) {
                $blocks[] = "<ul class=\"lcars-list\">\n".implode("\n", $items)."\n</ul>";
                $items = [];
            }
        };

        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim($line);

            if ($line === '') {
                $flushParagraph();
            } elseif (preg_match('/^-{4,}$/', $line) === 1) {
                $flushParagraph();
                $flushList();
                $blocks[] = '<hr class="lcars-bar">';
            } elseif (preg_match('/^-\s+(.+)$/', $line, $matches) === 1) {
                $flushParagraph();
                $items[] = '<li>'.e($matches[1]).'</li>';
            } else {
                $flushList();
                $paragraph[] = e($line);
            }
        }

        $flushParagraph();
        $flushList();

        return implode("\n", $blocks);
    }
}
