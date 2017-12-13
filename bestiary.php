<?php
include_once __DIR__ . '/generic.php';

function format_creature(string $creature): string
{
    $creature = fix_content($creature);
    $creature = format_2k6_plus($creature);
    $creature = preg_replace('~(\s)\s+~', '$1', $creature);
    preg_match('~^(?<title>[\w ]+)[\n\r]+(?<parameters>.+)(?<description>Popis:.+)$~us', $creature, $matches);

    return "<h3 id='{$matches['title']}'>{$matches['title']}</h3>\n\n"
        . '<img src="images/123.png" class="float-right">' . "\n\n"
        . creature_to_table($matches['parameters']) . "\n\n"
        . creature_description($matches['description'], $matches['title']);
}

function creature_to_table(string $creature): string
{
    $creature = preg_replace('~((?:Vlastnosti|Smysly):[^\r\n]+)[\r\n]+(.+)~', '$1 $2', $creature);
    $rawRows = preg_split('~[\r\n]+~', $creature);
    $rows = [];
    foreach ($rawRows as $rawRow) {
        $rawRow = trim($rawRow);
        if ($rawRow === '') {
            continue;
        }
        $parameterName = substr($rawRow, 0, strpos($rawRow, ':'));
        $cells = [];
        $cells[] = $parameterName . ':';
        $cells[] = substr($rawRow, strpos($rawRow, ':') + 1);
        $rows[] = matches_to_cells($cells);
    }
    $tableContent = implode(
        "\n",
        array_map(
            function (array $rowWithCells) {
                return '        <tr>' . implode($rowWithCells) . '</tr>';
            },
            $rows
        )
    );

    return htmlspecialchars(<<<HTML
<table>
    <tbody>
{$tableContent}
    </tbody>
</table>
HTML
    );
}

function creature_description(string $description, string $mainTitle): string
{
    $description = preg_replace('~:\s*[\r\n]+~', ': ', $description); // sometimes are titles on new lines, we want them single-lined
    $rows = preg_split('~[\r\n]+~', $description);
    $parts = [];
    $part = '';
    $firstRowAfterTitle = false;
    $descriptionTitle = '';
    foreach ($rows as $row) {
        if (strpos($row, ':') > 0) { // new block
            if ($part !== '') { // finishing previous block
                $parts[] = $part . "</div>\n";
                $part = '';
            }
            $descriptionTitle = substr($row, 0, strpos($row, ':'));
            $part .= "<h5 id='$descriptionTitle $mainTitle'>$descriptionTitle</h5>\n";
            $row = substr($row, strpos($row, ':') + 1);
            $firstRowAfterTitle = true;
        }
        $row = trim($row);
        if ($row !== '') {
            if ($firstRowAfterTitle) {
                if (strpos($descriptionTitle, 'Setkání') === 0) {
                    $part .= '<div class="introduction">' . "\n";
                } else {
                    $part .= "<div>\n";
                }
            }
            $part .= $row . "\n";
            $firstRowAfterTitle = false;
        }
    }
    if ($part !== '') {
        $parts[] = $part . "</div>\n"; // last one
    }
    foreach ($parts as &$part) {
        $part = detect_paragraphs($part) . "\n";
    }

    return implode("\n", $parts);
}

function detect_paragraphs(string $content): string
{
    $rows = explode("\n", $content);
    $previousEndsByDot = false;
    $paragraph = '';
    $rowsWithParagraphs = [];
    foreach ($rows as $row) {
        if ($row === '') {
            continue;
        }
        if ($paragraph !== '' && preg_match('~^</\w+>~u', $row)) { // HTML tag
            $rowsWithParagraphs[] = $paragraph . "\n</p>"; // end of paragraph;
            $paragraph = '';
        }
        if ($previousEndsByDot && preg_match('~^[[:upper:]]~u', $row)) {
            if ($paragraph !== '') {
                $rowsWithParagraphs[] = $paragraph . "\n</p>"; // end of paragraph;
            }
            $paragraph = "<p>\n" . $row; // start of paragraph
        } elseif ($paragraph !== '') { // continue of paragraph
            $paragraph .= $row . "\n";
        } else {
            $rowsWithParagraphs[] = $row; // out of paragraph
        }
        $previousEndsByDot = (bool)preg_match('~[.]$~', $row);
    }
    if ($paragraph !== '') {
        $rowsWithParagraphs[] = $paragraph . "\n</p>"; // end of paragraph;
    }

    return implode("\n", $rowsWithParagraphs);
}

function matches_to_cells(array $matches, array $collSpans = [])
{
    $matches = array_values($matches); // re-index from zero
    $cells = [];
    foreach ($matches as $index => $match) {
        $collSpan = 1;
        if (array_key_exists($index, $collSpans)) {
            $collSpan = $collSpans[$index];
        }
        $cells[] = ($collSpan > 1 ? "<td colspan=\"$collSpan\">" : '<td>') . trim($match) . '</td>';
    }

    return $cells;
}
