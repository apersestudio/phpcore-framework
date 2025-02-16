<?php

namespace PC;

class Core {

    /**
     * Root directory where we can found the codebase for the whole project
     * @return string 
     */
    public static function DIR_ROOT():string { return realpath(__DIR__."/.."); }

    /**
     * Application directory where we can found the developer logic
     * @return string 
     */
    public static function DIR_APP():string { return realpath(__DIR__."/../app"); }

    /**
     * Source directory where we can found PHPCore logic (Please do not touch it)
     * @return string 
     */
    public static function DIR_SRC():string { return realpath(__DIR__."/../src"); }

    /**
     * Public directory where guest users can usually navigate freely
     * @return string 
     */
    public static function DIR_PUBLIC():string { return realpath(__DIR__."/../public"); }

    /**
     * Vendor directory where composer downloads its core functionality (Please do not touch it)
     * @return string 
     */
    public static function DIR_VENDOR():string { return realpath(__DIR__."/../vendor"); }

    /**
     * The program type requesting the php process to execute, can be "browser" or "terminal"
     * @return string 
     */
    public static function PHPAGENT():string {
        // Constants definition logic for PHPAGENT
        switch (php_sapi_name()) {
            case 'fpm-fcgi': return "browser";
            case 'cli':  return "terminal";
            default: return "";
        }
    }

    /**
     * The arguments passed to the terminal
     * @return array 
     */
    public static function ARGUMENTS():array { 
        return $_SERVER["argv"] ?? [];
    }

}

?>