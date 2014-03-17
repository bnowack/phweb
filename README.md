phweb
=====

A light-weight web framework for PHP

### Components

* Request
* Response
* Routing
* Template
* String Utilities
* Email sending (requires PHPMailer)

### Sample directory structure for web applications

* /config
    * application.ini
* /src
    * /phweb
    * /your-project
* /templates
* /vendor
* .htaccess
* composer.json
* index.php

### Sample .htaccess
    RewriteEngine On
    RewriteBase /

    # Disable access to php files in vendor or src folders
    RewriteCond %{REQUEST_URI} /(vendor|src).+\.php$
    RewriteRule .* - [F,L]

    # Redirect all other requests to the front controller.
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule .* index.php [L]



### Sample index.php

    <?php

    require_once('vendor/autoload.php');
    require_once('src/phweb/Application.php');

    $config = parse_ini_file('config/application.ini', true, INI_SCANNER_RAW);
    $app = new \phweb\Application($config);
    $app->run();

### Sample composer.json

    {
        "require": {
            "phpmailer/phpmailer": ">5.2.0"
        }
    }
