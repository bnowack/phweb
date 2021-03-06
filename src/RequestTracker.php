<?php

namespace phweb;

class RequestTracker {
    
    protected $app;
    protected $db;
    
	protected $schema = array(
		'requests' => array(
			'fields' => array(
				'dateUts INTEGER',
				'ip',
                'host',
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
        $this->db = $this->getDatabase();
    }

    public function getDatabase($monthOffset = 0) {
        $nowXsd = DateTimeUtils::format('Y-m', DateTimeUtils::getUtcUts());
        $offsetSuffix = " -$monthOffset month";
        $date = date_create($nowXsd . $offsetSuffix);
        $name = "requests-" . $date->format('Y-m');
        //$name = "requests-" . DateTimeUtils::format('Y-m', DateTimeUtils::getUtcUts());
        $path = 'data/tracker/';
        $db = new Database($name, $path);
        if ($db->created) {
            $db->exec('PRAGMA journal_mode=WAL;');
			$db->updateSchema($this->schema);
		}
        return $db;
    }
    
    public function track() {
        $req = $this->app->request;
        $ip = getenv('HTTP_CLIENT_IP')?:
            getenv('HTTP_X_FORWARDED_FOR')?:
            getenv('HTTP_X_FORWARDED')?:
            getenv('HTTP_FORWARDED_FOR')?:
            getenv('HTTP_FORWARDED')?:
            getenv('REMOTE_ADDR')
        ;
        if ($req->method === 'GET' && !preg_match('/^(192|::)/', $ip)) {
            $ipInfo = json_decode(file_get_contents("http://freegeoip.net/json/$ip"), true);
            $data = array(
                'dateUts' => DateTimeUtils::getUtcUts(),
                'ip' => $ip,
                'host' => gethostbyaddr($ip),
                'city' => !empty($ipInfo['city']) ? $ipInfo['city'] : '',
                'region' => !empty($ipInfo['region_name']) ? $ipInfo['region_name'] : '',
                'country' => !empty($ipInfo['country_code']) ? $ipInfo['country_code'] : '',
                'path' => $req->cleanPath,
                'referrer' => $req->arg('HTTP_REFERER', 'server'),
                'misc' => ''
            );
            if (!$this->isDupe($data)) {
                $this->db->insert('requests', $data);
            }
        }
    }
    
    protected function isDupe($data) {
        $q = 'SELECT 1 FROM requests WHERE ip = :ip AND dateUts > :expUts';
        $parms = array(
            'ip' => $data['ip'],
            'expUts' => $data['dateUts'] - 1800 // 30 mins
        );
        return $this->db->selectFirst($q, $parms) ? true : false;
    }
    
}
