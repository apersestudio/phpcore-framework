<?php

namespace PC\Handlers;

use PC\Core;
use PC\Interfaces\IHandler;
use PC\Terminal\StyleTerminal;

class CommandHandler implements IHandler {

    private static function getFlags(array $arguments) {
        
        $flags = [];
        foreach ($arguments as $argument) {
            if (stripos($argument, "--") !== false) {
                if (stripos($argument, "=") !== false) {
                    $pair = explode("=", $argument);
                } else {
                    $pair = [$argument, true];
                }
                $flags[$pair[0]] = $pair[1];
            }
        }
        return $flags;
    }

    public static function start():void {

        $arguments = Core::ARGUMENTS();
        
        echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"cyan"], "PHPCORE: ");
        echo StyleTerminal::format([], [], "Version 1.0\n");

        /* If there's one argument at position "one" it should be the command to execute */
        if (isset($arguments[1])) {

            $command = str_replace("--", "", $arguments[1]);
            $commandParts = explode(":", $command);

            echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"cyan"], "Command: ");
            echo StyleTerminal::format([], [], "{$command}\n\n");

            // Main command name
            $commandName = ucfirst(strtolower($commandParts[0]));
            
            // If there's a subcommand to execute append it to the commandName
            if (isset($commandParts[1])) { $commandName .= ucfirst(strtolower($commandParts[1])); }
            $commandName .= "Command";

            $commandPath = Core::DIR_SRC()."/Commands/".$commandName.".php";
            $exists = file_exists($commandPath);
            if ($exists) {

                $flags = self::getFlags($arguments);

                // If the user does include a method name we try to call it
                $commandNamespace = "PC\\Commands\\{$commandName}";
                $commandClass = new $commandNamespace($flags);

                $methodName = "handle";
                if (method_exists($commandClass, $methodName)) {
                    $commandClass->$methodName();
                } else {
                    echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"red"], "Error: ");
                    echo StyleTerminal::format([], [], "There's no method with name {$methodName}\n\n");
                }
                
            } else {

                echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"red"], "Error: ");
                echo StyleTerminal::format([], [], "There's no command called ");
                echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"yellow"], "{$command}\n\n");

            }

        }

    }

}

?>