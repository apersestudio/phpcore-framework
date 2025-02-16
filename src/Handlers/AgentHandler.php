<?php

namespace PC\Handlers;

use PC\Core;
use PC\Interfaces\IHandler;

class AgentHandler implements IHandler {

    public static function start():void {

        // The agent is in charge to load routes, middlewares, and so on.
        if (Core::PHPAGENT() === 'browser') {

            // Generates all the constants which depends on the server global
            require_once(Core::DIR_SRC()."/Constants/Server.php");

            // In case of 500 error, it gets captures to output a friendly json message
            ErrorHandler::start();

            // To link developer routes to user requests
            RoutesHandler::start();
        
        // Terminal handler is used to execute commands on the terminal based on the user input
        } else if (Core::PHPAGENT() === 'terminal') {

            CommandHandler::start();

        }
    }

}