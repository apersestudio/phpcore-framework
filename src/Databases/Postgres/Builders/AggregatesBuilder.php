<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

class AggregatesBuilder extends ABuilder implements IBuilder {

	public function getSQL():string {
		$sql = " ".implode(", ", $this->sqlFragments)." ";
		$this->clear();
		return $sql;
	}

	/* ================================================================================================ //
	// INDIVIDUAL DEFINITION
	// ================================================================================================ */

	private function aggregate(string $aggregate, string $column):self {
		$columnParts = $this->getAlias($column);
		$alias = isset($columnParts["alias"]) ? " AS {$columnParts["alias"]}" : "";
		$qualifiedColumn = $this->addSchemaAndTable($columnParts["name"], $this->model->getTableName(), $this->model->getSchema());
		$this->sqlFragments[] = "{$aggregate}({$qualifiedColumn}){$alias}";
		return $this;
	}

	public function avg(string $column):self {
		return $this->aggregate("AVG", $column);
	}
	public function min(string $column):self {
		return $this->aggregate("MIN", $column);
	}
	public function max(string $column):self {
		return $this->aggregate("MAX", $column);
	}
	public function sum(string $column):self {
		return $this->aggregate("SUM", $column);
	}
	public function count(string $column):self {
		return $this->aggregate("COUNT", $column);
	}

}

?>