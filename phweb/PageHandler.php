<?php

namespace phweb;

class PageHandler {
    
    protected $app;
    
    public function __construct(Application $app) {
        $this->app = $app;
    }
    
    public function hasSubMethod() {
        $methodName = $this->app->request->arg('_method');
        return $methodName ? true : false;
    }
    
    public function executeSubMethod() {
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
