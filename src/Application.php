<?php

namespace phweb;

class Application {
    
    public $config;
    public $request;
    public $response;
    protected $routes;
    protected $backgroundProcesses = array();
    protected $session;
    
    public function __construct($config = array(), $request = null) {
        $this->config = $config;
        $this->activateAutoloadDirectories();
        $this->request = $request ?: new Request();
        $this->response = new Response($this);
        $this->routes = !empty($config['routes']) ? $config['routes'] : array();
    }
    
    public function activateAutoloadDirectories() {
        $codeDirs = $this->config('app/autoloadDirectories') ?: array();
        foreach ($codeDirs as $codeDir) {
            $this->activateAutoloadDirectory($codeDir);
        }
        return $this;
    }
    
    public function activateAutoloadDirectory($codeDir) {
        spl_autoload_register(function($className) use ($codeDir) {
            $pathParts = preg_split('/\\\/', $className);
            while ($pathParts) {
                $path = $codeDir . '/' . implode('/', $pathParts) . '.php';
                if (file_exists($path)) {
                    include_once($path);
                }
                array_shift($pathParts);
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
        foreach ($paths as $path => $handlerClasses) {
            if ($this->response->isComplete()) {
                break;
            }
            $pathMatch = $this->isMatchingPath($path);
            if (!$pathMatch) {
                continue;
            }
            foreach (explode(' ', $handlerClasses) as $handlerClass) {
                $this->executeRouteHandler($handlerClass, $pathMatch);
            }
        }
    }
    
    protected function executeRouteHandler($className, $pathMatch) {
        $handler = new $className($this);
        $handler->setPathMatch($pathMatch);
        if ($handler->hasSubMethod()) {
            $handler->executeSubMethod();
        }
        else {
            $handler->execute();
        }
    }
    
    protected function isMatchingHttpMethod($method) {
        return ($method === 'any' || $method === $this->request->method);
    }
    
    protected function isMatchingPath($path) {
        $matches = false;
        return preg_match('~^' . $path . '$~', '/' . $this->request->cleanPath, $matches) ? $matches : false;
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
        if (count($this->backgroundProcesses)) {
            ignore_user_abort(true);
            ob_start();
            $this->response->send();
            header("Content-Encoding: none");               // disable gzipping
            header("Content-Length: " . ob_get_length());   // send length header
            header("Connection: close");                    // close the connection
            ob_end_flush();flush();                         // flush all buffers
            foreach ($this->backgroundProcesses as $process) {
                call_user_func_array($process['callback'], $process['params']);
            }
        }
        else {
            $this->response->send();
        }
    }
    
    public function addBackgroundProcess($callback, $params = array()) {
        $this->backgroundProcesses[] = array('callback' => $callback, 'params' => $params);
    }
    
    public function getSession() {
        if (!$this->session) {
            $this->session = new Session($this);
        }
        return $this->session;
    }
}

