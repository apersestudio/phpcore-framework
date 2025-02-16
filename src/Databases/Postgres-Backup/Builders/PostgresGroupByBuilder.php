<?php

namespace PC\Databases\Postgres\Builders;

use PC\Abstracts\ABuilder;
use PC\Databases\Model;
use PC\Interfaces\IBuilder;

class PostgresDeleteBuilderGroupByBuilder extends ABuilder implements IBuilder {

    public function getSQL():string {
		
		$groupBySQL = "";
		
		if ($this->hasFragments()) {
			$fragmentsSQL = implode(", ", $this->getFragments());
			$groupBySQL .= " GROUP BY {$fragmentsSQL}";
		}

		$this->clear();
		return $groupBySQL;
	}

	/* ================================================================================================ //
	// INDIVIDUAL DEFINITION
	// ================================================================================================ */

	// Extrae un número determinado de caracteres desde el inicio de una cadena.
	public function groupByLeft(string $column, int $length):self {
		$this->addFragment("LEFT({$column}, {$length})");
		return $this;
	}

	// Extrae un número determinado de caracteres desde el final de una cadena.
	public function groupByRight(string $column, int $length):self {
		$this->addFragment("RIGHT({$column}, {$length})");
		return $this;
	}

	// Convierte todos los caracteres de una cadena a mayúsculas.
	public function groupByUpper(string $column):self {
		$this->addFragment("UPPER({$column})");
		return $this;
	}

	// Convierte todos los caracteres de una cadena a minúsculas.
	public function groupByLower(string $column):self {
		$this->addFragment("LOWER({$column})");
		return $this;
	}

	// Devuelve la longitud de una cadena.
	public function groupByLength(string $column):self {
		$this->addFragment("LENGTH({$column})");
		return $this;
	}

	// Elimina los espacios en blanco al principio y al final de una cadena.
	public function groupByTrim(string $column):self {
		$this->addFragment("TRIM({$column})");
		return $this;
	}

	public function groupBy(string $column):self {
		$this->addFragment($column);
		return $this;
	}

}

?>