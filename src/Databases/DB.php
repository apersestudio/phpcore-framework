<?php

namespace PC\Databases;

use PC\Abstracts\ADatabaseModeler;
use PC\Abstracts\ADatabaseDriver;
use PC\Abstracts\AConnectionData;
use PC\Abstracts\ASchemaBuilder;
use PC\Singletons\Config;
use PC\Traits\SQLBuilder;

use Exception;
use Closure;
use PC\Core;

class DB extends ADatabaseModeler {

    use SQLBuilder;

    protected static self $instance;

    protected string $connection;

    protected ASchemaBuilder $schemaBuilder;

    protected ADatabaseDriver $driver;

    protected array $databaseConfig;
    protected string $driverClass;
    protected string $baseNamespace;
    protected string $driverNamespace;
    protected string $schemaNamespace;
    protected string $connectionNamespace;

    private function __construct(){}
    
    private static function getInstance():self {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function connection(string $connectionName):self {

        $instance = self::getInstance();
        
        $instance->connection = $connectionName;

        // Load the databases configuration file
        $instance->databaseConfig = Config::get("databases.{$instance->connection}");

        if (is_null($instance->databaseConfig)) {
            $baseMessage = "Missing configuration key";
            error_log($baseMessage.": {$instance->connection}", 0);
            throw new Exception($baseMessage);
        }

        // Extract the name of the driver from the config
        $instance->driverClass = ucfirst(strtolower($instance->databaseConfig["driver"]));

        // Check if the engine is installed
        $enginePath = Core::DIR_SRC()."/Databases/".$instance->driverClass;
        if (is_dir($enginePath) === false) {
            $baseMessage = "The PHPCore library used to connect to the ".$instance->driverClass." engine is not installed.";
            throw new Exception($baseMessage);
        }

        // Namespace of the engine
        $instance->baseNamespace = "\\PC\\Databases\\{$instance->driverClass}";

        // Namespace of the driver
        $instance->driverNamespace = "\\PC\\Databases\\{$instance->driverClass}\Driver";
        $instance->driver = $instance->driverNamespace::getInstance();

        // Connection Namespace
        $instance->connectionNamespace = "{$instance->baseNamespace}\\Connection";

        /** @var AConnectionData $connectionData */
        $connectionData = new $instance->connectionNamespace($instance->databaseConfig);
        $instance->driver->connect($connectionData);
        
        // The builder used to create, delete and edit databases, roles and schemas
        $instance->schemaNamespace = "{$instance->baseNamespace}\\Builders\\SchemaBuilder";

        // If the database configuration contains an schema
        if (isset($instance->databaseConfig["schema"])) {
            $instance->schema($instance->databaseConfig["schema"]);
        }

        if (class_exists($instance->driverNamespace) === false) {
            $baseMessage = "Missing driver";
            error_log($baseMessage.": the database driver {$instance->driverNamespace} is not installed.", 0);
            throw new Exception($baseMessage);
        }

        if (class_exists($instance->connectionNamespace) === false) {
            $baseMessage = "Missing connection handler";
            error_log($baseMessage.": the database connection handler {$instance->connectionNamespace} is not installed.", 0);
            throw new Exception($baseMessage);
        }

        return $instance;
    }

    /**
     * @return ADatabaseDriver 
     */
    public function getDriver():ADatabaseDriver {
        return $this->driver;
    }

    public function schema(string $schemaName):self {
        $this->schemaBuilder = new $this->schemaNamespace($schemaName);
        $this->schemaBuilder->setDriver($this->getDriver());
        return $this;
    }

    /**
     * Builds the query to be executed or executes the query depending on the debugMode flag
     * @return void 
     */
    public function prepareQuery(string $command, array $bindValues=[]):self {
        /* Builds a select statement which does not generate any result or modification to the database */
        if ($this->debugMode === true) {
            $this->addDebugSQL($command, $bindValues);
            $this->driver->execute("SELECT NULL");
        } else {
            $this->driver->execute($command, $bindValues);
        }
        return $this;
    }

    private function divideQueries(array $queries, array $bindValues=[]) {
        
        foreach ($queries as $query) {

            if (preg_match_all("/:\w+\b/", $query, $matches)) {
                $found = count($matches[0]);
                $values = array_splice($bindValues, 0, $found);
            } else {
                $values = [];
            }

            $this->prepareQuery($query, $values);
        }
    }

    public function tableExists(string $tableName):bool {
        return $this->schemaBuilder->tableExists($tableName);
    }

    public function createTable(string $tableName, Closure $closure):self {

        // Generate table SQL's 
        $createTableCommands = $this->schemaBuilder->createTable($tableName, $closure);

        // Get all possible bind values
        $bindValues = $this->schemaBuilder->getBindValues();
        
        $this->divideQueries($createTableCommands, $bindValues);
        
        return $this;
    }

    public function dropTable(string $tableName):self {
        
        $dropTableCommand = $this->schemaBuilder->dropTable($tableName);

        $this->divideQueries($dropTableCommand);

        return $this;
    }

    public function beginTransaction():self {
        
        $this->driver->beginTransaction();
        
        return $this;
    }

    public function commit():self {
        
        $this->driver->commit();
        
        return $this;
    }

    public function rollback():self {
        
        $this->driver->rollback();
        
        return $this;
    }

    /**
     * 
     * @param Closure $handler 
     * @return DB 
     */
    public function transaction(Closure $handler):self {
        
        $this->driver->beginTransaction();

        try {
            $handler($this);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
        }

        return $this;
    }

}

?>