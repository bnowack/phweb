<?php

namespace phweb;

class Application {
    
    public $config;
    public $request;
    public $response;
    protected $routes;
    
    public function __construct($config = array(), $request = null) {
        $this->activateAutoload();
        $this->config = $config;
        $this->request = $request ?: new Request();
        $this->response = new Response($this);
        $this->routes = !empty($config['routes']) ? $config['routes'] : array();
    }
    
    public function activateAutoload($sourceDir = 'src') {
        spl_autoload_register(function($className) use ($sourceDir) {
            $path = $sourceDir . '/' . str_replace('\\', '/', $className) . '.php';
            if (file_exists($path)) {
                include_once($path);
            }
        }, true, true);
    }
    
    public function processRoutes() {
        foreach ($this->routes as $httpMethod => $paths) {
            if ($this->isMatchingHttpMethod($httpMethod)) {
                $this->processRoute($paths);
            }
        }
        return $this;
    }
    
    public function processRoute($paths) {
        foreach ($paths as $path => $handlerClass) {
            if (!$this->isMatchingPath($path)) {
                continue;
            }
            $handler = new $handlerClass($this);
            if ($handler->hasSubMethod()) {
                $handler->executeSubMethod();
            }
            else {
                $handler->execute();
            }
        }
    }
    
    protected function isMatchingHttpMethod($method) {
        return ($method === 'any' || $method === $this->request->method);
    }
    
    protected function isMatchingPath($path) {
        return preg_match('~^' . $path . '$~', '/' . $this->request->cleanPath);
    }
    
    public function config($path) {
        list($section, $name) = explode('/', $path);
        if (!empty($this->config[$section]) && !empty($this->config[$section][$name])) {
            return $this->config[$section][$name];
        }
        return null;
    }
    
    public function run() {
        $this->processRoutes();
        $this->response->send();
    }
    
}
