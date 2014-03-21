<?php

namespace phweb;

class Database {
    
    protected $basePath = 'data';
    protected $dbPath;
    protected $pdo;
	protected $statements;
    public $created = false;
    
    public function __construct($name, $basePath = null) {
        if ($basePath && $basePath !== $this->basePath) {
            $this->basePath = $basePath;
        }
        $this->dbPath = "$this->basePath/$name.sqlite";
        $this->createPdo();
    }
    
    protected function createPdo() {
        if (!file_exists($this->dbPath)) {
            FileUtils::createFile($this->dbPath);
            $this->created = true;
        }
        $this->pdo = new \PDO("sqlite:$this->dbPath");
    }
    
	public function updateSchema($schema) {
		foreach ($schema as $table => $info) {
            // table
			if (!$this->hasTable($table)) {
				$this->createTable($table, $info['fields'], $info['indexes']);
			}
            // columns
            
            // indexes
            
		}
		return $this;
	}
	
	public function modified() {
		return filemtime($this->dbPath);
	}
	
	protected function cleanParameters($q, $parms) {
		if (!empty($parms)) {
            foreach ($parms as $key => $value) {
                $parms[$key] = StringUtils::toUtf8($value);
                if (!is_numeric($key) && !strpos($q, ":$key")) {
                    unset($parms[$key]);
                }
            }
        }
		return $parms;
	}
	
	public function getFilterInfo($parms) {
		$result = array('', array());
		if (!empty($parms)) {
            foreach ($parms as $col => $val) {
                $result[0] .= $result[0] ? ' AND ' : '';
                $result[0] .= "$col = :match_$col";
                $result[1]["match_$col"] = $val;
            }
        }
		return $result;
	}
	
	protected function getStatement($q) {
		if (!isset($this->statements[$q])) {
			$stmt = $this->pdo->prepare($q);
			if (!$stmt) {
				$errorInfo = $this->pdo->errorInfo();
				throw new \Exception("Could not prepare statement ($errorInfo[2]) from $q");
			}
			$this->statements[$q] = $stmt;
		}
		return $this->statements[$q];
	}
	
	public function resetStatementCache() {
		$this->statements = array();
		return $this;
	}
	
	protected function executeStatement($q, $parms) {
		$stmt = $this->getStatement($q);
		$parms = $this->cleanParameters($q, $parms);
		$result = $stmt->execute($parms);
		$err = $stmt->errorInfo();
		if ($err[1]) {
			throw new \Exception("$err[2] ($q)");
		}
		return $result;
	}
	
	public function exec($q, $parms = null) {
		if ($parms) {
			$this->executeStatement($q, $parms);
		}
		else {
			$this->pdo->exec($q);
		}
		return $this;
	}
	
	public function dropDatabase() {
        return FileUtils::removeFile($this->dbPath);
    }
	
	public function attach($path, $alias) {
		if (!file_exists($path)) {
			throw new \Exception("Database $path does not exist.");
		}
		return $this->exec("ATTACH '$path' as $alias")->resetStatementCache();
	}
	
	public function detach($alias) {
		return $this->exec("DETACH $alias")->resetStatementCache();
	}
	
	public function hasTable($table) {
		$q = "SELECT name FROM sqlite_master WHERE type = 'table' AND name = '$table'";
		$stmt = $this->pdo->query($q);
		$rows = $stmt ? $stmt->fetchAll() : array();
		$stmt->closeCursor();
		return empty($rows) ? false : true;
	}
	
	public function createTable($table, $fields, $indexes = array()) {
		$this->exec("CREATE TABLE IF NOT EXISTS $table (" . implode(', ', $fields) . ")");
		foreach($indexes as $index) {
			$column = is_array($index) ? $index[0] : $index;
			$dir = is_array($index) ? $index[1] : null;
			$this->createIndex($table, $column, $dir);
		}
		return $this->resetStatementCache();
	}
	
	public function dropTable($table) {
		return $this->exec("DROP TABLE IF EXISTS $table")->resetStatementCache();
	}
	
	public function clearTable($table) {
		return $this->exec("DELETE FROM $table")->resetStatementCache();
	}
	
	public function createIndex($table, $column, $dir = 'ASC') {
		$this->exec("CREATE INDEX IF NOT EXISTS $column ON $table ($column $dir)");
	}
	
	public function rowCount($q) {
		return $this->getStatement($q)->rowCount();
	}
	
	public function hasRow($table, $matchParms) {
		list ($filterQ, $filterParms) = $this->getFilterInfo($matchParms);
		$q = "SELECT 1 FROM $table WHERE ($filterQ)";
		return $this->selectFirst($q, $filterParms) ? true : false;
	}
	
	/**
	 * Insert the values into the given table.
	 * 
	 * @param str $table
	 * @param array $parms 
	 */
	public function insert($table, $parms, $returnCount = false) {
		$colString = implode(', ', array_keys($parms));
		$valString = implode(', :', array_keys($parms));
		$q = "INSERT INTO $table ($colString) VALUES (:$valString)";
		$this->executeStatement($q, $parms);
		return $returnCount ? $this->rowCount($q) : $this;
	}
	
	public function insertReplace($table, $parms, $matchParms, $returnCount = false) {
		if ($this->hasRow($table, $matchParms)) {
			return $this->update($table, $parms, $matchParms, $returnCount);
		}
		else {
			return $this->insert($table, $parms, $returnCount);
		}
	}
	
	public function insertMulti($table, $rows) {
		$this->beginTransaction();
		foreach ($rows as $row) {
			$this->insert($table, $row);
		}
		$this->commitTransaction();
		return $this;
	}
	
	public function beginTransaction() {
		try {
			$this->pdo->beginTransaction();
			return true;
		}
		catch (\PDOException $e) {
			return false;
		}
	}
	
	public function commitTransaction() {
		$this->pdo->commit();
	}
	
	public function select($q, $parms = null) {
		$this->executeStatement($q, $parms);
		return $this->getStatement($q)->fetchAll(\PDO::FETCH_ASSOC);
	}
		
	public function selectFirst($q, $parms = null, $field = null) {
		$rows = $this->select($q, $parms);
		$result = $rows ? $rows[0] : array();
		if (!$field) {
            return $result;
        }
		return isset($result[$field]) ? $result[$field] : null;
	}
	
	/**
	 * Updates the values into the given table.
	 * 
	 * @param string $table
	 * @param array $parms 
	 * @param array $matchParms 
	 */
	public function update($table, $parms, $matchParms = null, $returnCount = false) {
		$q = "UPDATE $table SET ";
		$subQ = '';
		foreach (array_keys($parms) as $col) {
			if (!is_numeric($col)) {
    			$subQ .= $subQ ? ', ' : '';
        		$subQ .= "$col = :$col";
            }
		}
		$q .= $subQ;
		list ($filterQ, $filterParms) = $this->getFilterInfo($matchParms);
		if ($filterQ) {
			$q .= " WHERE ($filterQ)";
			$parms = array_merge($parms, $filterParms);
		}
		$this->executeStatement($q, $parms);
		return $returnCount ? $this->rowCount($q) : $this;
	}
	
	public function delete($table, $matchParms, $returnCount = false) {
		list ($filterQ, $filterParms) = $this->getFilterInfo($matchParms);
		$q = "DELETE FROM $table WHERE ($filterQ)";
		$this->executeStatement($q, $filterParms);
		return $returnCount ? $this->rowCount($q) : $this;
	}
    
    
}
