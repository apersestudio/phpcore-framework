<?php

namespace PC\Singletons;

use Exception;
use SplFileInfo;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use PC\Singletons\Strings;

class Files {

	// Unidades de medida de bytes
	const UNITS = ['B','KB','MB','GB','TB','PB','EB','ZB','YB'];
	const FILESIZE_BIN = 1024;
	const FILESIZE_DEC = 1000; 

	const READ_ONLY = "r";
	const WRITE_ONLY = "w";
	const APPEND_ONLY = "a";
	const READ_AND_WRITE = "r+";
	const FORCE_WRITE = "w+";
	const APPEND = "a+";

	public static function isUploadedFile(string $tempPath):bool {
		if (preg_match("/^".sys_get_temp_dir()."/", $tempPath) !== 1) {
			throw new Exception("The file {$tempPath} was not found in the temp directory.");
		}
		return true;
	}
	
	public static function moveUploadedFile(string $tempPath, string $newpath):bool {
		if (self::isUploadedFile($tempPath) && rename($tempPath, $newpath) !== true) {
			throw new Exception("Could not move file to {$newpath}.");
		}
		return true;
	}
	
	public static function humanSize($bytes, $decimals=2) {
		// Calculamos el numero de veces que podemos dividir los bytes entre la base (factor)
		$factor=0; $n = $bytes;
		while ($n>self::FILESIZE_BIN) { $n /= self::FILESIZE_BIN; $factor++; }
		
		// Calculamos el porcentaje por unidad en base al factor
		$percent = $bytes / pow(self::FILESIZE_BIN, $factor);
		if ($percent == self::FILESIZE_BIN) { $percent = 1; $factor++; }
		
		// formateamos el numero de forma precisa
		$number = Strings::toFixed($percent, $decimals);
		return $number.' '.self::UNITS[$factor];
	}

	public static function formatSize($size) {
		$unit = strtoupper(preg_replace("/[^a-zA-Z]/", "", $size));
		$size = preg_replace("/[^\d\.]/", "", $size);
		$index = array_search($unit, self::UNITS);
		if (is_numeric($size) && $index !== false){
			return $size." ".self::UNITS[$index];
		} else {
			throw new Exception("Can't format file size because the input is bad written.");
		}
	}

	public static function uploadMaxFilesize():int {
		// When reading file from disk the limit we can read is the same as upload_max_filesize
		$maxSize = ini_get('upload_max_filesize');
		if (empty($maxSize)) {
			throw new Exception ("Upload max filesize should be defined");
		}

		$factors = ["k"=>1024, "m"=>1048576, "g"=>1073741824];
		$configRegexp = '/^(?<size>[\d.]+)\s*(?<unit>['.implode("", array_keys($factors)).']?)$/i';

		if (!preg_match($configRegexp, $maxSize, $metric)) {
			throw new Exception ("Could not determinate max file size");
		}
		
		// We use the same round algorithm used by php.ini file for upload_max_filesize
		$size = round($metric["size"], 0, PHP_ROUND_HALF_DOWN);
		$factor = $factors[$metric["unit"]];
		$bytes = $factor * $size;

		// Final calculation return the number of bytes
		return $bytes;
	}

	public static function getMime($file) {
		if (file_exists($file) && extension_loaded("fileinfo")) {
			$handler = finfo_open(FILEINFO_MIME_TYPE);
			$fileinfo = finfo_file($handler, $file);
			finfo_close($handler);
			return $fileinfo;
		}
		return false;
	}

	public static function delete($url) {
		if (file_exists($url) || is_link($url)) { return @unlink($url); }
		return false;
	}
	
	public static function getExtension(string $url) {
		$info = new SplFileInfo($url);
		return $info->getExtension();
	}
	
	public static function uniqName(string $originalName, bool $seo=false):string {
		// Dirección IP desde donde se genera el nombre [12 caracteres]
		$ip = ''; foreach(explode(".", REMOTE_IP) as $octet) { $ip.= str_pad($octet, 3, "0", STR_PAD_LEFT); }

		// Puerto en el que el cliente esta conectado al servidor [5 caracteres]
		$port = str_pad(REMOTE_PORT, 5, "0", STR_PAD_LEFT);

		// Timestamp en formato Unix [10 caracteres]
		list($microseconds, $unixtimestamp) = explode(" ", microtime());
		
		// Microsegundos transcurridos al momento de la ejecución del script [6 caracteres]
		$microseconds = str_pad(substr($microseconds, 2, 6), 6, "0");
		$newname = $ip.$port.$unixtimestamp.$microseconds;
		
		// Extension [4 caracteres]
		$extension = ".".self::getExtension($originalName);
		
		// Seo Name [ 60 caracteres]
		$basename = basename($originalName, $extension);
		$seopart = $seo ? "-".substr(Strings::urify($basename), 0, 60) : "";

		
		// Nombre Final del archivo
		$newname = mb_strtolower($newname.$seopart).$extension;
				
		unset($ip, $port, $unixtimestamp, $microseconds, $seopart, $extension, $basename);
		return $newname;
	}
	
	public static function upload(string $name=false, string $path, bool $seo=true):string {

		// If the file was not received by PHP POST method finish the function
		if (!isset($_FILES[$name])) {
			throw new Exception ("The file {$name} was not found in the posted files");
		}
	
		// Generate a unique name
		$file = $_FILES[$name];
		$uniqName = self::uniqName($file["name"], $seo);
		$finalPath = $path.$uniqName;
		
		// Move the file to the desired path
		self::moveUploadedFile($file["tmp_name"], $finalPath);
		return $finalPath;
	}
	
	public static function download(string $filePath):void {
		$fileName = basename($filePath);
		header("Content-type: application/octet-stream");
		header("Content-Length: ".filesize($filePath));
		header("Content-Disposition: attachment; filename=".$fileName);
		readfile($filePath);
	}
	
	public static function read(string $filePath, string $mode=self::READ_ONLY):string {
		if (is_file($filePath) == false) {
			throw new Exception("The file {$filePath} was not found");
		}
		
		if (filesize($filePath) === 0) {
			return "";
		}

		$fopen = fopen($filePath, $mode);
		$fread = fread($fopen, filesize($filePath));
		fclose($fopen);
		return $fread;
		
	}
	public static function write(string $filePath, string $content, string $mode=self::FORCE_WRITE):int {
		$fopen = fopen($filePath, $mode);
		$fwrite = fwrite($fopen, $content);
		fclose($fopen);
		return $fwrite;
	}
	
	public static function listDirectory(string $dir):array {
		if(is_dir($dir) === false) {
			throw new Exception ("The path {$dir} is not a directory");
		}
	
		$dirIterator = new RecursiveDirectoryIterator($dir);
		$iterator = new RecursiveIteratorIterator($dirIterator);
		$listTree = [];

		foreach ($iterator as $pointer) {
			if ($pointer->isDir()) {
				$listTree[] = [
					"type"=>"directory",
					"name"=>$pointer->getPathname(),
				];
			} else {
				$listTree[] = [
					"type"=>"file",
					"name"=>$pointer->getFilename()
				];
			}
		}

		return $listTree;
	}
	
	public static function listTreeDirectory(string $dir):array {
		if(is_dir($dir) === false) {
			throw new Exception ("The path {$dir} is not a directory");
		}
	
		$dirIterator = new RecursiveDirectoryIterator($dir);
		$iterator = new RecursiveIteratorIterator($dirIterator);
		$listTree = [];

		foreach ($iterator as $pointer) {
			if ($pointer->isDir()) {
				$item = [
					"type"=>"directory",
					"name"=>$pointer->getPathname(),
				];
				$listTree = array_merge($listTree, $item, self::listTreeDirectory($pointer->getPathname()));
			} else {
				$listTree[] = [
					"type"=>"file",
					"name"=>$pointer->getFilename()
				];
			}
		}

		return $listTree;
	}

	public static function copy(string $source, string $destiny, string $permissions="775"):void {
		copy($source, $destiny);
		chmod($destiny, $permissions);
	}
	
	public static function copyDirectory(string $source, string $destiny, string $permissions="775"):bool {

		if (!is_dir($source)) {
			throw new Exception("Could not find the directory {$source}");
		}
	
		if (!is_dir($destiny)) {
			mkdir($destiny, $permissions, true);
		}
	
		$iterator = new RecursiveDirectoryIterator($source);
		foreach ($iterator as $pointer) {
			$name = $pointer->getPathname();
			$destiny = $destiny . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
	
			if ($pointer->isDir() && $name != '.' && $name != '..') {
				mkdir($destiny, $permissions, true);
			} else {
				self::copy($name, $destiny, $permissions);
			}
		}
	
		return true;
	}

	
	public static function delTree(string $source):bool {	
		if (!is_dir($source)) {
			throw new Exception("Could not find the directory {$source}");
		}
		
		$iterator = new RecursiveDirectoryIterator($source);
		foreach ($iterator as $pointer) {
			$name = $pointer->getFilename();
			if ($pointer->isDir() && $name != '.' && $name != '..') {
				self::delTree($pointer->getPathname());
			} else {
				@unlink($name);
			}
		}
	
		return @rmdir($source);
	}

}

?>