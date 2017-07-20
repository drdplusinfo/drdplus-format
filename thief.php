<?php
function thief_properties_highlighted(string $text)
{
    $highlighted = '';
    $text = join_rows($text);
    foreach (split_to_rows($text) as $row) {
        $row = format_2k6_plus($row);
        $row = add_duration_link($row);
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

    return "<div class=\"parameters\">\n{$highlighted}</div>\n";
}

function split_to_rows(string $text): array
{
    return preg_split("~[\r\n]+~", $text, -1, PREG_SPLIT_NO_EMPTY);
}

function format_2k6_plus(string $text): string
{
    return preg_replace('~2k6\+\s*~', '2k6<span class="upper-index">+</span>', $text);
}

function add_duration_link(string $text): string
{
    return preg_replace('~(Trvání:) ((?:(?!(?:trvalý bonus|speciální|[-–])).)+)~', '$1 <a href="https://pph.drdplus.info/#tabulka_casu">$2</a>', $text);
}

function add_concentration_link(string $text): string
{
    return preg_replace_callback('~(plné soustředění|volné soustředění|automatická činnost)~', function (array $matches) {
        return '<a href="https://pph.drdplus.info/#' . ucfirst($matches[1]) . '">' . $matches[1] . '</a>';
    }, $text);
}

function format_master_bonus(string $text)
{
    return preg_replace('~Bonus Mistra:(\s*)([^-–]+)\s+[-–]~u', 'Bonus mistra:$1<span class="keyword"><a href="#$2">$2</a></span> –', $text);
}

function join_rows(string $text): string
{
    $joined = '';
    foreach (split_to_rows($text) as $row) {
        if (preg_match('~^([[:upper:]]|méně)~u', $row)) {
            $joined .= "\n";
        } else {
            $joined .= ' ';
        }
        $joined .= trim($row);
    }

    return $joined;
}

function format_extended_roll_on_success(string $text)
{
    $formatted = ['<div class="calculation">'];
    $rows = split_to_rows($text);
    $formula = array_shift($rows);
    $formatted[] = '<span class="formula">' . format_2k6_plus(str_replace(' :', ':', $formula)) . '</span>';
    $formatted[] = '<table class="result">';
    foreach ($rows as $row) {
        $cells = preg_split('/\s*~\s*/', $row, -1, PREG_SPLIT_NO_EMPTY);
        $formattedCells = array_map(function (string $cell) {
            return "<td>{$cell}</td>";
        }, $cells);
        $formattedRow = implode('<td>~</td>', $formattedCells);
        $formatted[] = "<tr>{$formattedRow}</tr>";
    }
    $formatted[] = '</table>';
    $formatted[] = '</div>';

    return implode("\n", $formatted);
}

function combat_parameters_to_table(string $combatParameters)
{
    $rows = split_to_rows($combatParameters);
    $headerRows = [];
    foreach ($rows as $index => $row) {
        if (preg_match('~\d~', $row)) { // only body contains numbers
            break;
        }
        $headerRows[] = $row;
        unset($rows[$index]);
    }
    $headerRows[0] = $headerRows[0] . ' ' . $headerRows[1]; // skill name begins with uppercase
    unset($headerRows[1]);
    $headerRows = array_map('split_to_header_cells', $headerRows);
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
    foreach ($parts as $part) {
        if ($cellContent !== [] && preg_match('~^([^[:lower:]]|[[:upper:]])~u', $part)) {
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