<?php
include_once __DIR__ . '/generic.php';

function format_text_with_headings(string $text): string
{
    $fixed = fix_content($text);
    $encodedBrackets = encode_brackets($fixed);
    $formatted2d6 = format_2d6_plus($encodedBrackets);
    $encodedBrackets = encode_bracket_to_html($formatted2d6);
    $withDivsAndHeadings = add_divs_and_headings($encodedBrackets);
    $withParagraphs = add_paragraphs($withDivsAndHeadings);

    return add_introductions($withParagraphs);
}

function format_text_without_headings(string $text): string
{
    $fixed = fix_content($text);
    $encodedBrackets = encode_brackets($fixed);
    $formatted2d6 = format_2d6_plus($encodedBrackets);
    $encodedBrackets = encode_bracket_to_html($formatted2d6);
    $withParagraphs = add_paragraphs($encodedBrackets);

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