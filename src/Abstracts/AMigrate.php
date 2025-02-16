<?php

namespace PC\Abstracts;

use Exception;
use DateTime;

use PC\Core;
use PC\Singletons\Config;
use PC\Interfaces\ICommand;
use PC\Abstracts\ACommand;
use PC\Databases\DB;
use PC\Terminal\StyleTerminal;

abstract class AMigrate extends ACommand implements ICommand {

    CONST MIGRATE_FILE = 'migration.json';

    CONST DATE_FORMAT = 'YmdHisu';

    protected string $defaultConnection;

    protected array $migrationFiles;

    public function save():void {
        $jsonFilePath = Core::DIR_ROOT()."/".self::MIGRATE_FILE;
        file_put_contents($jsonFilePath, json_encode($this->migrationFiles, JSON_PRETTY_PRINT, 16));
    }

    public function __construct() {

        // If the database config file does not have a default database connection we finish the terminal execution
        $databases = Config::get("databases");
        if (empty($databases)) {
            $databaseDir = Core::DIR_APP()."/Configs/Databases.php";
            echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"red"], "Error: ");
            echo StyleTerminal::format([], [], "No database configuration was found within file $databaseDir \n");
            exit();
        }

        // If everything goes well just define the default connection
        $this->defaultConnection = array_key_first($databases);
        print_r($databases);
        exit();

        // If the migrations tabla does not exists, try to create it
        $dbSchema = DB::connection($this->defaultConnection);
        if (!$dbSchema->tableExists("migrations")) {
            echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"red"], "Migrations table not found: ");
            echo StyleTerminal::format([], [], "Generating migrations table.\n");

            try {

                /** @var Migration $migrationClass */
                $migrationsPath = Core::DIR_SRC()."/Templates/CreateMigrationsTemplate.php";
                $migrationsClass = require_once($migrationsPath);
                $upMessage = $migrationsClass->up($dbSchema, $databases[$this->defaultConnection]);
                echo StyleTerminal::format(["bold"=>true], ["text"=>"green"], "{$upMessage}\r\n\r\n");

            } catch (Exception $e) {

                echo StyleTerminal::format(["bold"=>true], ["text"=>"red"], "{$e->getMessage()}\r\n");
                $errors++;

            }
        }
        exit();

        // If the filePath does not exists we create it
        $jsonFilePath = Core::DIR_ROOT()."/".self::MIGRATE_FILE;
        if (file_exists($jsonFilePath) === false) {
            $this->migrationFiles = [];
        } else {
            $this->migrationFiles = json_decode(file_get_contents($jsonFilePath), true, 16, JSON_BIGINT_AS_STRING | JSON_OBJECT_AS_ARRAY);
        }

        // Get the full list of migration files and compares it against the JSON history
        $hasModified = false;
        $migrationsPath = Core::DIR_APP()."/Migrations";
        if ($dirPointer = opendir($migrationsPath)) {
            while (false !== ($migrationFile = readdir($dirPointer))) {
                if ($migrationFile != "." && $migrationFile != "..") {

                    $fileNameParts = explode("-", $migrationFile, 2);
                    $timestamp = $fileNameParts[0];
                    
                    if (!isset($this->migrationFiles[$timestamp])) {
                        $hasModified = true;
                        $this->migrationFiles[$timestamp] = [
                            "path" => $migrationsPath."/".$migrationFile,
                            "file" => $migrationFile,
                            "date" => DateTime::createFromFormat(self::DATE_FORMAT, $fileNameParts[0]),
                            "migrated" => null,
                            "rolledback" => null
                        ];
                    }
                }
            }
        }

        print_r(array_keys($this->migrationFiles));
        exit();

        // If there are new migration files update the JSON file
        if ($hasModified) {
            $this->save();
        } else {
            echo StyleTerminal::format(["bold"=>true, "underline"=>false], ["text"=>"yellow"], "Notice: ");
            echo StyleTerminal::format([], [], "There was not found new migration files. \r\n\r\n");
        }
        
    }

}

?>