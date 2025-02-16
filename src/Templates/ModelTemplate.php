<?php

namespace App\Models;

use PC\Databases\Model;

class TableModel extends Model {

    protected $connection = "connection";
    protected $schema = "schema";
    protected $table = "table";
    protected $primaryKey = "table_id";

    protected $definition = [
        "table_id"=>"varchar",
        "table_number"=>"integer",
        "table_decimal"=>"decimal",
        "table_boolean"=>"boolean",
        "table_text"=>"text",
        "table_date"=>"date",
        "table_datetime"=>"timestamp"
    ];

    protected $relations = [
        "relation"=>[
            "foreignKey"=>"relation_idtable",
            "localKey"=>"table_id",
            "model"=>"App\Models\RelationModel"
        ]
    ];

}
?>