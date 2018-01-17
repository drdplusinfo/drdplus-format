<?php
require_once __DIR__ . '/generic.php';

function to_dm_table(string $text): string
{
    $text = unify_dash($text);
    $rows = split_to_rows($text);
    $headerRows = [['<th colspan="100%" id="' . $rows[0] . '">' . $rows[0] . '</th>']];
    unset($rows[0]);
    if (!preg_match('~\d~', $rows[1])) {
        $headerRows[] = split_to_header_cells($rows[1]);
        unset($rows[1]);
    }
    $header = join_to_table_rows($headerRows);
    $bodyRows = array_map('split_to_body_cells', $rows);
    $body = join_to_table_rows($bodyRows);
    $rowsStartWithNumber = true;
    foreach ($rows as $row) {
        if (!preg_match('~^-?\s*\d+\D~', $row)) {
            $rowsStartWithNumber = false;
            break;
        }
    }
    $classes = 'basic';
    if ($rowsStartWithNumber) {
        $classes .= ' first-column-centered second-column-to-left';
    }

    return htmlspecialchars(<<<HTML
<table class="$classes">
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
