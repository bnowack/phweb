
/* phweb.js */

(function($) {

    var lib = window['phweb'] = {

        /**
         * Replaces <span class="email">info AT host DOT tld</span>
         */
        activateEmails: function() {
            $('span.email').each(function() {
                var addr = $(this).text().replace(' AT ', '@').replace(' DOT ', '.');
                var label = addr;
                if ($(this).attr('data-address')) {
                    addr = $(this).attr('data-address').replace(' AT ', '@').replace(' DOT ', '.');
                    label = $(this).text();
                }
                $(this).replaceWith('<a class="email" href="mailto:' + addr + '">' + label + '</a>');
            });
        },

        /**
         * Replaces <span class="phone">PLUS 49 12345 DASH 67890</span>
         */
        activatePhones: function() {
            $('span.phone').each(function() {
                var nr = $(this).text().replace('PLUS ', '+').replace(/ DASH /g, '-');
                var hrefNr = nr
                    .replace('(0)', '')
                    .replace(/ /g, '')
                    .replace(/-/g, '')
                ;
                $(this).replaceWith('<a class="phone" href="tel:' + hrefNr + '">' + nr + '</a>');
            });
        },

        /**
         * Makes sure the #footer does not hang
         */
        initCanvasScaling: function() {
            $(window)
                .on('resize', function() {
                    try { window.clearTimeout(lib.canvasSizeTO); } catch(e) {};
                    lib.canvasSizeTO = window.setTimeout(function() {
                        var diff = $(window).height() - $('body').height() + parseInt($('#footer').css('margin-top'));
                        $('#footer').animate({'margin-top': Math.max(0, diff)}, 100);
                    }, 250);
                })
                .trigger('resize')
            ;
        },
        
        /**
         * Makes sure external links are opened in a new tab
         */
        setLinkTargets: function() {
            $('a[href]').each(function() {
                if (this.hostname && this.hostname !== location.hostname) {
                    $(this).attr('target', '_ext');
                }
            });
        },
        
        initTopLinks: function() {
            $('#canvas > a.top').on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: 0
                }, 1000);
            });
            // hide on large-enough screens
            if ($(window).height() > $('body').height()) {
                $('#canvas > a.top').hide();
            }
        },
        
        init: function() {
            lib.activateEmails();
            lib.activatePhones();
            lib.initTopLinks();
            //phweb.initCanvasScaling();
            //phweb.setLinkTargets();
        }

    };

    $(lib.init);

})(jQuery);
