<?php

namespace phweb;

/**
 * phweb file system utilities class
 * 
 * @package phweb
 * @author  Benjamin Nowack <mail@bnowack.de> 
 */
class FileUtils {

	/**
	 * Generates missing parent directories for a given $filePath.
	 * 
	 * @param string $filePath
	 * @param int $mode
	 * @return bool
	 */
	static public function createFileDirectories($filePath, $mode = 0777) {
		$dirPath = preg_replace('/\/[^\/]*$/', '', $filePath);
		if (!is_dir($dirPath)) {
			@mkdir($dirPath, $mode, true);
		}
		if (is_dir($dirPath)) {
            return true;
        }
        else {
            throw new \Exception('Could not create Directory "' . $dirPath . '"');
        }
	}
    
    static public function createFile($filePath, $mode = 0777) {
        self::createFileDirectories($filePath);
        touch($filePath);
        chmod($filePath, $mode);
    }
	
	static public function saveFile($filePath, $data, $mode = 0777) {
        self::createFile($filePath, $mode);
        $fp = fopen($filePath, 'wb');
        fwrite($fp, $data);
        fclose($fp);
	}
	
	static public function removeFile($filePath) {
		if (file_exists($filePath)) {
            unlink($filePath);
        }
        return !file_exists($filePath);
	}
    
    static public function getDirectoryModificationTime($path, $default = 0) {
        $stat = stat($path);
        return $stat ? $stat['mtime'] : $default;
    }
    
}

