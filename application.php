<?php
require("request.php");
require("route.php");
require("urlmatcher.php");

class Application {
    private $_routes = array();
    private $_matchers = array();
    

    public function run() {
        $req = new Request();  
        
        if ($this->handleRequest($req) !== false)
            return; // TODO: How to handle 404s
    }
      
    
    // Router wrappers
    public function all($path, $middleware, $handler=null) {
        $this->registerRoute(null, $path, $middleware, $handler);   
    }
    
    public function get($path, $middleware, $handler=null) {
        $this->registerRoute("GET", $path, $middleware, $handler);   
    }
    
    public function post($path, $middleware, $handler=null) {
        $this->registerRoute("POST", $path, $middleware, $handler);   
    }
    
    public function put($path, $middleware, $handler=null) {
        $this->registerRoute("PUT", $path, $middleware, $handler);   
    }
    
    public function delete($path, $middleware, $handler=null) {
        $this->registerRoute("DELETE", $path, $middleware, $handler);   
    }
    
    private function registerRoute($method, $path, $middleware, $handler) {
        if ($handler === null) {
            $handler = $middleware;
            $middleware = null;   
        }

        $this->_routes[] = new Route($method, $path, $middleware, $handler);   
    }
    
    private function handleRequest($req) {
        $route = $this->findRoute($req, $params);
        if ($route === null)
            return false;
            
        $url = $req->url; // keep track of the URL.
        
        if ($route->middleware !== null) {
            foreach($route->middleware as $mw) {
                if (call_user_func($mw, $req) === true)
                    return true;
                else if ($req->url !== $url)
                    return $this->handleRequest($req) !== false;   
            }
        }
        
        // All middleware executed and no handler found
        return call_user_func($route->handler, $req)
            || ($url !== $req->url && $this->handleRequest($req) !== false)
            || false;
    }
    
    private function findRoute($req, &$params) { 
        foreach($this->_routes as $route) {
            if ($route->method !== null && $route->method !== $req->method) 
                continue;
                
            $id = spl_object_hash($route);
            $matcher = isset($this->_matchers[$id]) 
                ? $this->_matchers[$id]
                : ($this->_matchers[$id] = UrlMatcher::create($route->path));
            
            if ($matcher->match($req->path, $params)) 
                return $route;    
        }
        
        return null;
    }
};