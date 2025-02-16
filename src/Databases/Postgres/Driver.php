<?php

namespace PC\Databases\Postgres;

use Exception;
use PC\Abstracts\ADatabaseDriver;
use PC\Abstracts\AConnectionData;
use PC\Core;
use PDO;
use PDOException;

class Driver extends ADatabaseDriver {

    /** Default values for this drivers */
    const DEFAULT_HOST = "localhost";
    const DEFAULT_PORT = 5432;
    
    public function connect(AConnectionData $connData):void {
        if ($this->isOpen === false) {
            /* Build the DSN with the data in the AConnectionData object */
            $dsn = $connData->getDSN();
            $user = $connData->getUserName();
            try {
                /* If the developer doesn't provide a hostname we use the driver's default */
                if ($connData->hostIsEmpty()) {
                    $connData->setHost(self::DEFAULT_HOST);
                }

                /* If the developer doesn't provide a port number we use the driver's default */
                if ($connData->portIsEmpty()) {
                    $connData->setPort(self::DEFAULT_PORT);
                }

                /* Try to connect */
                $this->connection = new PDO($dsn, $user, $connData->getPassword());

                /* Flag to avoid executing the same connection multiple times
                $this->isOpen = true;

                /* Default connection attributes */
                $this->setAttributes([
                    /* To enable PDO exceptions for the queries */
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    /* To set associative fetch as default fetch method */
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

            } catch (PDOException $e) {
                $exceptionMessage = "Can not connect the user {$user} with the dsn {$dsn} because \r\n{$e->getMessage()}\r\n";
                
                /* Error exception for the client */
                throw new Exception($exceptionMessage);
            }
        }
    }
    /**
     * It sets additional attributes for the connection
     * @param array $attributes 
     * @return void 
     */
    public function setAttributes(array $attributes):void {
        foreach ($attributes as $key=>$value) {
            $this->connection->setAttribute($key, $value);
        }
    }

    /* ================================================================================================ //
	// TRANSACTIONS
	// ================================================================================================ */
    /**
     * Approves a trasaction
     * @return void 
     * @throws Exception 
     */
    public function commit():void {
        try {
            $this->connection->commit();
        } catch (PDOException $e) {

            $exceptionMessage = "Can not approve the transaction because: \r\n{$e->getMessage()}\r\n";
            throw new Exception($exceptionMessage);

        }
    }

    /**
     * Cancels a transaction
     * @return void 
     * @throws Exception 
     */
    public function rollback():void {
        try {
            $this->connection->rollback();
        } catch (PDOException $e) {
            
            $exceptionMessage = "Can not cancel the transaction because: \r\n{$e->getMessage()}\r\n";
            throw new Exception($exceptionMessage);

        }
    }

    /**
     * Starts a transaction
     * @return void 
     * @throws Exception 
     */
    public function beginTransaction():void {
        try {
            $this->connection->beginTransaction();
        } catch (PDOException $e) {

            $exceptionMessage = "Can not start a transaction because: \r\n{$e->getMessage()}\r\n";
            throw new Exception($exceptionMessage);

        }
    }
    
    /**
     * Executes the given command with the given bind values
     * @param string $command 
     * @param array $values 
     * @return bool 
     * @throws Exception 
     */
    public function execute(string $command, array $values=[]):bool {
        try {
            $this->statement = $this->connection->prepare($command);
            foreach ($values as $item) {
                if (isset(self::PDOMapping[$item["type"]])) {
                    $pdoDataType = self::PDOMapping[$item["type"]];
                } else {
                    $pdoDataType = PDO::PARAM_STR;
                }
                $this->statement->bindParam($item["identifier"], $item["value"], $pdoDataType);
            }
            return $this->statement->execute();

        } catch (PDOException $e) {
            
            $exceptionMessage = "Can not execute the command {$command} because: \r\n{$e->getMessage()}\r\n";
            throw new Exception($exceptionMessage);

        }
        return false;
    }

    /**
     * Gets the number of rows for the last statement
     * @return int 
     * @throws Exception 
     */
    public function rowCount():int {
        try {
            return $this->statement->rowCount();
        } catch (PDOException $e) {
            
            $exceptionMessage = "Can not get the number of rows because: \r\n{$e->getMessage()}\r\n";
            throw new Exception($exceptionMessage);

        }
        return 0;
    }

    /**
     * Gets the number of columns for the last statement 
     * @return int 
     * @throws Exception 
     */
    public function columnCount():int {
        try {
            return $this->statement->columnCount();
        } catch (PDOException $e) {

            $exceptionMessage = "Can not get the number of columns because: \r\n{$e->getMessage()}\r\n";
            throw new Exception($exceptionMessage);

        }
        return 0;
    }

    /**
     * Fetches data from database using the given PDO::FETCH_? method
     * @param int $fetchMethod 
     * @return array 
     * @throws Exception 
     */
    public function fetch(int $fetchMethod):array {
        try {
            $rows = [];
            while ($row = $this->statement->fetch($fetchMethod)) { $rows[] = $row; }
            return $rows;
        } catch (PDOException $e) {
            
            $exceptionMessage = "Can not fetch data because: \r\n{$e->getMessage()}\r\n";
            throw new Exception($exceptionMessage);

        }
        return [];
    }

    /**
     * Fetches data from database using PDO::FETCH_OBJ
     * @return array 
     * @throws Exception 
     */
    public function fetchObject():array {
        return $this->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Fetches data from database using PDO::FETCH_ASSOC
     * @return array 
     * @throws Exception 
     */
    public function fetchArray():array {
        return $this->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches data from database using PDO::FETCH_NUM
     * @return array 
     * @throws Exception 
     */
    public function fetchNum():array {
        return $this->fetch(PDO::FETCH_NUM);
    }
    
}
?>