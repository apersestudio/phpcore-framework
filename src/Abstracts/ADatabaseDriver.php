<?php

namespace PC\Abstracts;

use PC\Abstracts\AConnectionData;
use PDO;
use PDOStatement;

abstract class ADatabaseDriver {

    /** Default values for this drivers */
    const DEFAULT_HOST = "localhost";
    const DEFAULT_PORT = -1;
    
    /** Datatypes supported by most database drivers and its default mapping to PDO */
    const PDOMapping = [
        "integer" => PDO::PARAM_INT,
        "decimal" => PDO::PARAM_STR,
        "boolean" => PDO::PARAM_BOOL,
        "date"=> PDO::PARAM_STR,
        "timestamp"=> PDO::PARAM_STR,
        "text"=> PDO::PARAM_STR,
        "varchar"=> PDO::PARAM_STR,
    ];

    protected PDO $connection;
    protected PDOStatement $statement;
    protected bool $isOpen = false;

    /**
     * Singleton design pattern
     * @return Postgres 
     */
    public static self $instance;
    private function __construct() {}
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
    
    /**
     * Takes the AConnectionData object and tries to connect to the database with its information
     * @param AConnectionData $connData 
     * @return void 
     */
    abstract public function connect(AConnectionData $connData):void;

    /**
     * It sets additional attributes for the connection
     * @param array $attributes 
     * @return void 
     */
    abstract public function setAttributes(array $attributes):void;

    /* ================================================================================================ //
	// TRANSACTIONS
	// ================================================================================================ */
    /**
     * Approves a trasaction
     * @return void 
     * @throws Exception 
     */
    abstract public function commit():void;

    /**
     * Cancels a transaction
     * @return void 
     * @throws Exception 
     */
    abstract public function rollback():void;

    /**
     * Starts a transaction
     * @return void 
     * @throws Exception 
     */
    abstract public function beginTransaction():void;
    
    /**
     * Executes the given command with the given bind values
     * @param string $command 
     * @param array $values 
     * @return bool 
     * @throws Exception 
     */
    abstract public function execute(string $command, array $values=[]):bool;

    /**
     * Gets the number of rows for the last statement
     * @return int 
     * @throws Exception 
     */
    abstract public function rowCount():int;

    /**
     * Gets the number of columns for the last statement 
     * @return int 
     * @throws Exception 
     */
    abstract public function columnCount():int;

    /**
     * Fetches data from database using the given PDO::FETCH_? method
     * @param int $fetchMethod 
     * @return array 
     * @throws Exception 
     */
    abstract public function fetch(int $fetchMethod):array;

    /**
     * Fetches data from database using PDO::FETCH_OBJ
     * @return array 
     * @throws Exception 
     */
    abstract public function fetchObject():array;

    /**
     * Fetches data from database using PDO::FETCH_ASSOC
     * @return array 
     * @throws Exception 
     */
    abstract public function fetchArray():array;

    /**
     * Fetches data from database using PDO::FETCH_NUM
     * @return array 
     * @throws Exception 
     */
    abstract public function fetchNum():array;
    
}
?>