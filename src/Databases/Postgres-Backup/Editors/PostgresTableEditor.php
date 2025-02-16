<?php

namespace PC\Databases\Postgres\Editors;

class TableBuilder {

    protected array $fields = [];

    private function addField(array $field):self {
        $this->fields[] = $field;
        return $this;
    }

    public function getFields():array {
        return $this->fields;
    }

    public function nullable():self {
        $lastIndex = count($this->fields);
        $this->fields[$lastIndex]["nullable"] = true;
        return $this;
    }

    public function addIncrements(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"integer", "auto_increment"=>true, "primary_key"=>true]);
        return $this;
    }
    public function addSmallIncrements(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"small_integer", "auto_increment"=>true, "primary_key"=>true]);
        return $this;
    }
    public function addBigIncrements(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"big_integer", "auto_increment"=>true, "primary_key"=>true]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addUlid(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"varchar", "length"=>26, "primary_key"=>true]);
        return $this;
    }
    public function addUuid(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"varchar", "length"=>36, "primary_key"=>true]);
        return $this;
    }
    public function addInteger(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"integer"]);
        return $this;
    }
    public function addSmallInteger(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"small_integer"]);
        return $this;
    }
    public function addBigInteger(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"big_integer"]);
        return $this;
    }
    public function addUnsignedInteger(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"integer", "unsigned"=>true]);
        return $this;
    }
    public function addUnsignedSmallInteger(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"small_integer", "unsigned"=>true]);
        return $this;
    }
    public function addUnsignedBigInteger(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"big_integer", "unsigned"=>true]);
        return $this;
    }
    public function addDecimal(string $fieldName, int $precision, int $scale):self {
        $this->addField(["name"=>$fieldName, "type"=>"decimal", "unsigned"=>true, "precision"=>$precision, "scale"=>$scale]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addBoolean(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"boolean"]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addChar(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"char"]);
        return $this;
    }
    public function addString(string $fieldName, int $length):self {
        $this->addField(["name"=>$fieldName, "type"=>"varchar", "length"=>$length]);
        return $this;
    }
    public function addText(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"text"]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addDate(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"date"]);
        return $this;
    }
    public function addTime(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"time"]);
        return $this;
    }
    public function addDatetime(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"datetime"]);
        return $this;
    }
    public function addTimestamp(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"timestamp"]);
        return $this;
    }
    /* ------------------------------------------- */
    public function addTimeTz(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"time", "timezone"=>true]);
        return $this;
    }
    public function addDateTimeTz(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"datetime", "timezone"=>true]);
        return $this;
    }
    public function addTimestampTz(string $fieldName):self {
        $this->addField(["name"=>$fieldName, "type"=>"timestamp", "timezone"=>true]);
        return $this;
    }
    


}

?>