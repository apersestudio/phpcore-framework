<?php

namespace PC\Handlers;

use PC\Interfaces\IHandler;

class ErrorHandler implements IHandler {

    public static function start():void {

        set_exception_handler(function($exception) {
            echo json_encode([
                "success"=>false,
                "http_code" => 500,
                "file"=>$exception->getFile(),
                "line"=>$exception->getLine(),
                "message"=>$exception->getMessage()
            ]);
        });

        set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            echo json_encode([
                "success" => false,
                "http_code" => 500,
                "severity" => $severity,
                "file" => $file,
                "line" => $line,
                "message" => $message,
            ]);
        });

    }

}

?>