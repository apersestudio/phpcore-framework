<?php

namespace PC\Abstracts;

use PC\Databases\DB;

abstract class AMigration {

    abstract public function up(DB $db, array $params=[]):string;

    abstract public function down(DB $db, array $params=[]):string;

}

?>