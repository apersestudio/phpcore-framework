<?php


# Variables which comes from nginx or php rewrite
$parsedURL = parse_url($_SERVER["REQUEST_URI"]);
$parsedQuery = $parsedURL["query"] ?? "";
parse_str($parsedQuery, $query);

/**
 * @var boolean If the server is running on an unix like os
 */
define("SERVER_UNIXLIKE", stripos(PHP_OS, "win") === false);

/**
 * @var string The software running the php process, usually "nginx" or "apache"
 */
define("SERVER_SOFTWARE", $_SERVER["SERVER_SOFTWARE"] ?? null);

/**
 * @var string The IP address of the server
 */
define("SERVER_IP", $_SERVER["SERVER_ADDR"] ?? null);

/**
 * @var int The port number of the server listening for the request
 */
define("SERVER_PORT", intval($_SERVER["SERVER_PORT"]) ?? null);

/**
 * @var string The IP address of the user who makes the request
 */
define("REMOTE_IP", $_SERVER["REMOTE_ADDR"] ?? null);

/**
 * @var int The port number of the user who makes the request
 */
define("REMOTE_PORT", intval($_SERVER["REMOTE_PORT"]) ?? null);

/**
 * @var string The URL part after the domain without the query string
 */
define("REQUEST_URI", $parsedURL["path"]);

/**
 * @var float The time with the float fragment when the request start
 */
define("REQUEST_TIME", $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true));

/**
 * @var string The HTTP method which invokes the request
 */
define("REQUEST_METHOD", strtoupper($_SERVER["REQUEST_METHOD"] ?? ''));

/**
 * @var string The user agent used by the user
 */
define("REQUEST_USER_AGENT", $_SERVER["HTTP_USER_AGENT"] ?? null);

/**
 * @var string The accepted mime types by the user agent
 */
define("REQUEST_ACCEPT", $_SERVER["HTTP_ACCEPT"] ?? null);

/**
 * @var string The accepted languages by the user agent
 */
define("REQUEST_ACCEPT_LANGUAGE", $_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? null);

/**
 * @var boolean If the request was made using a mobile device
 */
define("REQUEST_IS_MOBILE", preg_match('/mobile/i', REQUEST_USER_AGENT));

/**
 * @var string The query string without parsing
 */
define("QUERY_STRING", $parsedQuery);

/**
 * @var array The parameters extracted from the query string as an array
 */
define("QUERY_PARAMS", $query);

/**
 * @var boolean If the request was made using SSL
 */
define("HTTP_SSL", $_SERVER["REQUEST_SCHEME"] === "https" || $_SERVER["HTTPS"] === "on" || SERVER_PORT === 443);

/**
 * @var string The protocol used for the user-server communication, can be "http" or "https"
 */
define("HTTP_PROTOCOL", "http".(HTTP_SSL ? "s" : ""));

/**
 * @var string The http version used for the user-server communication, cab be "HTTP/1.0", "HTTP/1.1", "HTTP/2.0" and so on
 */
define("HTTP_PROTOCOL_VERSION", $_SERVER["SERVER_PROTOCOL"] ?? null);

/**
 * @var string The self fragment without the protocol, request uri and query string
 */
define("HTTP_HOST", $_SERVER["HTTP_HOST"]);

/**
 * @var string The self fragment including the protocol
 */
define("HTTP_DOMAIN", HTTP_PROTOCOL."://".HTTP_HOST);

/**
 * @var string The full URL as requested by the user
 */
define("HTTP_SELF", HTTP_DOMAIN.$_SERVER["REQUEST_URI"]);

/**
 * @var string The full URL of the window which originates this request (not always defined)
 */
define("HTTP_REFERER", $_SERVER["HTTP_REFERER"] ?? null);