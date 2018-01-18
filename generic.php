<?php
function to_table(string $text): string
{
    $text = unify_dash($text);
    $rows = split_to_rows($text);
    $isResultTable = true;
    foreach ($rows as $row) {
        if (substr_count($row, '~') !== 1) {
            $isResultTable = false;
            break;
        }
    }
    if (!$isResultTable) {
        $headerRows = [['<th colspan="100%">' . $rows[0] . '</th>']];
        unset($rows[0]);
        if (!preg_match('~\d~', $rows[1])) {
            $headerRows[] = split_to_header_cells($rows[1]);
            unset($rows[1]);
        }
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
    $bodyRows = array_map('split_to_body_cells', $rows);
    $body = join_to_table_rows($bodyRows);

    return htmlspecialchars(<<<HTML
<table class="result">
    <tbody>
        {$body}
    </tbody>
</table>

HTML
    );

}

function unify_dash(string $text): string
{
    return str_replace(['−', '­', '–'], '-', $text);
}

function split_to_rows(string $text): array
{
    $text = \trim($text);
    $text = preg_replace('~-[\n\r]+((?![\n\r])\s)*([[:lower:]])~u', '$1', $text);

//    $text = preg_replace('~\s*[\n\r]+\s*([^[:upper:]])~u', ' $1', $text);

    return preg_split("~[\r\n]+~", $text, -1, PREG_SPLIT_NO_EMPTY);
}

function format_2d6_plus(string $text): string
{
    return preg_replace('~2k6\+\s*~', '2k6<span class="upper-index">+</span>', $text);
}

function split_to_header_cells(string $row): array
{
    return split_to_cells($row, 'th');
}

function split_to_body_cells(string $row): array
{
    return split_to_cells($row, 'td');
}

function split_to_cells(string $row, string $wrappingTag): array
{
    $parts = preg_split('~\s~', $row, -1, PREG_SPLIT_NO_EMPTY);
    $cellContent = [];
    $previousPart = '';
    foreach ($parts as $index => $part) {
        if ($cellContent !== []
            && ($part === '~' || $previousPart === '~'
                || preg_match('~^([[:upper:]])~u', $part)
                || (preg_match('~^(-|\+|\d)~', $part) && ($parts[$index + 1] ?? '') !== 'm')
                || (preg_match('~^(-|\+|\d)~', $previousPart) && ($parts[$index + 1] ?? '') !== 'm')
            )
        ) {
            $cell = "<$wrappingTag>" . implode(' ', $cellContent) . "</$wrappingTag>";
            $cells[] = $cell;
            $cellContent = [];
        }
        $cellContent[] = $part;
        $previousPart = $part;
    }
    $cell = "<$wrappingTag>" . implode(' ', $cellContent) . "</$wrappingTag>";
    $cells[] = $cell;

    return $cells;
}

function join_to_table_rows(array $rows): string
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

function fix_content(string $content)
{
    return fix_title(fix_rows(unify_dash(unify_space(unify_new_lines($content)))));
}

function unify_space(string $content): string
{
    return str_replace('	' /* ord 9 */, ' ' /* space */, $content);
}

function unify_new_lines(string $content): string
{
    return str_replace("\r\n", "\n", $content);
}

function fix_rows(string $content): string
{
    $delimitedRowsConcatenated = preg_replace('~-[\n\r]+\s*~', '', $content);
    $upsilonsConcatenated = preg_replace('~[\n\r]+\s*(y|ý)~u', '$1', $delimitedRowsConcatenated);
    $efConcatenated = preg_replace('~[\n\r]+\s*(f|fa|fě|fou|fu|fovi) ~u', '$1 ', $upsilonsConcatenated);
    $czechLiConcatenated = preg_replace('~([[:alpha:]])\s*-\s*li([^[:alpha:]])~', '$1-li$2', $efConcatenated);

    return preg_replace('~=\s+=~', '=', $czechLiConcatenated);
}

function fix_title(string $content): string
{
    // D raci = Draci
    return preg_replace('~^([[:upper:]]) ([[:lower:]])~u', '$1$2', $content);
}

function encode_bracket_to_html(string $content): string
{
    $lessThan = preg_replace('~<([a-z]*[ěščřžýáíéůúó])~u', '&lt;$1', $content);

    return preg_replace('~([a-z]*[ěščřžýáíéůúó])>~u', '$1&gt;', $lessThan);
}

function add_divs_and_headings(string $content): string
{
    $blocks = preg_split(
        '~(?:^|\n+)((?:[[:upper:]]{1,2}\.\s*)?[[:upper:]][[:lower:]]+(?:\s+)?(?:\s+(?:–\s+)?(?:[[:upper:]]?[[:lower:]]+|[[:upper:]]{2,}))*[?]?)[\r\n]+~u',
        $content,
        -1,
        PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
    );
    if (count($blocks) < 2) {
        return $content;
    }
    $formatted = '';
    for ($blockTitleIndex = 0, $blockIndex = 1, $blocksCount = count($blocks); $blockIndex < $blocksCount; $blockTitleIndex += 2, $blockIndex += 2) {
        $blockTitle = $blocks[$blockTitleIndex];
        $block = $blocks[$blockIndex];
        $rows = preg_split('~[\r\n]+~', $block, -1, PREG_SPLIT_NO_EMPTY);
        $parts = ["<h3 id=\"$blockTitle\">$blockTitle</h3>"];
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
                    $part .= "<div>\n";
                }
                $part .= $row . "\n";
                $firstRowAfterTitle = false;
            }
        }
        if ($part !== '') {
            $parts[] = $part . "</div>\n"; // last one
        }
        $formatted .= implode("\n", $parts) . "\n";
    }

    return str_replace('</div>
<h3 ', '</div>

<h3', $formatted);
}

function add_paragraphs(string $content): string
{
    $rows = explode("\n", $content);
    $previousIsEndOfSentence = false;
    $paragraph = '';
    $rowsWithParagraphs = [];
    foreach ($rows as $row) {
        if ($row === '') {
            $rowsWithParagraphs[] = ''; // will be turned back to new line
            continue;
        }
        if ($paragraph !== '' && preg_match('~^</\w+>~u', $row)) { // HTML tag
            $rowsWithParagraphs[] = trim($paragraph) . "\n</p>"; // end of paragraph;
            $paragraph = '';
        }
        if ($previousIsEndOfSentence && preg_match('~^[[:upper:]„]~u', $row)) {
            if ($paragraph !== '') {
                $rowsWithParagraphs[] = trim($paragraph) . "\n</p>"; // end of paragraph;
            }
            $paragraph = "<p>\n" . $row . "\n"; // start of paragraph
        } elseif ($paragraph !== '') { // continue of paragraph
            $paragraph .= $row . "\n";
        } else {
            $rowsWithParagraphs[] = $row; // out of paragraph
        }
        $previousIsEndOfSentence = (bool)preg_match('~[.!“)?…]\s*$~u', $row);
    }
    if ($paragraph !== '') {
        $rowsWithParagraphs[] = trim($paragraph) . "\n</p>"; // end of paragraph;
    }

    return trim(implode("\n", $rowsWithParagraphs));
}