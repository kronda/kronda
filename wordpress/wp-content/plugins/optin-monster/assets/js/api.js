/* ==========================================================
 * api.js
 * http://optinmonster.com/
 * ==========================================================
 * Copyright 2014 Thomas Griffin.
 *
 * Use of this file is bound by the Terms of Service agreed
 * upon when purchasing OptinMonster.
 * http://optinmonster.com/terms/
 * ========================================================== */
// Global variable to hold optins and determine their visibility.
var OptinMonsterOptins = {};

// Main OptinMonster API class for handling optin operations.
function OptinMonster(){
    // Holds all of the class properties.
    this.public = {},

    // The init method that loads the object class.
    this.init = function(params){
        // Set the optin properties.
        for ( key in params ) {
            this.public[key] = params[key];
        }

        // Only check for popup-type optins.
        if ( 'sidebar' !== this.getProp('type') && 'post' !== this.getProp('type') ) {
            // Only check against cookies if this is in live mode.
            if ( ! this.getProp('preview') ) {
                // Only process if this is a normal loading optin. If manually triggered, ignore cookie settings.
                if ( ! this.getProp('click') ) {
                    // If cookies are not enabled, we can't reliably determine optin visibility, so return early.
                    if ( ! this.cookiesEnabled() ) {
                        if ( ! this.getProp('test') ) {
                            return;
                        }
                    }

                    // If we have a cookie set and it is valid, bail out and do nothing.
                    var global_cookie = this.getCookie('om-global-cookie'),
                        cookie        = this.getCookie('om-' + this.getProp('id')),
                        second        = this.getProp('second'),
                        test          = this.getProp('test'),
                        type          = this.getProp('type');

                    // If on a mobile device and not a mobile type of optin, return early regardless of any other settings.
                    if ( this.isMobile() && ! this.getProp('mobile') ) {
                        return;
                    }

                    // If not on a mobile device but a mobile optin is present, return early.
                    if ( ! this.isMobile() && this.getProp('mobile') ) {
                        return;
                    }

                    // Check for 2nd pageview, and return early if it is the first pageview (meaning the "second" cookie does not exist yet).
                    if ( second && ! test ) {
                        if ( ! this.getCookie('om-second-' + this.getProp('id')) ) {
                            return this.createCookie('om-second-' + this.getProp('id'), true, this.getProp('cookie'));
                        }
                    }

                    // Now run our other optin-specific checks.
                    if ( global_cookie || cookie ) {
                        if ( ! test ) {
                            if ( 'slide' !== type ) {
                                return;
                            }
                        }
                    }
                }
            }
        }

        // Load the optin now that the checks are complete.
        this.run();
    },

    // Loads the optin.
    this.run = function(){
        // Prepare variables.
        var self = this,
            type = self.getProp('type');

        // If this is a slide-in, set a handler to toggle its current visible state.
        if ( 'slide' == type ) {
            this.setProp('slide_open', false);
        }

        // Store the optin in the global variable and set its visibility to false by default.
        OptinMonsterOptins[this.getProp('optin_js')] = { type: type, visible: false };

        // Possibly load jQuery and handle the rest of the output based on response.
        this.loadjQuery();
    },

    // Checks to see if jQuery is loaded and if it is a high enough version; if not, jQuery is loaded.
    this.loadjQuery = function(){
        // Store localized copy of our main object instance.
        var self   = this,
            loaded = false;

        // If jQuery is not present or not the correct version, load it asynchronously and fire the rest of the app once jQuery has loaded.
        if ( window.jQuery === undefined ) {
            var om    = document.createElement('script');
            om.src    = '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js';
            om.onload = om.onreadystatechange = function(){
                var s = this.readyState;
                if (s) if (s != 'complete') if (s != 'loaded') return;
                try {
                    if ( ! loaded ) {
                        self.loadjQueryHandler(false);
                        loaded = true;
                    }
                } catch(e){}
            };

            // Attempt to append it to the <head>, otherwise append to the document.
            (document.getElementsByTagName('head')[0] || document.documentElement).appendChild(om);
        } else if ( window.jQuery.fn.jquery !== '1.11.1' ) {
            // Set the window jQuery property for event triggering.
            this.public.ejQuery = window.jQuery;

            // jQuery exists, but it is not the version we want, so we need to manage duplicate jQuery objects.
            var om    = document.createElement('script');
            om.src    = '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js';
            om.onload = om.onreadystatechange = function(){
                var s = this.readyState;
                if (s) if (s != 'complete') if (s != 'loaded') return;
                try {
                    if ( ! loaded ) {
                        self.loadjQueryHandler(true);
                        loaded = true;
                    }
                } catch(e){}
            };

            // Attempt to append it to the <head>, otherwise append to the document.
            (document.getElementsByTagName('head')[0] || document.documentElement).appendChild(om);
        } else {
            // The version of jQuery loaded into the window is what we want to use, so we can just load the handler and output content.
            self.public.$ = self.public.ejQuery = window.jQuery;
            self.loadApp();
        }
    },

    // Stores a localized copy of jQuery and removes any potential conflicts with other jQuery libraries.
    this.loadjQueryHandler = function(exists){
        // If jQuery already exists, don't overwrite the global but set noConflict to properly handle multiple versions.
        if ( exists ) {
            // Don't set global jQuery object - just store in our property.
            this.public.$ = window.jQuery.noConflict(true);
            this.loadApp();
        } else {
            // Store the global jQuery object since it does not exist yet.
            jQuery = window.jQuery.noConflict(true);
            this.public.$ = this.public.ejQuery = jQuery;
            this.loadApp();
        }
    },

    // Callback to load the content of our app into the page.
    this.loadApp = function(){
        // Store localized copy of our main object instance.
        var self = this;

        // Woot, we now have jQuery! Run our application inside of our jQuery callback to have full access to jQuery.
        self.public.$(document).ready(function($){
            self.runOptinMonster();
        });
    },

    // Runs the OptinMonster JS engine!
    this.runOptinMonster = function(){
        // Store localized copy of our main object instance.
        var self  = this,
            click = self.getProp('click');

        // Trigger event to say OptinMonster is initialized.
        self.trigger('OptinMonsterInit');

        // Initialize based on click or normal load.
        if ( click ) {
            self.manual();
        } else {
            self.load();
        }
    },

    // Processes loading optins in manually via MonsterLinks.
    this.manual = function(){
        // Prepare variables.
        var self = this,
            data = {
                optin_monster_ajax_action: 'get_optinmonster',
                slug: self.getProp('optin')
            },
            success = self.manualSuccess(self),
            error   = self.manualError(self);

        // Make the request to retrieve the optin.
        self.request(data, success, error);
    },

    // Processes the success callback for manually loading an optin on click.
    this.manualSuccess = function(self){
        // Prepare variables.
        var $      = self.getProp('$'),
            loaded = false;

        // Return the callback.
        return function(params){
            // Set the optin properties.
            for ( key in params.success ) {
                self.public[key] = params.success[key];
            }

            // Set the global optin object with the proper type and set some property values (we want this to load immediately).
            OptinMonsterOptins[self.getProp('optin_js')].type = self.getProp('type');
            self.setProp('delay', 0);
            self.setProp('exit', false);

            // Load the WebFont loader and then proceed with the optin output.
            var wf    = document.createElement('script');
            wf.src    = '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js';
            wf.onload = wf.onreadystatechange = function(){
                var s = this.readyState;
                if (s) if (s != 'complete') if (s != 'loaded') return;
                try {
                    // Only apply once.
                    if ( ! loaded ) {
                        // Place the optin HTML output into the DOM.
                        $('body').append(self.getProp('html'));

                        // Trigger event to say OptinMonster has been loaded successfully via click.
                        self.trigger('OptinMonsterManualOptinSuccess');

                        // Now proceed with loading the optin.
                        self.load();

                        // Set the loaded flag to true.
                        loaded = true;
                    }
                } catch(e){}
            };

            // Attempt to append it to the <head>, otherwise append to the document.
            (document.getElementsByTagName('head')[0] || document.documentElement).appendChild(wf);
        }
    },

    // Processes the error callback for manually loading an optin on click.
    this.manualError = function(self){
        // Return the callback.
        return function(err, xhr, message){
            // Trigger event to say OptinMonster has encountered an error processing the optin.
            self.trigger('OptinMonsterManualOptinError');
        };
    },

    // Loads an optin.
    this.load = function(){
        // Prepare variables.
        var self = this;

        // Sanitize the form.
        self.sanitize();

        // Runs fixes for IE.
        self.iehotfix();

        // Open the optin.
        self.open();
    },

    // Sanitize form fields to ensure they will submit properly.
    this.sanitize = function(){
        // Prepare variables.
        var self         = this,
            $            = self.getProp('$'),
            form_html    = $('#om-' + self.getProp('optin')),
            submit_field = $('#om-' + self.getProp('optin')).find(':submit'),
            name_attr    = submit_field.attr('name'),
            name_id      = submit_field.attr('id');

        // Fix any name attributes on the submit button.
        if ( 'submit' == name_attr ) {
            form_html.find(':submit').attr('name', 'submit-om');
        }

        // Fix any ID attributes on the submit button.
        if ( 'submit' == name_id ) {
            form_html.find(':submit').attr('id', 'submit-om');
        }
    },

    // Adds hotfixes for IE bugs.
    this.iehotfix = function(){
        // If not IE, return early.
        if ( ! this.isIE() ) {
            return;
        }

        // Load the placeholder shim for IE.
        this.loadPlaceholder();

        // Apply the placeholder shim.
        this.doPlaceholder();
    },

    // Opens the optin.
    this.open = function(onclick){
        // Prepare variables.
        var self     = this,
            exit     = self.getProp('exit'),
            click    = self.getProp('click'),
            optin_js = self.getProp('optin_js'),
            delay    = 'slide' == self.getProp('type') ? 0 : self.getProp('delay'),
            onclick  = onclick || false;

        // If the popup is already opened, don't do anything.
        if ( OptinMonsterOptins.hasOwnProperty(optin_js) && true === OptinMonsterOptins[optin_js].visible ) {
	        return;
        }

        // Set delay to 0 if forced click or exit intent.
        if ( onclick || exit ) {
	        delay = 0;
        }

        // Only load if not a manual click optin.
        if ( ! click || onclick ) {
            // Wrap everything in a timeout to catch any optin loading delays.
            setTimeout(function(){
                // Possibly append the optin holder to the body and style.
                if ( 'mobile' !== self.getProp('type') ) {
                	self.appendHolder();
                }

                // If we have a custom HTML optin, we need to prepare it now.
                if ( self.getProp('custom') ) {
                    self.prepareCustomOptin();
                }

                // Trigger event that OptinMonster is now fully loaded and about to fire the open mechanism.
                self.trigger('OptinMonsterLoaded');

                // Handle the type of open.
                if ( exit && ! click && ! onclick ) {
                    self.exitOpen();
                } else {
                    self.normalOpen();
                }
            }, delay || 0);
        }
    },

    // Handle the opening of exit intent optins.
    this.exitOpen = function(){
        // Prepare variables.
        var self     = this,
            $        = self.getProp('$'),
            optin_js = self.getProp('optin_js'),
            opened   = false;

        // Load the exit intent handler.
        $(document).on('mouseleave', function(e){
            // Only trigger if leaving near the top of the page, a cookie has not been set or the optin is not yet open.
            if ( e.clientY > (self.getProp('exit_sensitivity') || 20) || self.getCookie('om-' + self.getProp('id')) || self.getCookie('om-global-cookie') || OptinMonsterOptins.hasOwnProperty(optin_js) && true === OptinMonsterOptins[optin_js].visible || opened ) {
                return;
            }

            // Set the flag that the optin has been opened once and cannot be opened again,
            opened = true;

            // Show (and possibly animate showing) the optin.
            self.show(true);
        });
    },

    // Handle the opening of normal optins.
    this.normalOpen = function(){
        // Prepare variables.
    	var self = this,
    		type = self.getProp('type'),
    		$	 = self.getProp('$');

        // Show (and possibly animate showing) the optin.
        // Make a check for mobile optins to check the window scrolling feature first before showing the optin.
        if ( 'mobile' == type && ! self.getProp('preview') ) {
            self.setProp('dw', $(document).width());
        	if ( ! $(window).scrollTop() ) {
	        	self.show();
        	} else {
		        $(window).on('scroll.omMobile', function(){
    		        clearTimeout($.data(this, 'omScrollTimer'));
            	    $.data(this, 'omScrollTimer', setTimeout(function(){
            	        self.show();
            	    }, 300));
		        });
		    }
        } else {
	        self.show();
        }
    },

    // Handle actually displaying the optin.
    this.show = function(exit){
        // Prepare variables.
        var self     = this,
            $        = self.getProp('$'),
            id       = self.getProp('id'),
            optin    = self.getProp('optin'),
            optin_js = self.getProp('optin_js'),
            type     = self.getProp('type'),
            theme    = self.getProp('theme'),
            preview  = self.getProp('preview'),
            exit     = exit || false;

        // If the popup is already opened, don't do anything.
        if ( OptinMonsterOptins.hasOwnProperty(optin_js) && true === OptinMonsterOptins[optin_js].visible ) {
	        return;
        }

        // If the global cookie has been set at some point between the time loading the page and actually showing this optin (and this is not a click event or preview), don't show it.
        if ( self.getCookie('om-global-cookie') && ! self.getProp('click') && ! preview ) {
            if ( 'sidebar' !== type && 'post' !== type ) {
                return;
            }
        }

        // Trigger event to say OptinMonster is about to be shown.
        self.trigger('OptinMonsterBeforeShow');

        // Determine the type of loading pattern based on exit intent or not.
        if ( exit ) {
            // Only process for lightbox and canvas optin types.
            if ( 'lightbox' == type || 'canvas' == type ) {
                // Check optin visibility to ensure no other popup optins are showing on the page at the same time.
                if ( ! self.hasVisiblePopup() ) {
                    // Immediately display the optin.
                    $('#om-' + optin).show().css('display', 'block');
                    $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin').show().css('display', 'block');
                    self.positionOptin();

                    // Set the visibility of the optin to open.
                    OptinMonsterOptins[self.getProp('optin_js')].visible = true;

                    // Trigger event to say OptinMonster is now open.
                    self.trigger('OptinMonsterOnShow');
                }
            } else if ( 'footer' == type || 'slide' == type ) {
                // If an optin is successful with the slide-in, don't show it at all.
                if ( ! self.getCookie('om-' + id + '-closed') ) {
                    // Make the optin visible.
                    $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin').show().css('display', 'block');
                    var position = preview ? 78 : 0;

                    // Display the optin.
                    $('#om-' + optin).css('bottom', '-' + $('#om-' + optin).outerHeight() + 'px').show().animate({ 'bottom' : parseInt(position) }, 300, function(){
                        // Set the visibility of the optin to open.
                        OptinMonsterOptins[self.getProp('optin_js')].visible = true;

                        // Trigger event to say OptinMonster is now open.
                        self.trigger('OptinMonsterOnShow');

                        // Do some extra custom opening/closing magic for slide-in optins.
                        if ( 'slide' == type ) {
                        	// Register open/close handlers now.
                            self.slideHandlers();

                            setTimeout(function(){
                               self.showSlide();
                            }, 0);
                        }
                    });
                }
            }
        } else {
            if ( 'lightbox' == type || 'canvas' == type || 'mobile' == type ) {
                // Check optin visibility to ensure no other popup optins are showing on the page at the same time.
                if ( ! self.hasVisiblePopup() ) {
                	if ( 'mobile' == type ) {
			            // Style other items necessary for mobile optins.
			            $('#om-' + optin + ', #om-' + optin + '-overlay').appendTo('body');
			            $('#om-' + optin + '-overlay').height($(document).height()).show().css('display', 'block');
			            $('#om-' + optin).show().css('display', 'block');

			            // Fix scaling.
			            if ( ! preview ) {
			            	self.fixMobileScaling();
			            }

	                	// Fade the optin into view.
	                    $('#om-' + type + '-' + theme + '-optin').hide().fadeIn(300, function(){
	                        // Set the proper position of the optin.
	                        $('#om-' + optin).css('top', $(document).scrollTop());

	                        // Set the visibility of the optin to open.
	                        OptinMonsterOptins[self.getProp('optin_js')].visible = true;

	                        // Trigger event to say OptinMonster is now open.
	                        self.trigger('OptinMonsterOnShow');
	                    });
                	} else {
	                    // Fade the optin into view.
	                    $('#om-' + optin).fadeIn(300, function(){
	                        // Show the optin.
	                        $(this).find('#om-' + type + '-' + theme + '-optin').show().css('display', 'block');
	                        self.positionOptin();

	                        // Set the visibility of the optin to open.
	                        OptinMonsterOptins[self.getProp('optin_js')].visible = true;

	                        // Trigger event to say OptinMonster is now open.
	                        self.trigger('OptinMonsterOnShow');
	                    });
	                }
                }
            } else if ( 'footer' == type || 'slide' == type ) {
                // If an optin is successful with the slide-in, don't show it at all.
                if ( ! self.getCookie('om-' + id + '-closed') || preview ) {
                    // Make the optin visible.
                    $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin').show().css('display', 'block');
                    var position = preview ? 78 : 0;

                    // Display the optin.
                    $('#om-' + optin).css('bottom', '-' + $('#om-' + optin).outerHeight() + 'px').show().animate({ 'bottom' : parseInt(position) }, 300, function(){
                        // Set the visibility of the optin to open.
                        OptinMonsterOptins[self.getProp('optin_js')].visible = true;

                        // Trigger event to say OptinMonster is now open.
                        self.trigger('OptinMonsterOnShow');

                        // Do some extra custom opening/closing magic for slide-in optins.
                        if ( 'slide' == type ) {
                            // Register open/close handlers now.
                            self.slideHandlers();

                            // Open the slide-in optin.
                            setTimeout(function(){
                               self.showSlide();
                            }, self.getProp('delay') || 0);
                        }
                    });
                }
            } else {
                // Trigger event to say OptinMonster is now open.
                self.trigger('OptinMonsterOnShow');
            }
        }

        // Register the submit handler.
        self.submit();

        // Register the close handler.
        self.close();

        // Track the impression stats.
        self.track();
    },

    // Fixes mobile scaling.
    this.fixMobileScaling = function(){
    	// Prepare variables.
    	var self  = this,
    		type  = this.getProp('type'),
    		optin = this.getProp('optin'),
    		$     = this.getProp('$');

    	// If the meta viewport tag exists, return. This is a responsive site.
    	var meta_exists = $('meta[name="viewport"]');
    	if ( meta_exists.length > 0 ) {
	    	return;
    	}

    	// Append our tag and start doing things.
    	$('head').append('<meta id="optin-monster-viewport" name="viewport" content="width=device-width, initial-scale=1.0">');
    	$('html, body').css('overflow', 'hidden');
    },

    // Positions an optin according to the optin type.
    this.positionOptin = function(){
        // Prepare variables.
        var self    = this,
            $       = self.getProp('$'),
            id      = self.getProp('id'),
            optin   = self.getProp('optin'),
            type    = self.getProp('type'),
            theme   = self.getProp('theme'),
            preview = self.getProp('preview'),
            dims    = preview ? (($(window).height() - $('#om-' + optin + ' .om-theme-' + theme).height()) / 2) - 39 : ($(window).height() - $('#om-' + optin + ' .om-theme-' + theme).height()) / 2;

        // Position based on type.
        if ( 'lightbox' == type || 'canvas' == type ) {
            $('#om-' + optin + ' .om-theme-' + theme + ', #om-' + optin + ' .optin-monster-success-overlay').css({
                top: dims,
                left: ($(window).width() - $('#om-' + optin + ' .om-theme-' + theme).width()) / 2
            });
            $(window).resize(function(){
            	dims = preview ? (($(window).height() - $('#om-' + optin + ' .om-theme-' + theme).height()) / 2) - 39 : ($(window).height() - $('#om-' + optin + ' .om-theme-' + theme).height()) / 2;
                $('#om-' + optin + ' .om-theme-' + theme + ', #om-' + optin + ' .optin-monster-success-overlay').css({
                    top: dims,
                    left: ($(window).width() - $('#om-' + optin + ' .om-theme-' + theme).width()) / 2
                });
            });

            // Trigger event to say OptinMonster has now been positioned.
            self.trigger('OptinMonsterPositionOptin');
        } else {
            // Trigger event to say OptinMonster has now been positioned.
            self.trigger('OptinMonsterPositionOptin');
        }
    },

    // Registers the open/close slide-in handlers.
    this.slideHandlers = function(){
        // Prepare variables.
        var self  = this,
            $     = self.getProp('$'),
            id    = self.getProp('id'),
            optin = self.getProp('optin'),
            type  = self.getProp('type'),
            theme = self.getProp('theme');

        // Register handler to close the optin.
        $(document).on('click.closeOptin', '#om-' + optin + ' .om-slide-close-content, #om-' + optin + ' .om-close', function(e){
            // Don't allow event bubbling.
            if ( e.target !== this ) {
                return;
            }

            e.preventDefault();

            // Trigger event to say OptinMonster is about to close.
            self.trigger('OptinMonsterBeforeClose');

            // Set the optin to closed.
            $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin').removeClass('om-slide-open').addClass('om-slide-closed');

            // Remove any success messages.
            $('#om-' + optin).find('.optin-monster-success-overlay').remove();

            // Run the cleanup method.
            self.cleanup();
        });

        // Register handler to open the optin.
        $(document).on('click.openOptin', '#om-' + optin + ' .om-slide-open-content', function(e){
            // Don't allow event bubbling.
            if ( e.target !== this ) {
                return;
            }

            e.preventDefault();

            // Trigger event to say OptinMonster is about to open.
            self.trigger('OptinMonsterBeforeOpen');

            // Set the optin to open.
            $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin').removeClass('om-slide-closed').addClass('om-slide-open');

            // Set the visibility to open.
            self.setProp('slide_open', true);
        });
    },

    // Displays the slide-in optin.
    this.showSlide = function(){
        // Prepare variables.
        var self  = this,
            $     = self.getProp('$'),
            id    = self.getProp('id'),
            optin = self.getProp('optin'),
            type  = self.getProp('type'),
            theme = self.getProp('theme');

        // Open up the optin if the cookie has not been set.
        if ( ! self.getCookie('om-' + id) && ! self.getProp('slide_open') || self.getProp('preview') ) {
            $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin').removeClass('om-slide-closed').addClass('om-slide-open');
            self.setProp('slide_open', true);
        }
    },

    // Handles optin submissions.
    this.submit = function(){
        // Prepare variables.
        var self   = this,
            $      = self.getProp('$'),
            optin  = self.getProp('optin'),
            type   = self.getProp('type'),
            theme  = self.getProp('theme'),
            custom = self.getProp('custom');

        // Prevent submitting if in Preview mode.
        if ( self.getProp('preview') ) {
            return;
        }

        // Handle the submission process based on custom or native implementation.
        if ( custom ) {
            // Create flag to test if submitted yet or not.
            var submitted = false;

            // Register event on custom form submission.
            $(document).on('submit.doCustomOptin', '.om-custom-html-form form', function(e){
                if ( ! submitted ) {
                    // Set submitted flag to true.
                    submitted = true;

                    // Trigger event to say OptinMonster is about to process an optin.
                    self.trigger('OptinMonsterBeforeOptin');

                    // Run a custom ajax request to capture the conversion for custom HTML forms.
                    self.optin(e.target, true);

                    // Prevent the default action AND return false to prevent form submission.
                    e.preventDefault();
                    return false;
                } else {
                    // Close out the optin based on the type of optin.
                    if ( 'lightbox' == type || 'canvas' == type ) {
                        $('#om-' + optin).fadeOut(300, self.onClose(self));
                    } else if ( 'footer' == type || 'slide' == type ) {
                        $('#om-' + optin).animate({ 'bottom' : '-' + $('#om-' + optin).outerHeight() + 'px' }, 300, self.onClose(self));
                    }
                }
            });
        } else {
            // Process the native submission
            $(document).on('click.doOptin', '#om-' + optin + ' #om-' + type + '-' + theme + '-optin-submit', function(e){
                // Prevent the default event from occurring.
                e.preventDefault();

                // Trigger event to say OptinMonster is about to process an optin.
                self.trigger('OptinMonsterBeforeOptin');

                // Handle the action.
                self.optin(e.target);
            });
        }
    },

    // Handles closing the optin.
    this.close = function(close){
        // Prepare variables.
        var self  = this,
            $     = self.getProp('$'),
            optin = self.getProp('optin'),
            type  = self.getProp('type'),
            close = close || false;

        // Prevent closing if in Preview mode.
        if ( self.getProp('preview') ) {
            return;
        }

        // If close is true, force closing the optin instead of registering it to a click event.
        if ( close ) {
            // Close out the optin based on the type of optin.
            if ( 'lightbox' == type || 'canvas' == type || 'mobile' == type ) {
                $('#om-' + optin).fadeOut(300, self.onClose);
            } else if ( 'footer' == type || 'slide' == type ) {
                $('#om-' + optin).animate({ 'bottom' : '-' + $('#om-' + optin).outerHeight() + 'px' }, 300, self.onClose);
            }
        } else {
            // Close out the optin.
            $(document).on('click.closeOptin', '#om-' + optin + ' .om-close, #om-' + optin + '.optin-monster-overlay', function(e){
                // Don't allow event bubbling or closing by clicking on the overlay for mobile optins.
                if ( e.target !== this || 'mobile' == type && $(e.target).hasClass('optin-monster-overlay') ) {
                    return;
                }

                // Prevent the default from occurring.
                e.preventDefault();

                // Trigger event to say OptinMonster is about to close.
                self.trigger('OptinMonsterBeforeClose');

                // Close out the optin based on the type of optin.
                if ( 'lightbox' == type || 'canvas' == type || 'mobile' == type ) {
                    $('#om-' + optin).fadeOut(300, self.onClose(self));
                } else if ( 'footer' == type ) {
                    $('#om-' + optin).animate({ 'bottom' : '-' + $('#om-' + optin).outerHeight() + 'px' }, 300, self.onClose(self));
                }
            });
        }
    },

    // Handles all of the close operations for optins, including setting cookies and visibility.
    this.onClose = function(self){
        return function(){
            // Set cookies and visibility.
            self.cleanup();

            // If on a mobile optin, we need to do some extra cleanup.
            if ( 'mobile' == self.getProp('type') ) {
                // Prepare variables.
            	var $ = self.getProp('$'),
            	    w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0),
                    d = self.getProp('dw'),
                    s = Math.round((w/d) * 100) / 100;

                // Unbind our scroll event.
                $(window).off('scroll.omMobile');

                // If our custom viewport tag exists, reset scaling back to the proper state.
                if ( $('#optin-monster-viewport').length > 0 ) {
	            $('#optin-monster-viewport').attr('content', 'width=device-width, initial-scale=' + s + ', minimum-scale=' + s + ', maximum-scale=' + s);
	                $('html, body').css('overflow', '');

	                // Reset user zooming.
	                setTimeout(function(){
    	                $('#optin-monster-viewport').attr('content', 'width=device-width, maximum-scale=10.0');
	                }, 1000);
                }

                // Hide the overlay.
	            $('#om-' + self.getProp('optin') + '-overlay').hide();
            }

            // Trigger the optin as closed.
            self.trigger('OptinMonsterOnClose');
        };
    },

    // Run some cleanup to set cookies and optin visibility.
    this.cleanup = function(success){
        // Prepare variables.
        var self  = this,
            $     = self.getProp('$'),
            id    = self.getProp('id'),
            scs   = success || false;

        // Set the visibility of the optin back to closed.
        OptinMonsterOptins[self.getProp('optin_js')].visible = false;

        // If on a mobile optin, make sure we unbind our scroll event at all times.
        if ( 'mobile' == self.getProp('type') ) {
            $(window).off('scroll.omMobile');
        }

        // If the cookie value is 0, don't set any cookies.
        if ( 0 === self.getProp('cookie') ) {
            return;
        }

        // Set the cookie for the optin.
        self.createCookie('om-' + id, true, self.getProp('cookie'));

        // If using a clone, make sure the clone has a cookie as well to prevent it from displaying.
        if ( self.getProp('clones') ) {
            $.each(self.getProp('clones'), function(i, id){
                if ( 0 === id.length ) {
                    return;
                }
                self.createCookie('om-' + id, true, self.getProp('cookie'));
            });
        }

        // If the global setting is populated (and this is a success cleanup), set a global cookie.
        if ( self.getProp('global_cookie') && scs ) {
            self.createCookie('om-global-cookie');
        }

        // If a success cleanup and the optin type is slide, set another cookie to prevent it from displaying period.
        if ( scs && 'slide' == self.getProp('type') ) {
            self.createCookie('om-' + id + '-closed', true, self.getProp('cookie'));
        }
    },

    // Track impression stats for optins.
    this.track = function(conversion){
        // If we have already tracked this optin, are in preview mode or test mode, don't do anything.
        if ( this.getProp('tracked') || this.getProp('preview') ) {
            return;
        }

        // Prepare variables.
        var self       = this,
            $          = self.getProp('$'),
            optin      = self.getProp('optin'),
            ga         = self.getProp('ga_uaid'),
            conversion = conversion || false,
            data       = {
                optin_monster_ajax_action: 'track_optinmonster',
                optin_id:                  self.getProp('id'),
                post_id:                   self.getProp('post_id'),
                referrer:                  window.location.href,
                user_agent:                navigator.userAgent,
                previous:                  document.referer
            };

        // If we are tracking an impression for a sidebar/after post optin and we are not using GA, don't do it.
        if ( 'sidebar' == self.getProp('type') || 'post' == self.getProp('type') ) {
	        if ( ! conversion && ! ga ) {
		        return;
	        }
        }

        // Make the ajax request to track the data. If using Google Analytics, send to GA, otherwise track locally.
        if ( ga ) {
            self.trackGoogleAnalytics(ga, conversion);
        } else {
            self.request(data);
        }

        // Fire an event to say the OptinMonster is about to track stats.
        self.trigger('OptinMonsterTracked');
    },

    // Method for tracking impression data via Google Analytics.
    this.trackGoogleAnalytics = function(id, track){
        // Prepare variables.
        var self     = this,
            type     = track ? 'conversion' : 'impression',
            campaign = self.getProp('campaign') || self.getProp('optin'),
            init     = self.getProp('ga_init');

        // Create a custom event tracker and dimensions if it has not been initialized.
        if ( ! init ) {
            ga('create', id, 'auto', { 'name' : 'omTracker' });
            ga('omTracker.set', {
                'appName':    self.getProp('app_name'),
                'appId':      self.getProp('app_id'),
                'appVersion': self.getProp('app_version')
            });
            self.setProp('ga_init', true);
        }

        // Send the event tracking data to Google Analytics.
        ga('omTracker.send', 'event', campaign, type, self.getProp('id').toString());
    },

    // Handles the actual optin process for any type of optin.
    this.optin = function(target, custom){
        // Prepare variables.
        var self   = this,
            $      = self.getProp('$'),
            optin  = self.getProp('optin'),
            custom = custom || false;

        // Prevent submitting if in Preview mode.
        if ( self.getProp('preview') ) {
            return;
        }

        // Output the loading icon and styles.
        self.loading(target);

        // Prepare the ajax data variable and callbacks.
        var data = {
                optin_monster_ajax_action: 'do_optinmonster',
                optin_id:                  self.getProp('id'),
                post_id:                   self.getProp('post_id'),
                referrer:                  window.location.href,
                user_agent:                navigator.userAgent,
                previous:                  document.referrer,
                email:                     $('#om-' + optin + ' #om-' + self.getProp('type') + '-' + self.getProp('theme') + '-optin-email').val(),
                name:                      $('#om-' + optin + ' #om-' + self.getProp('type') + '-' + self.getProp('theme') + '-optin-name').val()
            },
            success = false,
            error   = false;

        // Handle the submission differently based on custom vs native implementation.
        if ( custom ) {
            // Prepare the callbacks.
            success = self.optinCustomSuccess(self, target);
        } else {
            // Verify the fields. If there is an error, output the error.
            var error = self.verify();
            if ( error ) {
                // Return and output the error response.
                return self.error(target, error);
            }

            // Prepare the callbacks.
            success = self.optinSuccess(self, target);
            error   = self.optinError(self, target);
        }

        // Make the request.
        self.request(data, success, error);
    },

    // Handle any errors with the optin output.
    this.error = function(target, error){
        // Prepare variables.
        var self   = this,
            $      = self.getProp('$'),
            submit = $(target),
            optin  = self.getProp('optin');

        // Remove the loading icon.
        self.removeLoading(target);

        // Output the error message on the screen.
        submit.parent().append('<p class="optin-monster-error" style="font-family:Georgia;font-size:13px;font-style:italic;color:#ff0000;margin:10px 0;text-align:center;line-height:18px;">' + error + '</p>');

        // Trigger the error event.
        self.trigger('OptinMonsterOnError');
    },

    // Output the loading icon and overlay.
    this.loading = function(target){
        // Prepare variables.
        var self     = this,
            $        = self.getProp('$'),
            submit   = $(target),
            position = submit.position(),
            margin   = parseInt(submit.css('marginTop')),
            width    = submit.outerWidth(),
            height   = submit.outerHeight();

        // Remove any error messages.
        $('#om-' + self.getProp('optin')).find('.optin-monster-error').remove();

        // Add the loading icon overlay on the button.
        submit.after('<span class="optin-monster-loading"></span>').css('opacity', '.25');
        $('#om-' + self.getProp('optin')).find('.optin-monster-loading').css({ width: width, height: height, top: position.top + margin, left: position.left, background: 'url(' + self.getProp('preloader') + ') no-repeat 50% 50%', position: 'absolute', zIndex: 84736365452, backgroundSize: '20px' });
    },

    // Verifies that all fields are populated correctly before proceeding to process the optin.
    this.verify = function(){
        // Prepare variables.
        var self   = this,
            $      = self.getProp('$'),
            optin  = self.getProp('optin'),
            type   = self.getProp('type'),
            theme  = self.getProp('theme'),
            name   = $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin-name'),
            email  = $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin-email'),
            ret    = false;

        // If the name field is present, verify that it is filled out.
        if ( name && name.length > 0 ) {
            if ( name.val().length == 0 ) {
                ret = self.getProp('name_error') || self.getProp('error');
            }
        }

        // If the email field is present, verify that the email address is correct.
        if ( email && email.length > 0 ) {
            if ( email.val().length == 0 || ! self.isValidEmail(email.val()) ) {
                ret = self.getProp('email_error') || self.getProp('error');
            }
        }

        // Return the error response.
        return ret;
    },

    // Remove the loading icon from view.
    this.removeLoading = function(target){
        // Prepare variables.
        var self   = this,
            $      = self.getProp('$'),
            submit = $(target);

        // Remove the loading icon helper and holder.
        submit.css('opacity', '1');
        $('#om-' + self.getProp('optin')).find('.optin-monster-loading').remove();
    },

    // Handle the optin success callback.
    this.optinSuccess = function(self, target){
        // Prepare variables.
        var $     = self.getProp('$'),
            optin = self.getProp('optin'),
            type  = self.getProp('type'),
            theme = self.getProp('theme');

        // Return the callback.
        return function(res){
            // If for some reason we do not receive a response or the response is empty, return with an error.
            if ( ! res || $.isEmptyObject(res) ) {
                return self.error(target, self.getProp('error'));
            }

            // If we have an error, output the error message.
            if ( res && res.error ) {
                return self.error(target, res.error);
            }

            // Handle the success pieces of the optin.
            self.cleanup(true);

            // Trigger event to say OptinMonster has processed the optin successfully.
            self.trigger('OptinMonsterOptinSuccess');

            // If using GA, track the conversion.
            if ( self.getProp('ga_id') ) {
                self.trackGoogleAnalytics(self.getProp('ga_uaid'), true);
            }

            // If a redirect is specified, redirect to the specified page.
            if ( self.getProp('redirect') ) {
                // Trigger event to say OptinMonster is about to redirect to the success page.
                self.trigger('OptinMonsterOnRedirect');

                // Possibly append lead data to the redirect url.
                var redirect = self.getProp('redirect'),
                	pass 	 = self.getProp('redirect_pass');
                if ( pass && res && res.success && ! $.isEmptyObject(res.success) ) {
                	var i   = 0,
                		sep = '&';
	                for ( key in res.success ) {
		                if ( 0 == i ) {
			                if ( redirect.indexOf('?') < 0 ) {
				                sep = '?';
			                }
		                }
			            redirect = redirect + sep + encodeURIComponent(key) + '=' + encodeURIComponent(res.success[key]);
			            i++;
	                }
                }

                // Redirect the user.
                window.location.href = redirect;
            } else {
                // If we have a success message, fade in the success message, otherwise close the optin.
                if ( self.getProp('success') ) {
                    self.successMessage(target);
                } else {
                    self.close(true);

                    // Remove the loading icon.
                    self.removeLoading(target);
                }

                // Trigger event to say OptinMonster has processed the optin successfully and has closed.
                self.trigger('OptinMonsterOptinSuccessClose');
            }
        };
    },

    // Handle outputting success messages for the optin.
    this.successMessage = function(target){
        // Prepare variables.
        var self      = this,
            $         = self.getProp('$'),
            optin     = self.getProp('optin'),
            type      = self.getProp('type'),
            theme     = self.getProp('theme'),
            container = $('#om-' + optin + ' #om-' + type + '-' + theme + '-optin'),
            position  = container.position(),
            width     = container.outerWidth(),
            height    = container.outerHeight(),
            zindex    = 'sidebar' == type || 'post' == type ? 7271832 : 1000098373462;

        // Remove the old close button.
        if ( 'slide' !== type ) {
            $('#om-' + optin).find('.om-close').remove();
        }

        // Prepare the success message overlay and actual success message.
        var overlay = ( 'sidebar' == type || 'post' == type ) ? '<div class="optin-monster-success-overlay" style="display:none;"></div>' : '<div class="optin-monster-success-overlay" style="display:none;"><a href="#" class="om-close om-success-close">&times;</a></div>',
            message = self.getProp('success');

        // Output the overlay.
        container.after(overlay);

        // Style the new overlay.
        $('#om-' + optin).find('.optin-monster-success-overlay').css({ width: width, height: height, top: position.top, left: position.left, background: '#fff', position: 'absolute', zIndex: zindex, padding: '0px 20px', opacity: 0, display: 'block' }).append('<div class="optin-monster-success-message">' + message + '</div>');

        // Position the content inside of the div to be vertically centered.
        $('#om-' + optin).find('.optin-monster-success-message').css({ 'margin-top': (height - $('#om-' + optin).find('.optin-monster-success-message').height()) / 2 });

        // Display the overlay.
        $('#om-' + optin).find('.optin-monster-success-overlay').fadeTo(300, 1, function(){
            // Remove the loading icon.
            self.removeLoading(target);

            // Initialize social services if neeeded.
            self.socialServices();
        });

        // Bind to window resize to ensure the success message is always the proper dimensions.
        $(window).resize(function(){
            $('.optin-monster-success-overlay').css({ width: $('#om-' + type + '-' + theme + '-optin').outerWidth(), height: $('#om-' + type + '-' + theme + '-optin').outerHeight(), top: $('#om-' + type + '-' + theme + '-optin').position().top, left: $('#om-' + type + '-' + theme + '-optin').position().left });
            $('.optin-monster-success-message').css({ 'margin-top': ($('#om-' + type + '-' + theme + '-optin').outerHeight() - $('.optin-monster-success-message').height()) / 2 });
        });
    },

    // Handle the custom HTML optin success callback.
    this.optinCustomSuccess = function(self, target){
        // Prepare variables.
        var $ = self.getProp('$');

        // Return the callback.
        return function(res){
            // Trigger event to say OptinMonster has processed the optin.
            self.trigger('OptinMonsterOptinSuccess');

            // If using GA, track the conversion.
            if ( self.getProp('ga_id') ) {
                self.trackGoogleAnalytics(self.getProp('ga_uaid'), true);
            }

            // Resubmit the form with extra data passed to it.
            $(target).submit();
        };
    },

    // Handle the optin error callback.
    this.optinError = function(self, target){
        // Prepare variables.
        var $     = self.getProp('$'),
            optin = self.getProp('optin'),
            type  = self.getProp('type'),
            theme = self.getProp('theme');

        // Return the callback.
        return function(err, xhr, message){
            // Trigger event to say OptinMonster has encountered an error processing the optin.
            self.trigger('OptinMonsterOptinError');

            return self.error(target, self.getProp('ajax_error') + message);
        };
    },

    // Utility method for making ajax requests.
    this.request = function(data, success_cb, error_cb){
        // Prepare variables.
        var self       = this,
            $          = self.getProp('$'),
            ajax       = {
                url:      self.getProp('ajax'),
                data:     data,
                cache:    false,
                type:     'POST',
                dataType: 'json',
                timeout:  30000
            },
            success_cb = success_cb || false,
            error_cb   = error_cb   || false;

        // Set success callback if supplied.
        if ( success_cb ) {
            ajax.success = success_cb;
        }

        // Set error callback if supplied.
        if ( error_cb ) {
            ajax.error = error_cb;
        }

        // Make the ajax request.
        $.ajax(ajax);
    },

    // Appends the optin holder to the <body> tag on the page.
    this.appendHolder = function(){
        var $      = this.getProp('$')
            type   = this.getProp('type'),
            styles = false;

        // Prepare the styles.
        if ( 'lightbox' == type || 'canvas' == type ) {
            styles = { 'position' : 'fixed', 'z-index' : '7371832', 'top' : '0', 'left' : '0', 'zoom' : '1', 'width' : '100%', 'height' : '100%', 'margin' : '0', 'padding' : '0' };
        } else if ( 'footer' == type ) {
            styles = { 'position' : 'fixed', 'z-index' : '7371832', 'bottom' : '0', 'left' : '0', 'zoom' : '1', 'width' : '100%', 'margin' : '0', 'padding' : '0' };
        }

        // If we have styles, output them now.
        if ( styles ) {
            $('#om-' + this.getProp('optin')).css(styles).appendTo('body');
        }

        // Fire event for handling styles.
        this.trigger('OptinMonsterAppendHolder');
    },

    // Prepare custom optin output.
    this.prepareCustomOptin = function(){
        // Store localized copy of our main object instance.
        var self   = this,
            optin  = self.getProp('optin'),
            $      = self.getProp('$'),
            inputs = $('#om-' + optin + ' input[data-om-render=label]');

        // Only proceed if we have inputs to parse.
        if ( inputs.length > 0 ) {
            // Load the element changer.
            self.loadElementChange();

            // Loop through each input and make adjustments as necessary.
            inputs.each(function(){
                if ( $.fn.changeElementType ) {
                    $(this).changeElementType('label');
                }
            });

            // Now convert the labels to their proper format.
            $('#om-' + optin + ' label[data-om-render=label]').each(function(){
                $(this).text($(this).attr('value')).removeAttr('value type');
            });
        }

        // Trigger event to say OptinMonster has finished preparing custom optins.
        self.trigger('OptinMonsterCustomDone');
    },

    // Polling helper for executing at certain intervals.
    this.poll = (function(){
        var timer = 0;
        return function(callback, ms){
            clearInterval(timer);
            timer = setInterval(callback, ms);
        };
    })(),

    // Tests the validity of an email address entered by the user.
    this.isValidEmail = function(email){
        return (new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i)).test(email);
    },

    // Sets cookies.
    this.createCookie = function(name, value, days){
        // If test mode, do nothing.
        if ( this.getProp('test') ) {
            return;
        }

        // If we have a days value, set it in the expiry of the cookie.
        if ( days ) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            var expires = '; expires=' + date.toGMTString();
        } else {
            var expires = '';
        }

        // Write the cookie.
        document.cookie = name + '=' + value + expires + '; path=/';
    },

    // Retrieves cookies.
    this.getCookie = function(name){
        var nameEQ = name + '=';
        var ca = document.cookie.split(';');
        for ( var i = 0; i < ca.length; i++ ) {
            var c = ca[i];
            while ( c.charAt(0) == ' ' ) {
                c = c.substring(1, c.length);
            }
            if ( c.indexOf(nameEQ) == 0 ) {
                return c.substring(nameEQ.length, c.length);
            }
        }

        return null;
    },

    // Removes cookies.
    this.removeCookie = function(name){
        this.createCookie(name, '', -1);
    },

    // Generic logging utility.
    this.log = function(text){
        (typeof console === 'object') ? console.log(text) : '';
    },

    // Generic event triggering utility for OptinMonster.
    this.trigger = function(event_name){
        var self = this;
        self.public.ejQuery(document).trigger(event_name, self.public, self);
    },

    // Loads the element changer.
    this.loadElementChange = function(){
        (function(a){a.fn.changeElementType=function(c){var b={};a.each(this[0].attributes,function(e,d){b[d.nodeName]=d.nodeValue});this.replaceWith(function(){return a('<'+c+'/>',b).append(a(this).contents())})}})(this.getProp('$'));
    }

    // Checks to ensure cookies are enabled for the current visitor.
    this.cookiesEnabled = function(){
        var cookieEnabled = (navigator.cookieEnabled) ? true : false;
        if ( typeof navigator.cookieEnabled == 'undefined' && ! cookieEnabled ) {
            document.cookie = 'testcookie';
            cookieEnabled   = (document.cookie.indexOf('testcookie') != -1) ? true : false;
        }
        return (cookieEnabled);
    },

    // Retrieves properties inside of the class.
    this.getProp = function(name){
        return this.public.hasOwnProperty(name) ? this.public[name] : false;
    },

    // Set properties inside of the class.
    this.setProp = function(name, value){
        this.public[name] = value;
    },

    // Mobile device check.
    this.isMobile = function(){
        var check = false;
        (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check=true})(navigator.userAgent||navigator.vendor||window.opera);
        return check;
    },

    // Checks to see if a popup is visible or not.
    this.hasVisiblePopup = function(){
        // Get all optins currently visible and check against their type.
        var visible = [],
            $       = this.getProp('$'),
            i       = 0;

        // Loop through and grab all visible optins.
        for ( var prop in OptinMonsterOptins ) {
            if ( OptinMonsterOptins[prop].hasOwnProperty('visible') ) {
                if ( true === OptinMonsterOptins[prop]['visible'] ) {
                    visible[i] = OptinMonsterOptins[prop]['type'];
                    i++;
                }
            }
        }

        // Return the flag based on if the type of optin is visible or not.
        return $.inArray('lightbox', visible) > -1 || $.inArray('canvas', visible) > -1 || false;
    },

    // Placeholder polyfill for IE 8/9.
    this.loadPlaceholder = function(){
        var self = this,
            $    = self.getProp('$');
        !function(e,a,t){function l(e){var a={},l=/^jQuery\d+$/;return t.each(e.attributes,function(e,t){t.specified&&!l.test(t.name)&&(a[t.name]=t.value)}),a}function r(e,a){var l=this,r=t(l);if(l.value==r.attr("placeholder")&&r.hasClass("placeholder"))if(r.data("placeholder-password")){if(r=r.hide().next().show().attr("id",r.removeAttr("id").data("placeholder-id")),e===!0)return r[0].value=a;r.focus()}else l.value="",r.removeClass("placeholder"),l==d()&&l.select()}function o(){var e,a=this,o=t(a),d=this.id;if(""==a.value){if("password"==a.type){if(!o.data("placeholder-textinput")){try{e=o.clone().attr({type:"text"})}catch(c){e=t("<input>").attr(t.extend(l(this),{type:"text"}))}e.removeAttr("name").data({"placeholder-password":o,"placeholder-id":d}).bind("focus.placeholder",r),o.data({"placeholder-textinput":e,"placeholder-id":d}).before(e)}o=o.removeAttr("id").hide().prev().attr("id",d).show()}o.addClass("placeholder"),o[0].value=o.attr("placeholder")}else o.removeClass("placeholder")}function d(){try{return a.activeElement}catch(e){}}var c,n,i="[object OperaMini]"==Object.prototype.toString.call(e.operamini),p="placeholder"in a.createElement("input")&&!i,u="placeholder"in a.createElement("textarea")&&!i,h=t.fn,s=t.valHooks,v=t.propHooks;p&&u?(n=h.placeholder=function(){return this},n.input=n.textarea=!0):(n=h.placeholder=function(){var e=this;return e.filter((p?"textarea":":input")+"[placeholder]").not(".placeholder").bind({"focus.placeholder":r,"blur.placeholder":o}).data("placeholder-enabled",!0).trigger("blur.placeholder"),e},n.input=p,n.textarea=u,c={get:function(e){var a=t(e),l=a.data("placeholder-password");return l?l[0].value:a.data("placeholder-enabled")&&a.hasClass("placeholder")?"":e.value},set:function(e,a){var l=t(e),c=l.data("placeholder-password");return c?c[0].value=a:l.data("placeholder-enabled")?(""==a?(e.value=a,e!=d()&&o.call(e)):l.hasClass("placeholder")?r.call(e,!0,a)||(e.value=a):e.value=a,l):e.value=a}},p||(s.input=c,v.value=c),u||(s.textarea=c,v.value=c),t(function(){t(a).delegate("form","submit.placeholder",function(){var e=t(".placeholder",this).each(r);setTimeout(function(){e.each(o)},10)})}),t(e).bind("beforeunload.placeholder",function(){t(".placeholder").each(function(){this.value=""})}))}(this,document,$);
    },

    // Ensure that the placeholder polyfill is added.
    this.doPlaceholder = function(){
        // Store localized copy of our main object instance.
        var self   = this,
            $      = self.getProp('$'),
            fields = $('#om-' + self.getProp('optin') + ' input');

        if ( fields.length > 0 && $.fn.placeholder ) {
            fields.each(function(){
                $(this).placeholder();
            });
        }

        // Trigger event to say OptinMonster placeholders are set.
        self.trigger('OptinMonsterPlaceholderDone');
    },

    // Helper to see if we are using IE8 or 9.
    this.isIE = function(){
        return typeof om_ie_browser != 'undefined';
    },

    // Helper for some custom social services.
    this.socialServices = function(){
        // Prepare variables.
        var self = this,
            $    = self.getProp('$');

        // Facebook helper.
        if ( typeof(FB) != 'undefined' && FB != null ) {
            FB.XFBML.parse();
        }

        // Twitter helper.
        if ( typeof(twttr) != 'undefined' && twttr != null ) {
            twttr.widgets.load();
        }

        // Trigger an event for social services.
        self.trigger('OptinMonsterSocial');
    }
}

// Handle loading manual trigger optins.
jQuery(document).ready(function($){
    var OptinMonsterLinkSlugs = {};
    $(document).find('.manual-optin-trigger, .om-monster-link').each(function(i, el){
        var $this  = $(this),
            slug   = $this.data('optin-slug'),
            slugjs = slug.replace('-', '_'),
            optin  = $('#om-' + slug);

        // If the slug is not found, return early.
        if ( ! slug ) {
            return;
        }

        // If the slug already exists in the global variable, return early.
        if ( OptinMonsterLinkSlugs.hasOwnProperty(slugjs) ) {
            return;
        }

        // If the optin already exists on the page and it is defined in the global scope, set our holder to it, otherwise remove the optin because it won't be used (a cookie has been set).
        if ( 0 !== optin.length ) {
            if ( typeof OptinMonsterOptins != 'undefined' && OptinMonsterOptins.hasOwnProperty(slugjs) ) {
                OptinMonsterLinkSlugs[slugjs] = window[slugjs];
                return;
            } else {
                // Remove the optin to pave the way for the proper one to be loaded.
                $(optin).remove();
            }
        }

        // Load the optin on the page.
        var monsterlink = new OptinMonster();
        monsterlink.init({
            optin:    slug,
            optin_js: slugjs,
            click:    true
        });
        OptinMonsterLinkSlugs[slugjs] = monsterlink;
    });

    // Register the click handler to open the optin.
    $(document).on('click', '.manual-optin-trigger, .om-monster-link', function(e){
        e.preventDefault();

        // Prepare variables.
        var $this  = $(this),
            slug   = $this.data('optin-slug'),
            slugjs = slug.replace('-', '_'),
            optin  = $('#om-' + slug);

        // If the slug is not found, return early.
        if ( ! slug ) {
            return;
        }

        // If the slug does not exist in the global variable, return early.
        if ( ! OptinMonsterLinkSlugs.hasOwnProperty(slugjs) ) {
            return;
        }

        // Open the optin.
        OptinMonsterLinkSlugs[slugjs].open(true);
    });
});