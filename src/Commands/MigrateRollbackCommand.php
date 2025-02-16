<?php

namespace PC\Commands;

use Exception;
use DateTime;

use PC\Abstracts\AMigrate;
use PC\Core;
use PC\Databases\DB;
use PC\Interfaces\ICommand;
use PC\Terminal\StyleTerminal;
use ReflectionClass;

class MigrateRollbackCommand extends AMigrate implements ICommand {

    protected string $description = "Start a migration rollback process which will try to undo last changes to database.";

    protected string $signature = "migrate:rollback";

    protected array $arguments = [];

    public function handle(): void {

        DB::connection($this->defaultConnection)->transaction(function(DB $dbSchema) {

            $errors = 0;

            foreach($this->migrationFiles as $migrationKey => $migrationData) {

                // EMPTY rolledback flag means no rolled back has been done before and 
                // NOT EMPTY migrated flag means there's something to be roll back
                // then roll back the file and save the date into that flag
                if (empty($migrationData["rolledback"]) && !empty($migrationData["migrated"])) {

                    // Notify to the user what is going on the terminal
                    echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"cyan"], "Rolling back: ");
                    echo StyleTerminal::format([], [], "{$migrationData["file"]}\r\n");

                    // Change the value of the flags but without updating them
                    $date = new DateTime();
                    $this->migrationFiles[$migrationKey]["migrated"] = null;
                    $this->migrationFiles[$migrationKey]["rolledback"] = $date->format(self::DATE_FORMAT);

                    try {

                        /** @var Migration $migrationClass */
                        $migrationClass = require_once($migrationData["path"]);
                        $upMessage = $migrationClass->down($dbSchema);
                        echo StyleTerminal::format(["bold"=>true], ["text"=>"green"], "{$upMessage}\r\n\r\n");

                    } catch (Exception $e) {

                        echo StyleTerminal::format(["bold"=>true], ["text"=>"red"], "{$e->getMessage()}\r\n");
                        $errors++;

                    }
                }
                
            }

            // If all the migrations from the transaction succeed the save the JSON file
            if ($errors == 0) {
                $this->save();
            }

        });
        
    }

}

?>