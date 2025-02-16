<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

class UpdateBuilder extends ABuilder implements IBuilder {

    public function getSQL():string {

        $updateSQL = "";
        if ($this->hasFragments()) {
            $updateSQL .= implode("", $this->getFragments());
        }

        $this->clear();
        return $updateSQL;
    }

    /**
	 * Builds an update query without the where part
	 * @param array $columns 
	 * @return QueryBuilder 
	 */
	public function update(array $updateData):self {

		/** The name of the table to insert the data */
		$qualifiedTable = $this->addSchema($this->model->getTableName(), $this->model->getSchema());

		$sets = array();
		foreach ($updateData as $column=>$value) {
			$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
			$valueBinded = $this->getBindIdentifier("update", $columnQualified, $value);
			$sets[] = "{$column} = {$valueBinded}";
		}

		$values = implode(", ", $sets);
		$sqlFragment = "UPDATE {$qualifiedTable} SET {$values}";
		
		$this->addFragment($sqlFragment);
		return $this;
	}

}