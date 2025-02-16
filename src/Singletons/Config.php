<?php

namespace PC\Singletons;

use PC\Core;

use Exception;

class Config {

    private static $data = [];

    public static function load(string $file):void {
        
        $configKey = strtolower($file);
        if (!isset(self::$data[$configKey])) {
            $fileName = ucfirst($configKey);
            $dbConfigFile = Core::DIR_APP()."/Configs/{$fileName}.php";
            if (file_exists($dbConfigFile) === false) {
                $baseMessage = "Missing configuration file";
                error_log($baseMessage.": {$dbConfigFile}", 0);
                throw new Exception($baseMessage);
            }
            // Load the configuration file
            $dbConfig = require_once($dbConfigFile);
            self::$data[$configKey] = $dbConfig;
        }
    }

    public static function get($indexes=''):mixed {
        $indexes = trim($indexes);
        // Si la cadena no contiene piezas regresámos nulo
        if (!empty($indexes)) {
            // Cada parte de la cadena representa un indice del array
            $vars = explode(".", $indexes);
            // The first index has the name of the file
            self::load($vars[0]);
            // Si la cadena tiene piezas
            if (count($vars) > 0) {
                // Usamos el primer indice para extraer el valor
                $first = array_shift($vars);
                $value = self::$data[$first];
                foreach ($vars as $var) {
                    $value = $value[$var] ?? null;
                }
                return $value;	
            }
            
        }
        return null;
    }

    public static function set($indexes='', $value=null):void {
        if (trim($indexes) !== '') {
            // Cada parte de la cadena representa un indice del array
            $vars = explode(".", $indexes);
            // Si la cadena tiene piezas
            if (count($vars) > 0) {
                // Use the first element in the index to load the configuration file
                $first = array_shift($vars);
                // If the index does not exists, create it
                if (!isset(self::$data[$first])) { self::$data[$first] = []; }
                // Reference to the variable instead of its value
                $item = &self::$data[$first];
                foreach ($vars as $var) {
                    // Si el subindice no existe lo creamos
                    if (!isset($item[$var])) { $item[$var] = []; }
                    $item = &$item[$var];
                }
                $item = $value;
            }
        }
    }

}

?>