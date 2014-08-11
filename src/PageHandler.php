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
        $methodName = 'execute' . StringUtils::camelCase($this->app->request->arg('_method'));
        // default
        $this->app->response
            ->setTemplate('vendor/bnowack/phweb/src/templates/raw.tpl')
            ->setStatusCode(501)
            ->setTemplateVar('content', 'Not Implemented')
        ;
        // custom        
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        }
    }   

    public function execute() {
        $this->app->response
            ->setStatusCode(200)
            ->setTemplate('vendor/bnowack/phweb/src/templates/page.html.tpl')
            ->setTemplateVar('app-last-modified', $this->app->getModificationTime())
            ->addScript('{base}vendor/jrburke/requirejs/require.js')
            ->addScript('{base}config/require-config.js')
            ->addScript('{base}src/js/app.js')
        ;
    }

}
