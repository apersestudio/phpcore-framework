<?php

namespace PC\Abstracts;

use Exception;
use PC\Singletons\Config;

abstract class AConnectionData {

    protected string $driver="";
    protected string $host="";
    protected int|string $port="";
    protected string $database="";
    protected string $sslMode="";
    protected string $sslCert="";
    protected string $sslKey="";
    protected string $sslRootCert="";
    protected string $username="";
    protected string $password="";

    /**
     * Takes the parameters passed to the constructor and builds a valid DSN string
     * @return string 
     */
    abstract public function getDSN():string;

    /**
     * Takes the parameters for the driver for later building the DSN
     * @param array $params 
     * @return mixed 
     */
    abstract public function __construct(array $params);

    /**
     * Sets the driver name
     * @param string $driver 
     * @return void 
     */
    public function setDriver(string $driver):void { $this->driver = $driver; }

    /**
     * Sets the database name
     * @param string $database 
     * @return void 
     */
    public function setDatabase(string $database):void { $this->database = $database; }

    /**
     * Sets the username to connect to the database
     * @param string $username 
     * @return void 
     */
    public function setUsername(string $username):void { $this->username = $username; }

    /**
     * Sets the password to connect to the database
     * @param string $password 
     * @return void 
     */
    public function setPassword(string $password):void { $this->password = $password; }

    /**
     * Sets the host to connect to
     * @param string $host 
     * @return void 
     */
    public function setHost(string $host):void { $this->host = $host; }
    
    /**
     * Sets the port number to connect to
     * @param int|string $port 
     * @return void 
     */
    public function setPort(int | string $port):void { $this->port = $port; }

    /**
     * Sets the SSL mode for the connection
     * @param string $sslMode 
     * @return void 
     */
    public function setSSLMode(string $sslMode):void { $this->sslMode = $sslMode; }

    /**
     * Sets the path for the client's SSL certificate
     * @param string $sslCert 
     * @return void 
     */
    public function setSSLCert(string $sslCert):void { $this->sslCert = $sslCert; }

    /**
     * Sets the path for the client's SSL private key
     * @param string $sslKey 
     * @return void 
     */
    public function setSSLKey(string $sslKey):void { $this->sslKey = $sslKey; }

    /**
     * Sets the path for the server's SSL root certificate
     * @param string $sslRootCert 
     * @return void 
     */
    public function setSSLRootCert(string $sslRootCert):void { $this->sslRootCert = $sslRootCert; }

    /**
     * Gets the driver name
     * @return string 
     */
    public function getDriver():string { return $this->driver; }

    /**
     * Gets the database name
     * @return string 
     */
    public function getDatabase():string { return $this->database; }

    /**
     * Gets the username to connect to the database
     * @return string 
     */
    public function getUsername():string { return $this->username; }

    /**
     * Gets the password to connect to the database
     * @return string 
     */
    public function getPassword():string { return $this->password; }

    /**
     * Gets the host to connect to
     * @return string 
     */
    public function getHost():string { return $this->host; }
    
    /**
     * Gets the port number to connect to
     * @return int|string 
     */
    public function getPort():int | string { return $this->port; }

    /**
     * Gets the SSL mode for the connection
     * @return string 
     */
    public function getSSLMode():string { return $this->sslMode; }

    /**
     * Gets the path for the client's SSL certificate
     * @return string 
     */
    public function getSSLCert():string { return $this->sslCert; }

    /**
     * Gets the path for the client's SSL private key
     * @return string 
     */
    public function getSSLKey():string { return $this->sslKey; }

    /**
     * Gets the path for the server's SSL root certificate
     * @return string 
     */
    public function getSSLRootCert():string { return $this->sslRootCert; }

    /**
     * Checks if the driver name is empty
     * @return bool 
     */
    public function driverIsEmpty():bool { return empty($this->driver); }

    /**
     * Checks if the database name is empty
     * @return bool 
     */
    public function databaseIsEmpty():bool { return empty($this->database); }

    /**
     * Checks if the username to connect to the database is empty
     * @return bool 
     */
    public function usernameIsEmpty():bool { return empty($this->username); }

    /**
     * Checks if the password to connect to the database is empty
     * @return bool 
     */
    public function passwordIsEmpty():bool { return empty($this->password); }

    /**
     * Checks if the host to connect to is empty
     * @return bool 
     */
    public function hostIsEmpty():bool { return empty($this->host); }

    /**
     * Checks if the port number to connect to is empty
     * @return bool 
     */
    public function portIsEmpty():bool { return empty($this->port); }

    /**
     * Checks if the SSL mode for the connection is empty
     * @return bool 
     */
    public function sslModeIsEmpty():bool { return empty($this->sslMode); }

    /**
     * Checks if the path for the client's SSL certificate is empty
     * @return bool 
     */
    public function sslCertIsEmpty():bool { return empty($this->sslCert); }

    /**
     * Checks if the path for the client's SSL private key is empty
     * @return bool 
     */
    public function sslKeyIsEmpty():bool { return empty($this->sslKey); }

    /**
     * Checks if the path for the server's SSL root certificate is empty
     * @return bool 
     */
    public function sslRootCertIsEmpty():bool { return empty($this->sslRootCert); }
    
}
?>