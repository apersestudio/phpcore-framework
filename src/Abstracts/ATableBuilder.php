<?php

namespace PC\Abstracts;

use Exception;

use PC\Traits\SQLBuilder;
use PC\Traits\StringTrait;

abstract class ATableBuilder {

    use SQLBuilder, StringTrait;

    protected string $table;

    protected string $schema;

    protected array $fields = [];

    protected array $sequences = [];

    protected array $indexes = [];

    protected array $unique = [];

    protected array $constraints = [];

    CONST INVALID_FIELD_NAME = "Invalid field name";
    
    CONST SQL_FUNCTION_NAMES = [
        "CURRENT_TIMESTAMP"
    ];

    public function setSchema(string $schemaName) {
        $this->schema = $schemaName;
    }

    public function setTable(string $tableName) {
        $this->table = $tableName;
    }

    public function getTableName():string {
        return $this->table;
    }

    private function addField(array $field):self {
        $this->fields[] = $field;
        return $this;
    }

    public function getFields():array {
        return $this->fields;
    }

    public function nullable():self {
        $lastIndex = count($this->fields) - 1;
        $this->fields[$lastIndex]["nullable"] = true;
        return $this;
    }

    public function comment(string $comment):self {
        $lastIndex = count($this->fields) - 1;
        $this->fields[$lastIndex]["comment"] = $comment;
        return $this;
    }

    public function primary():self {
        $lastIndex = count($this->fields) - 1;
        $this->fields[$lastIndex]["primary_key"] = true;
        return $this;
    }

    public function index():self {
        $lastIndex = count($this->fields) - 1;
        $this->fields[$lastIndex]["index"] = true;
        return $this;
    }

    public function unique():self {
        $lastIndex = count($this->fields) - 1;
        $this->fields[$lastIndex]["unique"] = true;
        return $this;
    }

    public function default(mixed $value):self {
        $lastIndex = count($this->fields) - 1;
        $field = &$this->fields[$lastIndex];

        // SQL Functions should not be surrounded by quotes
        if (in_array($value, self::SQL_FUNCTION_NAMES)) {
            $field["default"] = $value;
        } else {
            $field["default"] = "'".$value."'";
        }
        return $this;
    }

    public function addIncrements(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"integer", "auto_increment"=>true]);
        return $this;
    }
    
    public function addSmallIncrements(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"small_integer", "auto_increment"=>true]);
        return $this;
    }
    public function addBigIncrements(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"big_integer", "auto_increment"=>true]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addUlid(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"varchar", "length"=>26]);
        return $this;
    }
    public function addUuid(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"varchar", "length"=>36]);
        return $this;
    }
    public function addInteger(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"integer"]);
        return $this;
    }
    public function addSmallInteger(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"small_integer"]);
        return $this;
    }
    public function addBigInteger(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"big_integer"]);
        return $this;
    }
    public function addUnsignedInteger(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"integer", "unsigned"=>true]);
        return $this;
    }
    public function addUnsignedSmallInteger(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"small_integer", "unsigned"=>true]);
        return $this;
    }
    public function addUnsignedBigInteger(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"big_integer", "unsigned"=>true]);
        return $this;
    }
    public function addDecimal(string $fieldName, int $precision, int $scale):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"decimal", "unsigned"=>true, "precision"=>$precision, "scale"=>$scale]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addBoolean(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"boolean"]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addChar(string $fieldName, int $length):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"char", "length"=>$length]);
        return $this;
    }
    public function addString(string $fieldName, int $length):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"varchar", "length"=>$length]);
        return $this;
    }
    public function addText(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"text"]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addDate(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"date"]);
        return $this;
    }
    public function addTime(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"time"]);
        return $this;
    }
    public function addDatetime(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"datetime"]);
        return $this;
    }
    public function addTimestamp(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"timestamp"]);
        return $this;
    }
    
    /* ------------------------------------------- */
    public function addTimeTz(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"time", "timezone"=>true]);
        return $this;
    }
    public function addDateTimeTz(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"datetime", "timezone"=>true]);
        return $this;
    }
    public function addTimestampTz(string $fieldName):self {
        if (!$this->validCreateColumn($fieldName)) {
            error_log(self::INVALID_FIELD_NAME.": ".__FUNCTION__."({$fieldName})");
            throw new Exception(self::INVALID_FIELD_NAME);
        }
        $this->addField(["name"=>$fieldName, "type"=>"timestamp", "timezone"=>true]);
        return $this;
    }

    // ADDITIONAL FEATURES

    abstract public function createSequence(string $sequenceName, int $startValue=1, int $increment=1):void;

    abstract public function createIndex(string $schema, string $tableName, string $indexName, string $fieldName):void;

    abstract public function createConstraint(string $schema, string $tableName, string $constraintName, string $fieldName, string $type):void;
    
    abstract public function getSQL():string;

    abstract public function getDropTable():string;

    abstract public function getDropIndex(string $indexName):string;

    abstract public function getDropSequence(string $sequenceName):string;

    abstract public function getDropConstraint(string $constraintName):string;

    abstract public function getSequencesSQL():array;

    abstract public function getIndexesSQL():array;

    abstract public function getUniqueSQL():array;

}

?>