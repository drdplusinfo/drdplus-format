<?php
include_once __DIR__ . '/generic.php';

function format_text_with_numbered_list(string $text): string
{
    $fixed = fix_content($text);
    $formatted2d6 = format_2d6_plus($fixed);
    $encodedBrackets = encode_bracket_to_html($formatted2d6);

    return format_numbered_list($encodedBrackets);
}

function format_numbered_list(string $content): string
{
    $content = trim($content);
    if (strpos($content, '1.') !== 0) {
        return $content;
    }
    $start = 1;
    $items = [];
    while ($content !== '') {
        $end = strpos($content, ($start + 1) . '.') ?: strlen($content);
        $item = substr($content, 0, $end);
        $items[] = preg_replace('~^\d[.]\s*~', '', $item);
        $content = (string)substr($content, $end);
        $start++;
    }

    return "<ul>\n"
        . implode(
            "\n",
            array_map(function (string $item) {
                return "<li>$item</li>";
            },
                $items)
        ) . "\n</ul>";
}