<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

use Closure;
use Exception;

class PostgresDeleteBuilderFromBuilder extends ABuilder implements IBuilder {

    /**
     * Builds the SQL generated and clears the previous fragments
     * @return string 
     */
    public function getSQL():string {
        
        $fromSQL = '';
		if ($this->hasFragments()) {
			$fromSQL .= ' FROM '.$this->sqlFragments[0];
		}
		
		$this->clear();
		return $fromSQL;
    }

    /**
     * Sends a query builder to the closure and takes the generated SQL as the source of the from statement.
     * @param Closure $closure 
     * @param string $alias 
     * @return void 
     * @throws Exception 
     */
    public function query(Closure $closure, string $alias):void {
        $queryBuilder = new QueryBuilder($this->model);
        $closure($queryBuilder);

        $queryBindValues = $queryBuilder->getBindValues();
        $querySQL = $queryBuilder->getSQL();

        $aliasParts = $this->getFromAlias($alias);
        $queryAlias = isset($aliasParts["alias"]) ? " AS ".$aliasParts["alias"] : "";

		$queryFragment = "({$querySQL}){$queryAlias}";
		$queryBuilder->clear();

		$this->addFragment($queryFragment);
		$this->addBindValues($queryBindValues);

        /** Performance best practice */
        unset($queryBuilder);
    }

    /**
     * Uses the given table as the source of the from statement.
     * It accepts an alias (table as t).
     * @param string $table 
     * @return void 
     * @throws Exception 
     */
    public function table(string $table):void {
        $tableParts = $this->getAlias($table);
		$alias = isset($tableParts["alias"]) ? " AS {$tableParts["alias"]}" : "";
        $qualified = $this->addSchema($tableParts["name"], $this->model->getSchema());
        $queryFragment = "{$qualified}{$alias}";
        $this->addFragment($queryFragment);
    }

}