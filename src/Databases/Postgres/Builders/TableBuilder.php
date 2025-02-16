<?php

namespace PC\Databases\Postgres\Builders;

use Exception;
use PC\Abstracts\ATableBuilder;


class TableBuilder extends ATableBuilder {

    public function createSequence(string $sequenceName, int $startValue=1, int $increment=1):void {
        $this->sequences[] = "CREATE SEQUENCE {$sequenceName} START {$startValue} INCREMENT {$increment};";
    }

    public function createIndex(string $schema, string $tableName, string $indexName, string $fieldName):void {
        $tableNameWithSchema = $this->addSchema($tableName, $schema);
        $this->indexes[] = "CREATE INDEX {$indexName} ON {$tableNameWithSchema} ({$fieldName});";
    }

    public function createConstraint(string $schema, string $tableName, string $constraintName, string $fieldName, string $type):void {
        $tableNameWithSchema = $this->addSchema($tableName, $schema);
        $this->unique[] = "ALTER TABLE {$tableNameWithSchema} ADD CONSTRAINT {$constraintName} $type ({$fieldName});";
    }

    public function getSQL():string {

        $sqlFields = "";

        $fields = $this->getFields();
        $totalFields = count($fields) - 1;

        foreach ($fields as $index => $field) {

            $sqlFields .= "\t";
                        
            if (!empty($field["name"])) {
                $sqlFields .= " {$field["name"]}";
            }

            $type = $field["type"] ?? "string";

            switch($type) {
                case "integer":
                    $sqlFields .= " INTEGER";
                    break;
                case "small_integer":
                    $sqlFields .= " SMALLINT";
                    break;
                case "big_integer":
                    $sqlFields .= " BIGINT";
                    break;
                case "decimal":
                    $sqlFields .= " NUMERIC({$field["precision"]}, {$field["scale"]})";
                    break;
                case "boolean":
                    $sqlFields .= " SMALLINT";
                    break;
                case "char":
                    $sqlFields .= " CHAR({$field["length"]})";
                    break;
                case "varchar":
                    $sqlFields .= " VARCHAR({$field["length"]})";
                    break;
                case "text":
                    $sqlFields .= " TEXT";
                    break;
                case "date":
                    $sqlFields .= " DATE";
                    break;
                case "time":
                    $sqlFields .= " TIME";
                    break;
                case "datetime":
                    $sqlFields .= " TIMESTAMP";
                    break;
                case "timestamp":
                    $sqlFields .= " TIMESTAMP";
                    break;
            }

            if (!empty($field["auto_increment"])) {
                
                $sequenceName = "seq_".$this->table."_".$this->str_urify($field["name"], "_");
                $this->createSequence($sequenceName);
                $sqlFields .= " DEFAULT nextval('{$sequenceName}')";
            }

            if (!empty($field["primary_key"])) {
                $sqlFields .= " PRIMARY KEY";
            }

            if (!empty($field["index"])) {
                $indexName = "index_".$this->table."_".$this->str_urify($field["name"], "_");
                $this->createIndex($this->schema, $this->table, $indexName, $field["name"]);
            }

            if (!empty($field["unique"])) {
                $uniqueName = "unique_".$this->table."_".$this->str_urify($field["name"], "_");
                $this->createConstraint($this->schema, $this->table, $uniqueName, $field["name"], "UNIQUE");
            }

            if (!empty($field["timezone"])) {
                $sqlFields .= " WITH TIME ZONE";
            }

            if (empty($field["nullable"])) {
                $sqlFields .= " NOT NULL";
            }

            if (!empty($field["default"])) {
                $sqlFields .= " DEFAULT ".$field["default"];
            }

            if ($index < $totalFields) {
                $sqlFields .= ",";
            }

            if (!empty($field["comment"])) {
                $sqlFields .= " -- ".$field["comment"];
            }

            if ($index < $totalFields) {
                $sqlFields .= "\r\n";
            }
            
        }
        
        $createSQL = "-- TABLE CREATION \r\n";
        $createSQL.= "CREATE TABLE ".$this->addSchema($this->table, $this->schema)." (\r\n";
        $createSQL.= $sqlFields;
        $createSQL.= "\r\n);";

        return $createSQL;
    }

    public function getSequencesSQL():array {
        $sequences = [];
        foreach ($this->sequences as $sequence) {
            $sequenceSQL = "-- SEQUENCE\r\n";
            $sequenceSQL.= $sequence;
            $sequences[] = $sequenceSQL;
        }
        return $sequences;
    }

    public function getIndexesSQL():array {
        $indexes = [];
        foreach ($this->indexes as $index) {
            $indexSQL = "-- INDEX\r\n";
            $indexSQL.= $index;
            $indexes[] = $indexSQL;
        }
        return $indexes;
    }

    public function getUniqueSQL():array {
        $uniqueList = [];
        foreach ($this->unique as $unique) {
            $uniqueSQL = "-- UNIQUE\r\n";
            $uniqueSQL.= $unique;
            $uniqueList[] = $uniqueSQL;
        }
        return $uniqueList;
    }

    public function getDropTable():string {
        return "DROP TABLE IF EXISTS {$this->schema}.{$this->table}";
    }

    public function getDropSequence(string $sequenceName):string {
        return "DROP SEQUENCE IF EXISTS {$this->schema}.{$sequenceName}";
    }

    public function getDropIndex(string $indexName):string {
        return "DROP INDEX IF EXISTS {$this->schema}.{$indexName}";
    }

    public function getDropConstraint(string $constraintName):string {
        return "ALTER TABLE IF EXISTS {$this->schema}.{$this->table} DROP CONSTRAINT IF EXISTS {$constraintName}";
    }

}

?>