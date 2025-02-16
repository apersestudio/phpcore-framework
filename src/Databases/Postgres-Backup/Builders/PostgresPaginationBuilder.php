<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

class PaginationBuilder extends ABuilder implements IBuilder {

    public function getSQL():string {
        $sql = "";
        if ($this->hasFragments()) {
            $sql .= implode(" ", $this->getFragments());
        }
        $this->clear();
        return $sql;
    }

    public function limit(int $rows):self {
        $fragment = " LIMIT ".$rows;
        $this->addFragment($fragment);
        return $this;
    }

    public function offset(int $skipRows):self {
        $fragment = " OFFSET ".$skipRows;
        $this->addFragment($fragment);
        return $this;
    }

}

?>