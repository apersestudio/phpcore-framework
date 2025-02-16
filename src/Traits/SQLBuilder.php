<?php

namespace PC\Traits;

use Exception;
use PC\Abstracts\ADatabaseDriver;

trait SQLBuilder {

    public CONST AGGREGATES = ["AVG", "MIN", "MAX", "SUM", "COUNT"];

    public CONST COMPARISONS = [">", "=", "<", ">=", "<=", "<>"];

    public CONST ORDERS = ["ASC", "DESC"];

    protected array $sqlFragments = [];

    protected array $sqlDebug = [];
    
    protected array $bindValues = [];

    protected ADatabaseDriver $driver;

    public function clear():void {
        $this->sqlFragments = [];
		$this->bindValues = [];
	}

    public function setDriver(ADatabaseDriver $driver):void {
        $this->driver = $driver;
    }

    public function validateAggregate(string $aggregate):bool {
		return in_array(strtoupper($aggregate), self::AGGREGATES) === true;
    }

    public function validateComparison(string $comparison):bool {
        return in_array(strtoupper($comparison), self::COMPARISONS) === true;
    }
    
    public function validateOrder(string $order):bool {
        return in_array(strtoupper($order), self::ORDERS) === true;
    }

    public function validCreateColumn(string $column):bool {
        return preg_match("/^[a-zA-Z][\w]+$/", $column) === 1;
    }

    public function getConjunction(string $conjunction):string {
		if ($this->hasFragments() === false) { return ""; }
		return !empty($conjunction) ? " ".strtoupper($conjunction)." " : " AND ";
	}

    public function getFromAlias(string $alias):array {
        if (preg_match('#^\s*(?<alias>[\w]+)\s*$#mi', $alias, $matches)) {
            return array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
        }
        $baseError = "Bad from alias";
        error_log("{$baseError} [" . $alias . "]", 0); /* For the developer */
        throw new Exception($baseError); /* For the user */
    }

    public function getColumn(string $expression):string {
        if (preg_match("#^(?<name>[\w.]+)$#mi", trim($expression), $match)) {
            return $match["name"];
        }
        error_log("Bad column [" . $expression . "]", 0); /* For the developer */
        throw new \Exception("Bad column"); /* For the user */
    }

    public function getOrder(string $expression):array {
        if (preg_match("#^(?<name>[\w\*.]+)(\s+(?<order>(asc|desc))){0,1}$#mi", trim($expression), $matches)) {
            return array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
        }
        $baseError = "Bad order";
        error_log("{$baseError} [" . $expression . "]", 0); /* For the developer */
        throw new \Exception($baseError); /* For the user */
    }

    public function getAlias(string $expression):array {
        if (preg_match("#^(?<name>[\w\*.]+)(\s+AS\s+(?<alias>\w+)){0,1}$#mi", trim($expression), $matches)) {
            return array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
        }
        error_log("Bad alias [" . $expression . "]", 0); /* For the developer */
        throw new \Exception("Bad alias"); /* For the user */
    }

    public function cleanSpaces(string $sql) {
        return preg_replace("/[ ]+/m", " ", $sql);
    }

    public function hasFragments():bool {
        return !empty($this->sqlFragments);
    }

    public function addFragment(mixed $fragment):void {
        $this->sqlFragments[] = $fragment;
    }

    public function getFragments():array {
        return $this->sqlFragments;
    }

    public function addFragments(array $fragments):void {
        $this->sqlFragments = array_merge($this->sqlFragments, $fragments);
    }

    public function getBindValues(): array {
		return $this->bindValues;
	}

    public function addBindValues(array $bindValues):void {
        $this->bindValues[] = $bindValues;
    }

    public function mergeBindValues(array $bindValues):void {
        $this->bindValues = array_merge($this->bindValues, $bindValues);
    }

    protected function getBindIdentifier($prefix, $name, $value):string {
        $identifier = ":" . strtolower($prefix) . round(microtime(true) + rand()) . count($this->bindValues);
        $this->addBindValues([
            "fieldname"=>$name, 
            "identifier"=>$identifier, 
            "value"=>$value,
            "type"=>$this->model->getDefinitionType($name)
        ]);
        return $identifier;
    }

    protected function getBindIdentifierByType($prefix, $name, $value, $type):string {
        $identifier = ":" . strtolower($prefix) . round(microtime(true) + rand()) . count($this->bindValues);
        $this->addBindValues([
            "fieldname"=>$name, 
            "identifier"=>$identifier, 
            "value"=>$value,
            "type"=>$type
        ]);
        return $identifier;
    }

    /**
     * Prepends the schema to the table name
     * @param string $table 
     * @param string $schema 
     * @return string 
     */
    public function addSchema(string $table, string $schema):string {
		return "{$schema}.{$table}";
	}

    /**
     * Prepends the schema and table name at the begining of the column
     * @param string $column
     * @param string $table 
     * @param string $schema 
     * @return string
     */
    public function addSchemaAndTable(string $column, string $table, string $schema):string {
        $parts = explode(".", $column);
        switch (count($parts)) {
            case 1 : return "{$schema}.{$table}.{$parts[0]}"; break;
            case 2 : return "{$schema}.{$parts[0]}.{$parts[1]}"; break;
            case 3 : return "{$parts[0]}.{$parts[1]}.{$parts[2]}"; break;
        }
        return $column;
	}

    /**
     * Prepends the schema and table name at the begining of each column
     * @param array $columns 
     * @param string $table 
     * @param string $schema 
     * @return array
     */
    public function addSchemaAndTableMultiple(array $columns, string $table, string $schema):array {
		return array_map(function($column) use ($table, $schema) {
            return $this->addSchemaAndTable($column, $table, $schema);
        }, $columns);
	}

    /**
     * If setted to true, all SQL statements would not be executed 
     * and an array of SQL sentences would be created instead with its real values
     * Use this feature with care to avoid filtering sensitive information
     * @param bool $debugMode 
     * @return Model 
     */
    public function setDebugMode(bool $debugMode):self {
        $this->debugMode = $debugMode;
        return $this;
    }

    /**
     * Cleans a SQL sentences removing unnecessary spaces and newlines from the query
     * It's useful for comparing SQL queries builded for unit testing
     * @param string $sql
     * @return string 
     */
    public function cleanSQL(string $sql):string {
        return preg_replace("/\s+/", " ", trim($sql));
    }

    /**
     * Adds a sql sentence to the list only available when debugMode is true
     * @param string $command 
     * @param array $bindValues 
     * @return void 
     */
    public function addDebugSQL(string $command, array $bindValues):self {
        foreach ($bindValues as $item) {
            $value = ($item["type"] === "string") ? "'".$item["value"]."'" : $item["value"];
            $command = str_replace($item["identifier"], $value, $command);
        }
        $this->sqlDebug[] = $this->cleanSpaces($command);
        return $this;
    }

    /**
     * Returns the list of sql sentences created by the model when debugMode is true
     * Is debugMode is false the returned array will be empty
     * @return array 
     */
    public function getDebugSQL():string {
        if ($this->debugMode === true) {
            $sql = "";
            foreach ($this->sqlDebug as $sqlCommand) {
                $sql.= $sqlCommand."\r\n";
            }
        } else {
            $sql = "";
        }
        
        $this->sqlDebug = [];
        return $sql;
    }
}