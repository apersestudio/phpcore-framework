<?php

namespace PC\Databases\Postgres\Builders;

use Closure;
use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

class WhereBuilder extends ABuilder implements IBuilder {

	private array $whereComparisons = ["!=", ">=", "<=", "=", "<", ">", "<>"];

	public function getSQL():string {

		$whereSQL = '';
		if ($this->hasFragments()) {
			$whereSQL.= ' WHERE ';
			foreach ($this->getFragments() as $where) {
				$whereSQL .= $where["conjunction"]." ".$where["query"];
			}
		}
		
		$this->clear();
		return $whereSQL;
	}

	private function whereGroupStatement(Closure $closure, string $conjunction=""):self {

		$subBuilder = new self($this->model);
		$closure($subBuilder);
		
		$bindValues = $subBuilder->getBindValues();
		$conjunction = $this->getConjunction($conjunction);
		$queryFragment = str_replace(" WHERE ", "(", $subBuilder->getSQL().")");

		$this->addFragment(["query"=>$queryFragment, "conjunction"=>$conjunction]);
		$this->mergeBindValues($bindValues);

		unset($subBuilder);
		return $this;
	}

	public function whereGroup(Closure $closure):self {
		return $this->whereGroupStatement($closure);
	}

	public function orWhereGroup(Closure $closure):self {
		return $this->whereGroupStatement($closure, "OR");
	}
	
	private function whereStatement(string $column, string $comparison, mixed $value, string $conjunction=""):self {
		$validComparison = in_array($comparison, $this->whereComparisons);
		
		$comparison = $validComparison ? $comparison : "=";
		$column = $this->getColumn($column);
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSchema());
        $valueBinded = $this->getBindIdentifier("where", $columnQualified, $value);
		$query = " {$columnQualified} {$comparison} {$valueBinded} ";
		
		$conjunction = $this->getConjunction($conjunction);
		$this->addFragment(["query"=>$query, "conjunction"=>$conjunction]);
		return $this;
	}

	private function startsWithStatement(string $column, mixed $value, bool $not, string $conjunction=""):self {
		$NEGATION = ($not ? "NOT " : "");
		$column = $this->getColumn($column);
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSchema());
        $valueBinded = $this->getBindIdentifier("starts_with", $columnQualified, "$value%");
		$query = " LOWER({$columnQualified}) {$NEGATION}LIKE LOWER({$valueBinded}) ";

		$conjunction = $this->getConjunction($conjunction);
		$this->addFragment(["query"=>$query, "conjunction"=>$conjunction]);
		return $this;
	}

	private function endsWithStatement($column, $value, bool $not, string $conjunction=""):self {
		$NEGATION = ($not ? "NOT " : "");
		$column = $this->getColumn($column);
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSchema());
        $valueBinded = $this->getBindIdentifier("ends_with", $columnQualified, "%$value");
		$query = " LOWER({$columnQualified}) {$NEGATION}LIKE LOWER({$valueBinded}) ";

		$conjunction = $this->getConjunction($conjunction);
		$this->addFragment(["query"=>$query, "conjunction"=>$conjunction]);
		return $this;
	}

	private function containsStatement($column, $value, bool $not, string $conjunction=""):self {
		$NEGATION = ($not ? "NOT " : "");
		$column = $this->getColumn($column);
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSchema());
        $valueBinded = $this->getBindIdentifier("contains", $columnQualified, "%$value%");
		$query = " LOWER({$columnQualified}) {$NEGATION}LIKE LOWER({$valueBinded}) ";

		$conjunction = $this->getConjunction($conjunction);
		$this->addFragment(["query"=>$query, "conjunction"=>$conjunction]);
		return $this;
	}

	private function betweenStatement(string $column, mixed $startValue, mixed $endValue, bool $not, string $conjunction=""):self {
		$NEGATION = ($not ? "NOT " : "");
		$column = $this->getColumn($column);
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSchema());
        $valueBindedA = $this->getBindIdentifier("between_start", $columnQualified, $startValue);
        $valueBindedB = $this->getBindIdentifier("between_end", $columnQualified, $endValue);
		$query = " {$columnQualified} {$NEGATION}BETWEEN {$valueBindedA} AND {$valueBindedB} ";

		$conjunction = $this->getConjunction($conjunction);
		$this->addFragment(["query"=>$query, "conjunction"=>$conjunction]);
		return $this;
	}

	private function betweenColumnsStatement(string $startColumn, mixed $endColumn, mixed $value, bool $not, string $conjunction=""):self {
		$NEGATION = ($not ? "NOT " : "");
		$startColumn = $this->getColumn($startColumn);
		$startColumnQualified = $this->addSchemaAndTable($startColumn, $this->model->getTableName(), $this->model->getSchema());
		$endColumn = $this->getColumn($endColumn);
		$endColumnQualified = $this->addSchemaAndTable($endColumn, $this->model->getTableName(), $this->model->getSchema());
        $valueBinded = $this->getBindIdentifier("between_columns", $startColumnQualified, $value);
		$query = " {$valueBinded} {$NEGATION}BETWEEN {$startColumnQualified} AND {$endColumnQualified} ";

		$conjunction = $this->getConjunction($conjunction);
		$this->addFragment(["query"=>$query, "conjunction"=>$conjunction]);
		return $this;
	}

	public function where($column, $comparison, $value):self { return $this->whereStatement($column, $comparison, $value); }
	public function orWhere($column, $comparison, $value):self { return $this->whereStatement($column, $comparison, $value, "OR"); }

	public function startsWith($column, $value):self { return $this->startsWithStatement($column, $value, false); }
	public function notStartsWith($column, $value):self { return $this->startsWithStatement($column, $value, true); }
	public function orStartsWith($column, $value):self { return $this->startsWithStatement($column, $value, false, "OR"); }
	public function orNotStartsWith($column, $value):self { return $this->startsWithStatement($column, $value, true, "OR"); }

	public function endswith($column, $value):self { return $this->endsWithStatement($column, $value, false); }
	public function notEndsWith($column, $value):self { return $this->endsWithStatement($column, $value, true); }
	public function orEndsWith($column, $value):self { return $this->endsWithStatement($column, $value, false, "OR"); }
	public function orNotEndsWith($column, $value):self { return $this->endsWithStatement($column, $value, true, "OR"); }

	public function contains($column, $value):self { return $this->containsStatement($column, $value, false); }
	public function notContains($column, $value):self { return $this->containsStatement($column, $value, true); }
	public function orContains($column, $value):self { return $this->containsStatement($column, $value, false, "OR"); }
	public function orNotContains($column, $value):self { return $this->containsStatement($column, $value, true, "OR"); }
	
	public function between($column, $startValue, $endValue):self { return $this->betweenStatement($column, $startValue, $endValue, false); }
	public function notBetween($column, $startValue, $endValue):self { return $this->betweenStatement($column, $startValue, $endValue, true); }
	public function orBetween($column, $startValue, $endValue):self { return $this->betweenStatement($column, $startValue, $endValue, false, "OR"); }
	public function orNotBetween($column, $startValue, $endValue):self { return $this->betweenStatement($column, $startValue, $endValue, true, "OR"); }

	public function betweenColumns($starColumn, $endColumn, $value):self { return $this->betweenColumnsStatement($starColumn, $endColumn, $value, false); }
	public function notBetweenColumns($starColumn, $endColumn, $value):self { return $this->betweenColumnsStatement($starColumn, $endColumn, $value, true); }
	public function orBetweenColumns($starColumn, $endColumn, $value):self { return $this->betweenColumnsStatement($starColumn, $endColumn, $value, false, "OR"); }
	public function orNotBetweenColumns($starColumn, $endColumn, $value):self { return $this->betweenColumnsStatement($starColumn, $endColumn, $value, true, "OR"); }

	public function greaterThan($column, $value):self { return $this->whereStatement($column, ">", $value); }
	public function greaterThanOrEqual($column, $value):self { return $this->whereStatement($column, ">=", $value); }
	public function orGreaterThan($column, $value):self { return $this->whereStatement($column, ">", $value, "OR"); }
	public function orGreaterThanOrEqual($column, $value):self { return $this->whereStatement($column, ">=", $value, "OR"); }

	public function lowerThan($column, $value):self { return $this->whereStatement($column, "<", $value); }
	public function lowerThanOrEqual($column, $value):self { return $this->whereStatement($column, "<=", $value); }
	public function orLowerThan($column, $value):self { return $this->whereStatement($column, "<", $value, "OR"); }
	public function orLowerThanOrEqual($column, $value):self { return $this->whereStatement($column, "<=", $value, "OR"); }

	public function equal($column, $value):self { return $this->whereStatement($column, "=", $value); }
	public function different($column, $value):self { return $this->whereStatement($column, "<>", $value); }
	public function orEqual($column, $value):self { return $this->whereStatement($column, "=", $value, "OR"); }
	public function orDifferent($column, $value):self { return $this->whereStatement($column, "<>", $value, "OR"); }

}
?>