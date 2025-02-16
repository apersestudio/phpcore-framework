<?php

namespace PC\Traits {

	trait ValidateTrait {

		public static function validAlpha($alpha){
			return preg_match("/[a-zA-Z]+$/", $alpha);
		}
		public static function validAlphaNumeric($value){
			return preg_match("/[\w]+$/", $value);
		}
		public static function validString($str){
			return is_string($str);
		}
		public static function validArray($arr){
			return is_array($arr);
		}
		public static function validIn($value, $arr){
			return in_array($value, $arr);
		}
		public static function validBool($value, $arr){
			return ($value=="t" || $value=="on" || $value=="yes" || $value=="true" || $value=="enabled" || $value==1 || $value=="1" || $value==true ||
					$value=="f" || $value=="off" || $value=="no" || $value=="false" || $value=="disabled" || $value==0 || $value=="0" || $value==false);
		}
		public static function validNumber($num){
			return is_numeric($num);
		}
		public static function validInteger($int){
			return is_integer($int);
		}
		public static function validFloat($float){
			return is_float($float);
		}
		public static function validMin($num, $min=1){
			return is_numeric($num) && $num >= $min;
		}
		public static function validMax($num, $max=2){
			return is_numeric($num) && $num <= $max;
		}
		public static function validRange($num, $min=1, $max=2){
			return is_numeric($num) && $num >= $min && $num <= $max;
		}
		public static function validMinLength($str, $min=1){
			return strlen($str) >= $min;
		}
		public static function validMaxLength($str, $max=2){
			return strlen($str) <= $max;
		}
		public static function validRangeLength($str, $min=1, $max=2){
			$l = strlen($str);
			return ($l >= $min) && ($l <= $max);
		}
		public static function validHash($str, $length) {
			return preg_match('/^[a-fA-F0-9]{'.$length.'}$/i', $str);
		}
		public static function validMd5($str) {
			return self::validHash($str, 32);
		}
		public static function validSha1($str){
			return self::validHash($str, 40);
		}
		public static function validSha256($str='z'){
			return self::validHash($str, 64);
		}
		public static function validId($id) {
			return is_numeric($id) && floatval($id) > 0;
		}
		public static function validUuid($uuid) {
			return preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', trim($uuid));
		}
		public static function validUlid($ulid) {
			return preg_match('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/', trim($ulid)) === 1;
		}
		public static function validEmail($email) {
			if ($email != "" && filter_var($email, FILTER_VALIDATE_EMAIL)) { return true; }
			return false;
		}
		public static function validIpv4($ip) {
			if ($ip != "" && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) { return true; }
			return false;
		}
		public static function validIpv6($ip) {
			if ($ip != "" && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) { return true; }
			return false;
		}
		public static function validIp($ip) {
			if ($ip != "" && filter_var($ip, FILTER_VALIDATE_IP)) { return true; }
			return false;
		}
		public static function validUrl($url) {
			if ($url != "" && filter_var($url, FILTER_VALIDATE_URL)) { return true; }
			return false;
		}
		public static function validDate($date) {
			return strtotime(($date == "CURRENT_TIMESTAMP") ? "now" : $date) !== false;
		}
		public static function validDateRange($date, $startDate, $endDate) {
			$date = strtotime($date);
			return (($date >= strtotime($startDate)) && ($date <= strtotime($endDate)));
		}
	}

}

?>