<?php

namespace PC\Commands;

use PC\Abstracts\ACommand;
use PC\Core;
use PC\Terminal\StyleTerminal;

class TestCoreCommand extends ACommand {

    private string $testPath;

    protected int $order = 2;

    protected bool $enableHelp = false;

    protected string $description = "Runs testing for PHPCore internal processes. Test files are located at src/Test";

    protected string $signature = "test:core --no-cache?";

    protected array $arguments = [
        "--no-cache" => "Disable PHPUnit from caching results"
    ];

    private function run(array $flags):void {
        
        $phpUnitPath = Core::DIR_VENDOR()."/phpunit/phpunit/phpunit";
        if (file_exists($phpUnitPath) === false) {
            $this->installScreen("PHPUnit is not installed", "composer require --dev phpunit/phpunit");
        }

        $options = [];
        foreach ($flags as $flag=>$value) {
            if ($value === true) { $options[] = $flag; } 
            else { $options[] = $flag.'="'.$value.'"'; }
        }
        $optionsString = implode(" ", $options);

        $output = [];
        $exec = $phpUnitPath." {$optionsString} ".$this->testPath."/Test";
        exec($exec, $output, $results);

        foreach ($output as $item=>$value) {
            echo StyleTerminal::format([], [], "{$value}\n");
        }
    }
    
    public function handle(array $flags):void {
        $this->testPath = Core::DIR_SRC();
        $this->run($flags);
    }

}

?>