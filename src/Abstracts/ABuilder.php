<?php

namespace PC\Abstracts;

use PC\Databases\Model;
use PC\Traits\SQLBuilder;

abstract class ABuilder {

    use SQLBuilder;

    protected Model $model {
        set(Model $newModel) {
            $this->model = $newModel;
        }
    }

    public function __construct(Model $model) {
        $this->model = $model;
	}

}