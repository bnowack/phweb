<?php

namespace phweb;

class Session {
    
    protected $db;
	protected $schema = array(
		'sessions' => array(
			'fields' => array(
                'id',
				'createdUts INTEGER',
				'modifiedUts INTEGER',
                'data',
			),
			'indexes' => array(
				'id',
			)
		)
	);
    protected $id;
    protected $createdUts;
    protected $modifiedUts;
    protected $data;
    protected $cookieName;
    protected $cookieBase;
    protected $expiration = 30; // in days
    
    public function __construct(Application $app, $id = null) {
        $this->app = $app;
        $this->db = $this->getDatabase();
		$this->cookieName = substr(md5($this->app->request->siteUrl), -16);
		$this->cookieBase = $this->app->request->base;
        if (!$id) {
            $this->restoreIdFromCookie();
        }
        else {
            $this->id = $id;
        }
        $this->load();
    }
    
    public function getDatabase() {
        $name = 'sessions';
        $path = 'data/';
        $db = new Database($name, $path);
        if ($db->created) {
            $db->exec('PRAGMA journal_mode=WAL;');
			$db->updateSchema($this->schema);
		}
        return $db;
    }
    
    public function restoreIdFromCookie() {
        $this->id = $this->app->request->arg($this->cookieName, 'cookie');
    }
    
    public function load() {
        $row = $this->db->selectFirst('SELECT * FROM sessions WHERE id = :id', array('id' => $this->id));
        if ($row) {
            $this->createdUts = $row['createdUts'];
            $this->modifiedUts = $row['modifiedUts'];
            $this->data = json_decode($row['data'], true);
        }
    }
    
	public function get($name) {
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
    
	public function set($name, $value) {
		$this->data[$name] = $value;
		$this->changed = true;
		return $this;
	}
    
	public function save($force = false) {
		if ($this->id && ($force || $this->changed)) {
            $this->db->insertReplace(
                'sessions', 
                array(
                    'id' => $this->id,
                    'createdUts' => $this->createdUts ?: DateTimeUtils::getUtcUts(),
                    'modifiedUts' => DateTimeUtils::getUtcUts(),
                    'data' => json_encode($this->data),
                ),
                array('id' => $this->id)
            );
            $this->load();
        }
        return $this;
	}
	
	public function create() {
		$this->id = StringUtils::rand(16, 's');
		$this->setCookie()
            ->save()
			->removeExpiredSessions();
		return $this;
	}
	
	public function destroy() {
		if ($this->id) {
            $this->db->delete('sessions', array('id' => $this->id));
        }
        return $this;
	}
	
	protected function removeExpiredSessions() {
		$expUts = DateTimeUtils::getUtcUts() - (3600 * 24 * $this->expiration);
		$q = "DELETE FROM sessions WHERE (modifiedUts < :expUts)";
		$this->db->executeStatement($q, array('expUts' => $expUts));
		return $this;
	}
	
	protected function setCookie() {
		$exp = time() + (3600 * 24 * $this->expiration);
		$this->app->response->addCookie($this->cookieName, $this->id, $exp, $this->cookieBase);
		return $this;
	}
	
	protected function removeCookie() {
		$exp = time() - 1000; // past
		$this->app->response->setCookie($this->cookieName, $this->id, $exp, $this->cookieBase);
		return $this;
	}
	
	public function getToken() {
		return md5($this->id);
	}
	    
}
