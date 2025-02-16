<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Interfaces\IBuilder;

class OrderByBuilder extends ABuilder implements IBuilder {

    public function getSQL():string {
        $sql = "";
        if ($this->hasFragments()) {
            $sql .= " ORDER BY  ".implode(", ", $this->getFragments());
        }
        return $sql;
    }


    private function orderByStatement(string $column, string $order):self {
        $columnQualified = $this->addSchemaAndTable($column, $this->model->getTableName(), $this->model->getSChema());
        $fragment = "{$columnQualified} {$order}";
        $this->addFragment($fragment);
        return $this;
    }

    public function orderByAsc(string $column):self {
        return $this->orderByStatement($column, "ASC");
    }

    public function orderByDesc(string $column):self {
        return $this->orderByStatement($column, "DESC");
    }


}

?>