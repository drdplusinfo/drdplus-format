<?php
function to_table(string $text)
{
    $rows = split_to_rows($text);
    $headerRows = [
        ['<th colspan="100%">' . $rows[0] . '</th>'],
        split_to_header_cells($rows[1]),
    ];
    unset($rows[0], $rows[1]);
    $header = join_to_table_rows($headerRows);
    $bodyRows = array_map('split_to_body_cells', $rows);
    $body = join_to_table_rows($bodyRows);

    return htmlspecialchars(<<<HTML
<table class="basic">
    <thead>
        {$header}
    </thead>
    <tbody>
        {$body}
    </tbody>
</table>

HTML
    );
}

function split_to_rows(string $text): array
{
    $text = preg_replace('~[--][\n\r]+\s*~', '', $text);
    $text = preg_replace('~\s*[\n\r]\s*([^[:upper:]])~u', ' $1', $text);

    return preg_split("~[\r\n]+~", $text, -1, PREG_SPLIT_NO_EMPTY);
}

function format_2k6_plus(string $text): string
{
    return preg_replace('~2k6\+\s*~', '2k6<span class="upper-index">+</span>', $text);
}

function split_to_header_cells(string $row)
{
    return split_to_cells($row, 'th');
}

function split_to_body_cells(string $row)
{
    return split_to_cells($row, 'td');
}

function split_to_cells(string $row, string $wrappingTag)
{
    $parts = preg_split('~\s~', $row, -1, PREG_SPLIT_NO_EMPTY);
    $cellContent = [];
    foreach ($parts as $index => $part) {
        if ($cellContent !== []
            && (preg_match('~^([[:upper:]])~u', $part) || (preg_match('~^−?\d~', $part) && ($parts[$index + 1] ?? '') !== 'm'))
        ) {
            $cell = "<$wrappingTag>" . implode(' ', $cellContent) . "</$wrappingTag>";
            $cells[] = $cell;
            $cellContent = [];
        }
        $cellContent[] = $part;
    }
    $cell = "<$wrappingTag>" . implode(' ', $cellContent) . "</$wrappingTag>";
    $cells[] = $cell;

    return $cells;
}

function join_to_table_rows(array $rows)
{
    return implode(
        "\n",
        array_map(
            function (array $cells) {
                return '<tr>' . implode($cells) . '</tr>';
            },
            $rows
        )
    );
}

function join_rows(string $text): string
{
    $joined = '';
    foreach (split_to_rows($text) as $row) {
        if (preg_match('~^[[:upper:]]~u', $row)) {
            $joined .= "\n";
        } else {
            $joined .= ' ';
        }
        $joined .= trim($row);
    }

    return $joined;
}