<?php

namespace phweb;

class RequestTracker {
    
    protected $app;
    
	protected $schema = array(
		'requests' => array(
			'fields' => array(
				'dateUts INTEGER',
				'ip',
                'host',
                'network',
                'city',
                'region',
                'country',
                'path',
                'referrer',
                'misc'
			),
			'indexes' => array(
				array('dateUts', 'DESC'),
				'path',
				'referrer',
			)
		)
	);
    
    public function __construct(Application $app) {
        $this->app = $app;
    }
    
    public function track() {
        $req = $this->app->request;
        $ip = $req->arg('REMOTE_ADDR', 'server');
        //$ip = '37.201.227.117';
        if ($req->method === 'GET' && !preg_match('/^(192|::)/', $ip)) {
            $ipInfo = json_decode(file_get_contents("http://ipinfo.io/$ip/json"), true);
            $data = array(
                'dateUts' => DateTimeUtils::getUtcUts(),
                'ip' => $ip,
                'host' => !empty($ipInfo['hostname']) ? $ipInfo['hostname'] : gethostbyaddr($ip),
                'network' => !empty($ipInfo['org']) ? $ipInfo['org'] : '',
                'city' => !empty($ipInfo['city']) ? $ipInfo['city'] : '',
                'region' => !empty($ipInfo['region']) ? $ipInfo['region'] : '',
                'country' => !empty($ipInfo['country']) ? $ipInfo['country'] : '',
                'path' => $req->cleanPath,
                'referrer' => $req->arg('HTTP_REFERER', 'server'),
                'misc' => ''
            );
            print_r($data);
            $db = $this->getDatabase();
            $db->insert('requests', $data);
        }
    }
    
    public function getDatabase() {
        $name = "requests-" . DateTimeUtils::format('Y-m', DateTimeUtils::getUtcUts());
        $path = 'data/tracker/';
        $db = new Database($name, $path);
        if ($db->created) {
            $db->exec('PRAGMA journal_mode=WAL;');
			$db->updateSchema($this->schema);
		}
        return $db;
    }
    
}
