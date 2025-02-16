<?php

namespace PC\Terminal;

class StyleTerminal {

    CONST TEXT_STYLES = [
        "bold"=>1,
        "slim"=>2,
        "italic"=>3,
        "underline"=>4,
        "blink"=>5,
        "inverse"=>7,
        "hidden"=>8,
        "line-through"=>9
    ];

    CONST COLOR_STYLES = [
        "background"=>4,
        "text"=>3
    ];
    
    CONST COLORS_VALUES = [
        "black" => 0,
        "red" => 1,
        "green" => 2,
        "yellow" => 3,
        "blue" => 4,
        "magenta" => 5,
        "cyan" => 6,
        "white" => 7,
    ];

    CONST OPEN = "\e[";

    CONST CLOSE = "m";

    CONST NEWLINE = "\n";

    CONST RESET = "\e[0m\e[K";
    
    public static function format($textParams, $colorParams, $text) {
        $options = [];
        foreach ($textParams as $textStyle=>$enabled) {
            if ($enabled === true) {
                $options[] = self::TEXT_STYLES[$textStyle];
            }
        }
        foreach ($colorParams as $colorStyle=>$colorValue) {
            $options[] = self::COLOR_STYLES[$colorStyle].self::COLORS_VALUES[$colorValue];
        }
        
        return self::OPEN . implode(";",$options) . self::CLOSE . $text . self::RESET;
    }
    
}

?>