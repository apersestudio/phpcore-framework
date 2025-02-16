<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

class InsertBuilder extends ABuilder implements IBuilder {

    public function getSQL():string {

        $inserts = "";
        if ($this->hasFragments()) {
            $inserts = implode("; ", $this->getFragments()).";";
        }

        $this->clear();
        return $inserts;
    }

    public function insert(array $insertData):self {

		$columns = array_keys($insertData);
		$rowData = array_values($insertData);

		/** The name of the table to insert the data */
		$qualifiedTable = $this->addSchema($this->model->getTableName(), $this->model->getSchema());
		
		/** Fields which are to receive data */
		$columnsList = implode(", ", $columns);
		
		/** List of qualified column names to bind the values to */
		$columnsQualified = $this->addSchemaAndTableMultiple($columns, $this->model->getTableName(), $this->model->getSChema());
		
		// Prevent SQL Injection by using bindValues
		$values = [];
		foreach ($rowData as $index=>$value) {
			$columnQualified = $columnsQualified[$index];
			$valueBinded = $this->getBindIdentifier("insert", $columnQualified, $value);
			$values[] = $valueBinded;
		}
		$valuesList = implode(", ", $values);
			
		// This is what's gonna be inserted
		$this->addFragment("INSERT INTO {$qualifiedTable} ({$columnsList}) VALUES ({$valuesList})");
		
		return $this;
	}

	public function insertMultiple(array $insertData):self {
        
        // Nothing to insert
        if (count($insertData) === 0) { return ""; }

        // Extract column names from first element
        $columns = array_keys($insertData[0]);

		/** List of qualified column names to bind the values to */
        $columnsQualified = [];
        foreach ($columns as $column) {
            $columnsQualified[$column] = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
        }
		
		/** Intead of create multiple insert, create just one to boost the performance */
		$valuesData = [];

		foreach ($insertData as $rowData) {

			// Prevent SQL Injection by using bindValues
			$values = [];
			foreach ($rowData as $column=>$value) {
				$columnQualified = $columnsQualified[$column];
				$valueBinded = $this->getBindIdentifier("insert", $columnQualified, $value);
				$values[] = $valueBinded;
			}
			
			$valuesData[] = "(".implode(",", $values).")";
		}

        /** The name of the table to insert the data */
		$qualifiedTable = $this->addSchema($this->model->getTableName(), $this->model->getSchema());
		
		/** Fields which are to receive data (can't be qualified) */
		$columnsList = implode(", ", $columns);

        /** And array of values in SQL format */
        $valuesList = implode(", ", $valuesData);
        $this->addFragment("INSERT INTO {$qualifiedTable} ({$columnsList}) VALUES {$valuesList}");

        return $this;

	}

}