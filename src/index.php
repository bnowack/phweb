<?php

/**
 * Default index.php
 * 
 * Just require/include it from your application's index.php.
 * 
 */
require_once('vendor/autoload.php');
require_once('vendor/bnowack/phweb/src/Application.php');

$config = parse_ini_file('config/application.ini', true, INI_SCANNER_RAW);
$app = new \phweb\Application($config);
$app->run();
