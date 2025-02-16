<?php

namespace PC\Interfaces;

interface ICommand {

    /**
     * The function that get executed when the command gets invoked
     */
    public function handle():void;

}