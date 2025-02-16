<?php

namespace PC\Test\Models;

use PC\Databases\Model;

class UserModel extends Model {

    protected $connection = "master";
    protected $schema = "public";
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $sequence = "user_user_id_seq";

    /** Definition as */
    protected $definition = [
        "user_id"=>"string",
        "user_name"=>"string",
        "user_email"=>"string",
        "user_password"=>"string",
        "user_age"=>"integer",
        "user_min_credit"=>"integer",
        "user_max_credit"=>"integer",
        "created_at"=>"timestamp",
        "updated_at"=>"timestamp"
    ];

    protected $relations = [
        "tokens"=>[
            "foreignKey"=>"token_tokenable_id",
            "localKey"=>"user_id",
            "model"=>"PC\Test\Models\TokensModel"
        ],
        "sessions"=>[
            "foreignKey"=>"session_iduser",
            "localKey"=>"user_id",
            "model"=>"PC\Test\Models\SessionsModel"
        ]
    ];

}
?>