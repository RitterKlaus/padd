<?php

namespace App\Services;

/**
 * Converts the simple notation of a handout into safe HTML:
 * "- " starts a list item, a line of four or more "-" is an LCARS bar and
 * "[image]" is the place of the image.
 */
class HandoutFormatter
{
    /**
     * Convert the text of a handout to HTML. All text is escaped, the image must
     * be safe HTML. Without a tag the image is placed below the text.
     */
    public function toHtml(string $body, ?string $image = null): string
    {
        $body = (string) preg_replace('/\[image\]/i', "\n[image]\n", $body);
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
            } elseif ($line === '[image]') {
                $flushParagraph();
                $flushList();

                if ($image !== null) {
                    $blocks[] = trim($image);
                    $image = null;
                }
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

        if ($image !== null) {
            $blocks[] = trim($image);
        }

        return implode("\n", $blocks);
    }
}
