<?php

namespace web;

class Template {
    
    protected $path;
    public $vars;
    public $result;
    

    public function __construct($path, $vars = array()) {
        $this->path = "templates/$path";
        $this->vars = $vars;
        $this->result = '';
    }
    
    public function render() {
        $this->execute();
        echo $this->result;
    }
    
    public function execute($depth = 0) {
        return $this
            ->load()
            ->replaceVariables()
            ->replaceVariables()                // support nested variables
            ->renderSubTemplates($depth + 1)
        ;
    }
    
    protected function load() {
        if (file_exists($this->path)) {
            $this->result = file_get_contents($this->path);
        }
        return $this;
    }
    
    protected function replaceVariables() {
        foreach ($this->vars as $name => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                $value = print_r($value, true);
            }
            if (is_string($name)) {
                $this->result = str_replace("{{$name}}", $value, $this->result);
            }
        }
        return $this;
    }
    
    protected function renderSubTemplates($depth) {
        $template = $this;
        $this->result = preg_replace_callback('/\{(\/[^\}]+)\}/', function($matches) use ($template, $depth) {
            if ($depth <= 16) {
                $subTemplate = new Template($matches[1], $template->vars);
                $subTemplate->execute($depth);
                return $subTemplate->result;
            }
            else {
                return "[too many nested templates in $matches[1]]";
            }
        }, $this->result);
        return $this;
    }
    
}

