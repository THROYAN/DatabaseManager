<?php

namespace Output;

/**
* Common string functions
*/
class CommonFunctions
{
    // /**
    //  * [strPad description]
    //  * @param  [type] $str     [description]
    //  * @param  [type] $pad_len [description]
    //  * @param  string $pad_str [description]
    //  * @param  [type] $dir     [description]
    //  * @return [type]          [description]
    //  */
    // public static function strPad($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT) {
    //     $str_len = mb_strlen($str);
    //     $pad_str_len = mb_strlen($pad_str);
    //     if (!$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
    //         $str_len = 1; // @debug
    //     }
    //     if (!$pad_len || !$pad_str_len || $pad_len <= $str_len) {
    //         return $str;
    //     }

    //     $result = null;
    //     $repeat = ceil($str_len - $pad_str_len + $pad_len);
    //     if ($dir == STR_PAD_RIGHT) {
    //         $result = $str . str_repeat($pad_str, $repeat);
    //         $result = mb_substr($result, 0, $pad_len);
    //     } else if ($dir == STR_PAD_LEFT) {
    //         $result = str_repeat($pad_str, $repeat) . $str;
    //         $result = mb_substr($result, -$pad_len);
    //     } else if ($dir == STR_PAD_BOTH) {
    //         $length = ($pad_len - $str_len) / 2;
    //         $repeat = ceil($length / $pad_str_len);
    //         $result = mb_substr(str_repeat($pad_str, $repeat), 0, floor($length))
    //                     . $str
    //                        . mb_substr(str_repeat($pad_str, $repeat), 0, ceil($length));
    //     }

    //     return $result;
    // }

    /**
     * @link http://ua2.php.net/manual/ru/function.str-pad.php#89754
     */
    function mbStrPad($input, $pad_length, $pad_string=' ', $pad_type=STR_PAD_RIGHT, $encoding = null) {
        $diff = strlen($input) - mb_strlen($input, $encoding);
        return str_pad($input, $pad_length+$diff, $pad_string, $pad_type);
    }

    /**
     * @link http://stackoverflow.com/questions/4356289/php-random-string-generator
     */
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ -_=!';
        $charsLen = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charsLen - 1)];
        }
        return $randomString;
    }
}