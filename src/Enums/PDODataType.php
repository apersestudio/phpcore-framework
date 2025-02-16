<?php

namespace PC\Enums;

use PDO;

enum PDODataType:int {
    case Boolean = PDO::PARAM_BOOL;
    case Null = PDO::PARAM_NULL;
    case Integer = PDO::PARAM_INT;
    case String = PDO::PARAM_STR;
}

?>