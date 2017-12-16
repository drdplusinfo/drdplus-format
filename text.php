<?php
include_once __DIR__ . '/generic.php';

function format_text(string $text): string
{
    $fixed = fix_content($text);
    $formatted2d6 = format_2k6_plus($fixed);
    $withDivs = add_divs($formatted2d6);
    $withParagraphs = add_paragraphs($withDivs);

    return add_introductions($withParagraphs);
}

function add_introductions(string $content): string
{
    return preg_replace(
        '~(„[[:upper:]].+[.]“\n</\w+>\n)((?:<\w+>\n)?[^„])~us',
        "<div class=\"introduction\">\n\$1</div>\n\$2",
        $content
    );
}