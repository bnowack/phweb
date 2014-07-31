
/* backbone-router.js */

define(function(require) {
    "use strict";

    var Backbone = require("backbone");
    
    return Backbone.Router.extend({
        
        // properties
        app: null,
        routes: {
            "*path": "onUndefined"
        },
        
        /**
         * Default route, logs the path to the console.
         * 
         * @param {string} path Current path fragment
         */
        onUndefined: function(path) {
            console.log("No route defined for \"/" + path + "\"");
        },
        
        /**
         * Constructor, auto-called on instantiation
         * 
         * @param {object} app
         */
        constructor: function(app) {
            this.app = app;
            // call initialize() on sub-class instance
            Backbone.Router.apply(this, arguments);            
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
