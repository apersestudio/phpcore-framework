<?php

namespace PC\Traits;

trait StringTrait {

    /**
     * Formats the first character of a string to uppercase in multibyte mode
     * @param string $str 
     * @return string 
     */
    public static function str_ucfirst(string $str):string {
        return mb_convert_case(mb_substr($str, 0, 1), MB_CASE_TITLE) . mb_substr($str, 1, -1);
    }

    /**
     * Formats a string to title case in multibyte mode
     * @param string $str 
     * @return string 
     */
    public static function str_ucwords(string $str) {
        return mb_convert_case($str, MB_CASE_TITLE);
    }

    /**
     * Converts a string to lowercase in multibyte mode
     * @param string $str 
     * @return string 
     */
    public static function str_lower(string $str) {
        return mb_convert_case($str, MB_CASE_LOWER);
    }

    /**
     * Converts a string to uppercase in multibyte mode
     * @param string $str 
     * @return string 
     */
    public static function str_upper(string $str) {
        return mb_convert_case($str, MB_CASE_UPPER);
    }

    /**
     * Generates a random password with the given length
     * @param mixed $length 
     * @return string 
     */
    public static function str_random_pass($length) {
        $chars = array(
            "*-+.#%&=?/",
            "abcdefghijklmnopqrstuvwxyz",
            "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
            "0123456789"
        );
        
        $groupLimit = count($chars) - 1;
        $password = '';
        for ($i=0; $i<$length; $i++) {
            $group = rand(0, $groupLimit);
            $limit = strlen($chars[$group]) - 1;
            $index = rand(0, $limit);
            $char = substr($chars[$group], $index, 1);
            $password .= $char;
        }
        return $password;
    }
    
    /**
     * Normalizes a string by removing all types of accents
     * @param mixed $s 
     * @return string 
     */
    public static function str_normalize(string $str) {
        // Convert to HTML to remove accents with regexp
        $str = htmlentities($str, ENT_QUOTES);
        // Remove accents
        $str = preg_replace('~&([a-z]{1,2})(acute|caron|cedilla|macron|stroke|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $str);
        // Revert back from html
        $str = html_entity_decode($str, ENT_QUOTES);
        return trim($str);
    }

    /**
     * Removes punctation symbols from a string
     * @param string $str 
     * @return string 
     */
    public static function str_remove_punc(string $str):string {
        return preg_replace("/[[:punct:]]/", "", $str);
    }

    /**
     * Removes alphanumeric characters from a string
     * @param string $str 
     * @return string|string[]|null 
     */
    public static function str_remove_alpha(string $str) {
        return preg_replace("/[\w]/i", "", $str);
    }

    /**
     * Removes every character which is not an alphanumeric characters from a string
     * @param string $str 
     * @return string|string[]|null 
     */
    public static function str_only_alpha(string $str) {
        return preg_replace("/[^\w]/i", "", $str);
    }

    /**
     * Removes numbers from a string
     * @param string $str 
     * @return string|string[]|null 
     */
    public static function str_remove_numbers(string $str) {
        return preg_replace("/[\d]/", "", $str);
    }

    /**
     * Removes every character which is not a number from a string
     * @param string $str 
     * @return string|string[]|null 
     */
    public static function str_only_numbers(string $str) {
        return preg_replace("/[^\d]/", "", $str);
    }

    /**
     * Formats a person name by leaving only the first name and the first letter of its second names
     * @param string $name 
     * @return string 
     */
    public static function str_short_name(string $name):string {
        $str = "";
        foreach(explode(" ", $name) as $counter=>$word) {
            $str.= ($counter == 0) ? $word : " ". mb_substr($word, 0, 1).".";
        }
        return $str;
    }

    /**
     * Removes spaces, tabs and breaklines from the string
     * @param string $str 
     * @return string 
     */
    public static function str_supertrim(string $str):string {
        return trim(preg_replace(array('/\s+/','/\t+/','/\r+/','/\n+/'), ' ', $str));
    }
    
    /**
     * Removes html code, spaces, tabs and breaklines to leave a single line of text
     * @param string $str 
     * @return string 
     */
    public static function str_singleline(string $str):string {
        $str = self::str_supertrim($str);
        return strip_tags($str);
    }

    /**
     * Extract whole words from a string up to the limit string length
     * @param string $str 
     * @param int $limit 
     * @return string 
     */
    public static function str_short(string $str, int $limit):string {

        $limitZeroIndex = $limit - 1;
        $length = strlen($str);
        $minimum = 2;
    
        // The string is too short
        if ($length <= $minimum || $length <= $limit) { return $str; }

        // There's no spaces so probably we only have a big word
        if (strpos($str, " ") === false) { return substr($str, 0, $limit); }
        
        $str = self::str_singleline($str);
        $str = substr($str, 0, $limitZeroIndex);
        $last = substr($str, $limitZeroIndex-1, 1);
    
        // Limit points to a white space so is safe to cut there
        if ($last == " ") { return $str; }

        // Limit points to a character so search for last space and cut there
        if ($last != " ") { return substr($str, 0, strrpos($str, " ")); }
    }

    /**
     * Removes spaces, punctation symbols, normalize the string and change its spaces for the given symbol or hyphens
     * @param string $str
     * @param string $symbol 
     * @param int $limit 
     * @return string 
     */
    public static function str_urify(string $str, string $symbol="-"):string {
        $str = self::str_singleline($str);
        $str = self::str_normalize($str);
        $str = self::str_only_alpha($str);
        return mb_strtolower(preg_replace("/\s+/", $symbol, $str));
    }

    /**
     * Converts a string into a upper camel case
     * @param string $str 
     * @return string|string[]|null 
     */
    public static function str_upper_camelcase(string $str) {
        $str = self::str_ucwords($str);
        return preg_replace("/\s+/", "", $str);

    }

    /**
     * Converts a string into a lower camel case
     * @param string $str 
     * @return string|string[]|null 
     */
    public static function str_lower_camelcase(string $str) {
        $str = self::str_ucwords($str);
        $str = self::str_ucfirst($str);
        return preg_replace("/\s+/", "", $str);

    }

    /**
     * Normalizes a string and change its spaces for underscores
     * @param string $str 
     * @return string|string[]|null 
     */
    public static function str_underscore(string $str) {
        $str = trim(self::str_normalize($str));
        return preg_replace("/\s+/", "_", $str);
    }

    /**
     * Removes extra spaces, change single spaces for hyphens and convert to uppercase the first letter or each word
     * @example heLLo    worLD  öö -> Hello-World-Öo
     * @param string $str 
     * @return string 
     */
    public static function str_hyphenate(string $str) {
        $str = trim(self::str_ucwords($str));
        return preg_replace("/\s+/", "-", $str);
    }

    /**
     * Convert to lowercase the whole string and apply uppercase to the first letter
     * @param string $str
     * @return string 
     */
    public static function str_entitle(string $str):string {
        $str = self::str_lower($str);
        return self::str_ucfirst($str);
    }

    /**
     * Gives a number a currency format and prepend the currency code at the begining
     * @param string $code 
     * @param mixed $number 
     * @param int $decimals 
     * @param string $decSeparator 
     * @param string $thousandSeparator 
     * @return string 
     */
    public static function str_monefy(mixed $number, string $code, int $decimals=2, string $decSeparator=".", string $thousandSeparator=","):string {
        $formatted_number = number_format(floatval($number), $decimals, $decSeparator, $thousandSeparator);
        return trim($code)." ".$formatted_number;
    }

    /**
     * Removes up to a given number of decimals from a number without rounding
     * @param mixed $number
     * @param int $decimals
     * @return string 
     */
    public static function str_to_fixed(mixed $number, int $decimals=2):string {
        $number = strval($number);
        if (stripos(".", $number) === false) {
            return $number.".".str_repeat("0", $decimals);
        } else {
            $explode = explode(".", $number);
            $intPart = $explode[0];
            $decPart = substr($explode[1], 0, $decimals);
            return $intPart.".".str_pad($decPart, $decimals, "0", STR_PAD_RIGHT);
        }
    }
}

?>