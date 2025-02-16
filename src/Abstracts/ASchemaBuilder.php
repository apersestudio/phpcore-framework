<?php

namespace PC\Abstracts;

use Closure;
use PC\Abstracts\ATableBuilder;
use PC\Traits\SQLBuilder;
use PDO;

abstract class ASchemaBuilder {

    use SQLBuilder;

    /** Datatypes supported by most database drivers and its default mapping to PDO */
    const PDOMapping = [
        "integer" => PDO::PARAM_INT,
        "small_integer" => PDO::PARAM_INT,
        "big_integer" => PDO::PARAM_INT,
        "decimal" => PDO::PARAM_STR,
        "boolean" => PDO::PARAM_BOOL,
        "char" => PDO::PARAM_STR,
        "varchar" => PDO::PARAM_STR,
        "text" => PDO::PARAM_STR,
        "date" => PDO::PARAM_STR,
        "time" => PDO::PARAM_STR,
        "datetime" => PDO::PARAM_STR,
        "timestamp" => PDO::PARAM_STR
    ];

    protected ATableBuilder $tableBuilder;

    abstract public function __construct(string $schemaName);

    abstract public function getBindValues(): array;

    abstract public function tableExists(string $tableName):bool;

    abstract public function createTable(string $tableName, Closure $closure);

    abstract public function dropTable(string $tableName);

    abstract public function setDriver(ADatabaseDriver $driver);

}

?>