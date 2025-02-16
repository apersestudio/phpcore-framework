<?php

namespace PC\Singletons;

use Normalizer;

class Strings {

	CONST PASSWORD_SYMBOLS = ';!#$%&/()=?*.+-';
	CONST PASSWORD_LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
	CONST PASSWORD_UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	CONST PASSWORD_NUMBERS = '0123456789';

	public static function randomPassword(int $length):string {
		$groups = array(self::PASSWORD_SYMBOLS, self::PASSWORD_LOWERCASE, self::PASSWORD_UPPERCASE, self::PASSWORD_NUMBERS);
		$groupLimit = count($groups) - 1;
		
		$password = [];
		for ($i=0; $i<$length; $i++) {
			$groupIndex = rand(0, $groupLimit);
			$indexLimit = strlen($groups[$groupIndex]) - 1;
			$charIndex = rand(0, $indexLimit);
			$char = substr($groups[$groupIndex], $charIndex, 1);
			$password[] = $char;
		}
		return implode("", $password);
	}
	
	public static function normalize(string $text):string {
		// Separates combined characters to its individual versions
		$text = Normalizer::normalize($text, Normalizer::FORM_D);
		// Remove non-spaceable mark characters
  		return trim(preg_replace('/[\p{Mn}]/u', '', $text));
	}

	public static function deleteAlpha(string $text):string {
		return preg_replace("/[^a-zA-Zs]/", "", $text);
	}

	public static function deleteNumbers(string $text):string {
		return preg_replace("/[^0-9]/", "", $text);
	}

	public static function minimalName(string $name):string {
		$output = [];
		foreach(explode(" ", $name) as $counter => $word) {
			$output[] = ($counter == 0) ? $word." " : substr($word, 0, 1).".";
		}
		return trim(implode("", $output));
	}
	
	public static function singleline(string $text):string {
		$linesAndSpaces = array('/\t+/','/\r+/','/\n+/','/\s+/');
		return trim(preg_replace($linesAndSpaces, ' ', $text));
	}

	public static function shortText(string $text, int $limit, string $etc='...'):string {
	
		// As the text is shorter than the limit we just return the string
		if (strlen($text) < $limit) { return $text; }

		// If we found an only word, we can chop the word to the limit
		if (strpos($text, " ") == false) { return substr($text, 0, $limit).$etc; }
		
		// Remove tabs, break lines, new lines, and multiple spaces
		$text = self::singleline($text);

		// By default we try to chop the string to the limit
		$short = substr($text, 0, $limit-1);

		// But if the limit is not a space it means it is chopping a word
		// So we search the last space to chop there
		if (substr($text, $limit-1, 1) !== " ") {
			$short = substr($text, 0, strrpos($short, " "));
		}

		return $short.$etc;
	}

	public static function urify(string $text, int $limit=0):string {
		$text = preg_replace('/[[:punct:]]/', ' ', self::normalize($text));
		$text = mb_strtolower(preg_replace("/\s+/", "-", self::singleline($text)));
		return ($limit==0) ? $text : substr($text, 0, $limit);
	}

	public static function camelcase(string $text):string {
		return ucwords(mb_strtolower($text));
	}

	public static function underscore(string $text):string {
		return preg_replace("/\s+/", "_", trim(self::normalize(mb_strtolower($text))));
	}

	public static function hyphenate(string $text):string {
		return preg_replace("/\s+/", "-", trim($text));
	}

	public static function entitle(string $text):string {
		return ucfirst(mb_strtolower($text));
	}

	public static function monefy(string $symbol, string $countryCode, float $quantity, $decimals=0):string {
		return $symbol." ".number_format($quantity, $decimals, ".", ",")." ".$countryCode;
	}

	public static function toFixed(mixed $number, int $decimals=2):string {
		$number.= (stripos(".", $number) === FALSE) ? ".0" : "";
		$explode = explode(".", $number);
		return $explode[0].".".str_pad(substr($explode[1], 0, $decimals), $decimals, 0);
	}

	public static function words(string $text, int $count):string {
		// If there is only one word (no spaces)
		if (strpos($text," ") === false) { return $text; }

		// Only return the amount of words requested
		return implode(" ", str_split(" ", $count));
	}
	
}

?>