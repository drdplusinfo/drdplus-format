<?php
function thief_properties_highlighted(string $text)
{
    $highlighted = '';
    foreach (preg_split("~[\r\n]+~", $text, -1, PREG_SPLIT_NO_EMPTY) as $row) {
        $row = format_2k6_plus($row);
        $row = add_concentration_link($row);
        $row = format_master_bonus($row);
        preg_match('~^(?<property>[^:]+):(?<description>(?:[^+]*|.+\+\d.*))$~', $row, $matches);
        if ($matches) {
            $row = "<strong>{$matches['property']}</strong>:{$matches['description']}";
            $highlighted .= "<div>{$row}</div>\n";
        } else {
            $highlighted .= "<p>{$row}</p>\n";
        }
    }

    return "<div class='properties'>\n{$highlighted}</div>\n";
}

function format_2k6_plus(string $text): string
{
    return preg_replace('~2k6\+\s+~', '2k6<span class="upper-index">+</span>', $text);
}

function add_concentration_link(string $text): string
{
    return str_replace('plné soustředění', '<a href="https://pph.drdplus.info/#Plné soustředění">plné soustředění</a>', $text);
}

function format_master_bonus(string $text)
{
    return preg_replace('~Bonus Mistra:(\s*)([^-–]+)\s+[-–]~u', 'Bonus mistra:$1<span class="keyword"><a href="#$2">$2</a></span> –', $text);
}