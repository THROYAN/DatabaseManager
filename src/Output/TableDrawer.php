<?php

namespace Output;

/**
* Draws tables (ну не совсем)
*/
class TableDrawer
{
    const DEFAULT_SYMBOL_UNDERSCORE = '-';
    const DEFAULT_SYMBOL_UNDERSCORE_LEFT_BORDER = '|';
    const DEFAULT_SYMBOL_UNDERSCORE_RIGHT_BORDER = '|';
    const DEFAULT_SYMBOL_TOP_LEFT_BORDER = '|';
    const DEFAULT_SYMBOL_TOP_RIGHT_BORDER = "|";
    const DEFAULT_SYMBOL_BOTTOM_LEFT_BORDER = "|";
    const DEFAULT_SYMBOL_BOTTOM_RIGHT_BORDER = '|';
    const DEFAULT_SYMBOL_TOP_BORDER = '=';
    const DEFAULT_SYMBOL_BOTTOM_BORDER = '=';
    const DEFAULT_SYMBOL_LEFT_BORDER = '|';
    const DEFAULT_SYMBOL_RIGHT_BORDER = '|';
    const DEFAULT_SYMBOL_PAD = ' ';
    const DEFAULT_SYMBOL_NEW_LINE = "\n";
    const DEFAULT_SYMBOL_COLUMN_SEPARATOR = "|";

    public static $encoding = 'UTF-8';
    public static $padding = 1;
    public static $padMode = STR_PAD_BOTH;

    // ╚ ╔ ╟ ╞ ┼ b╞ ┼ ─├ к┬├ ┴└┐╛╜ ╗ ╝ ╩ ╦ ╠ ═ ╬ ╧ ╨ ╤ ╣ ╕╖║ ╣╕╖Q╢╡┤ ╬╧╨╥╙▄╘╒z╓╫╪┘┌█▄ к┤│
    public static $symbols = array(
        // 'underscore' => '─',
        // 'underscoreLeftBorder' => '├',
        // 'underscoreRightBorder' => '┤',
        // 'topBorder' => '═',
        // 'topLeftBorder' => '╔',
        // 'topRightBorder' => '╗',
        // 'bottomBorder' => '═',
        // 'bottomLeftBorder' => '╚',
        // 'bottomRightBorder' => '╝',
        // 'leftBorder' => '║',
        // 'rightBorder' => '║',
    );

    public static function getSymbols($symbols = array())
    {
        return array_merge(
            array(
                'underscore' => self::DEFAULT_SYMBOL_UNDERSCORE,
                'underscoreLeftBorder' => self::DEFAULT_SYMBOL_UNDERSCORE_LEFT_BORDER,
                'underscoreRightBorder' => self::DEFAULT_SYMBOL_UNDERSCORE_RIGHT_BORDER,
                'topBorder' => self::DEFAULT_SYMBOL_TOP_BORDER,
                'topLeftBorder' => self::DEFAULT_SYMBOL_TOP_LEFT_BORDER,
                'topRightBorder' => self::DEFAULT_SYMBOL_TOP_RIGHT_BORDER,
                'bottomBorder' => self::DEFAULT_SYMBOL_BOTTOM_BORDER,
                'bottomLeftBorder' => self::DEFAULT_SYMBOL_BOTTOM_LEFT_BORDER,
                'bottomRightBorder' => self::DEFAULT_SYMBOL_BOTTOM_RIGHT_BORDER,
                'leftBorder' => self::DEFAULT_SYMBOL_LEFT_BORDER,
                'rightBorder' => self::DEFAULT_SYMBOL_RIGHT_BORDER,
                'newLine' => self::DEFAULT_SYMBOL_NEW_LINE,
                'pad' => self::DEFAULT_SYMBOL_PAD,
                'columnSeparator' => self::DEFAULT_SYMBOL_COLUMN_SEPARATOR,
            ),
            self::$symbols,
            $symbols
        );
    }

    public static function draw($table, $symbols = array())
    {
        $symbols = self::getSymbols($symbols);
        $table = is_array($table) ? $table : explode($symbols['newLine'], $table);

        if (!array_key_exists('body', $table)) {
            $table['body'] = $table;
        }
        if (array_key_exists('body', $table['body'])) {
            $table['body'] = array( $table['body'] ); // для вложенных таблиц
        }

        $maxLengths = array(); // для массивов
        $maxLength = 0;

        $lines = array();
        if (array_key_exists('head', $table)) {
            $lines = array( $table['head'] );
            if (is_array($table['head'])) {
                $i = 0;
                foreach ($table['head'] as $str) {
                    if (!array_key_exists($i, $maxLengths)) {
                        $maxLengths[$i] = 0;
                    }
                    if (mb_strlen($str, self::$encoding) > $maxLengths[$i]) {
                        $maxLengths[$i] = mb_strlen($str, self::$encoding);
                    }
                    $i++;
                }
            } elseif (is_string( $table['head'] )) {
                $maxLength = mb_strlen($table['head'], self::$encoding);
            }
        }


        foreach ($table['body'] as $tableStr) {
            if (is_string($tableStr)) { // если это просто строка, то разбиаем по ентерам, так и будем обрабатывать
                $lines = array_merge($lines, explode($symbols['newLine'], $tableStr));
            } elseif (is_array($tableStr)) { // если это массив, то это строка с колонками
                if (array_key_exists('body', $tableStr)) { // вложенная таблица
                    $lines = array_merge($lines, explode($symbols['newLine'], self::draw($tableStr)));
                    continue;
                }
                // @TODO Сделать вывод по одной таблице (с одним количеством столбиков), и выделять каждую такую бордерами сверху и снизу (вроде несложно)
                $i = 0;
                foreach ($tableStr as $str) {
                    if (!array_key_exists($i, $maxLengths)) {
                        $maxLengths[$i] = 0;
                    }
                    if (mb_strlen($str, self::$encoding) > $maxLengths[$i]) {
                        $maxLengths[$i] = mb_strlen($str, self::$encoding);
                    }
                    $i++;
                }
                $lines[] = $tableStr;
            }
        }

        // Размер строк со столбиками
        $columnsLength = array_sum($maxLengths) + mb_strlen($symbols['columnSeparator']) * (count($maxLengths) - 1);
        if (count($maxLengths) > 0 && $columnsLength > $maxLength) {
            $maxLength = $columnsLength;
        }

        // находим, может что-то длиннее строк со столбиками
        foreach ($lines as $key => $str) {
            if (is_string($str)) {
                if (mb_strlen($str, self::$encoding) > $maxLength) {
                    $maxLength = mb_strlen($str, self::$encoding);
                }
            }
        }
        // если так и есть
        if ($maxLength > $columnsLength) {
            $maxLengths[] = array_pop($maxLengths) + $maxLength - $columnsLength; // делаем последнее поле очень длинным
        }

        foreach ($maxLengths as $key => $value) {
             $maxLengths[$key] += (self::$padMode == STR_PAD_BOTH ? 2 * self::$padding : self::$padding);
        }

        $maxLength += self::$padMode == STR_PAD_BOTH ? 2 * self::$padding : self::$padding;


        // Перебираем строки со столбиками
        foreach ($lines as $key => $str) {
            if (is_array($str)) {
                $i = 0;
                foreach ($str as $_key => $value) {
                    $str[$_key] = CommonFunctions::mbStrPad($value, $maxLengths[$i], $symbols['pad'], self::$padMode, self::$encoding);
                    $i++;
                }
                $lines[$key] = implode($symbols['columnSeparator'], $str);
            }
        }

        if (array_key_exists('head', $table)) {
            if (count($lines) > 1) { // вставляем полоску после заголовка
                array_splice($lines, 1, 0, array(
                    $symbols['underscoreLeftBorder'] . implode("", array_fill(0, $maxLength, $symbols['underscore'])) . $symbols['underscoreRightBorder']
                ));
            }
        }


        // array_splice($lines, 1, 0, $underscore); // after name
        // array_splice($lines, 0, 0, $underscore); // before name
        // $lines[] = $underscore; // last line

        // ╚╔  ╟╞┼b╞┼─├к┬├┴└┐╛╜╗╝╩╦╠═╬╧╨╤ ╣ ╕╖║

        $self = __CLASS__;
        return
            $symbols['topLeftBorder'] . implode("", array_fill(0, $maxLength, $symbols['topBorder'])) . $symbols['topRightBorder'] . $symbols['newLine'] . // верхние границы
            implode($symbols['newLine'], array_map(
                function($str) use ($maxLength, $symbols, $self) {
                    // mb_str_pad
                    $str = CommonFunctions::mbStrPad($str, $maxLength, $symbols['pad'], $self::$padMode, $self::$encoding);
                    return mb_strlen($str, $self::$encoding) <= $maxLength ? $symbols['leftBorder'] . $str . $symbols['rightBorder'] : $str;
                },
            $lines)) . $symbols['newLine'] .
            $symbols['bottomLeftBorder'] . implode("", array_fill(0, $maxLength, $symbols['bottomBorder'])) . $symbols['bottomRightBorder'] // нижние границы
        ;
    }
}