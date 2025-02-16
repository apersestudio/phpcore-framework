<?php

namespace PC\Databases\Postgres;

use PC\Abstracts\AConnectionData;

class PostgresConnection extends AConnectionData {

    public function getDSN():string {
        $dsnParameters = [];
        if (!$this->hostIsEmpty()) { $dsnParameters[] = "host={$this->host}"; }
        if (!$this->portIsEmpty()) { $dsnParameters[] = "port={$this->port}"; }
        if (!$this->databaseIsEmpty()) { $dsnParameters[] = "dbname={$this->database}"; }
        if (!$this->sslModeIsEmpty()) { $dsnParameters[] = "sslmode={$this->sslMode}"; }
        if (!$this->sslCertIsEmpty()) { $dsnParameters[] = "sslcert={$this->sslCert}"; }
        if (!$this->sslKeyIsEmpty()) { $dsnParameters[] = "sslkey={$this->sslKey}"; }
        if (!$this->sslRootCertIsEmpty()) { $dsnParameters[] = "sslrootcert={$this->sslRootCert}"; }
        return "pgsql:".implode(";", $dsnParameters);
    }

    /**
     * @param array $params Connection parameters:
     *  - driver: (string) The name of sql engine to use
     *  - database: (string) The name of the schema or database
     *  - username: (string) The user name to connecto to the database
     *  - password: (string) The password
     *  - host: (string) The hostname or IP address to connect to
     *  - port: (int|string) The port number of the database server
     *  - sslmode: (string|null) The mode to connect to the database
     *  - sslcert: (string|null) The directory path to the database certificate
     *  - sslkey: (string|null) The directory path to the database key
     *  - sslrootcert: (string|null) The directory path to the issue root certificate
     * @return string 
     * @throws Exception 
     */
    public function __construct(array $params) {
        if (isset($params["driver"])) { $this->driver = $params["driver"]; }
        if (isset($params["database"])) { $this->database = $params["database"]; }
        if (isset($params["username"])) { $this->username = $params["username"]; }
        if (isset($params["password"])) { $this->password = $params["password"]; }

        if (isset($params["host"])) { $this->host = $params["host"]; }
        if (isset($params["port"])) { $this->port = $params["port"]; }
        if (isset($params["sslmode"])) { $this->sslMode = $params["sslmode"]; }

        if (isset($params["sslcert"])) { $this->sslCert = $params["sslcert"]; }
        if (isset($params["sslkey"])) { $this->sslKey = $params["sslkey"]; }
        if (isset($params["sslrootcert"])) { $this->sslRootCert = $params["sslrootcert"]; }
    }
    
}
?>