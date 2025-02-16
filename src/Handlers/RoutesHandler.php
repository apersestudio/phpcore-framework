<?php

namespace PC\Handlers;

use PC\Core;
use PC\Interfaces\IHandler;

class RoutesHandler implements IHandler {

    public static function start():void {

        $MainRoutePath = Core::DIR_APP()."/Routes/MainRoutes.php";
        if (!file_exists($MainRoutePath)) {
            throw new \Exception("{$MainRoutePath} does not exists");
        }

        require_once($MainRoutePath);

    }

}

?>