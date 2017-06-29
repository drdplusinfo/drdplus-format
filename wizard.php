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
        preg_match('~^(?<word>\w*)~u', $row, $wordMatch);
        switch ($wordMatch['word']) {
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
                preg_match('~^(\w+:)(.+)$~u', $row, $matches);
                $cells[] = matches_to_cells($matches, [1 => 2]);
                unset($matches);
                break;
            default:
                throw new \LogicException();
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