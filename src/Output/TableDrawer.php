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

    public static $encoding = 'UTF-8';
    public static $padding = 1;
    public static $padMode = STR_PAD_BOTH;

    // ╚ ╔ ╟ ╞ ┼ b╞ ┼ ─├ к┬├ ┴└┐╛╜ ╗ ╝ ╩ ╦ ╠ ═ ╬ ╧ ╨ ╤ ╣ ╕╖║ ╣╕╖Q╢╡┤
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

        $lines = array();

        $maxLength = array_key_exists('head', $table) ? mb_strlen($table['head'], self::$encoding) : 0;
        foreach ($table['body'] as $key => $tableStr) {
            if (is_string($tableStr)) {
                foreach (explode($symbols['newLine'], $tableStr) as $str) {
                    $lines[] = $str;
                    if (mb_strlen($str, self::$encoding) > $maxLength) {
                        $maxLength = mb_strlen($str, self::$encoding);
                    }
                }
            } else {
                $lines[] = self::drawTable($tableStr, $symbols);
            }
        }
        $maxLength += self::$padMode == STR_PAD_BOTH ? 2 * self::$padding : self::$padding;

        if (array_key_exists('head', $table)) {
            if (count($lines) == 0) {
                $lines = array( $table['head'] );
            } else {
                array_splice($lines, 0, 0, array(
                    $table['head'],
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