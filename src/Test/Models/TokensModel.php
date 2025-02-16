<?php

namespace PC\Test\Models;

use PC\Databases\Model;

class TokensModel extends Model {

    protected $connection = "master";
    protected $schema = "public";
    protected $table = 'tokens';
    protected $primaryKey = 'token_id';

    /** Definition as */
    protected $definition = [
        "token_id"=>"string",
        "token_tokenable_type"=>"string",
        "token_tokenable_id"=>"string",
        "token_device"=>"string",
        "token_ip"=>"string",
        "token_data"=>"string",
        "token_abilities"=>"string",
        "last_used_at"=>"timestamp",
        "expires_at"=>"timestamp",
        "created_at"=>"timestamp",
        "updated_at"=>"timestamp"
    ];

    protected $fillable = [
        "token_id",
        "token_tokenable_type",
        "token_tokenable_id",
        "token_device",
        "token_ip",
        "token_data",
        "token_abilities",
        "last_used_at",
        "expires_at",
        "created_at",
        "updated_at"
    ];

}
?>