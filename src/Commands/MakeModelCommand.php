<?php

namespace PC\Commands;

use PC\Core;
use PC\Abstracts\ACommand;
use PC\Terminal\StyleTerminal;

class MakeModelCommand extends ACommand {

    protected int $order = 3;

    protected bool $enableHelp = true;

    protected string $description = "Generate a model file into the app/Models directory";

    protected string $signature = "make:model --model=ClientUser --table=users --prefix?=user --connection=client --schema=public";

    protected array $arguments = [
        "--model"=>"The name of the model in the php file",
        "--table"=>"the name of the table as in the database",
        "--connection"=>"The name of the connection to connecto to the database",
        "--schema"=>"The schema where the table is located",
        "--prefix?"=>"A prefix to add at the begining of each field name"
    ];

    public function handle():void {

        // Mandatory flags
        $table = $this->flags["--table"];
        $model = $this->flags["--model"];
        $connection = $this->flags["--connection"];
        $schema = $this->flags["--schema"];
        $relationExample = $this->flags["--relation-example"] ?? false;

        // Optional flags
        $prefix = $this->flags["--prefix"] ?? $table;

        $modelTemplate = Core::DIR_SRC()."/Templates/ModelTemplate.php";
        $content = file_get_contents($modelTemplate);

        // Model replacement
        preg_match("/class\s(?<model>\w+)Model/m", $content, $matches, PREG_OFFSET_CAPTURE);
        $content = substr_replace ($content, $model, $matches["model"][1], strlen($matches["model"][0]));

        // Table replacement
        preg_match("/table\s=\s\"(?<table>.*)\"/m", $content, $matches, PREG_OFFSET_CAPTURE);
        $content = substr_replace ($content, $table, $matches["table"][1], strlen($matches["table"][0]));

        // Connection replacement
        preg_match("/connection\s=\s\"(?<connection>.*)\"/m", $content, $matches, PREG_OFFSET_CAPTURE);
        $content = substr_replace ($content, $connection, $matches["connection"][1], strlen($matches["connection"][0]));

        // Schema replacement
        preg_match("/schema\s=\s\"(?<schema>.*)\"/m", $content, $matches, PREG_OFFSET_CAPTURE);
        $content = substr_replace ($content, $schema, $matches["schema"][1], strlen($matches["schema"][0]));
        
        // Definition replacement
        $content = str_replace("table_", $prefix."_", $content);

        // Relations replacement
        if ($relationExample === false) {
            preg_match("/(?<relations>\n.*\n.*protected\s[$]relations(.|\n)*;)/m", $content, $matches, PREG_OFFSET_CAPTURE);
            $content = substr_replace ($content, "", $matches["relations"][1], strlen($matches["relations"][0]));
        }
        
        $filePath = Core::DIR_APP()."/Models/{$model}Model.php";
        if (file_exists($filePath)) { $this->errorScreen("The model already exists"); }

        // Create the model file
        file_put_contents($filePath, $content);
        $this->successScreen("File created at", $filePath);
    }

}

?>