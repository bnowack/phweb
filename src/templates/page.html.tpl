<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <base href="{base}"/>

        <title>{page-title}{page-title-glue}{meta/site-title}</title>

        <meta name="robots" content="index,follow"/>
        <meta name="description" content="{meta/description}"/>
        <meta name="keywords" content="{meta/keywords}"/>

        <meta name="og:type" content="website"/>
        <meta name="og:site_name" content="{meta/site-title}"/>
        <meta name="og:title" content="{page-title}"/>
        <meta name="og:image" content="{base}{meta/logo}"/>
        
        <link rel="shortcut icon" href="{base}favicon.ico" data-size="32x32"/>
        <link rel="apple-touch-icon" href="{base}apple-touch-icon.png" data-size="152x152"/>
        
        <link rel="stylesheet" href="{base}vendor/h5bp/html5-boilerplate/css/normalize.css">
        <link rel="stylesheet" href="{base}vendor/h5bp/html5-boilerplate/css/main.css">
        <link rel="stylesheet" href="{base}src/css/page.css">
        <link rel="stylesheet" href="{base}src/css/responsive.css">
        {stylesheets}
		
        <script type="text/javascript" src="{base}vendor/h5bp/html5-boilerplate/js/vendor/modernizr-2.8.0.min.js"></script>
    </head>

    <body data-base="{base}" data-path="{resource-path}" data-version="{app-last-modified}">
        
        {layout}
        
        {scripts}
    </body>
</html>