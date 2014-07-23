phweb
=====

A light-weight web framework for PHP

### Components

* Request
* Response
* Routing
* Template
* String/Date/File Utilities
* Email sending (requires PHPMailer)
* SQLite storage
* Sessions
* Request Tracker

### Sample directory structure for web applications

* /config
    * application.ini
* /src
    * /css
    * /js
    * /templates
    * /[class].php
* /vendor
    * /bnowack/phweb
    * /phpmailer/phpmailer
* .htaccess
* composer.json
* index.php

### Sample .htaccess
    RewriteEngine On
    RewriteBase /

    # Hide Git files 
    RedirectMatch 404 /\.git

    # Redirect non-file/non-dir requests to the front controller.
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule .* index.php [L]


### Sample index.php

    <?php

    require_once('vendor/autoload.php');
    require_once('vendor/phweb/src/Application.php');

    $config = parse_ini_file('config/application.ini', true, INI_SCANNER_RAW);
    $app = new \phweb\Application($config);
    $app->run();


### Sample composer.json

    "require": {
        "bnowack/phweb": "@dev",
        "phpmailer/phpmailer": "v5.2.8",
        "jquery/jquery": "1.11.1",
        "h5bp/html5-boilerplate": "v4.3.0",
        "jrburke/requirejs": "2.1.14",
        "jashkenas/underscore": "1.6.0",
        "jashkenas/backbone": "1.1.2",
        "requirejs/text": "2.0.12"
    }

    see composer.json for repository definitions


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

