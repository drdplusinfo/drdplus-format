<?php
include_once __DIR__ . '/numbered-list.php';

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
    $efConcatenated = preg_replace('~[\n\r]+\s*(f|fa|fě|fou|fu|fovi|lé) ~u', '$1 ', $upsilonsConcatenated);
    $czechLiConcatenated = preg_replace('~([[:alpha:]])\s*-\s*li([^[:alpha:]])~u', '$1-li$2', $efConcatenated);

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

    return preg_replace('~([a-z]*[ěščřžýáíéůúó]|divočiny)>~u', '$1&gt;', $lessThan);
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
    $encloseDiv = function (string $text) {
        if (mb_strlen($text) <= 80) {
            $text = rtrim($text); // remove end of line of very-short text
        }

        return $text . "</div>\n";
    };
    $formatted = '';
    for ($blockTitleIndex = 0, $blockIndex = 1, $blocksCount = count($blocks); $blockIndex < $blocksCount; $blockTitleIndex += 2, $blockIndex += 2) {
        $blockTitle = $blocks[$blockTitleIndex];
        $block = $blocks[$blockIndex];
        $rows = preg_split('~[\r\n]+~', $block, -1, PREG_SPLIT_NO_EMPTY);
        $parts = ["<h5 id=\"$blockTitle\">$blockTitle</h5>"];
        if (count($rows) > 0) {
            $part = "<div>\n";
            $firstRowAfterTitle = true;
            $hasRangerSkillSubHeading = false;
            $inList = false;
            $toList = '';
            $inParagraph = false;
            $paragraphComes = false;
            $subHeadings = 'Podmínky|Tajné mechanismy|Spouštěcí moment|Popis zaměření|Počet stupňů|Stupně znalosti|Předpoklady|BP|Doba trvání';
            foreach ($rows as $row) {
                $row = trim($row);
                if (preg_match('~^\w+(\s+\w+)?:~u', $row)) { // new sub-block
                    if (preg_match('~^(?:' . $subHeadings . '):~u', $row)) {
                        if (strpos($row, 'Stupně znalosti') === 0) {
                            $firstRowAfterTitle = true;
                            $inList = true;
                        } elseif (strpos($row, 'BP') === 0) { // priest only
                            $paragraphComes = true;
                            $row = preg_replace('~^BP:\s*~', '<strong>BP</strong> (<a href="#Body přízně">Body přízně</a>): ', $row);
                        } elseif (strpos($row, 'Doba trvání') === 0) { // priest only
                            $paragraphComes = true;
                            $row = preg_replace('~^(Doba trvání):\s*([-+]?\d+)~u', '<strong>$1</strong>: <a href="https://pph.drdplus.info/#tabulka_casu">$2</a>', $row);
                        } else {
                            $hasRangerSkillSubHeading = true;
                        }
                        $row = preg_replace('~^(' . $subHeadings . '):\s*~u', '<strong>$1</strong>: ', $row);
                    }
                    if ($toList !== '') {
                        $part .= format_numbered_list($toList);
                        $toList = '';
                        $inList = false;
                    }
                    if ($part !== '') { // finishing previous sub-block
                        if ($inParagraph) {
                            $parts[] = rtrim($part) . '</p>';
                            $inParagraph = false;
                        } else {
                            $enclosed = $encloseDiv($part);
                            if ($paragraphComes) {
                                $enclosed = rtrim($enclosed);
                            }
                            $parts[] = $enclosed;
                        }
                        $part = '';
                        $firstRowAfterTitle = true; // de facto a subtitle
                    }
                }
                if ($row !== '') {
                    if ($hasRangerSkillSubHeading) {
                        $part .= '<div class="reversed-paragraph">';
                    } elseif ($firstRowAfterTitle) {
                        if (strpos($row, 'Příklad:') === 0) {
                            $part .= "<div class=\"example\">\n";
                        } elseif ($paragraphComes) {
                            $inParagraph = true;
                            $paragraphComes = false;
                            $part .= '<p>';
                        } else {
                            $part .= "<div>\n";
                        }
                    }
                    if ($inList && !$firstRowAfterTitle /* not the title itself */) {
                        $toList .= $row . "\n";
                    } else {
                        $part .= $row . "\n";
                    }
                    $firstRowAfterTitle = false;
                    $hasRangerSkillSubHeading = false;
                }
            }
            if ($toList !== '') {
                $part .= format_numbered_list($toList);
            }
            if ($part !== '') {
                if ($inParagraph) {
                    $parts[] = rtrim($part) . '</p>';
                } else {
                    $parts[] = $encloseDiv($part);
                } // last one
            }
            $parts[] = "</div>\n";
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