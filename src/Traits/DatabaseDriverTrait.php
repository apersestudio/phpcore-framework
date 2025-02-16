<?php

namespace PC\Traits;

use Exception;
use PC\Abstracts\ADatabaseDriver;
use PC\Singletons\Config;

trait DatabaseDriverTrait {

    /**
     * Establishes a connection with the database using the configuration entry with the given name
     * @param string $connectionName 
     * @return void 
     * @throws Exception 
     */
    public static function getDatabaseDriver(string $connectionName):ADatabaseDriver {

        // Load the databases configuration file
        $databaseConfig = Config::get("databases.{$connectionName}");
        if (is_null($databaseConfig)) {
            $baseMessage = "Missing configuration key";
            error_log($baseMessage.": {$connectionName}", 0);
            throw new Exception($baseMessage);
        }

        $driverClass = ucfirst(strtolower($databaseConfig["driver"]));
        $driverClass = ($driverClass === "Postgres") ? "Base" : $driverClass;
        $driverClassPath = "\\PC\\Databases\\{$driverClass}";
        
        $connectionClass = "{$driverClass}Connection";
        $connectionClassPath = "\\PC\\Databases\\{$driverClass}\\{$connectionClass}";

        if (class_exists($driverClassPath) === false) {
            $baseMessage = "Missing driver";
            error_log($baseMessage.": the database driver {$driverClassPath} is not installed.", 0);
            throw new Exception($baseMessage);
        }

        if (class_exists($connectionClassPath) === false) {
            $baseMessage = "Missing connection handler";
            error_log($baseMessage.": the database connection handler {$connectionClassPath} is not installed.", 0);
            throw new Exception($baseMessage);
        }
        
        /** @var ADatabaseDriver $connection */
        $databaseDriver = $driverClassPath::getInstance();

        // Connect to the database
        /** @var AConnectionData $connectionData */
        $connectionData = new $connectionClassPath($databaseConfig);
        $databaseDriver->connect($connectionData);

        return $databaseDriver;

    }

}

?>