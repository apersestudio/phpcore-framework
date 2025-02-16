<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

class PostgresDeleteBuilder extends ABuilder implements IBuilder {

    public function getSQL():string {
        
        if ($this->hasFragments()) {
            return implode(" ", $this->getFragments());
        }

        return "";
        
    }

    public function delete():void {
        $qualifiedTable = $this->addSchema($this->model->getTableName(), $this->model->getSchema());
        $this->addFragment("DELETE FROM {$qualifiedTable}");
    }

}

?>