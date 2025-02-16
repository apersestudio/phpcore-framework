<?php

namespace PC\Handlers;

use PC\Core;
use PC\Interfaces\IHandler;

class LanguageHandler implements IHandler {

    public static function start():void {

        $langConfigFile = Core::DIR_APP().'/Configs/Languages.php';
        if (!file_exists($langConfigFile) || !is_readable($langConfigFile)) {
            throw new \Exception("Couldn't complete the execution of ".__FILE__." because the config file ".$langConfigFile." doesn't exists");
        }
        
        $langConfig = require_once($langConfigFile);
        $langList = $langConfig["availables"];

        if ($langConfig["autodetect"] === true && isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            do {
                $htmlIso = key($langList);
                if (strpos($_SERVER["HTTP_ACCEPT_LANGUAGE"], $htmlIso) !== false) {
                    $language = $htmlIso;
                    break;
                }
            } while (next($langList));
        } else if ($langConfig["autodetect"] === false && isset($_COOKIE["lang"]) === true) {
            reset($langList);
            do {
                $htmlIso = key($langList);
                if ($_COOKIE["lang"] === $htmlIso) {
                    $language = $htmlIso;
                    break;
                }
            } while (next($langList));
        } else {
            $language = $langConfig["default"];
        }

        // Guardamos una cookie con el idioma
        //setcookie("lang", $lang, time()+3600, "/", HOST, SSL, SSL);
        
        $langItem = $langList[$language];
        define("LANG", $language);
        
        // Configuramos la zona horaria
        date_default_timezone_set ($langItem["timezone"]);
        define("TIMEZONE", $langItem["timezone"]);
        
        // Configuramos las cadenas de texto
        setlocale(LC_ALL, $langItem["linux_locale"]);
        define("LOCALE", $langItem["linux_locale"]);
    }

}
?>