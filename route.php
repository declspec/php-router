<?php
class Route {
    public $method;
    public $handler;
    public $path;
    
    public function __construct($method, $path, callable $handler) {
        $this->method = $method !== null ? strtoupper($method) : null;
        $this->path = $path;
        $this->handler = $handler;
    }
};