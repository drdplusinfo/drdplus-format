<?php
include_once __DIR__ . '/generic.php';

function format_creature(string $creature): string
{
    $creature = fix_content($creature);
    $creature = format_2k6_plus($creature);
    preg_match('~^(?<title>[\w –]+)[\n\r]+(?<parameters>.+)(?<description>Popis:.+)$~us', $creature, $matches);

    return "<h3 id=\"{$matches['title']}\">{$matches['title']}</h3>\n\n"
        . '<img src="images/123.png" class="float-right">' . "\n\n"
        . creature_to_table($matches['parameters']) . "\n"
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
        $cells[] = $parameterName . ($parameterName !== '' ? ':' : '');
        $cells[] = $parameterName !== '' ? substr($rawRow, strpos($rawRow, ':') + 1) : $rawRow;
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
    $blocks = preg_split('~(Popis|Výskyt|Chování|Setkání(?:\s+I+)?|Boj|Zvláštní vlastnosti):~u', $description, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $formattedDescription = '';
    for ($blockTitleIndex = 0, $blockIndex = 1, $blocksCount = count($blocks); $blockIndex < $blocksCount; $blockTitleIndex += 2, $blockIndex += 2) {
        $blockTitle = $blocks[$blockTitleIndex];
        $block = $blocks[$blockIndex];
        $rows = preg_split('~[\r\n]+~', $block, -1, PREG_SPLIT_NO_EMPTY);
        $parts = ["<h5 id=\"$blockTitle $mainTitle\">$blockTitle</h5>"];
        $part = '';
        $firstRowAfterTitle = true;
        foreach ($rows as $row) {
            if (preg_match('~^\w+(\s+\w+)?:~u', $row)) { // new sub-block
                if ($part !== '') { // finishing previous sub-block
                    $parts[] = $part . "</div>\n";
                    $part = '';
                }
            }
            $row = trim($row);
            if ($row !== '') {
                if ($firstRowAfterTitle) {
                    if (strpos($blockTitle, 'Setkání') === 0) {
                        $part .= '<div class="introduction">' . "\n";
                    } else {
                        $part .= "<div>\n";
                        if ($blockTitle === 'Zvláštní vlastnosti') {
                            $row = preg_replace('~^([^:]+)(.*)$~', '<p><span class="keyword" id="$1 ' . $mainTitle . '">$1</span>$2</p>', $row);
                        }
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
            $part = add_paragraphs($part);
        }
        unset($part);

        $formattedDescription .= "\n" . implode("\n", $parts) . "\n";
    }

    return $formattedDescription;
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
