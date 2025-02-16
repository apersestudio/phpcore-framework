<?php

namespace PC\Databases\Postgres\Editors;

use Closure;
use PC\Abstracts\ADatabaseDriver;
use PC\Databases\Base\Builders\TableBuilder;
use PC\Traits\DatabaseDriverTrait;

class SchemaEditor {

    use DatabaseDriverTrait;

    private ADatabaseDriver $driver;

    private static self $instance;

    private function __construct() {}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setConnection(string $connection):self {
        $this->driver = self::getDatabaseDriver($connection);
        return $this;
    }

    public function createTable(string $tableName, Closure $closure):self {
        $instance = self::getInstance();

        $tableBuilder = new TableBuilder();
        $closure($tableBuilder);

        print_r($this->driver);
        echo "YOU ARE HERE";
        print_r($tableBuilder->getFields());

        return $instance;
    }

    public function dropTable(string $tableName) {
        $instance = self::getInstance();

        return $instance;
    }

}

?>