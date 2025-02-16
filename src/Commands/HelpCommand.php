<?php

namespace PC\Commands;

use PC\Abstracts\ACommand;
use PC\Core;
use PC\Terminal\TableTerminal;
use ReflectionClass;

class HelpCommand extends ACommand {

    protected int $order = 1;

    protected string $signature = "help";

    protected string $description = "Shows the list of available commands within PHPCore";

    public function handle():void {

        $commandsPath = Core::DIR_SRC()."/Commands/";
        $files = scandir($commandsPath, SCANDIR_SORT_DESCENDING);

        $commandList = [];
        foreach ($files as $index => $file) {
            if (stripos($file, "Command.php") !== false) {

                $commandName = str_replace(".php", "", $file);
                $commandNamespace = "PC\\Commands\\{$commandName}";
                $reflectionClass = new ReflectionClass($commandNamespace);
                $props = $reflectionClass->getDefaultProperties();

                $order = str_pad($props["order"] ?? 10, 3, " ", STR_PAD_LEFT).")";
                $signature = explode(" ", $props["signature"]);
                $commandList[$index] = [
                    "order" => $order,
                    "signature" => array_shift($signature),
                    "description" => $props["description"]
                ];
            }
        }

        TableTerminal::tabulated($commandList, [
            "order"=>[SORT_ASC, SORT_STRING],
            "signature"=>[SORT_ASC, SORT_STRING],
            "description"=>[SORT_ASC, SORT_STRING]
        ]);

    }

}

?>