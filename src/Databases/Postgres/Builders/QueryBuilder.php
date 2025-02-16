<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;
use PC\Databases\Model;

use PC\Databases\Base\Builders\SelectBuilder;
use PC\Databases\Base\Builders\FromBuilder;
use PC\Databases\Base\Builders\WhereBuilder;
use PC\Databases\Base\Builders\JoinBuilder;
use PC\Databases\Base\Builders\GroupByBuilder;
use PC\Databases\Base\Builders\HavingBuilder;
use PC\Databases\Base\Builders\OrderByBuilder;
use PC\Databases\Base\Builders\InsertBuilder;
use PC\Databases\Base\Builders\UpdateBuilder;
use PC\Databases\Base\Builders\DeleteBuilder;
use PC\Databases\Base\Builders\PaginationBuilder;

use Closure;
use Exception;

class QueryBuilder extends ABuilder implements IBuilder {

	protected SelectBuilder $selectBuilder;

	protected FromBuilder $fromBuilder;

	protected WhereBuilder $whereBuilder;

	protected JoinBuilder $joinBuilder;

	protected GroupByBuilder $groupByBuilder;

	protected HavingBuilder $havingBuilder;

	protected OrderByBuilder $orderByBuilder;

	protected InsertBuilder $insertBuilder;

	protected UpdateBuilder $updateBuilder;

	protected DeleteBuilder $deleteBuilder;

	protected PaginationBuilder $paginationBuilder;

	protected string $from = "";

	protected string $limit = "";

	protected string $update = "";

	public function __construct(Model $model) {
		$this->model = $model;
		$this->fromBuilder = new FromBuilder($model);
		$this->selectBuilder = new SelectBuilder($model);
		$this->whereBuilder = new WhereBuilder($model);
		$this->joinBuilder = new JoinBuilder($model);
		$this->groupByBuilder = new GroupByBuilder($model);
		$this->havingBuilder = new HavingBuilder($model);
		$this->orderByBuilder = new OrderByBuilder($model);
		$this->insertBuilder = new InsertBuilder($model);
		$this->updateBuilder = new UpdateBuilder($model);
		$this->deleteBuilder = new DeleteBuilder($model);
		$this->paginationBuilder = new PaginationBuilder($model);
	}

	/**
	 * Get the bind values from all the builders.
	 * Once we call getSQL the bindValues get cleared.
	 * @return array 
	 */
	public function getBindValues():array {
		return array_merge(
			$this->bindValues,
			$this->selectBuilder->getBindValues(),
			$this->fromBuilder->getBindValues(),
			$this->joinBuilder->getBindValues(),
			$this->whereBuilder->getBindValues(),
			$this->groupByBuilder->getBindValues(),
			$this->havingBuilder->getBindValues(),
			$this->orderByBuilder->getBindValues(),
			$this->insertBuilder->getBindValues(),
			$this->updateBuilder->getBindValues(),
			$this->deleteBuilder->getBindValues(),
			$this->paginationBuilder->getBindValues()
		);
	}

	public function getSelectIndexes():array { 
		return $this->selectBuilder->getFragments();
	}

	public function getSQL():string {

		$command = "";

		/* THE ORDER OF THE BUILDERS MATTERS */
		$command .= $this->insertBuilder->getSQL();
		$command .= $this->deleteBuilder->getSQL();
		$command .= $this->updateBuilder->getSQL();
		$command .= $this->selectBuilder->getSQL();
		$command .= $this->fromBuilder->getSQL();
		$command .= $this->joinBuilder->getSQL();
		$command .= $this->whereBuilder->getSQL();
		$command .= $this->groupByBuilder->getSQL();
		$command .= $this->havingBuilder->getSQL();
		$command .= $this->orderByBuilder->getSQL();
		$command .= $this->paginationBuilder->getSQL();
		
		$this->clear();

		return trim($command);
	}

	// ===================================================================================================================================== //
	// INSERT
	// ===================================================================================================================================== //

	public function insert(array $insertData):self {

		$this->insertBuilder->insert($insertData);

		return $this;
	}

	public function insertMultiple(array $insertData):self {

		$this->insertBuilder->insertMultiple($insertData);

		// The group of inserts
		return $this;
	}

	// ===================================================================================================================================== //
	// UPDATE
	// ===================================================================================================================================== //

	public function update(array $updateData):self {
		$this->updateBuilder->update($updateData);
		return $this;
	}

	// ===================================================================================================================================== //
	// FROM BUILDER MAPPING
	// ===================================================================================================================================== //
	public function fromQuery(Closure $closure, string $alias):self {
		$this->fromBuilder->query($closure, $alias);
		return $this;
	}

	public function fromTable(string $table):self {
		$this->fromBuilder->table($table);
		return $this;
	}

	// ===================================================================================================================================== //
	// SELECT BUILDER MAPPING
	// ===================================================================================================================================== //

	public function select(array $columns=["*"]):self {
		$this->selectBuilder->select($columns);
		return $this;
	}

	public function avg(string $column):self {
		$this->selectBuilder->avg($column);
		return $this;
	}

	public function sum(string $column):self {
		$this->selectBuilder->sum($column);
		return $this;
	}

	public function count(string $column):self {
		$this->selectBuilder->count($column);
		return $this;
	}

	public function min(string $column):self {
		$this->selectBuilder->min($column);
		return $this;
	}

	public function max(string $column):self {
		$this->selectBuilder->max($column);
		return $this;
	}

	// ===================================================================================================================================== //
	// WHERE BUILDER MAPPING
	// ===================================================================================================================================== //

	public function whereGroup(Closure $closure):self {
		$this->whereBuilder->whereGroup($closure);
		return $this;
	}

	public function orWhereGroup(Closure $closure):self {
		$this->whereBuilder->orWhereGroup($closure);
		return $this;
	}

	public function where($column, $comparison, $value):self {
		$this->whereBuilder->where($column, $comparison, $value);
		return $this;
	}
	public function orWhere($column, $comparison, $value):self {
		$this->whereBuilder->orWhere($column, $comparison, $value);
		return $this;
	}
	public function startsWith($column, $value):self {
		$this->whereBuilder->startsWith($column, $value);
		return $this;
	}
	public function notStartsWith($column, $value):self {
		$this->whereBuilder->notStartsWith($column, $value);
		return $this;
	}
	public function orStartsWith($column, $value):self {
		$this->whereBuilder->orStartsWith($column, $value);
		return $this;
	}
	public function orNotStartsWith($column, $value):self {
		$this->whereBuilder->orNotStartsWith($column, $value);
		return $this;
	}
	public function endsWith($column, $value):self {
		$this->whereBuilder->endsWith($column, $value);
		return $this;
	}
	public function notEndsWith($column, $value):self {
		$this->whereBuilder->notEndsWith($column, $value);
		return $this;
	}
	public function orEndsWith($column, $value):self {
		$this->whereBuilder->orEndsWith($column, $value);
		return $this;
	}
	public function orNotEndsWith($column, $value):self {
		$this->whereBuilder->orNotEndsWith($column, $value);
		return $this;
	}
	public function contains($column, $value):self {
		$this->whereBuilder->contains($column, $value);
		return $this;
	}
	public function notContains($column, $value):self {
		$this->whereBuilder->notContains($column, $value);
		return $this;
	}
	public function orContains($column, $value):self {
		$this->whereBuilder->orContains($column, $value);
		return $this;
	}
	public function orNotContains($column, $value):self {
		$this->whereBuilder->orNotContains($column, $value);
		return $this;
	}
	public function between($column, $startValue, $endValue):self {
		$this->whereBuilder->between($column, $startValue, $endValue);
		return $this;
	}
	public function notBetween($column, $startValue, $endValue):self {
		$this->whereBuilder->notBetween($column, $startValue, $endValue);
		return $this;
	}
	public function orBetween($column, $startValue, $endValue):self {
		$this->whereBuilder->orBetween($column, $startValue, $endValue);
		return $this;
	}
	public function orNotBetween($column, $startValue, $endValue):self {
		$this->whereBuilder->orNotBetween($column, $startValue, $endValue);
		return $this;
	}
	public function betweenColumns($starColumn, $endColumn, $value):self {
		$this->whereBuilder->betweenColumns($starColumn, $endColumn, $value);
		return $this;
	}
	public function notBetweenColumns($starColumn, $endColumn, $value):self {
		$this->whereBuilder->notBetweenColumns($starColumn, $endColumn, $value);
		return $this;
	}
	public function orBetweenColumns($starColumn, $endColumn, $value):self {
		$this->whereBuilder->orBetweenColumns($starColumn, $endColumn, $value);
		return $this;
	}
	public function orNotBetweenColumns($starColumn, $endColumn, $value):self {
		$this->whereBuilder->orNotBetweenColumns($starColumn, $endColumn, $value);
		return $this;
	}

	public function greaterThan($column, $value):self { $this->whereBuilder->greaterThan($column, $value); return $this; }
	public function greaterThanOrEqual($column, $value):self { $this->whereBuilder->greaterThanOrEqual($column, $value); return $this; }
	public function orGreaterThan($column, $value):self { $this->whereBuilder->orGreaterThan($column, $value, "OR"); return $this; }
	public function orGreaterThanOrEqual($column, $value):self { $this->whereBuilder->orGreaterThanOrEqual($column, $value, "OR"); return $this; }

	public function lowerThan($column, $value):self { $this->whereBuilder->lowerThan($column, $value); return $this; }
	public function lowerThanOrEqual($column, $value):self { $this->whereBuilder->lowerThanOrEqual($column, $value); return $this; }
	public function orLowerThan($column, $value):self { $this->whereBuilder->orLowerThan($column, $value, "OR"); return $this; }
	public function orLowerThanOrEqual($column, $value):self { $this->whereBuilder->orLowerThanOrEqual($column, $value, "OR"); return $this; }

	public function equal($column, $value):self { $this->whereBuilder->equal($column, $value); return $this; }
	public function different($column, $value):self { $this->whereBuilder->different($column, $value); return $this; }
	public function orEqual($column, $value):self { $this->whereBuilder->orEqual($column, $value, "OR"); return $this; }
	public function orDifferent($column, $value):self { $this->whereBuilder->orDifferent($column, $value, "OR"); return $this; }

	// ===================================================================================================================================== //
	// JOIN BUILDER MAPPING
	// ===================================================================================================================================== //

	public function join($relation, Closure $closure=null):self {
		$this->joinBuilder->join($relation, $closure);
		return $this;
	}

	public function leftJoin($relation, Closure $closure=null):self {
		$this->joinBuilder->leftJoin($relation, $closure);
		return $this;
	}

	public function rightJoin($relation, Closure $closure=null):self {
		$this->joinBuilder->rightJoin($relation, $closure);
		return $this;
	}

	public function fullJoin($relation, Closure $closure=null):self {
		$this->joinBuilder->fullJoin($relation, $closure);
		return $this;
	}

	// ===================================================================================================================================== //
	// GROUP BY BUILDER MAPPING
	// ===================================================================================================================================== //
	public function groupBy(string $column): self {
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
		$this->groupByBuilder->groupBy($columnQualified);
		return $this;
	}

	public function groupByLeft(string $column, int $length): self {
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
		$this->groupByBuilder->groupByLeft($columnQualified, $length);
		return $this;
	}

	public function groupByRight(string $column, int $length): self {
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
		$this->groupByBuilder->groupByRight($columnQualified, $length);
		return $this;
	}

	public function groupByUpper(string $column): self {
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
		$this->groupByBuilder->groupByUpper($columnQualified);
		return $this;
	}

	public function groupByLower(string $column): self {
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
		$this->groupByBuilder->groupByLower($columnQualified);
		return $this;
	}

	public function groupByLength(string $column): self {
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
		$this->groupByBuilder->groupByLength($columnQualified);
		return $this;
	}

	public function groupByTrim(string $column): self {
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
		$this->groupByBuilder->groupByTrim($columnQualified);
		return $this;
	}

	// ===================================================================================================================================== //
	// DELETE BUILDER MAPPING
	// ===================================================================================================================================== //
	public function delete():self {
		$this->deleteBuilder->delete();
		return $this;
	}

	/* ================================================================================================================================================================================================ //
    // HAVING / START
    // ================================================================================================================================================================================================ */

	/**
     * Having max aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMaxGreaterThan(string $column, mixed $value):self { $this->havingBuilder->maxGreaterThan($column, $value); return $this; }
	/**
     * Having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMaxGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->maxGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMaxGreaterThan(string $column, mixed $value):self { $this->havingBuilder->orMaxGreaterThan($column, $value); return $this; }
	/**
     * Having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMaxGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orMaxGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having max aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMaxLowerThan(string $column, mixed $value):self { $this->havingBuilder->maxLowerThan($column, $value); return $this; }
	/**
     * Having max aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMaxLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->maxLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having max aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMaxLowerThan(string $column, mixed $value):self { $this->havingBuilder->orMaxLowerThan($column, $value); return $this; }
	/**
     * Or having max aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMaxLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orMaxLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having max aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMaxEqual(string $column, mixed $value):self { $this->havingBuilder->maxEqual($column, $value); return $this; }
	/**
     * Or having max aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMaxEqual(string $column, mixed $value):self { $this->havingBuilder->orMaxEqual($column, $value); return $this; }
	/**
     * Having max aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMaxDifferent(string $column, mixed $value):self { $this->havingBuilder->maxDifferent($column, $value); return $this; }
	/**
     * Having max aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orHavingMaxDifferent(string $column, mixed $value):self { $this->havingBuilder->orMaxDifferent($column, $value); return $this; }

	/**
     * Having min aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMinGreaterThan(string $column, mixed $value):self { $this->havingBuilder->minGreaterThan($column, $value); return $this; }
	/**
     * Having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMinGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->minGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMinGreaterThan(string $column, mixed $value):self { $this->havingBuilder->orMinGreaterThan($column, $value); return $this; }
	/**
     * Having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMinGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orMinGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having min aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMinLowerThan(string $column, mixed $value):self { $this->havingBuilder->minLowerThan($column, $value); return $this; }
	/**
     * Having min aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMinLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->minLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having min aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMinLowerThan(string $column, mixed $value):self { $this->havingBuilder->orMinLowerThan($column, $value); return $this; }
	/**
     * Or having min aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMinLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orMinLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having min aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMinEqual(string $column, mixed $value):self { $this->havingBuilder->minEqual($column, $value); return $this; }
	/**
     * Or having min aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingMinEqual(string $column, mixed $value):self { $this->havingBuilder->orMinEqual($column, $value); return $this; }
	/**
     * Having min aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingMinDifferent(string $column, mixed $value):self { $this->havingBuilder->minDifferent($column, $value); return $this; }
	/**
     * Having min aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orHavingMinDifferent(string $column, mixed $value):self { $this->havingBuilder->orMinDifferent($column, $value); return $this; }

	/**
     * Having avg aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingAvgGreaterThan(string $column, mixed $value):self { $this->havingBuilder->avgGreaterThan($column, $value); return $this; }
	/**
     * Having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingAvgGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->avgGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingAvgGreaterThan(string $column, mixed $value):self { $this->havingBuilder->orAvgGreaterThan($column, $value); return $this; }
	/**
     * Having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingAvgGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orAvgGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having avg aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingAvgLowerThan(string $column, mixed $value):self { $this->havingBuilder->avgLowerThan($column, $value); return $this; }
	/**
     * Having avg aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingAvgLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->avgLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having avg aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingAvgLowerThan(string $column, mixed $value):self { $this->havingBuilder->orAvgLowerThan($column, $value); return $this; }
	/**
     * Or having avg aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingAvgLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orAvgLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having avg aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingAvgEqual(string $column, mixed $value):self { $this->havingBuilder->avgEqual($column, $value); return $this; }
	/**
     * Or having avg aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingAvgEqual(string $column, mixed $value):self { $this->havingBuilder->orAvgEqual($column, $value); return $this; }
	/**
     * Having avg aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingAvgDifferent(string $column, mixed $value):self { $this->havingBuilder->avgDifferent($column, $value); return $this; }
	/**
     * Having avg aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orHavingAvgDifferent(string $column, mixed $value):self { $this->havingBuilder->orAvgDifferent($column, $value); return $this; }

	/**
     * Having sum aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingSumGreaterThan(string $column, mixed $value):self { $this->havingBuilder->sumGreaterThan($column, $value); return $this; }
	/**
     * Having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingSumGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->sumGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingSumGreaterThan(string $column, mixed $value):self { $this->havingBuilder->orSumGreaterThan($column, $value); return $this; }
	/**
     * Having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingSumGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orSumGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having sum aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingSumLowerThan(string $column, mixed $value):self { $this->havingBuilder->sumLowerThan($column, $value); return $this; }
	/**
     * Having sum aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingSumLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->sumLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having sum aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingSumLowerThan(string $column, mixed $value):self { $this->havingBuilder->orSumLowerThan($column, $value); return $this; }
	/**
     * Or having sum aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingSumLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orSumLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having sum aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingSumEqual(string $column, mixed $value):self { $this->havingBuilder->sumEqual($column, $value); return $this; }
	/**
     * Or having sum aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingSumEqual(string $column, mixed $value):self { $this->havingBuilder->orSumEqual($column, $value); return $this; }
	/**
     * Having sum aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingSumDifferent(string $column, mixed $value):self { $this->havingBuilder->sumDifferent($column, $value); return $this; }
	/**
     * Having sum aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orHavingSumDifferent(string $column, mixed $value):self { $this->havingBuilder->orSumDifferent($column, $value); return $this; }

	/**
     * Having count aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingCountGreaterThan(string $column, mixed $value):self { $this->havingBuilder->countGreaterThan($column, $value); return $this; }
	/**
     * Having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingCountGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->countGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingCountGreaterThan(string $column, mixed $value):self { $this->havingBuilder->orCountGreaterThan($column, $value); return $this; }
	/**
     * Having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingCountGreaterThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orCountGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having count aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingCountLowerThan(string $column, mixed $value):self { $this->havingBuilder->countLowerThan($column, $value); return $this; }
	/**
     * Having count aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingCountLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->countLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having count aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingCountLowerThan(string $column, mixed $value):self { $this->havingBuilder->orCountLowerThan($column, $value); return $this; }
	/**
     * Or having count aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingCountLowerThanOrEqual(string $column, mixed $value):self { $this->havingBuilder->orCountLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having count aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingCountEqual(string $column, mixed $value):self { $this->havingBuilder->countEqual($column, $value); return $this; }
	/**
     * Or having count aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orHavingCountEqual(string $column, mixed $value):self { $this->havingBuilder->orCountEqual($column, $value); return $this; }
	/**
     * Having count aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function havingCountDifferent(string $column, mixed $value):self { $this->havingBuilder->countDifferent($column, $value); return $this; }
	/**
     * Having count aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orHavingCountDifferent(string $column, mixed $value):self { $this->havingBuilder->orCountDifferent($column, $value); return $this; }

	/**
     * Creates a group of having sentences delimited by a pair of parenthesis using the AND conjunction
     * @param Closure $closure 
     * @return HavingBuilder 
     */
	public function havingGroup(Closure $closure):self {
		$this->havingBuilder->havingGroup($closure);
		return $this;
	}

	/**
     * Creates a group of having sentences delimited by a pair of parenthesis using the OR conjunction
     * @param Closure $closure 
     * @return HavingBuilder 
     */
	public function orHavingGroup(Closure $closure):self {
		$this->havingBuilder->orHavingGroup($closure);
		return $this;
	}

	/* ================================================================================================================================================================================================ //
    // HAVING / END
    // ================================================================================================================================================================================================ */

	/* ================================================================================================================================================================================================ //
    // ORDER BY / START
    // ================================================================================================================================================================================================ */

	public function orderByAsc(string $column):self {
		$this->orderByBuilder->orderByAsc($column);
		return $this;
	}

	public function orderByDesc(string $column):self {
		$this->orderByBuilder->orderByDesc($column);
		return $this;
	}

	/* ================================================================================================================================================================================================ //
    // ORDER BY / END
    // ================================================================================================================================================================================================ */

	/* ================================================================================================================================================================================================ //
    // LIMIT-OFFSET / START
    // ================================================================================================================================================================================================ */
	public function limit(int $rows):self {
		$this->paginationBuilder->limit($rows);
		return $this;
	}
	public function offset(int $skipRows):self {
		$this->paginationBuilder->offset($skipRows);
		return $this;
	}
	/* ================================================================================================================================================================================================ //
    // LIMIT-OFFSET / END
    // ================================================================================================================================================================================================ */

}

?>