<?php

namespace PC\Commands;

use DateTime;
use PC\Abstracts\ACommand;
use PC\Traits\StringTrait;

class MakeMigrationCommand extends ACommand {

    use StringTrait;

    protected string $description = "Generate a migration file which is used to create database assets";

    protected string $signature = "make:migration --intention=\"Create table tokens\"";

    protected array $arguments = [
        "--intention" => "The purpose for this migration file"
    ];

    public function handle():void {

        $intention = $this->str_urify($this->flags["--intention"]);

        $now = new DateTime();
        $microdate = $now->format('YmdHisu');

        echo "{$microdate}-{$intention}";
        exit();

    }

}

?>