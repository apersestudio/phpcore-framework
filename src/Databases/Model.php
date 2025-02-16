<?php

namespace PC\Databases;

use PC\Abstracts\ADatabaseDriver;
use PC\Databases\Base\Builders\QueryBuilder;
use PC\Traits\DatabaseDriverTrait;

use Closure;
use Exception;
use ReflectionClass;

class Model {

    use DatabaseDriverTrait;

    // Connection is the name of the database config entry where parameter to connect are defined
    protected $connection;

    // Some models have schemas which are like container between the database and the tables
    protected $schema = false;

    // Where all the model information will be stored
    protected $data;

    /** Filters are the parts of the where clause **/
    private $filters;

    /** The last command being executed **/
    private $command;

    /** Some models have sequences such as PostgreSQL or Oracle **/
    protected $sequence;

    /** Doesn't need explanation :P **/
    protected $primaryKey;

    /* TableName Without schema */
    protected $table;

    /** Where we define the relations with other models **/
    protected $relations;

    /** Once we make a relation we store it here, to avoid overloading **/
    protected $relationModels;

    /** Variable to give back the name of the model's child **/
    protected $className;

    /** Contains the column names and datatypes of the model **/
    protected $definition = [];

    /** Contains the column names and datatypes of the model and its relationships **/
    protected $fullDefinition = [];

    protected QueryBuilder $queryBuilder;

    protected ADatabaseDriver $driver;

    /** When working in debugMode this variables holds the different execution history */
    protected array $sql = [];

    protected bool $debugMode = false;

    protected bool $hasDelete = false;
    protected bool $forceDelete = false;

    protected bool $hasUpdate = false;
    protected bool $forceUpdate = false;
    
    protected bool $hasWhere = false;

    public function __construct() {
        $this->queryBuilder = new QueryBuilder($this);
        $this->className = strtolower(str_replace("Model", "", (new ReflectionClass($this))->getShortName()));
        
        /** Takes the connection name to extract information from the databases config file */
        $this->driver = self::getDatabaseDriver($this->connection);

        // Update the definition to be qualified
        $this->addDefinition($this);
    }

    /**
     * If setted to true, all SQL statements would not be executed 
     * and an array of SQL sentences would be created instead with its real values
     * Use this feature with care to avoid filtering sensitive information
     * @param bool $debugMode 
     * @return Model 
     */
    public function setDebugMode(bool $debugMode):self {
        $this->debugMode = $debugMode;
        return $this;
    }

    /**
     * Adds a sql sentence to the list only available when debugMode is true
     * @param string $command 
     * @param array $bindValues 
     * @return void 
     */
    private function addSQL(string $command, array $bindValues) {
        foreach ($bindValues as $item) {
            $value = ($item["type"] === "string") ? "'".$item["value"]."'" : $item["value"];
            $command = str_replace($item["identifier"], $value, $command);
        }
        $cleanedSQL = $this->cleanSQL($command);
        $this->sql[] = $cleanedSQL;
    }

    /**
     * Returns the list of sql sentences created by the model when debugMode is true
     * Is debugMode is false the returned array will be empty
     * @return array 
     */
    public function getSQL():array {
        $sql = $this->debugMode === true ? $this->sql : [];
        $this->sql = [];
        return $sql;
    }

    /**
     * Cleans a SQL sentences removing unnecessary spaces and newlines from the query
     * It's useful for comparing SQL queries builded for unit testing
     * @param string $sql
     * @return string 
     */
    public function cleanSQL(string $sql):string {
        return preg_replace("/\s+/", " ", trim($sql));
    }

    /**
     * Get the fields definition only for this model without its relationships
     * @return array 
     */
    public function getDefinition():array {
        return $this->definition;
    }

    /**
     * Get the fields definition for this model and its relationships
     * @return array 
     */
    public function getFullDefinition():array {
        return $this->fullDefinition;
    }

    /**
     * Adds the definition of the given model to this model
     * @param Model $model 
     * @return void 
     */
    public function addDefinition(Model $model):void {
        $table = $model->getTableName();
        $definition = $model->getDefinition();
        foreach ($definition as $column=>$type) {
            $qualified = $this->queryBuilder->addSchemaAndTable($column, $table, $model->getSchema());
            $this->fullDefinition[$qualified] = $type;
        }
    }

    /**
     * Gets the datatype from the full definition based on the qualified column name
     * @param string $qualified 
     * @return string 
     */
    public function getDefinitionType(string $qualified):string {
        return $this->fullDefinition[$qualified];
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // RELATIONS
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * Checks if the model has a relation definition with the given table name
     * @param string $table 
     * @return bool 
     */
    public function hasRelation(string $table):bool {
        return isset($this->relationModels[$table]);
    }

    /**
     * Adds a model relation to the list of relations for the model with the given table name
     * @param string $table 
     * @param Model $model 
     * @return void 
     */
    public function addRelationModel(string $table, Model $model) {
        $this->relationModels[$table] = $model;
    }

    /**
     * Get the relation definition for the given table name
     * @param mixed $relation 
     * @return array|null 
     */
    public function getRelation(string $table):array|null {
        return $this->relations[$table] ?? null;
    }

    /**
     * Get the relation definition for all of the model relationships
     * @return array 
     */
    public function getRelations():array {
        return $this->relations;
    }
    
    /**
     * Get the model of a relationship for the given table name
     * @param string $table 
     * @return Model|null 
     */
    public function getRelationModel(string $table):Model|null {
        return $this->relationModels[$table] ?? null;
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // SCHEMA
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * Updates the schema for this model
     * @param bool $schema 
     * @return void 
     */
    public function setSchema($schema=false):void {
        $this->schema = $schema;
    }

    /**
     * Gets the schema for this model
     * @return string 
     */
    public function getSchema():string {
        return $this->schema === false ? "" : $this->schema;
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // TABLE
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * Gets the table name for this model or if it's empty returns the class name
     * @return string 
     */
    public function getTableName():string {
        return empty($this->table) ? strtolower($this->className) : $this->table;
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // FROM BUILDER
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * Sets a table as the source data for the from statement.
     * It accepts an alias (table as t).
     * @param string $table 
     * @return Model 
     * @throws Exception 
     */
    public function from(string $table):self {
        $this->queryBuilder->fromTable($table);
        return $this;
    }

    /**
     * Accepts a closure which would receive as first parameter a query builder.
     * Use this builder to create a source data for the from statement.
     * @param Closure $closure 
     * @param string $alias 
     * @return Model 
     * @throws Exception 
     */
    public function fromQuery(Closure $closure, string $alias):self {
        $this->queryBuilder->fromQuery($closure, $alias);
        return $this;
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // SELECT AND AGGREGATES
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * Adds an average aggregate to the list of selected elements
     * @param mixed $column 
     * @return Model
     * @throws Exception 
     */
    public function avg(string $column):self {
        $this->from($this->table);
        $this->queryBuilder->avg($column);
        return $this;
    }

    /**
     * Adds a minimum aggregate to the list of selected elements
     * @param mixed $column 
     * @return Model 
     * @throws Exception 
     */
    public function min(string $column):self {
        $this->from($this->table);
        $this->queryBuilder->min($column);
        return $this;
    }

    /**
     * Adds a maximum aggregate to the list of selected elements
     * @param mixed $column 
     * @return Model
     * @throws Exception 
     */
    public function max(string $column):self {
        $this->from($this->table);
        $this->queryBuilder->max($column);
        return $this;
    }

    /**
     * Adds a sum aggregate to the list of selected elements
     * @param mixed $column 
     * @return Model 
     * @throws Exception 
     */
    public function sum($column):self {
        $this->from($this->table);
        $this->queryBuilder->sum($column);
        return $this;
    }

    /**
     * Adds a count aggregate to the list of selected elements
     * @param string $column 
     * @return Model 
     * @throws Exception 
     */
    public function count(string $column):self {
        $this->from($this->table);
        $this->queryBuilder->count($column);
        return $this;
    }

    /**
     * Adds one or more columns to the list of selected elements
     * @param array $columns 
     * @return Model 
     * @throws Exception 
     */
    public function select(array $columns = ['*']):self {
        $this->from($this->table);
        $this->queryBuilder->select($columns);
        return $this;
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // DAO Data Access Object 
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * 
     * @param mixed $id 
     * @param array $columns 
     * @return array 
     * @throws Exception 
     */
    public function find(mixed $id, array $columns = ['*']):array|null {
        try {
            $first = $this->select($columns)->where($this->primaryKey, '=', $id)->first();
            if (empty($first)) { return null; }
            
            $this->data[$this->primaryKey] = array_pop($first);
            return $this->data[$this->primaryKey];
        } catch (Exception $e) {
            $baseMessage = "Couldn't find the {$this->getTableName()}";
            error_log("{$baseMessage} because {$e->getMessage()}", 0);
            throw new Exception($baseMessage);
        }
        
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // CREATE
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    
    /**
     * Inserts multiple elements at once
     * @param array $insertData 
     * @return int The number of inserted elements
     */
    public function insertMultiple(array $insertData):int {
        
        $this->queryBuilder->insertMultiple($insertData);

        $this->prepareQuery();
        
        return $this->driver->rowCount();
    }

    /**
     * Inserts one row
     * @param array $insertData 
     * @return int The number of inserted elements
     */
    public function insert(array $insertData):int {

        $this->queryBuilder->insert($insertData);

        $this->prepareQuery();

        return $this->driver->rowCount();

    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // UPDATE
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * Starts an update statement but this will not execute if no where statement is called
     * @param array $updateData 
     * @return Model 
     */
    public function update(array $updateData):self {
        $this->hasUpdate = true;
        $this->queryBuilder->update($updateData);
        return $this;
    }

    /**
     * Force a delete statement which does not have a where part
     * @return Model 
     */
    public function forceUpdate(array $updateData):self {
        $this->forceUpdate = true;
        return $this->update($updateData);
    }

    /**
     * Updates a row by its ID
     * @param mixed $id 
     * @param array $updateData 
     * @return int 
     * @throws Exception 
     */
    public function updateByID(mixed $id, array $updateData):int {
        
        $item = $this->find($id, [$this->primaryKey]);
        
        // When there is no record and the debugMode is true, no exception will be thrown
        if (empty($item) && $this->debugMode === false) {
            $baseError = "Record does not exists";
            error_log("{$baseError}: The id {$id} was not found.");
            throw new Exception($baseError);
        }

        $this->update($updateData)->where($this->primaryKey, '=', $id);

        $this->prepareQuery();

        return $this->driver->rowCount();

    }

    /**
     * Tries to update the row with the given ID and if it does not exists
     * It tries to create that record with the given data
     * @param mixed $id 
     * @param array $insertData 
     * @return int 
     * @throws Exception 
     */
    public function updateOrInsert(mixed $id, array $insertData):int {
        
        $item = $this->find($id, [$this->primaryKey]);
        
        // Create
        if (empty($item)) {
            $insertData[$this->primaryKey] = $id;
            return $this->insert($insertData);
        }

        // Update
        return $this->updateByID($id, $insertData);
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // DELETE
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * Starts a delete statement
     * @return Model 
     */
    public function delete():self {
        $this->hasDelete = true;
        $this->queryBuilder->delete();
        return $this;
    }

    /**
     * Force a delete statement which does not have a where part
     * @return Model 
     */
    public function forceDelete():self {
        $this->forceDelete = true;
        return $this->delete();
    }

    /**
     * Deletes an entity by its ID which can be a number or a string
     * @param mixed $id 
     * @return int 
     * @throws Exception 
     */
    public function deleteByID(mixed $id):int {
        return $this->delete()->where($this->primaryKey, '=', $id)->execute();
    }

    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    // JOIN
    /* //////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */

    /**
     * Creates an inner join with the given table
     * The closure can be used to add where like conditions to the join
     * @param string $table 
     * @param Closure|null $closure 
     * @return Model
     * @throws Exception 
     */
    public function join(string $table, Closure $closure=null):self {
        $this->queryBuilder->join($table, $closure);
        return $this;
    }

    /**
     * Creates a left join with the given table
     * The closure can be used to add where like conditions to the join
     * @param string $table 
     * @param Closure|null $closure 
     * @return Model 
     * @throws Exception 
     */
    public function leftJoin(string $table, Closure $closure=null):self {
        $this->queryBuilder->leftJoin($table, $closure);
        return $this;
    }

    /**
     * Creates a right join with the given table
     * The closure can be used to add where like conditions to the join
     * @param string $table 
     * @param Closure|null $closure 
     * @return Model 
     * @throws Exception 
     */
    public function rightJoin(string $table, Closure $closure=null):self {
        $this->queryBuilder->rightJoin($table, $closure);
        return $this;
    }

    /**
     * Creates a full join with the given table
     * The closure can be used to add where like conditions to the join
     * @param string $table 
     * @param Closure|null $closure 
     * @return Model 
     * @throws Exception 
     */
    public function fullJoin(string $table, Closure $closure=null):self {
        $this->queryBuilder->fullJoin($table, $closure);
        return $this;
    }

    /* ================================================================================================================================================================================================ //
    // WHERE / START
    // ================================================================================================================================================================================================ */

    /**
     * Creates a group of where conditions encapsulated by a parenthesis
     * @param Closure $closure 
     * @return Model 
     */
    public function whereGroup(\Closure $closure):self {
        $this->hasWhere = true;
        $this->queryBuilder->whereGroup($closure);
        return $this;
    }

    /**
     * Creates a group of where conditions encapsulated by a parenthesis
     * This group would be preceded by an "OR" conjunction
     * @param Closure $closure 
     * @return Model 
     */
    public function orWhereGroup(\Closure $closure):self {
        $this->hasWhere = true;
        $this->queryBuilder->orWhereGroup($closure);
        return $this;
    }

    /**
     * Creates a where condition
     * @param string $column 
     * @param string $comparison 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
    public function where(string $column, string $comparison, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->where($column, $comparison, $value);
		return $this;
	}

    /**
     * Creates a where condition preceded by an OR conjunction
     * @param string $column 
     * @param string $comparison 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhere(string $column, string $comparison, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orWhere($column, $comparison, $value);
		return $this;
	}

    /**
     * Creates a condition where the column starts with the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereStartsWith(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->startsWith($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column does not start with the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereNotStartsWith(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->notStartsWith($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column starts with the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereStartsWith(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orStartsWith($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column does not start with the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereNotStartsWith(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orNotStartsWith($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column ends with the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereEndsWith(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->endsWith($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column does not end with the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereNotEndsWith(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->notEndsWith($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column ends with the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereEndsWith(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orEndsWith($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column does not end with the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereNotEndsWith(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orNotEndsWith($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column contains the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereContains(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->contains($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column does not contain the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereNotContains(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->notContains($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column contains the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereContains(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orContains($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column does not contain the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereNotContains(string $column, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orNotContains($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column value is between the given values
     * @param string $column 
     * @param mixed $startValue 
     * @param mixed $endValue 
     * @return Model 
     * @throws Exception 
     */
	public function whereBetween(string $column, mixed $startValue, mixed $endValue):self {
        $this->hasWhere = true;
		$this->queryBuilder->between($column, $startValue, $endValue);
		return $this;
	}

    /**
     * Creates a condition where the column value is not between the given values
     * @param string $column 
     * @param mixed $startValue 
     * @param mixed $endValue 
     * @return Model 
     * @throws Exception 
     */
	public function whereNotBetween(string $column, mixed $startValue, mixed $endValue):self {
        $this->hasWhere = true;
		$this->queryBuilder->notBetween($column, $startValue, $endValue);
		return $this;
	}

    /**
     * Creates a condition where the column value is between the given values preceded by an OR conjunction
     * @param string $column 
     * @param mixed $startValue 
     * @param mixed $endValue 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereBetween(string $column, mixed $startValue, mixed $endValue):self {
        $this->hasWhere = true;
		$this->queryBuilder->orBetween($column, $startValue, $endValue);
		return $this;
	}

    /**
     * Creates a condition where the column value is not between the given values preceded by an OR conjunction
     * @param string $column 
     * @param mixed $startValue 
     * @param mixed $endValue 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereNotBetween(string $column, mixed $startValue, mixed $endValue):self {
        $this->hasWhere = true;
		$this->queryBuilder->orNotBetween($column, $startValue, $endValue);
		return $this;
	}

    /**
     * Creates a condition where the given value is between the given columns
     * @param string $starColumn 
     * @param string $endColumn 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereBetweenColumns(string $starColumn, string $endColumn, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->betweenColumns($starColumn, $endColumn, $value);
		return $this;
	}

    /**
     * Creates a condition where the given value is not between the given columns
     * @param string $starColumn 
     * @param string $endColumn 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereNotBetweenColumns(string $starColumn, string $endColumn, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->notBetweenColumns($starColumn, $endColumn, $value);
		return $this;
	}

    /**
     * Creates a condition where the given value is between the given columns preceded by an OR conjunction
     * @param string $starColumn 
     * @param string $endColumn 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereBetweenColumns(string $starColumn, string $endColumn, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orBetweenColumns($starColumn, $endColumn, $value);
		return $this;
	}

    /**
     * Creates a condition where the given value is not between the given columns preceded by an OR conjunction
     * @param string $starColumn 
     * @param string $endColumn 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereNotBetweenColumns(string $starColumn, string $endColumn, mixed $value):self {
        $this->hasWhere = true;
		$this->queryBuilder->orNotBetweenColumns($starColumn, $endColumn, $value);
		return $this;
	}

    /**
     * Creates a condition where the column value is greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
    public function whereGreaterThan(string $column, mixed $value):self {
		$this->queryBuilder->greaterThan($column, $value); return $this;
	}

    /**
     * Creates a condition where the column value is greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereGreaterThanOrEqual(string $column, mixed $value):self {
		$this->queryBuilder->greaterThanOrEqual($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column value is greater than the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereGreaterThan(string $column, mixed $value):self {
		$this->queryBuilder->orGreaterThan($column, $value, "OR");
		return $this;
	}

    /**
     * Creates a condition where the column value is greater than or equal to the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereGreaterThanOrEqual(string $column, mixed $value):self {
		$this->queryBuilder->orGreaterThanOrEqual($column, $value, "OR");
		return $this;
	}

    /**
     * Creates a condition where the column value is lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereLowerThan(string $column, mixed $value):self {
		$this->queryBuilder->lowerThan($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column value is lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereLowerThanOrEqual(string $column, mixed $value):self {
		$this->queryBuilder->lowerThanOrEqual($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column value is lower than the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereLowerThan(string $column, mixed $value):self {
		$this->queryBuilder->orLowerThan($column, $value, "OR");
		return $this;
	}

    /**
     * Creates a condition where the column value is lower than or equal to the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereLowerThanOrEqual(string $column, mixed $value):self {
		$this->queryBuilder->orLowerThanOrEqual($column, $value, "OR");
		return $this;
	}

    /**
     * Creates a condition where the column value is equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereEqual(string $column, mixed $value):self {
		$this->queryBuilder->equal($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column value is different to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function whereDifferent(string $column, mixed $value):self {
		$this->queryBuilder->different($column, $value);
		return $this;
	}

    /**
     * Creates a condition where the column value is equal to the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereEqual(string $column, mixed $value):self {
		$this->queryBuilder->orEqual($column, $value, "OR");
		return $this;
	}

    /**
     * Creates a condition where the column value is different to the given value preceded by an OR conjunction
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orWhereDifferent(string $column, mixed $value):self {
		$this->queryBuilder->orDifferent($column, $value, "OR");
		return $this;
	}

    /* ================================================================================================================================================================================================ //
    // WHERE / END 
    // ================================================================================================================================================================================================ */

    /* ================================================================================================================================================================================================ //
    // HAVING / START
    // ================================================================================================================================================================================================ */

    /**
     * Having max aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMaxGreaterThan(string $column, mixed $value):self { $this->queryBuilder->havingMaxGreaterThan($column, $value); return $this; }
	/**
     * Having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMaxGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingMaxGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMaxGreaterThan(string $column, mixed $value):self { $this->queryBuilder->orHavingMaxGreaterThan($column, $value); return $this; }
	/**
     * Having max aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMaxGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingMaxGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having max aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMaxLowerThan(string $column, mixed $value):self { $this->queryBuilder->havingMaxLowerThan($column, $value); return $this; }
	/**
     * Having max aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMaxLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingMaxLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having max aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMaxLowerThan(string $column, mixed $value):self { $this->queryBuilder->orHavingMaxLowerThan($column, $value); return $this; }
	/**
     * Or having max aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMaxLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingMaxLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having max aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMaxEqual(string $column, mixed $value):self { $this->queryBuilder->havingMaxEqual($column, $value); return $this; }
	/**
     * Or having max aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMaxEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingMaxEqual($column, $value); return $this; }
	/**
     * Having max aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMaxDifferent(string $column, mixed $value):self { $this->queryBuilder->havingMaxDifferent($column, $value); return $this; }
	/**
     * Having max aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model
     * @throws Exception 
     */
	public function orHavingMaxDifferent(string $column, mixed $value):self { $this->queryBuilder->orHavingMaxDifferent($column, $value); return $this; }

	/**
     * Having min aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMinGreaterThan(string $column, mixed $value):self { $this->queryBuilder->havingMinGreaterThan($column, $value); return $this; }
	/**
     * Having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMinGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingMinGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMinGreaterThan(string $column, mixed $value):self { $this->queryBuilder->orHavingMinGreaterThan($column, $value); return $this; }
	/**
     * Having min aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMinGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingMinGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having min aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMinLowerThan(string $column, mixed $value):self { $this->queryBuilder->havingMinLowerThan($column, $value); return $this; }
	/**
     * Having min aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMinLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingMinLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having min aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMinLowerThan(string $column, mixed $value):self { $this->queryBuilder->orHavingMinLowerThan($column, $value); return $this; }
	/**
     * Or having min aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMinLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingMinLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having min aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMinEqual(string $column, mixed $value):self { $this->queryBuilder->havingMinEqual($column, $value); return $this; }
	/**
     * Or having min aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingMinEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingMinEqual($column, $value); return $this; }
	/**
     * Having min aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingMinDifferent(string $column, mixed $value):self { $this->queryBuilder->havingMinDifferent($column, $value); return $this; }
	/**
     * Having min aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model
     * @throws Exception 
     */
	public function orHavingMinDifferent(string $column, mixed $value):self { $this->queryBuilder->orHavingMinDifferent($column, $value); return $this; }

	/**
     * Having avg aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingAvgGreaterThan(string $column, mixed $value):self { $this->queryBuilder->havingAvgGreaterThan($column, $value); return $this; }
	/**
     * Having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingAvgGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingAvgGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingAvgGreaterThan(string $column, mixed $value):self { $this->queryBuilder->orHavingAvgGreaterThan($column, $value); return $this; }
	/**
     * Having avg aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingAvgGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingAvgGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having avg aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingAvgLowerThan(string $column, mixed $value):self { $this->queryBuilder->havingAvgLowerThan($column, $value); return $this; }
	/**
     * Having avg aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingAvgLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingAvgLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having avg aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingAvgLowerThan(string $column, mixed $value):self { $this->queryBuilder->orHavingAvgLowerThan($column, $value); return $this; }
	/**
     * Or having avg aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingAvgLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingAvgLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having avg aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingAvgEqual(string $column, mixed $value):self { $this->queryBuilder->havingAvgEqual($column, $value); return $this; }
	/**
     * Or having avg aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingAvgEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingAvgEqual($column, $value); return $this; }
	/**
     * Having avg aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingAvgDifferent(string $column, mixed $value):self { $this->queryBuilder->havingAvgDifferent($column, $value); return $this; }
	/**
     * Having avg aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model
     * @throws Exception 
     */
	public function orHavingAvgDifferent(string $column, mixed $value):self { $this->queryBuilder->orHavingAvgDifferent($column, $value); return $this; }

	/**
     * Having sum aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingSumGreaterThan(string $column, mixed $value):self { $this->queryBuilder->havingSumGreaterThan($column, $value); return $this; }
	/**
     * Having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingSumGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingSumGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingSumGreaterThan(string $column, mixed $value):self { $this->queryBuilder->orHavingSumGreaterThan($column, $value); return $this; }
	/**
     * Having sum aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingSumGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingSumGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having sum aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingSumLowerThan(string $column, mixed $value):self { $this->queryBuilder->havingSumLowerThan($column, $value); return $this; }
	/**
     * Having sum aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingSumLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingSumLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having sum aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingSumLowerThan(string $column, mixed $value):self { $this->queryBuilder->orHavingSumLowerThan($column, $value); return $this; }
	/**
     * Or having sum aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingSumLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingSumLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having sum aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingSumEqual(string $column, mixed $value):self { $this->queryBuilder->havingSumEqual($column, $value); return $this; }
	/**
     * Or having sum aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingSumEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingSumEqual($column, $value); return $this; }
	/**
     * Having sum aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingSumDifferent(string $column, mixed $value):self { $this->queryBuilder->havingSumDifferent($column, $value); return $this; }
	/**
     * Having sum aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model
     * @throws Exception 
     */
	public function orHavingSumDifferent(string $column, mixed $value):self { $this->queryBuilder->orHavingSumDifferent($column, $value); return $this; }

	/**
     * Having count aggregate greater than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingCountGreaterThan(string $column, mixed $value):self { $this->queryBuilder->havingCountGreaterThan($column, $value); return $this; }
	/**
     * Having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingCountGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingCountGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Or having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingCountGreaterThan(string $column, mixed $value):self { $this->queryBuilder->orHavingCountGreaterThan($column, $value); return $this; }
	/**
     * Having count aggregate greater than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingCountGreaterThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingCountGreaterThanOrEqual($column, $value); return $this; }
	/**
     * Having count aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingCountLowerThan(string $column, mixed $value):self { $this->queryBuilder->havingCountLowerThan($column, $value); return $this; }
	/**
     * Having count aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingCountLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->havingCountLowerThanOrEqual($column, $value); return $this; }
	/**
     * Or having count aggregate lower than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingCountLowerThan(string $column, mixed $value):self { $this->queryBuilder->orHavingCountLowerThan($column, $value); return $this; }
	/**
     * Or having count aggregate lower than or equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingCountLowerThanOrEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingCountLowerThanOrEqual($column, $value); return $this; }
	/**
     * Having count aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingCountEqual(string $column, mixed $value):self { $this->queryBuilder->havingCountEqual($column, $value); return $this; }
	/**
     * Or having count aggregate equal to given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function orHavingCountEqual(string $column, mixed $value):self { $this->queryBuilder->orHavingCountEqual($column, $value); return $this; }
	/**
     * Having count aggregate equal to the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model 
     * @throws Exception 
     */
	public function havingCountDifferent(string $column, mixed $value):self { $this->queryBuilder->havingCountDifferent($column, $value); return $this; }
	
    /**
     * Having count aggreate different than the given value
     * @param string $column 
     * @param mixed $value 
     * @return Model
     * @throws Exception 
     */
	public function orHavingCountDifferent(string $column, mixed $value):self { $this->queryBuilder->orHavingCountDifferent($column, $value); return $this; }

    /**
     * Creates a group of having conditions encapsulated by a parenthesis
     * @param Closure $closure 
     * @return Model 
     */
    public function havingGroup(\Closure $closure):self {
        $this->hasWhere = true;
        $this->queryBuilder->havingGroup($closure);
        return $this;
    }

    /**
     * Creates a group of having conditions encapsulated by a parenthesis preceded by an OR conjunction
     * @param Closure $closure 
     * @return Model 
     */
    public function orHavingGroup(\Closure $closure):self {
        $this->hasWhere = true;
        $this->queryBuilder->orHavingGroup($closure);
        return $this;
    }

    /* ================================================================================================================================================================================================ //
    // HAVING / END 
    // ================================================================================================================================================================================================ */

    /* ================================================================================================================================================================================================ //
    // GROUP BY / START
    // ================================================================================================================================================================================================ */

    /**
     * Groups the query by the given column
     * @param string $column 
     * @return Model 
     */
    public function groupBy(string $column): self {
		$this->queryBuilder->groupBy($column);
		return $this;
	}

    /**
     * Groups the query by the first left $length of character of the given column
     * @param string $column 
     * @param int $length 
     * @return Model 
     */
	public function groupByLeft(string $column, int $length): self {
		$this->queryBuilder->groupByLeft($column, $length);
		return $this;
	}

    /**
     * Groups the query by the first right $length of character of the given column
     * @param string $column 
     * @param int $length 
     * @return Model 
     */
	public function groupByRight(string $column, int $length): self {
		$this->queryBuilder->groupByRight($column, $length);
		return $this;
	}

    /**
     * Groups the query by the given column converted to uppercase
     * Only makes sense when the database support case sensitive
     * @param string $column 
     * @return Model 
     */
	public function groupByUpper(string $column): self {
		$this->queryBuilder->groupByUpper($column);
		return $this;
	}

    /**
     * Groups the query by the given column converted to lowercase
     * Only makes sense when the database support case sensitive
     * @param string $column 
     * @return Model 
     */
	public function groupByLower(string $column): self {
		$this->queryBuilder->groupByLower($column);
		return $this;
	}

    /**
     * Groups the query by the number of character on the given column
     * @param string $column 
     * @return Model 
     */
	public function groupByLength(string $column): self {
		$this->queryBuilder->groupByLength($column);
		return $this;
	}

    /**
     * Groups the query trimming the extra spaces on the given column 
     * @param string $column 
     * @return Model 
     */
	public function groupByTrim(string $column): self {
		$this->queryBuilder->groupByTrim($column);
		return $this;
	}

    /* ================================================================================================================================================================================================ //
    // GROUP BY / END
    // ================================================================================================================================================================================================ */

    /* ================================================================================================================================================================================================ //
    // ORDER BY / START
    // ================================================================================================================================================================================================ */

    /**
     * Order the query by the given columns in ascending mode
     * @param mixed $columns 
     * @return Model
     */
    public function orderByAsc(string ...$columns):self {
        foreach ($columns as $column) { 
            $this->queryBuilder->orderByAsc($column);
        }
        return $this;
    }

    /**
     * Order the query by the given columns in descending mode
     * @param string $columns 
     * @return Model 
     */
    public function orderByDesc(string ...$columns):self {
        foreach ($columns as $column) {
            $this->queryBuilder->orderByDesc($column);
        }
        return $this;
    }

    /* ================================================================================================================================================================================================ //
    // ORDER BY / END
    // ================================================================================================================================================================================================ */

    /**
     * Limits the results to the given number of results
     * @param int $rows 
     * @return Model 
     */
    public function limit(int $rows=1):self {
        $this->queryBuilder->limit($rows);
        return $this;
    }

    /**
     * Skips the first given number of results from the results
     * @param int $skipRows 
     * @return Model 
     */
    public function offset(int $skipRows=0):self {
        $this->queryBuilder->offset($skipRows);
        return $this;
    }

    /**
     * Gets the records from the database and convert it to a data tree like structure
     * @return array 
     */
    public function get():array {
        
        $selectIndexes = $this->queryBuilder->getSelectIndexes();
        
        $this->prepareQuery();
        
        $rows = $this->driver->fetchNum();

        return $this->treeStructure($rows, $selectIndexes);

    }

    /**
     * Joins the model with the given relationships and adds their columns to the select list
     * @param array $relations 
     * @return Model 
     * @throws Exception 
     */
    public function with(array $relations):self {
        foreach ($relations as $relation) {
            $this->queryBuilder->join($relation);
        }
        return $this;
    }

    /**
     * Uses the rows indexes along with the select fully qualified column names to build a tree like structure of data
     * @param mixed $rows 
     * @param mixed $selectIndexes 
     * @return array 
     */
    public function treeStructure($rows, $selectIndexes):array {
        // TO have a tree structure we group by the primary key
        $treeStructure = [];
        foreach ($rows as $selectValues) {

            // This line uses the order of the selected fields and the number of the fetched row
            // To add fully qualified associative indexes to each fields
            // We want this behavior because it's easy to group the data in a tree like structure
            $qualifiedRow = array_combine($selectIndexes, $selectValues);

            foreach ($qualifiedRow as $qualifiedColumn=>$value) {
                // Use the parts of the qualified column to build the tree like structure
                $parts = explode(".", $qualifiedColumn);

                // First right part of the qualified column is the column
                $column = array_pop($parts);
                
                // We build the model pivot by using the qualified primary key of the model
                $modelPivot = $this->queryBuilder->addSchemaAndTable($this->primaryKey, $this->getTableName(), $this->getSchema());
                $modelIndex = $qualifiedRow[$modelPivot];

                // It's a model property if the table name of the current model is included in the qualified name of the column
                $isModelProperty = stripos($qualifiedColumn, $this->getTableName().".") !== false;
                if ($isModelProperty) {
                    $treeStructure[$modelIndex][$column] = $value;
                } else {
                    // Second right part of the qualified column is the relation
                    $relation = array_pop($parts);

                    // We build the relation pivot by using the qualified primary key of the relation
                    $relationModel = $this->getRelationModel($relation);
                    $relationPivot = $this->queryBuilder->addSchemaAndTable($relationModel->primaryKey, $relationModel->getTableName(), $relationModel->getSchema());
                    $relationIndex = $qualifiedRow[$relationPivot];
                    $treeStructure[$modelIndex][$relation][$relationIndex][$column] = $value;
                }
            }
        }

        return $treeStructure;
    }

    /**
     * Builds the query to be executed or executes the query depending on the debugMode flag
     * @return void 
     */
    private function prepareQuery():void {
        
        $bindValues = $this->queryBuilder->getBindValues();
        $command = $this->queryBuilder->getSQL();
        
        if ($this->debugMode === true) {

            /* Builds a select statement which does not generate any result or modification to the database */
            $column = $this->queryBuilder->addSchemaAndTable($this->primaryKey, $this->getTableName(), $this->getSchema());
            $table = $this->queryBuilder->addSchema($this->getTableName(), $this->getSchema());
            $debugSQL = "SELECT {$column} FROM {$table} WHERE 1=2";

            $this->addSQL($command, $bindValues);
            $this->driver->execute($debugSQL);
        } else {

            $this->driver->execute($command, $bindValues);
        }
    }

    /**
     * Get the first number of elements from the last executed query
     * @param int $limit 
     * @return array 
     */
    public function first(int $limit=1):array {

        /** 
         * Gets the name of the columns selected by the select command
         * It should be executed before preparing the query because otherwise it gets cleared
         * */
        $selectIndexes = $this->queryBuilder->getSelectIndexes();
        $this->limit($limit)->prepareQuery();

        /* Extracts the data from last executed query with a numeric indexed array of values */
        $rows = $this->driver->fetchNum();
        
        /* Builds an associative array of data */
        return $this->treeStructure($rows, $selectIndexes);
    }

    /**
     * Builds the final query, executes it and return the number of affected records
     * @return int 
     * @throws Exception 
     */
    public function execute():int {
        
        if ($this->hasDelete === true && $this->hasWhere === false && $this->forceDelete === false) {
            throw new Exception("Trying to execute a delete statement without where.");
        }

        if ($this->hasUpdate === true && $this->hasWhere === false && $this->forceUpdate === false) {
            throw new Exception("Trying to execute an update statement without where.");
        }

        $this->prepareQuery();

        return $this->driver->rowCount();
        
    }

}
?>