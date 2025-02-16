<?php

namespace PC\Databases\Postgres\Builders;

use Closure;
use PC\Abstracts\ATableBuilder;
use PC\Abstracts\ADatabaseDriver;
use PC\Abstracts\ASchemaBuilder;
use PC\Traits\StringTrait;
use PC\Databases\Postgres\Builders\TableBuilder;

class SchemaBuilder extends ASchemaBuilder {

    use StringTrait;

    protected string $schema;

    protected ATableBuilder $tableBuilder;

    protected ADatabaseDriver $driver;

    public function __construct(string $schemaName) {
        $this->schema = $schemaName;
        $this->tableBuilder = new TableBuilder();
        $this->tableBuilder->setSchema($this->schema);
    }

    public function setDriver(ADatabaseDriver $driver):void {
        $this->driver = $driver;
    }

    public function getBindValues(): array {
        return array_merge(
            [],
            $this->tableBuilder->getBindValues()
        );
    }

    public function tableExists(string $tableName):bool {
        $existsSQL = "SELECT COUNT(1) as total FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table";
        
        $bindValues = [
            ["identifier"=>":schema", "value"=>$this->schema, "type"=>"varchar"],
            ["identifier"=>":table", "value"=>$tableName, "type"=>"varchar"]
        ];

        echo $existsSQL;
        print_r($bindValues);
        exit();

        $this->driver->execute($existsSQL, $bindValues);
        $item = $this->driver->fetchArray();
        print_r($item);
        return intval($item[0]["total"]) === 1;
    }

    public function createTable(string $tableName, Closure $closure):array {

        $this->tableBuilder->setTable($tableName);
        $closure($this->tableBuilder);

        $createSQL = [$this->tableBuilder->getSQL()];
        $sequencesSQL = $this->tableBuilder->getSequencesSQL();
        $indexesSQL = $this->tableBuilder->getIndexesSQL();
        $uniqueSQL = $this->tableBuilder->getUniqueSQL();
        return array_merge($sequencesSQL, $createSQL, $indexesSQL, $uniqueSQL);

    }

    public function dropTable(string $tableName) {

        // Removing indexes
        $indexesSQL = "SELECT indexname FROM pg_indexes WHERE schemaname = :schema and tablename = :table";
        $bindValues = [
            ["identifier"=>":schema", "value"=>$this->schema, "type"=>"varchar"],
            ["identifier"=>":table", "value"=>$tableName, "type"=>"varchar"]
        ];
        $this->driver->execute($indexesSQL, $bindValues);
        $dropIndexes = [];
        foreach($this->driver->fetchArray() as $item) {
            $dropIndexes[] = $this->dropIndex($tableName, $item["indexname"]);
        }

        // Removing sequences
        $sequencesSQL = "SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = :schema and sequence_name LIKE :sequence";
        $bindValues = [
            ["identifier"=>":schema", "value"=>$this->schema, "type"=>"varchar"],
            ["identifier"=>":sequence", "value"=>"seq_{$tableName}%", "type"=>"varchar"]
        ];
        $this->driver->execute($sequencesSQL, $bindValues);
        $dropSequences = [];
        foreach($this->driver->fetchArray() as $item) {
            $dropSequences[] = $this->dropSequence($tableName, $item["sequence_name"]);
        }
        
        // Removing constraints
        $constraintsSQL = "SELECT c.conname AS constraint_name ";
        $constraintsSQL.= "FROM pg_constraint as c ";
        $constraintsSQL.= "INNER JOIN pg_namespace as n ON c.connamespace = n.oid ";
        $constraintsSQL.= "WHERE c.conrelid::regclass::text = :table and n.nspname = :schema";
        $bindValues = [
            ["identifier"=>":schema", "value"=>$this->schema, "type"=>"varchar"],
            ["identifier"=>":table", "value"=>$tableName, "type"=>"varchar"]
        ];
        $this->driver->execute($constraintsSQL, $bindValues);
        $dropConstraints = [];
        foreach($this->driver->fetchArray() as $item) {
            $dropConstraints[] = $this->dropConstraint($tableName, $item["constraint_name"]);
        }

        $this->tableBuilder->setTable($tableName);

        // The order does matter: 
        // Contraints indexes and sequences could not be removed if the table exists
        // Constraints should be removed before indexes because a possible relationship
        $dropTableSQL = array_merge(
            [$this->tableBuilder->getDropTable()],
            $dropConstraints,
            $dropIndexes,
            $dropSequences
        );

        return $dropTableSQL;
    }
    
    public function dropSequence(string $tableName, string $sequenceName) {
        $this->tableBuilder->setTable($tableName);
        return $this->tableBuilder->getDropSequence($sequenceName);
    }

    public function dropIndex(string $tableName, string $indexName) {
        $this->tableBuilder->setTable($tableName);
        return $this->tableBuilder->getDropIndex($indexName);
    }

    public function dropConstraint(string $tableName, string $constraintName) {
        $this->tableBuilder->setTable($tableName);
        return $this->tableBuilder->getDropConstraint($constraintName);
    }

}

?>