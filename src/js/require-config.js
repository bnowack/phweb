
/* config/require.js */

require.config({
    
    urlArgs: 'v=' + document.getElementsByTagName('body')[0].getAttribute('data-version'),
    
    paths: {
        jquery: 'vendor/jquery/jquery/dist/jquery.min',
        underscore: 'vendor/jashkenas/underscore/underscore-min',
        backbone: 'vendor/jashkenas/backbone/backbone-min',
        app: 'src/js',
        phweb: 'vendor/bnowack/phweb/src/js',
        config: 'config',
        commands: 'src/js/commands'
    } 
        
});
