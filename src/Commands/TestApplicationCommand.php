<?php

namespace PC\Commands;

use PC\Abstracts\ACommand;
use PC\Core;
use PC\Terminal\StyleTerminal;

class TestApplicationCommand extends ACommand {

    private string $testPath;

    protected int $order = 2;

    protected bool $enableHelp = false;

    protected string $description = "Runs testing for the application project. Test files are located at app/Test";

    protected string $signature = "test:application --no-cache?";

    protected array $arguments = [
        "--no-cache" => "Disable PHPUnit from caching results"
    ];

    private function run():void {
        
        $phpUnitPath = Core::DIR_VENDOR()."/phpunit/phpunit/phpunit";
        if (file_exists($phpUnitPath) === false) {
            $this->installScreen("PHPUnit is not installed", "composer require --dev phpunit/phpunit");
        }

        $options = [];

        // Map custom flags to PHPUnit flags
        if (isset($this->flags["--no-cache"])) {
            $options[] = "--do-not-cache-result";
            unset($this->flags["--no-cache"]);
        }
        
        foreach ($this->flags as $flag=>$value) {
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
    
    public function handle():void {
        $this->testPath = Core::DIR_APP();
        $this->run();
    }

}

?>