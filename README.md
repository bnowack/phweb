phweb
=====

A light-weight web framework for PHP

### Components

* Request
* Response
* Routing
* Template
* String/Date/FIle Utilities
* Email sending (requires PHPMailer)
* SQLite storage
* Request Tracker

### Sample directory structure for web applications

* /config
    * application.ini
* /src
    * /your-project
* /templates
* /vendor
    * /phweb
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
    require_once('vendor/phweb/phweb/Application.php');

    $config = parse_ini_file('config/application.ini', true, INI_SCANNER_RAW);
    $app = new \phweb\Application($config);
    $app->run();


### Sample composer.json

    {
        "require": {
            "phpmailer/phpmailer": ">5.2.0"
        }
    }


### Sample config/application.ini

    [app]
    project-id = my-project
    autoloadDirectories[] = "vendor/phweb"
    autoloadDirectories[] = "src"

    [meta]
    site-title = My Title

    [smtp]
    host = smtp.my.host
    port = 587
    user = my
    password = pass
    from-address = noreply@my.host
    from-name = "My Mailer"
    to-address = info@my.host
    to-name = "My Name"
    plain-template = "email.tpl"
    html-template = "email.html.tpl"
    ;debug = true

    [routes]
    any["/"] = "\my-namespace\HomePageHandler"
    any["/contact"] = "\my-namespace\ContactPageHandler"
    post["/items"] = "\my-namespace\ItemHandler"


### Template syntax

    <h1>Welcome to {meta/site-title}</h1>

    <!-- sub-template -->

    {/my-sub-template.html.tpl}

