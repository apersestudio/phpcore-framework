<?php

namespace PC\Databases\Postgres\Builders;

use PC\Databases\Model;
use PC\Databases\Postgres\Builders\WhereBuilder;
use PC\Interfaces\IBuilder;

use Closure;
use Exception;

class JoinBuilder extends WhereBuilder implements IBuilder {

	public function getSQL():string {
		$joinSQL = '';

		if (!empty($this->sqlFragments)) {
			foreach ($this->sqlFragments as $join) {
				$conjunction = isset($join["conjunction"]) ? $join["conjunction"]." " : "";
				$joinSQL .= $conjunction.$join["query"];
			}
		}

		$this->clear();
		return $joinSQL;
	}

	private function on(string $table, string $type) {

		// The relation could have an alias so we try to extract the alias name
		$relationParts = $this->getAlias($table);
		$relation = $this->model->getRelation($relationParts["name"]);
		if (is_null($relation)) {
			throw new Exception("You should add relation {$table} for model {$this->model->getTableName()}");
		}

		// Register the model
		if (!$this->model->hasRelation($table)) {
			/** @var Model $relationModel */
			$relationModel = new $relation["model"];
			$this->model->addRelationModel($table, $relationModel);
		} else {
			/** @var Model $relationModel */
			$relationModel = $this->model->getRelationModel($table);
		}

		// Add relation definition to the parent definition
		$this->model->addDefinition($relationModel);

		$foreignTable = isset($relationParts["alias"]) ? $relationParts["alias"] : $relationModel->getTableName();
		$foreignColumn =  $foreignTable.".".$relation["foreignKey"];
		$localColumn = $this->model->getTableName().".".$relation["localKey"];

		$types = ["INNER", "LEFT", "RIGHT", "FULL", "CROSS"];
		$type = !in_array($type, $types) ? "" : $type;

		$joinTable = $this->addSchema($relationModel->getTableName(), $relationModel->getSchema());
		$alias = isset($relationParts["alias"]) ? " AS {$relationParts["alias"]}" : "";
		$this->sqlFragments[] = ["query"=>" {$type} JOIN {$joinTable}{$alias} ON {$foreignColumn} = {$localColumn} "];
	}

	private function joinStatement(string $table, string $type, Closure $closure=null) {
		
		$this->on($table, $type);

		if (!empty($closure)) {

			$relationModel = $this->model->getRelationModel($table);
			$jBuilder = new Self($relationModel);
			$closure($jBuilder);

			// Add any possible bind value to the list of join values
			$this->bindValues = $jBuilder->getBindValues();

			// After the on sentence creates additional constraints similar to where (but not exactly the same)
			$query = str_replace(" WHERE ", " ", $jBuilder->getSQL());
			$this->sqlFragments[] = ["query"=>$query];

		}
		
		return $this;
	}

	public function join(string $table, Closure $closure=null) {
		return $this->joinStatement($table, "INNER", $closure);
	}

	public function leftJoin(string $table, Closure $closure=null) {
		return $this->joinStatement($table, "LEFT", $closure);
	}

	public function rightJoin(string $table, Closure $closure=null) {
		return $this->joinStatement($table, "RIGHT", $closure);
	}

	public function fullJoin(string $table, Closure $closure=null) {
		return $this->joinStatement($table, "FULL", $closure);
	}

}
?>