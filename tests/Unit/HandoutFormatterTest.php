<?php

use App\Services\HandoutFormatter;

test('handout text is converted to html', function (string $body, string $html) {
    expect((new HandoutFormatter)->toHtml($body))->toBe($html);
})->with([
    'empty text' => ['', ''],
    'paragraphs and line breaks' => ["One\nTwo\r\n\r\n  \r\nThree", "<p>One<br />\nTwo</p>\n<p>Three</p>"],
    'list' => ["- One\n- Two", "<ul class=\"lcars-list\">\n<li>One</li>\n<li>Two</li>\n</ul>"],
    'list between text' => ["Before\n- One\nAfter", "<p>Before</p>\n<ul class=\"lcars-list\">\n<li>One</li>\n</ul>\n<p>After</p>"],
    'blank line between items' => ["- One\n\n  -  Two ", "<ul class=\"lcars-list\">\n<li>One</li>\n<li>Two</li>\n</ul>"],
    'bar between lists' => ["- One\n----\n- Two", "<ul class=\"lcars-list\">\n<li>One</li>\n</ul>\n<hr class=\"lcars-bar\">\n<ul class=\"lcars-list\">\n<li>Two</li>\n</ul>"],
    'bar after text' => ["Before\n ---- \nAfter", "<p>Before</p>\n<hr class=\"lcars-bar\">\n<p>After</p>"],
    'long bar' => ['--------', '<hr class="lcars-bar">'],
    'three dashes' => ['---', '<p>---</p>'],
    'dash without space' => ['-5 degrees', '<p>-5 degrees</p>'],
    'single dash' => ['-', '<p>-</p>'],
    'en dash' => ['– One', '<p>– One</p>'],
    'multibyte characters' => ["Ångström\n- Å", "<p>Ångström</p>\n<ul class=\"lcars-list\">\n<li>Å</li>\n</ul>"],
    'escaping' => ["<b>One</b>\n- <img src=x>", "<p>&lt;b&gt;One&lt;/b&gt;</p>\n<ul class=\"lcars-list\">\n<li>&lt;img src=x&gt;</li>\n</ul>"],
]);

test('the image is placed in the text', function (string $body, ?string $image, string $html) {
    expect((new HandoutFormatter)->toHtml($body, $image))->toBe($html);
})->with([
    'below the text' => ['One', '<img>', "<p>One</p>\n<img>"],
    'own line' => ["One\n[image]\nTwo", '<img>', "<p>One</p>\n<img>\n<p>Two</p>"],
    'within a line' => ['One [IMAGE] two', '<img>', "<p>One</p>\n<img>\n<p>two</p>"],
    'between list items' => ["- One\n[image]\n- Two", '<img>', "<ul class=\"lcars-list\">\n<li>One</li>\n</ul>\n<img>\n<ul class=\"lcars-list\">\n<li>Two</li>\n</ul>"],
    'only once' => ["[image]\nOne\n[image]", '<img>', "<img>\n<p>One</p>"],
    'tag without image' => ["One\n[image]\nTwo", null, "<p>One</p>\n<p>Two</p>"],
]);
