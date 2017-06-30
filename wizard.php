<?php
function matches_to_cells(array $matches, array $collSpans = [])
{
    unset($matches[0]); // remove whole row match
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

function wizard_spell_to_table(string $spell)
{
    $rows = explode("\n", $spell);
    $heading = array_shift($rows);
    $cells = [];
    foreach ($rows as $index => $row) {
        $row = trim($row);
        if ($row === '') {
            continue;
        }
        $matches = [];
        $parameterName = substr($row, 0, strpos($row, ':'));
        switch ($parameterName) {
            case 'Magenergie':
                preg_match('~^(\w+:)(.+)$~u', $row, $matches);
                $matches[] = ''; // empty cell
                break;
            case 'Náročnost':
                preg_match('~^(\w+:)\s+([+-]?\d+)\s+(.+)$~u', $row, $matches);
                break;
            case '':
                preg_match('~^([+-]?\d+)\s+(.+)$~', $row, $matches);
                $matches[0] = ''; // empty cell to start
                array_unshift($matches, ''); // just something to remove as "full match"
                break;
            case 'Vyvolání':
            case 'Dosah':
                if (preg_match('~^Vyvolání: \+0(?<whiteSpace>\s*)~', $row, $castingMatches)) {
                    $row = 'Vyvolání: +0 (1 kolo)' . $castingMatches['whiteSpace'];
                } else if (preg_match('~^Vyvolání: \+6(?<whiteSpace>\s*)~', $row, $castingMatches)) {
                    $row = 'Vyvolání: +6 (2 kola)' . $castingMatches['whiteSpace'];
                } else if (preg_match('~^Dosah: \+20(?<whiteSpace>\s*)~', $row, $castingMatches)) {
                    $row = 'Dosah: +20 (10 metrů)' . $castingMatches['whiteSpace'];
                } else if (preg_match('~^Dosah: \+?0(?<whiteSpace>\s*)~', $row, $castingMatches)) {
                    $row = 'Dosah: +0 (1 metr)' . $castingMatches['whiteSpace'];
                }
                preg_match('~^(\w+:)(.+)$~u', $row, $matches);
                $matches[] = ''; // empty cell
                break;
            case 'Rozsah':
            case 'Trvání':
            case 'Nepřesnost':
            case 'Doba překladu':
                preg_match('~^([^:]+:)(.+)$~u', $row, $matches);
                $cells[] = matches_to_cells($matches, [1 => 2]);
                unset($matches);
                break;
            default:
                throw new \LogicException('Unexpected spell parameter ' . $parameterName);
        }
        if (!empty($matches)) {
            $cells[] = matches_to_cells($matches);
        }
    }
    $heading = trim($heading);
    $headingId = rtrim($heading, '*!');
    $tableContent = implode(
        "\n",
        array_map(
            function (array $rowWithCells) {
                return '        <tr>' . implode($rowWithCells) . '</tr>';
            },
            $cells
        )
    );

    return htmlspecialchars(<<<HTML
<h4 id="{$headingId}">{$heading}</h4>
<table>
    <tbody>
{$tableContent}
    </tbody>
</table>
HTML
    );
}

function wizard_spells_from_table_of_content_to_table(string $tableOfContent)
{
    $spells = array_filter(
        array_map(
            function (string $row) {
                return trim($row);
            },
            explode("\n", $tableOfContent)
        ),
        function (string $row) {
            return $row !== '';
        }
    );
    $tables = [];
    $table = [];
    foreach ($spells as $row) {
        if (!preg_match('~\d~', $row) && count($table) > 0) {
            $tables[] = $table;
            $table = [];
        }
        $table[] = $row;
    }
    $tables[] = $table;
    $result = '';
    /** @var array $table */
    foreach ($tables as $table) {
        $heading = array_shift($table);
        $cells = [];
        foreach ($table as $index => $spell) {
            $spell = trim($spell);
            if ($spell === '') {
                continue;
            }
            // Bahenní lázeň 4 mg [7] Mat. . . . . . . 57
            preg_match('~^(\D+)\s*(\d+[^.]+)[.\s]+(\d+)~u', $spell, $parts);
            if (!empty($parts)) {
                $parts = array_map(function(string $part) {
                    return trim($part);
                }, $parts);
                $spellId = rtrim($parts[1], '*');
                $parts[1] = "<a href=\"#{$spellId}\">{$parts[1]}</a>";
                $cells[] = matches_to_cells($parts);
            }
        }
        $heading = trim($heading);
        $tableContent = implode(
            "\n",
            array_map(
                function (array $rowWithCells) {
                    return '        <tr>' . implode($rowWithCells) . '</tr>';
                },
                $cells
            )
        );
        $result .= htmlspecialchars(<<<HTML
<table class="spells-list">
    <caption id="{$heading}">{$heading}</caption>
    <tbody>
{$tableContent}
    </tbody>
</table>

HTML
        );
    }

    return $result;
}