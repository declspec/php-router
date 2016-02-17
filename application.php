<?php
require("request.php");
require("response.php");
require("router.php");

class Application {
    private $_router;
    private $_errorHandlers = array();
    
    public function __construct($baseUrl=null) {
        $this->_router = new Router($baseUrl);   
    }

    public function run() {
        $req = new Request(); 
        $res = new Response(); 
        
        try {
            // This 'Application' is supposed to be the minimum
            // layer. It does not and will not implement one-size-fits all
            // 404 and 500 error handlers, they are up to the user to configure. 
            // As a courtesy there is a default handler which simply sets the appropriate
            // status code on the response.
            if (!$this->handleRequest($req, $res))
                $res->status(404);
        }
        catch(Exception $ex) {
            // NOTE: No try-catch here, if one of the error handlers is broken
            // then there's no point trying to re-run them with a new error, it could
            // end up infinitely looping
            if (!$this->handleError($ex, $req, $res))
                $res->status(500);
        }
    }

    // Router wrappers
    public function middleware(callable $handler) {
        $this->_router->register(null, null, null, $handler);   
    }
    
    public function all($path, $middleware, $handler=null) {
        $this->_router->register(null, $path, $middleware, $handler);   
    }
    
    public function get($path, $middleware, $handler=null) {
        $this->_router->register("GET", $path, $middleware, $handler);   
    }
    
    public function post($path, $middleware, $handler=null) {
        $this->_router->register("POST", $path, $middleware, $handler);   
    }
    
    public function put($path, $middleware, $handler=null) {
        $this->_router->register("PUT", $path, $middleware, $handler);   
    }
    
    public function delete($path, $middleware, $handler=null) {
        $this->_router->register("DELETE", $path, $middleware, $handler);   
    }
    
    public function error(callable $handler) {
        $this->_errorHandlers[] = $handler;   
    }
    
    private function handleRequest($req, $res, $depth = 0) {
        if ($depth >= 10) {
            throw new RuntimeException("Internal redirect loop detected");   
        }
        
        // snapshot the URL to check if any routes want an internal redirect
        // TODO: Is this really the desired behaviour, or should url changes only affect later routes?
        $url = $req->url;
        $handled = false;
        
        while(!$handled) {
            $route = $this->_router->match($req->url, $req->method, $params);
            if ($route === null)
                return false; // no more matching routes, bail.
            
            $req->params = $params;
            if ($route->middleware !== null) {
                foreach($route->middleware as $mw) {
                    if (call_user_func($mw, $req, $res) !== false)
                        return true;
                    else if ($req->url !== $url)
                        return $this->handleRequest($req, $res, $depth + 1);   
                }
            }
            
            $handled = call_user_func($route->handler, $req) !== false;
            if (!$handled && $url !== $req->url)
                return $this->handleRequest($req, $res, $depth + 1);
        }

        return $handled;
    }
    
    private function handleError($err, $req, $res) {
        foreach($this->_errorHandlers as $handler) {
            if (call_user_func($handler, $err, $req, $res) !== false)
                return true;   
        }
        
        // No error handler was able to deal with the error.
        return false;
    }
};