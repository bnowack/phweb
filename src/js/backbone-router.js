
/* backbone-router.js */

define(function(require) {
    "use strict";

    var Backbone = require("backbone");
    
    return Backbone.Router.extend({
        
        // default routes
        routes: {
            "*path": "default"
        },
        
        /**
         * Logs the path of undefined routes to th console.
         * 
         * @param string path
         */
        default: function(path) {
            console.log("Undefined route: " + path);
        },
        
        /**
         * Starts the route dispatching
         * 
         */
        start: function() {
            this.enablePushStateLinks();
            Backbone.history.start({
                pushState: true,
                root: $('head base').attr('href').replace(/\/$/, '')
            });
        },
        
        /**
         * Passes local link clicks to the router instead of the browser
         * 
         */
        enablePushStateLinks: function() {
            var router = this;
            $(document).on('click', 'a', function(e) {
                var base = $('head base').attr('href');
                var href = $(this).attr('href');
                var path = href.slice(base.length);
                if (href.match(/^\//)) {// local
                    e.preventDefault();
                    router.navigate(path, true);
                }
            });
        }
        
    });
});
