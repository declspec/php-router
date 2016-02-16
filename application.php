<?php
require("request.php");
require("route.php");
require("urlmatcher.php");

class Application {
    private $_routes;
    private $_matchers;
    
    public function __construct() {
        $this = new Router();   
    }  
    
    public function run() {
        $req = new Request();
        
        
        
        
        $route = $this->handle($req->method, $req->path, $params);
        if ($route === null)
            return;
            
        $req->params = $params;
        call_user_func($route->handler, $req);        
    }
      
    
    // Router wrappers
    public function all($path, callable $handler) {
        $this->registerRoute(null, $path, $handler);   
    }
    
    public function get($path, callable $handler) {
        $this->registerRoute("GET", $path, $handler);   
    }
    
    public function post($path, callable $handler) {
        $this->registerRoute("POST", $path, $handler);   
    }
    
    public function put($path, callable $handler) {
        $this->registerRoute("PUT", $path, $handler);   
    }
    
    public function delete($path, callable $handler) {
        $this->registerRoute("DELETE", $path, $handler);   
    }
    
    private function registerRoute($method, $path, callable $handler) {
        $this->_routes[] = new Route($method, $path, $handler);   
    }
    
    private function handleRequest($req) {
        // First, find a matching route.   
        foreach($this->_routes as $route) {
            if ($route->method !== null && $route->method !== $req->method) 
                continue;
                
            $id = spl_object_hash($route);
            $matcher = isset($this->_matchers[$id]) 
                ? $this->_matchers[$id]
                : ($this->_matchers[$id] = UrlMatcher::create($route->path));
            
            if ($matcher->match($req->url, $params)) {
                
            }  
        }
    }
};