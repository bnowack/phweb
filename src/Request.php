<?php

namespace phweb;

class Request {
    
    public $base;
    public $path;
    public $cleanPath;
    public $resourcePath;
    public $extension;
    public $pathParts;
    public $method;
    public $siteUrl;
    public $arguments;
    
    public function __construct() {
        $this->buildArguments();
        $this->host = $this->arg('Host', 'headers') ?: $this->arg('SERVER_NAME', 'server');
        $this->base = preg_replace('/index\.php$/', '', $this->arg('SCRIPT_NAME', 'server'));
        $this->path = preg_replace('/^' . preg_quote($this->base, '/') . '/', '', $this->arg('REQUEST_URI', 'server'));
        $this->cleanPath = preg_replace('/\?.*$/', '', $this->path);
        $this->resourcePath = preg_replace('/\..*$/', '', $this->cleanPath);
        $this->extension = preg_replace('/^.*\.?(.*)$/', '\\1', $this->cleanPath);
        $this->pathParts = explode('/', $this->cleanPath);
        $this->method = $this->arg('REQUEST_METHOD', 'server');
        $this->siteUrl = "http://{$this->host}{$this->base}";
    }
    
    protected function buildArguments() {
        $mq = get_magic_quotes_gpc();
        $this->arguments['get'] = $mq ? StringUtils::removeMagicQuotes($GLOBALS['_GET']) : $GLOBALS['_GET'];
        $this->arguments['post'] = $mq ? StringUtils::removeMagicQuotes($GLOBALS['_POST']) : $GLOBALS['_POST'];
        $this->arguments['cookie'] = $mq ? StringUtils::removeMagicQuotes($GLOBALS['_COOKIE']) : $GLOBALS['_COOKIE'];
        $this->arguments['files'] = $GLOBALS['_FILES'];
        $this->arguments['server'] = empty($_SERVER) ? array() : $_SERVER;
        $this->arguments['headers'] = $this->buildHeaderArguments();
        $this->arguments['put'] = $this->buildPutArguments();
    }
    
	/**
	 * Extracts HTTP headers from the 'server' global
	 */
	protected function buildHeaderArguments() {
		$result = array();
		foreach ($this->arg('*', 'server') as $k => $v) {
			if (substr($k, 0, 5) !== 'HTTP_') {
                continue;
            }
            $name = str_replace('_', '-',   // replace _ with -
                ucfirst(                    // uppercase 1st character
                    strtolower(             // lowercase value
                        substr($k, 5)       // remove HTTP_ prefix
                    )
                )
            );
            // ucfirst individual sections
            $m = null;
            while (preg_match('/\-([a-z])/', $name, $m)) {
                $name = str_replace("-${m[1]}", '-' . ucfirst($m[1]), $name);
            }
			$result[$name] = $v;
		}
		return $result;
	}
	
	protected function buildPutArguments() {
		$result = array();
		if ($this->arg('REQUEST_METHOD', 'server') !== 'PUT') {
            return $result;
        }
        $fp = fopen("php://input", "r");
        if ($fp) {
            $raw = stream_get_contents($fp);
            parse_str($raw, $result);
            if (get_magic_quotes_gpc()) {
                $result = StringUtils::removeMagicQuotes($result);
            }
            $result['RAW-DATA'] = $raw;
            fclose($fp);
        }
		return $result;
	}
    
    public function arg($name, $category = '') {
		$categories = $category ? array($category) : array_keys($this->arguments);
		foreach ($categories as $category) {
			if (!isset($this->arguments[$category])) {
                continue;
            }
            if (isset($this->arguments[$category][$name])) {
                return $this->arguments[$category][$name];
            }
            if ($name == '*') {
                return $this->arguments[$category];
            }
		}
		return null;
    }
        
}

