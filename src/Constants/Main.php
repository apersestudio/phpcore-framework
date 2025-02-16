<?php

// Ensures document root always points to root directory
chdir("/");

/** Extract environment variables into php */
$envFilePath = realpath(__DIR__."/../../.env");
if (file_exists($envFilePath)) {
    $envFile = fopen($envFilePath, "r");
    while (($line = fgets($envFile, 4096)) !== false) {
        $pair = explode("=", $line, 2);
        $var_name = $pair[0];
        // Remove newlines and double or singles quotes from the begining or end of the variable value
        $var_value = preg_replace('/^["\']|["\']$/m', "", trim($pair[1]));
        // Save the variable into the native php environments
        putenv("{$var_name}={$var_value}");
    }
}

// Configure server errors
error_reporting(E_ALL);
ini_set("display_errors", "On");
ini_set("max_execution_time", 0);
?>