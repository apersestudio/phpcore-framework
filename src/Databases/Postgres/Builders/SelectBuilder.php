<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;
use PC\Databases\Model;

use Closure;
use Exception;

class SelectBuilder extends ABuilder implements IBuilder {

    protected AggregatesBuilder $aggregatesBuilder;

    public function __construct(Model $model) {
        $this->model = $model;
        $this->aggregatesBuilder = new AggregatesBuilder($this->model);
    }

    public function getSQL(): string {
        
        $selectSQL = "";
        if ($this->hasFragments()) {
            $selectSQL = "SELECT " . implode(", ", $this->sqlFragments) . " ";
        }

        $this->clear();
        return $selectSQL;
    }

    private function selectStatement(string $column):self {
        $columnParts = $this->getAlias($column);
        $alias = isset($columnParts["alias"]) ? " AS {$columnParts["alias"]}" : "";
        $this->sqlFragments[] = $columnParts["name"].$alias;
        return $this;
    }

    public function select(array $columns):self {

        // Select everything from the model and its relationships
        if (count($columns) === 1 && $columns[0] === "*.*") {
            $relations = [$this->model->getTableName().".*"];
            foreach (array_keys($this->model->getRelations()) as $relation) {
                $relations[] = $relation.".*";
            }
            $columns = $relations;
        }
        
        $columnsList = [];
        foreach ($columns as $column) {
            $columnParts = explode(".", $column);
            $totalParts = count($columnParts);

            // Schema.Table.Column
            if ($totalParts === 3) {
                $model = $columnParts[1];
                $colName = $columnParts[2];
            // Table.Column
            } else if ($totalParts === 2) {
                $model = $columnParts[0];
                $colName = $columnParts[1];
            // Column
            } else if ($totalParts === 1) {
                $model = $this->model->getTableName();
                $colName = $column;
            } else {
                throw new Exception("Bad column definition");
            }

            // The select is on the model
            if ($model === $this->model->getTableName()) {
                // Select every column on the model
                if ($colName === "*") {
                    $modelDefinition = array_keys($this->model->getDefinition());
                    $qualifiedColumns = $this->addSchemaAndTableMultiple($modelDefinition, $this->model->getTableName(), $this->model->getSchema());
                    $columnsList = array_merge($columnsList, $qualifiedColumns);
                // Select a specific column on the model
                } else {
                    $qualifiedModel = $this->addSchema($model, $this->model->getSchema());
                    $columnsList[] = "{$qualifiedModel}.{$colName}";
                }
            // The select is on a relationship
            } else if ($model !== $this->model->getTableName()) {
                $relationModel = $this->model->getRelationModel($model);
                // Select every column on the relationship
                if ($colName === "*") {
                    $relationRefinition = array_keys($relationModel->getDefinition());
                    $qualifiedColumns = $this->addSchemaAndTableMultiple($relationRefinition, $relationModel->getTableName(), $relationModel->getSchema());
                    $columnsList = array_merge($columnsList, $qualifiedColumns);
                } else {
                    $qualifiedRelation = $this->addSchema($relationModel->getTableName(), $relationModel->getSchema());
                    $columnsList[] = "{$qualifiedRelation}.{$colName}";
                }
            }
        }

        foreach ($columnsList as $column) {
            $this->selectStatement($column);
        }

        return $this;
    }

    private function aggregate(string $aggregate, string $column) {
        $this->aggregatesBuilder->clear();
        $this->aggregatesBuilder->$aggregate($column);

        $aggBindValues = $this->aggregatesBuilder->getBindValues();
        $aggFragments = $this->aggregatesBuilder->getFragments();

        $this->addBindValues($aggBindValues);
        $this->addFragments($aggFragments);
        return $this;
    }

    public function avg($column) {
        return $this->aggregate("avg", $column);
    }

    public function min($column) {
        return $this->aggregate("min", $column);
    }

    public function max($column) {
        return $this->aggregate("max", $column);
    }

    public function sum($column) {
        return $this->aggregate("sum", $column);
    }

    public function count($column) {
        return $this->aggregate("count", $column);
    }

}

?>