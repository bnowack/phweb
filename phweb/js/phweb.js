
/* phweb.js */

(function($) {
	
	var lib = window['phweb'] = {

        /**
         * Replaces <span class="email">info AT host DOT tld</span>
         */
        activateEmails: function() {
			$('span.email').each(function() {
				var addr = $(this).text().replace(' AT ', '@').replace(' DOT ', '.');
				$(this).replaceWith('<a class="email" href="mailto:' + addr + '">' + addr + '</a>');
			});
		},
		
        /**
         * Replaces <span class="phone">PLUS 49 12345 DASH 67890</span>
         */
		activatePhones: function() {
			$('span.phone').each(function() {
				var nr = $(this).text().replace('PLUS ', '+').replace(/ DASH /g, '-');
				$(this).replaceWith('<a class="phone" href="tel:' + nr + '">' + nr + '</a>');
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
