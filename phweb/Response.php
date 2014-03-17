<?php

namespace web;

class Response {
    
    protected $statusCode = 404;
    protected $templatePath = 'page.html.tpl';
    protected $templateVars = array();
    protected $stylesheets = array();
    protected $scripts = array();
    protected $complete = false;
    protected $format = 'text/html';
    protected $app;
    
    public function __construct($app) {
        $this->app = $app;
        $this->buildDefaultTemplateVars();
    }
    
    protected function buildDefaultTemplateVars() {
        // base
        $this->setTemplateVar('host', $this->app->request->host);
        $this->setTemplateVar('base', $this->app->request->base);
        $this->setTemplateVar('clean-path', $this->app->request->cleanPath);
        $this->setTemplateVar('resource-path', $this->app->request->resourcePath);
        // config
        $sections = array('app', 'meta');
        foreach ($sections as $section) {
            if (empty($this->app->config[$section])) {
                continue;
            }
            foreach ($this->app->config[$section] as $key => $value) {
                $this->setTemplateVar("$section/$key", $value);
            }
        }
        // statusCode
        $this->setTemplateVar('statusCode', $this->statusCode);
        // title (404)
        $this->setTemplateVar('page-title', 'Seite nicht gefunden');
        // content (404)
        $this->setTemplateVar('content', '{/404.html.tpl}');
    }
    
    public function setStatusCode($code) {
        $this->statusCode = $code;
        $this->setTemplateVar('statusCode', $code);
        return $this;
    }

    public function setTemplate($templatePath) {
        $this->templatePath = $templatePath;
        return $this;
    }
    
    public function setTemplateVar($name, $value) {
        $this->templateVars[$name] = $value;
        return $this;
    }
    
    public function setTemplateVars($vars) {
        foreach ($vars as $key => $value) {
            $this->templateVars[$key] = $value;
        }
        return $this;
    }
    
    public function addStylesheet($path) {
        if (!in_array($path, $this->stylesheets)) {
            $this->stylesheets[] = $path;
        }
        return $this;
    }
    
    public function addScript($path) {
        if (!in_array($path, $this->scripts)) {
            $this->scripts[] = $path;
        }
        return $this;
    }
    
    public function send() {
        $this->setStylesheetTemplateVar();
        $this->setScriptTemplateVar();
        $this->sendHeaders();
        $this->sendBody();
    }
    
    protected function setStylesheetTemplateVar() {
        array_walk($this->stylesheets, function(&$value) {
            $value = "            @import url($value);";
        });
        $this->setTemplateVar('stylesheets', trim(implode(PHP_EOL, $this->stylesheets)));
    }
    
    protected function setScriptTemplateVar() {
        array_walk($this->scripts, function(&$value) {
            $value = "        <script type=\"text/javascript\" src=\"$value\"></script>";
        });
        $this->setTemplateVar('scripts', trim(implode(PHP_EOL, $this->scripts)));
    }
    
    protected function sendHeaders() {
        $protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') ?: 'HTTP/1.0';
        $code = $this->statusCode;
        header("$protocol $code");
    }
    
    protected function sendBody() {
        echo $this->getTemplateResult();
    }

    protected function getTemplateResult() {
        $template = new Template($this->templatePath, $this->templateVars);
        $template->execute();
        return $template->result;
    }
    
}

