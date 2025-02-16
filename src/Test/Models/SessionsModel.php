<?php

namespace PC\Test\Models;

use PC\Databases\Model;

class SessionsModel extends Model {

    protected $connection = "master";
    protected $schema = "public";
    protected $table = 'sessions';
    protected $primaryKey = 'session_id';

    /** Definition as */
    protected $definition = [
        "session_id"=>"string",
        "session_iduser"=>"string",
        "session_ipaddress"=>"string",
        "session_useragent"=>"string",
        "session_payload"=>"string",
        "session_last_activity"=>"timestamp"
    ];

}
?>