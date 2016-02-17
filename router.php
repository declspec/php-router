<?php
require('route.php');
require('urlmatcher.php');

class Router {
    private $_routes = array();   
    private $_matchers = array();
    
    public function register($method, $path, $middleware, $handler) {
        if ($handler === null) {
            $handler = $middleware;
            $middleware = null;   
        }

        $this->_routes[] = new Route(
            $method, 
            $path === "*" ? null : $path, // both * and null indicate an always matching route.
            $middleware, 
            $handler
        );   
    }
    
    public function match($url, $method, &$params) { 
        $search = strpos($url, '?');
        if ($search !== false)
            $url = substr($url, 0, $search);
        
        foreach($this->_routes as $route) {
            if ($route->method !== null && $route->method !== $method) 
                continue;
                
            // Is it a fast-matcher?
            if ($route->path === null) {
                // reset params so that they don't carry over from previous matches
                $params = array(); 
                return $route;
            }
                
            $id = spl_object_hash($route);
            $matcher = isset($this->_matchers[$id]) 
                ? $this->_matchers[$id]
                : ($this->_matchers[$id] = UrlMatcher::create($route->path));

            if ($matcher->match($url, $params)) 
                return $route;    
        }
        
        return null;
    }
}
?>