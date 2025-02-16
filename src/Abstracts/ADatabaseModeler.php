<?php

namespace PC\Abstracts;

abstract class ADatabaseModeler {

    protected bool $debugMode = false;

    abstract public function setDebugMode(bool $debugMode):self;
    abstract public function addDebugSQL(string $command, array $bindValues):self;
    abstract public function getDebugSQL():string;
    abstract public function prepareQuery(string $command, array $bindValues):self;
    abstract public function dropTable(string $tableName):self;

}