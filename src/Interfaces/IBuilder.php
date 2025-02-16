<?php

namespace PC\Interfaces;

use PC\Databases\Model;

interface IBuilder {

    /**
     * Every builder should receive a model because it contains information about the table
     */
    public function __construct(Model $model);

    /**
     * Build a SQL query with the internal arrays used to store the SQL fragments
     */
    public function getSQL():string;

    /**
     * Returns an array of the values that should be replaced in the prepared statement
     */
    public function getBindValues():array;

}

?>