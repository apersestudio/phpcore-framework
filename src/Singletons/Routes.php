<?php

namespace PC\Singletons;

use PC\Traits\ValidateTrait;

use Closure;
use PC\Core;

//$allConstants = get_defined_constants(true);
//print_r($allConstants["user"]);

class Routes {

    use ValidateTrait;

    private $uri = null;

    private function updateURI($replacement):void {
        // Everytime we match a fragment of the URL
        // This fragment gets removed from the current uri
        $this->uri = str_replace("/".$replacement, "", $this->uri);
    }

    private function cleanMatches(&$matches):void {
        $matches = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
    }

    /**
     * Takes a route expression defined by the developer in the routes file
     * and converts it to a valid regular expression to test the request uri
     * @param mixed $routeExpression 
     * @return string 
     */
    private function buildRegex($routeExpression) {
        return "#".preg_replace("#/\{([\w\d]+)\}#", "/(?<$1>[\w\d]+)", $routeExpression)."#";
    }

    private function matchesAtTheBegining(string $pattern, array | null &$matches=[]) {
        if (preg_match("#^/(?<".$pattern.">".$pattern.")#", $this->uri, $matches)) {

            // Since $matches variable was passed by reference,
            // this code updates the $matches
            $this->cleanMatches($matches);

            return true;
        }

        return false;
    }

    public function onPrefixLoadRoutes(string $prefix, string $routesFile) {
        if (is_null($this->uri)) {
            $this->uri = REQUEST_URI;
        }

        if ($this->matchesAtTheBegining($prefix, $matches)) {

            $this->updateURI($matches[$prefix]);
            
            $routesPath = Core::DIR_APP()."/Routes/".$routesFile.".php";
            require_once($routesPath);
            
        }
    }

    public function prefix(string $prefix, Closure $handler) {
        
        if (is_null($this->uri)) {
            $this->uri = REQUEST_URI;
        }

        if ($this->matchesAtTheBegining($prefix, $matches)) {

            $this->updateURI($matches[$prefix]);
            
            // Creates a new handler which scope is this instance
            $handler = $handler->bindTo($this);
            $handler($prefix);
        }
    }

    public function callControllerByMethod(string $controllerClass, string $method, array $arguments=[]) {
        $controllerInstance = new $controllerClass();
        call_user_func_array([$controllerInstance, $method], $arguments);

        // By calling the controller before we let the route to execute sub-routes
        // But if it does match, then exit should finish the execution to prevent future routes
        exit();
    }

    /**
     * Maps a URI to a group of controller's methods based on the request method and uri id.
     * Valid IDs are integer or float values, as well as ULID and UUID identifiers.
     * @param string $section 
     * @param string $controllerClass 
     * @return void 
     */
    public function resource(string $section, string $controllerClass) {

        if ($this->matchesAtTheBegining($section, $matches)) {

            $this->updateURI($matches[$section]);

            $id = ltrim($this->uri, "/");
            $hasValidId = $this->validUlid($id) || $this->validUuid($id) || $this->validId($id);
            if ($hasValidId && REQUEST_METHOD === "POST") {
                $this->callControllerByMethod($controllerClass, "update");
            } else if ($hasValidId && REQUEST_METHOD === "GET") {
                $this->callControllerByMethod($controllerClass, "show");
            } else if ($hasValidId && REQUEST_METHOD === "DELETE") {
                $this->callControllerByMethod($controllerClass, "delete");
            } else if (empty($id) && REQUEST_METHOD === "POST") {
                $this->callControllerByMethod($controllerClass, "create");
            } else {
                $this->callControllerByMethod($controllerClass, "index");
            }
        }

    }

    public function get(string $routeExpression, array $controllerOptions) {
        $regex = $this->buildRegex($routeExpression);
        if (preg_match($regex, $this->uri, $matches)) {
            $this->cleanMatches($matches);
            $this->callControllerByMethod($controllerOptions[0], $controllerOptions[1], $matches);
        }
    }

}
?>