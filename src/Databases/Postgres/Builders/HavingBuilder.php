<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

use Exception;
use Closure;

class HavingBuilder extends ABuilder implements IBuilder {

    public function getSQL():string {
        $command = "";
        if ($this->hasFragments()) {
			$command .= " HAVING  ";
            foreach ($this->getFragments() as $fragment) {
                $command .= $fragment["conjunction"]." ".$fragment["query"];
            }
		}
        return $command;
    }

    // ===================================================================================================================================== //
	// STATEMENTS
	// ===================================================================================================================================== //

    /**
     * Creates a group of having sentences delimited by a pair of parenthesis
     * @param Closure $closure 
     * @param string $conjunction 
     * @return HavingBuilder 
     */
    private function havingGroupStatement(Closure $closure, string $conjunction=""):self {

		$subBuilder = new self($this->model);
		$closure($subBuilder);
		
		$bindValues = $subBuilder->getBindValues();
		$conjunction = $this->getConjunction($conjunction);
		$queryFragment = str_replace(" HAVING ", "(", $subBuilder->getSQL().")");

		$this->addFragment(["query"=>$queryFragment, "conjunction"=>$conjunction]);
		$this->mergeBindValues($bindValues);

		unset($subBuilder);
		return $this;
	}

    /**
     * Creates a having statement to filter the select after the grouping 
     * @param string $aggregate Possible values are "AVG", "MIN", "MAX", "SUM", "COUNT"
     * @param string $column The name of the column to compare the aggregate
     * @param string $comparison Possible values are ">", "=", "<", ">=", "<=", "<>"
     * @param mixed $value The value to compare against
     * @param string $conjunction Possible values are "AND", "OR"
     * @return HavingBuilder 
     * @throws Exception 
     */
    private function havingStatement(string $aggregate, string $column, string $comparison, mixed $value, string $conjunction=""):self {
		
		$column = $this->getColumn($column);
		$columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSchema());
        $valueBinded = $this->getBindIdentifier("having", $columnQualified, $value);
		$query = " {$aggregate}({$columnQualified}) {$comparison} {$valueBinded} ";
		
		$conjunction = $this->getConjunction($conjunction);
		$this->addFragment(["query"=>$query, "conjunction"=>$conjunction]);
		return $this;
	}

    // ===================================================================================================================================== //
	// GROUP
	// ===================================================================================================================================== //

    /**
     * Creates a group of having sentences delimited by a pair of parenthesis using the AND conjunction
     * @param Closure $closure 
     * @return HavingBuilder 
     */
    public function havingGroup(Closure $closure):self {
		return $this->havingGroupStatement($closure);
	}

    /**
     * Creates a group of having sentences delimited by a pair of parenthesis using the OR conjunction
     * @param Closure $closure 
     * @return HavingBuilder 
     */
	public function orHavingGroup(Closure $closure):self {
		return $this->havingGroupStatement($closure, "OR");
	}

    // ===================================================================================================================================== //
	// MAX
	// ===================================================================================================================================== //

    /**
     * Having max aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function maxGreaterThan(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, ">", $value); }
    /**
     * Having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function maxGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, ">=", $value); }
    /**
     * Or having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orMaxGreaterThan(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, ">", $value, "OR"); }
    /**
     * Having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orMaxGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, ">=", $value, "OR"); }
    /**
     * Having max aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function maxLowerThan(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, "<", $value); }
    /**
     * Having max aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function maxLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, "<=", $value); }
    /**
     * Or having max aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orMaxLowerThan(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, "<", $value, "OR"); }
    /**
     * Or having max aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orMaxLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, "<=", $value, "OR"); }

    /**
     * Having max aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function maxEqual(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, "=", $value); }
    /**
     * Or having max aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function orMaxEqual(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, "=", $value, "OR"); }
    /**
     * Having max aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function maxDifferent(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, "<>", $value); }
    /**
     * Having max aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orMaxDifferent(string $column, mixed $value):self { return $this->havingStatement("MAX", $column, "<>", $value, "OR"); }

    // ===================================================================================================================================== //
	// MIN
	// ===================================================================================================================================== //

    /**
     * Having min aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function minGreaterThan(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, ">", $value); }
    /**
     * Having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function minGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, ">=", $value); }
    /**
     * Or having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orMinGreaterThan(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, ">", $value, "OR"); }
    /**
     * Having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orMinGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, ">=", $value, "OR"); }
    /**
     * Having min aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function minLowerThan(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, "<", $value); }
    /**
     * Having min aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function minLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, "<=", $value); }
    /**
     * Or having min aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orMinLowerThan(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, "<", $value, "OR"); }
    /**
     * Or having min aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orMinLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, "<=", $value, "OR"); }

    /**
     * Having min aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function minEqual(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, "=", $value); }
    /**
     * Or having min aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function orMinEqual(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, "=", $value, "OR"); }
    /**
     * Having min aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function minDifferent(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, "<>", $value); }
    /**
     * Having min aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orMinDifferent(string $column, mixed $value):self { return $this->havingStatement("MIN", $column, "<>", $value, "OR"); }

    // ===================================================================================================================================== //
	// AVG
	// ===================================================================================================================================== //

    /**
     * Having avg aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function avgGreaterThan(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, ">", $value); }
    /**
     * Having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function avgGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, ">=", $value); }
    /**
     * Or having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orAvgGreaterThan(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, ">", $value, "OR"); }
    /**
     * Having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orAvgGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, ">=", $value, "OR"); }
    /**
     * Having avg aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function avgLowerThan(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, "<", $value); }
    /**
     * Having avg aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function avgLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, "<=", $value); }
    /**
     * Or having avg aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orAvgLowerThan(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, "<", $value, "OR"); }
    /**
     * Or having avg aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orAvgLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, "<=", $value, "OR"); }

    /**
     * Having avg aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function avgEqual(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, "=", $value); }
    /**
     * Or having avg aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function orAvgEqual(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, "=", $value, "OR"); }
    /**
     * Having avg aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function avgDifferent(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, "<>", $value); }
    /**
     * Having avg aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orAvgDifferent(string $column, mixed $value):self { return $this->havingStatement("AVG", $column, "<>", $value, "OR"); }

    // ===================================================================================================================================== //
	// SUM
	// ===================================================================================================================================== //

    /**
     * Having sum aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function sumGreaterThan(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, ">", $value); }
    /**
     * Having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function sumGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, ">=", $value); }
    /**
     * Or having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orSumGreaterThan(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, ">", $value, "OR"); }
    /**
     * Having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orSumGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, ">=", $value, "OR"); }
    /**
     * Having sum aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function sumLowerThan(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, "<", $value); }
    /**
     * Having sum aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function sumLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, "<=", $value); }
    /**
     * Or having sum aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orSumLowerThan(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, "<", $value, "OR"); }
    /**
     * Or having sum aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orSumLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, "<=", $value, "OR"); }

    /**
     * Having sum aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function sumEqual(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, "=", $value); }
    /**
     * Or having sum aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function orSumEqual(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, "=", $value, "OR"); }
    /**
     * Having sum aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function sumDifferent(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, "<>", $value); }
    /**
     * Having sum aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orSumDifferent(string $column, mixed $value):self { return $this->havingStatement("SUM", $column, "<>", $value, "OR"); }

    // ===================================================================================================================================== //
	// COUNT
	// ===================================================================================================================================== //

    /**
     * Having count aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function countGreaterThan(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, ">", $value); }
    /**
     * Having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function countGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, ">=", $value); }
    /**
     * Or having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orCountGreaterThan(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, ">", $value, "OR"); }
    /**
     * Having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orCountGreaterThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, ">=", $value, "OR"); }
    /**
     * Having count aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function countLowerThan(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, "<", $value); }
    /**
     * Having count aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function countLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, "<=", $value); }
    /**
     * Or having count aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orCountLowerThan(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, "<", $value, "OR"); }
    /**
     * Or having count aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function orCountLowerThanOrEqual(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, "<=", $value, "OR"); }

    /**
     * Having count aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function countEqual(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, "=", $value); }
    /**
     * Or having count aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
    public function orCountEqual(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, "=", $value, "OR"); }
    /**
     * Having count aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder 
     * @throws Exception 
     */
	public function countDifferent(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, "<>", $value); }
    /**
     * Having count aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return HavingBuilder
     * @throws Exception 
     */
	public function orCountDifferent(string $column, mixed $value):self { return $this->havingStatement("COUNT", $column, "<>", $value, "OR"); }

}

?>