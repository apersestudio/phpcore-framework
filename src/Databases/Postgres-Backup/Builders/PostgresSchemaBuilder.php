<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Databases\Builders\TableBuilder;
use PC\Interfaces\IBuilder;

class SchemaBuilder {

    public TableBuilder $tableBuilder;

    public function __construct() {

    }

    public function getBindValues(): array {
        return [];
    }

    public function getSQL():string {
        return "";
    }

    public function createTable(string $tableName) {

        $sqlFields = "";

        foreach ($this->tableBuilder->getFields() as $field) {
            
            if (!empty($field["name"])) {
                $sqlFields .= " {$field["name"]} ";
            if ($field["type"] == "integer") {
                $sqlFields .= " INTEGER ";
            } else if ($field["type"] == "small_integer") {
                $sqlFields .= " SMALLINT ";
            } else if ($field["type"] == "big_integer") {
                $sqlFields .= " BIGINT ";
            } else if ($field["type"] == "decimal") {
                $sqlFields .= " NUMERIC({$field["precision"]}, {$field["scale"]}) ";
            } else if ($field["type"] == "boolean") {
                $sqlFields .= " SMALLINT ";
            } else if ($field["type"] == "char") {
                $sqlFields .= " CHAR({$field["length"]}) ";
            } else if ($field["type"] == "varchar") {
                $sqlFields .= " VARCHAR({$field["length"]}) ";
            } else if ($field["type"] == "text") {
                $sqlFields .= " TEXT ";
            } else if ($field["type"] == "date") {
                $sqlFields .= " DATE ";
            } else if ($field["type"] == "time") {
                $sqlFields .= " TIME ";
            } else if ($field["type"] == "datetime") {
                $sqlFields .= " TIMESTAMP ";
            }

            if (!empty($field["auto_increment"])) {
                $sqlFields .= " SERIAL ";
            }

            if (!empty($field["primary_key"])) {
                $sqlFields .= " PRIMARY KEY ";
            }

            if (!empty($field["nullable"])) {
                $sqlFields .= " NOT NULL ";
            }

            if (!empty($field["comment"])) {
                $sqlFields .= " -- {$field["comment"]}"
            }

            if (!empty($field["default"])) {
                $sqlFields .= " DEFAULT NOW() ";
            }
            
        }
            
        $createSQL = "CREATE TABLE {$tableName} (";
        $createSQL .= $sqlFields;
        $createSQL .= ");";

        echo $createSQL;

    }

}

?>