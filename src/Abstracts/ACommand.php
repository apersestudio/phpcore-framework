<?php

namespace PC\Abstracts;

use PC\Terminal\StyleTerminal;
use PC\Terminal\TableTerminal;

abstract class ACommand {

    protected int $order;

    protected bool $enableHelp = true;

    protected string $signature;

    protected array $arguments;

    protected array $flags;

    protected string $description;

    public function __construct($flags) {
        $this->flags = $flags;
        $this->checkHelp();
        $this->checkFlags();
    }

    private function checkHelp():void {
        if ($this->enableHelp === true && isset($this->flags["--help"])) {
            echo StyleTerminal::format([], [], "Description: ");
            echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"green"], "{$this->description}\n");

            echo StyleTerminal::format([], [], "Example: ");
            echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"green"], "{$this->signature}\n\n");

            $tableList = [];
            foreach ($this->arguments as $argument=>$description) {
                $optional = stripos($argument, "?") !== false ? "(optional)" : "";
                $tableList[] = [
                    "argument"=>str_replace("?", "", $argument).$optional,
                    "description"=>$description
                ];
            }

            TableTerminal::tabulated($tableList, [
                "argument"=>[SORT_ASC, SORT_STRING],
                "description"=>[SORT_ASC, SORT_STRING]
            ]);
            exit();
        }
    }

    private function checkFlags():void {

        preg_match_all("/(?<flag>--[\w\s?]+)=\"?(?<value>[\w\s]+)\"?/im", $this->signature, $matches);
        
        $errors = [];
        foreach ($matches["flag"] as $index=>$flag) {

            $value = $matches["value"][$index];
            $mandatory = stripos($flag, "?") === false;
            if ($mandatory && !isset($this->flags[$flag])) {
                $errors[] = [
                    "flag"=>$flag,
                    "message"=>"This field is mandatory"
                ];
            }
        }

        if (!empty($errors)) {
            TableTerminal::tabulated($errors, [
                "flag"=>[SORT_ASC, SORT_STRING],
                "message"=>[SORT_ASC, SORT_STRING]
            ]);
            exit();
        }
    }

    public function installScreen(string $errorMessage, string $commandInstall):void {
        echo StyleTerminal::format([], [], "Error: ");
        echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"red"], "{$errorMessage}\n");
        echo StyleTerminal::format([], [], "To install it execute: ");
        echo StyleTerminal::format(["bold"=>true], ["text"=>"yellow"], "{$commandInstall}\n\n");
        exit(); 
    }

    public function errorScreen(string $errorMessage):void {
        echo StyleTerminal::format([], [], "Error: ");
        echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"red"], "{$errorMessage}\n");
        exit(); 
    }

    public function successScreen(string $successlabel, string $successMessage):void {
        echo StyleTerminal::format([], [], "{$successlabel}: ");
        echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"green"], "{$successMessage}\n");
        exit(); 
    }

}

?>