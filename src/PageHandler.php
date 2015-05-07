<?php

namespace phweb;

class PageHandler {
    
    protected $app;
    protected $pathMatch = array();
    
    public function __construct(Application $app) {
        $this->app = $app;
    }
    
    public function setPathMatch($pathMatch) {
        $this->pathMatch = $pathMatch;
    }
    
    public function hasSubMethod() {
        $methodName = $this->app->request->arg('_method');
        return $methodName ? true : false;
    }
    
    public function executeSubMethod() {
        if ($this->app->response->isComplete()) {
            return;
        }
        $methodName = 'execute' . StringUtils::camelCase($this->app->request->arg('_method'));
        // default
        $this->app->response
            ->setTemplate('raw.tpl')
            ->setStatusCode(501)
            ->setTemplateVar('content', 'Not Implemented')
        ;
        // custom        
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        }
    }   

    public function execute() {
    }
    
}
