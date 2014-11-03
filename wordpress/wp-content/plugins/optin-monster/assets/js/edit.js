/* ==========================================================
 * edit.js
 * http://optinmonster.com/
 * ==========================================================
 * Copyright 2014 Thomas Griffin.
 *
 * Use of this file is bound by the Terms of Service agreed
 * upon when purchasing OptinMonster.
 * http://optinmonster.com/terms/
 * ========================================================== */
jQuery(document).ready(function($){
    // Hold the preview frame variable in global scope for easy reference for postMessage calls.
    var om_preview_frame,
        om_current_url = optin_monster_edit.iframe + '%23' + encodeURIComponent( document.location.href ),
        om_request_call,
        om_request_url = false,
        om_request_provider,
        om_save_forced,
        om_theme_forced;

    // Initialize the accordion.
    omAccordion();

    // Initialize query string parser.
    omInitQuery();

    // Initialize the email provider connection.
    omProvider();

    // Initialize the preview frame.
    omPreview();

    // Initialize the save features.
    omSave();

    // Initialize the live campaign title editing.
    omCampaignTitle();

    // Initialize the hiding of the help fields.
    omHideTips();

    // Initialize the color fields.
    omColors();

    // Initialize the input fields.
    omFields();

    // Initialize the theme selection dialog.
    omThemeSelection();

    // Initialize the media modal.
    omMediaModal();

    // Initialize the name show/hide feature.
    omNameDisplay();

    // Initialize the "powered by" link show/hide feature.
    omLinkDisplay();

    // Function to initialize the accordion.
    function omAccordion(){
        var panels          = $('.optin-monster-panels > dd'),
            active_panel    = $('.optin-monster-panels .optin-monster-panel-active'),
            panel_hash      = window.location.hash,
            panel_hash_sani = window.location.hash.replace('!', '');

        // If we have a hash and it begins with "soliloquy-tab", set the proper tab to be opened.
        if ( panel_hash && panel_hash.indexOf('optin-monster-panel-') >= 0 ) {
            active_panel.removeClass('optin-monster-panel-active');
            active_panel = $(panel_hash_sani);
            panels.slideUp(300);
            active_panel.addClass('optin-monster-panel-active').next().slideDown(300);
            omInitChosen();
        } else {
            $('.optin-monster-panels .optin-monster-panel-active').next().slideDown(300);
        }

        // Change panels on click.
        $(document).on('click', '.optin-monster-panels > dt > a', function(e){
            e.preventDefault();
            var $this  = $(this),
                parent = $this.parent();

            if ( parent.hasClass('optin-monster-panel-active') ) {
                active_panel.removeClass('optin-monster-panel-active');
                panels.slideUp(300);
            } else {
                active_panel.removeClass('optin-monster-panel-active');
                active_panel = parent;
                panels.slideUp(300);
                parent.addClass('optin-monster-panel-active').next().slideDown(300);
                omInitChosen();
            }
        });
    }

    // Registers the jQuery plugin to parse query strings.
    function omInitQuery(){
        $.extend({
            getQueryParameters : function(str) {
                return (str || document.location.search).replace(/(^\?)/,'').split("&").map(function(n){return n = n.split("="),this[n[0]] = n[1],this}.bind({}))[0];
            }
        });
    }

    // Function to set the email provider connection in motion.
    function omProvider(){
        var provider = $('#optin-monster-field-provider option:selected').val();
        if ( 0 === provider.length ) {
            return;
        }

        var data = {
            action:   'optin_monster_init_provider',
            provider: provider,
            id:       optin_monster_edit.id
        }
        $('.fa-spinner').fadeTo(0, 1);
        omInitProvider(data);

		// Hide the fields panel if this is a Custom HTML Form
		var fields_panel =  $('dt.optin-monster-panel-fields');
		if ( 'custom' === provider ) {
			fields_panel.hide();
		}

        // Also trigger data on item change.
        $(document).on('change', '#optin-monster-field-provider', function(){
            var provider = $(this).find(':selected').val();
            if ( 0 === provider.length ) {
                return;
            }

			// Hide the fields panel if this is a Custom HTML Form
			if ( 'custom' === provider ) {
				fields_panel.hide();
			} else {
				fields_panel.show();
			}

            var data = {
                action:   'optin_monster_init_provider',
                provider: provider,
                id:       optin_monster_edit.id
            }
            $('.om-error').remove();
            $('.optin-monster-provider-accounts, .optin-monster-provider-clients, .optin-monster-provider-lists, .optin-monster-provider-segments').remove();
            $('.fa-spinner').fadeTo(0, 1);
            omInitProvider(data);
        });

        // Switch account data when it is selected.
        $(document).on('change', '#optin-monster-provider-account', function(){
           var provider = $('#optin-monster-field-provider').find(':selected').val(),
               account  = $(this).find(':selected').val();
            if ( 0 === provider.length || 0 === account.length || 'none' == account ) {
                return;
            }

            var data = {
                action:   'optin_monster_get_provider',
                provider: provider,
                account:  account,
                id:       optin_monster_edit.id
            }
            $('.om-error').remove();
            $('.optin-monster-provider-clients, .optin-monster-provider-lists, .optin-monster-provider-segments').remove();
            $('.fa-spinner').fadeTo(0, 1);
            if ( 'campaign-monitor' !== provider ) {
                omGetProvider(data);
            } else {
                omGetProviderClient(data);
            }
        });

        // Switch lists when a different client is selected
        $(document).on('change', '#optin-monster-client-list', function() {
            var provider = $('#optin-monster-field-provider').find(':selected').val(),
                account  = $('#optin-monster-provider-account').find(':selected').val(),
                client = $(this).find(':selected').val();

            if ( 0 === provider.length || 0 === account.length || 'none' == account ) {
                return;
            }

            var data = {
                action:   'optin_monster_get_provider',
                provider: provider,
                account:  account,
                client:   client,
                id:       optin_monster_edit.id
            }
            $('.om-error').remove();
            $('.fa-spinner').fadeTo(0, 1);
            omGetProvider(data);
        });

        // Possibly grab list segments if the provider supports it.
        $(document).on('change', '#optin-monster-provider-list', function(){
           var provider = $('#optin-monster-field-provider').find(':selected').val(),
               account  = $('#optin-monster-provider-account').find(':selected').val()
               list     = $(this).find(':selected').val();
            if ( 0 === provider.length || 0 === account.length || 0 === list.length ) {
                return;
            }

            var data = {
                action:   'optin_monster_get_provider_segments',
                provider: provider,
                account:  account,
                list:     list,
                id:       optin_monster_edit.id
            }
            $('.om-error').remove();
            $('.fa-spinner').fadeTo(0, 1);
            omGetProviderSegments(data);
        });

        // Handler custom HTML optin forms.
        $(document).on('blur', '#optin-monster-custom-html', function (e) {
            var provider = $('#optin-monster-field-provider').find(':selected').val(),
                $this = $(this);

            var data = {
                action: 'optin_monster_save_custom_html',
                provider: provider,
                id: optin_monster_edit.id,
                content: $this.val()
            };

            $('.om-error').remove();
            $('fa-spinner').fadeTo(0, 1);
            omSaveCustomHtml(data, $this);
        });

        // Register a new provider on click when adding a new account.
        $(document).on('click', '.om-register-provider', function (e) {
            e.preventDefault();
            var $this    = $(this),
                provider = $this.data('om-email-provider'),
                href     = $this.attr('href');

            // Run custom args for certain providers that need external authentication.
            switch ( provider ) {
                case 'hubspot' :
                    var args = {
                        client_id:    '4c4b2343-fd6a-11e3-aead-bddfb3c95bea',
                        portalId:     $('#om-hubspot-portalid').val(),
                        redirect_uri: 'https://optinmonster.com/oauth-v2',
                        scope:        'contacts-rw+offline'
                    };
                    args = $.param(args);
                    // HubSpot needs a literal '+' to separate scopes.
                    args = args.replace( new RegExp("%2B", "g"), "+" );
                    href = href + '?' + args;
                    break;
                case 'constant-contact' :
                    args = {
                        response_type : 'token',
                        client_id:      'vsch38zpe6hm4wfb59t79nzh',
                        redirect_uri:   'https://optinmonster.com/oauth-v2'
                    };
                    args = $.param(args);
                    href = href + '?' + args;
                    break;
                case 'campaign-monitor' :
                    args = {
                        type:         'web_server',
                        client_id:    '100281',
                        redirect_uri: 'https://optinmonster.com/campaign-monitor-oauth-v2/',
                        scope:        'ManageLists,ImportSubscribers'
                    }
                    args = $.param(args);
                    href = href + '?' + args;
                    break;
            }

            // Set variables for the postMessage request.
            om_request_url      = 'https://optinmonster.com';
            om_request_provider = provider;
            var oauth_window    = window.open( href, '', 'resizable=yes,location=no,width=750,height=600,top=0,left=0' );

            // Listen to the callback and set the fields.
            om_request_call = setInterval(function(){
                var message = 'OptinMonster...';
                omSendMessage(message, om_request_url, oauth_window);
            }, 6000);

            $.receiveMessage(function(e){
                var data = $.getQueryParameters(decodeURIComponent(e.data));
                switch ( data.event ) {
                    case 'omSaveTrigger' :
                        $(document).trigger('OptinMonsterSave', data);
                        break;
                    case 'omFieldTrigger' :
                        omFieldTrigger(data.target);
                        break;
                    case 'omMediaTrigger' :
                        $(document).trigger('OptinMonsterMediaModal', data.theme);
                        break;
                    case 'omMediaSuccessTrigger' :
                        $(document).trigger('OptinMonsterMediaModalSuccess');
                        break;
                    case 'omOauthTrigger' :
                        if ( e.origin !== om_request_url ) {
                            return;
                        }

                        clearInterval(om_request_call);
                        omfillOauthFields($.getQueryParameters(e.data), om_request_provider);
                        break;
                }
            }, false);
        });

        // Connect a new provider on click when adding a new account.
        $(document).on('click', '.om-connect-provider', function(e){
            e.preventDefault();
            var $this    = $(this),
                provider = $this.data('om-email-provider'),
                inputs   = $('.optin-monster-account-fields').find(':input'),
                fields   = inputs.serialize(),
                data     = {
                    action:   'optin_monster_connect_provider',
                    provider: provider,
                    id:       optin_monster_edit.id,
                    fields:   fields
                }

            $('.om-error').remove();
            $('.om-input-error').removeClass('om-input-error');
            $('.fa-spinner').fadeTo(0, 1);
            if ( omValidateFields(inputs) ) {
                omConnectProvider(data, $this);
            } else {
                $('.fa-spinner').fadeTo(300, 0);
                $this.parent().append('<p class="om-error">' + optin_monster_edit.fields + '</p>');
            }
        });
    }

    // Fills the proper fields for OAuth.
    function omfillOauthFields(data, provider) {
        $('.om-register-provider').hide();
        switch ( provider ) {
            case 'hubspot' :
                $('#om-hubspot-access-token').val(data.access_token);
                $('#om-hubspot-refresh-token').val(data.refresh_token);
                $('#om-hubspot-expires-in').val(data.expires_in);
                break;
            case 'constant-contact' :
                $('#om-access-token').val(data.access_token);
                $('#om-expires-in').val(data.expires_in);
                break;
            case 'campaign-monitor' :
                $('#om-access-token').val(data.access_token);
                $('#om-refresh-token').val(data.refresh_token);
                $('#om-expires-in').val(data.expires_in);
                break;
        }
        $('#om-account-label').focus();
    }

    // Function to initiate the ajax call to get data for a provider.
    function omInitProvider(data){
        $.post(optin_monster_edit.ajax, data, function(res){
            $('.fa-spinner').fadeTo(300, 0);

            if ( res && res.error ) {
                $('#optin-monster-field-provider').after(res.error);
                return;
            }

            // If on IE and one of the OAuth providers is selected, show an error message.
            if ( omIsIE() ) {
                if ( 'campaign-monitor' == data.provider || 'constant-contact' == data.provider || 'hubspot' == data.provider ) {
                    if ( 0 !== $('.optin-monster-field-box-account').length ) {
                        $('.optin-monster-field-box-account').replaceWith('<p class="optin-monster-field-wrap">' + optin_monster_edit.ie + '</p>');
                    } else {
                        $('#optin-monster-field-provider').after('<div class="optin-monster-field-box-account"><p class="optin-monster-field-wrap">' + optin_monster_edit.ie + '</p></div>');
                    }
                    return;
                }
            }

            if ( 0 !== $('.optin-monster-field-box-account').length ) {
                $('.optin-monster-field-box-account').replaceWith(res);
            } else {
                $('#optin-monster-field-provider').after(res);
            }
        }, 'json');
    }

    // Function to get the provider data for the account selected.
    function omGetProvider(data){
        $.post(optin_monster_edit.ajax, data, function(res){
            $('.fa-spinner').fadeTo(300, 0);

            if ( res && res.error ) {
                $('.optin-monster-provider-lists').remove();
                $('#optin-monster-provider-account').after(res.error);
                return;
            }

            $('.optin-monster-field-box-account').remove();

            if ( 0 !== $('.optin-monster-provider-lists').length ) {
                $('.optin-monster-provider-lists').replaceWith(res);
            } else {
                $('#optin-monster-provider-account').after(res);
            }
        }, 'json');
    }

    // Function to get the provider segments data for the account selected.
    function omGetProviderSegments(data){
        $.post(optin_monster_edit.ajax, data, function(res){
            $('.fa-spinner').fadeTo(300, 0);

            if ( res && res.error ) {
                $('.optin-monster-provider-segments').remove();
                return;
            }

            if ( 0 !== $('.optin-monster-provider-segments').length ) {
                $('.optin-monster-provider-segments').replaceWith(res);
            } else {
                $('#optin-monster-provider-list').after(res);
            }
        }, 'json');
    }

    // Function to get client segments (for Campaign Monitor).
    function omGetProviderClient(data){
        data.action = 'optin_monster_get_provider_clients';
        return $.post(optin_monster_edit.ajax, data, function(res){
            $('.fa-spinner').fadeTo(300, 0);

            var client_selector = $('.optin-monster-provider-clients');

            if ( res && res.error ) {
                client_selector.remove();
                return;
            }

            if ( 0 !== client_selector.length ) {
                client_selector.replaceWith(res);
            } else {
                $('#optin-monster-provider-account').after(res);
            }
        }, 'json');
    }

    function omSaveCustomHtml(data, el){
        $.post(optin_monster_edit.ajax, data, function (res){
            $('.fa-spinner').fadeTo(300, 0);
        }, 'json');
    }

    // Function to initiate the ajax call to connect an email provider.
    function omConnectProvider(data, el){
        $.post(optin_monster_edit.ajax, data, function(res){
            $('.fa-spinner').fadeTo(300, 0);

            if ( res && res.error ) {
                el.parent().append(res.error);
                return;
            }

            // Empty out any old code and add the new.
            $('.optin-monster-field-box-account, .optin-monster-provider-accounts, .optin-monster-provider-lists').remove();
            $('#optin-monster-field-provider').after(res);
        }, 'json');
    }

    // Validate email provieer API fields so ensure that all are filled out.
    function omValidateFields(fields){
        var empty = false;
        $.each(fields, function(i, el){
            if ( 0 === $(this).val().length ) {
                $(this).addClass('om-input-error');
                empty = true;
            }
        });
        return ! empty;
    }

    // Generate the preview frame and create a postMessage port for communicating between the frames.
    function omPreview(){
        var preview = $('.optin-monster-preview-frame'),
            iframe  = {
                src:         om_current_url,
                frameborder: 0,
                cellspacing: 0,
                id:          'optin-monster-preview',
                name:        'optin-monster-preview'
            };
        preview.html($('<iframe />', iframe));
        om_preview_frame = $('#optin-monster-preview');
    }

    // Saves all of the panel options for the optin.
    function omSave(){
        $(document).on('click', '.om-toolbar-button', function(e, forced){
            om_theme_forced = $('.optin-monster-sidebar .om-active-theme').data('om-optin-theme');
            om_save_forced  = forced || false;
            var $this   = $(this),
                text    = $this.find('span').text(),
                action  = $this.data('om-action'),
                data    = {
                    action: 'optin_monster_save_optin',
                    fields: $('#optin-monster-settings-form').serialize(),
                    nonce:  optin_monster_edit.save_nonce,
                    id:     optin_monster_edit.id,
                    split:  optin_monster_edit.split,
                    forced: om_save_forced,
                    theme:  $('.optin-monster-sidebar .om-active-theme').data('om-optin-theme')
                };

            // If wanting to exit, show a dialog before they do and then proceed to exit.
            if ( 'exit-none' == action ) {
                return confirm(optin_monster_edit.confirm);
            }

            e.preventDefault();
            $('.fa-spinner').fadeTo(0, 1);
            $this.find('span').text(optin_monster_edit.saving);

            $.post(optin_monster_edit.ajax, data, function(res){
                // Now save the optin content.
                var request = {
                    action: encodeURIComponent(action),
                    text:   encodeURIComponent(text),
                    exit:   encodeURIComponent(res),
                    event:  'omSaveTrigger'
                };
                omSendMessage(request);
            }, 'json');
        });

        $(document).on('OptinMonsterSave', function(e, request){
            $('.fa-spinner').fadeTo(300, 0);
            if ( 'exit' == decodeURIComponent(request.action) ) {
                window.location.href = decodeURIComponent(request.exit);
            } else {
                $('.om-toolbar-button:first').find('span').text(decodeURIComponent(request.text));
            }

            // If this was a forced save, regenerate the preview frame with the new theme.
            if ( om_save_forced ) {
                // Set the URL so that it loads the custom theme.
                om_current_url = optin_monster_edit.iframe + '&om-live-theme=' + om_theme_forced + '#' + encodeURIComponent( document.location.href );
                omPreview();

                // Set the forced variable to false to prevent unwanted preview reloads when actually clicking on a save button.
                om_save_forced = false;
            }
        });
    }

    // Live edits the campaign title in the preview sidebar.
    function omCampaignTitle(){
        $(document).on('keyup keydown', '#optin-monster-field-campaign_title', function(){
            var $this  = $(this),
                val    = $this.val(),
                target = $this.data('target'),
                method = $this.data('method');

            // Throttle the live preview typing for performance.
            omDelay(function(){
                $(target)[method](val);
            }, 50);
        });
    }

    // Hides the tips from the user view.
    function omHideTips(){
        $(document).on('click', '.optin-monster-sidebar .om-tip-close', function(e){
        	e.preventDefault();
        	var data = {
	        	action: 'optin_monster_hide_tips'
        	};
            $.post(optin_monster_edit.ajax, data, function(){
	            $('.om-helpful-tips').fadeOut(300);
            });
        });
    }

    // Initializes the colorpicker fields for handling background colors in optins.
    function omColors(){
        $('.om-color-picker').each(function(){
            $(this).minicolors({
                changeDelay: 200,
                change: function(hex, opacity){
                    var target = $(this).data('target'),
                        props  = $(this).data('props').split(',');

                    // If opacity is defined, then we need to use an rgba string instead of hex.
                    if ( 'undefined' !== typeof opacity ) {
                        hex = $(this).minicolors('rgbaString');
                    }

                    $.each(props, function(i, prop){
                        var data = {
                            event: 'omApplyColor',
                            target: encodeURIComponent(target),
                            prop:   encodeURIComponent($.trim(prop)),
                            color:  encodeURIComponent(hex)
                        };
                        omSendMessage(data);
                    });

                    // See if there's a hidden border color field and assign the correct color.
                    var name   = $(this).attr('id'),
                        source = name.substr(name.lastIndexOf('-') + 1),
                        border = $('input[data-source="' + source + '"]');

                    if ( 'undefined' !== typeof border ) {
                        // See if the target color should be the same as the source
                        var same = border.data('same-color');
                        if ( same ) {
                            border.val($.Color(hex));
                            border.trigger('change');
                        } else {
                            border.val($.Color(hex).lightness(+0.45));
                            border.trigger('change');
                        }
                    }
                },
                opacity: !! $(this).data('opacity')
            });
        });
    }

    // Initializes the various live preview fields for the customizer.
    function omFields(){
        $(document).on('keyup keydown change', '.om-live-preview', function(){
            var $this  = $(this),
                val    = $this.val(),
                target = $this.data('target'),
                method = $this.data('method'),
                attr   = 'attr' == method || 'css' == method ? $this.data('attr') : false,
                data   = {
                    event:  'omFieldTrigger',
                    val:    encodeURIComponent(val),
                    target: encodeURIComponent(target),
                    method: encodeURIComponent(method),
                    attr:   encodeURIComponent(attr)
                };

            // Throttle the live preview typing for performance.
            omDelay(function(){
                omSendMessage(data);
            }, 50);
        });

        // Apply the custom CSS on blur.
        $(document).on('blur', '.om-custom-css-editor', function(){
            var $this  = $(this),
                target = $this.data('target'),
                theme  = $this.data('theme'),
                data   = {
                    event:  'omApplyStyles',
                    target: encodeURIComponent(target),
                    theme:  encodeURIComponent(theme),
                    val:    encodeURIComponent($(this).val())
                };

            omSendMessage(data);
        });

        // Apply custom HTML for Canvas on blur.
        $(document).on('blur', '.om-custom-html-editor', function(){
            var $this  = $(this),
                target = $this.data('target'),
                theme  = $this.data('theme'),
                data   = {
                    action: 'optin_monster_parse_canvas_shortcodes',
                    event: 'omApplyHtml',
                    target: target,
                    theme: theme,
                    val: $this.val()
                };

            // Send the content to be parsed before displaying
            $.post(optin_monster_edit.ajax, data, function(response){
                data.val = encodeURIComponent(JSON.parse(response));
                omSendMessage(data);
            });
        });
        // Initialize chosen for font dropdown fields.
        omInitChosen();
    }

    // Initializes the theme selection panel to live change optin themes.
    function omThemeSelection(){
        $(document).on('click', '.optin-monster-sidebar .optin-monster-theme, .optin-monster-sidebar .om-theme-select', function(e){
            e.preventDefault();
            e.stopPropagation();

            // Set the active theme and other contextual items for the new theme.
            var old_active = $('.om-active-theme');
            old_active.removeClass('om-active-theme');
            old_active.find('.optin-monster-theme-name span').remove();

            // Grab the newly activated theme and set some items for it.
            if ( $(this).hasClass('om-theme-select') ) {
                var new_active = $(this).closest('.optin-monster-theme').addClass('om-active-theme');
            } else {
                var new_active = $(this).addClass('om-active-theme');
            }
            new_active.find('.optin-monster-theme-name').prepend('<span>' + optin_monster_edit.theme + '</span>');
            new_active.find('.optin-monster-theme-actions').hide();

            // Replace the toolbar theme preview with the new theme preview name.
            var new_theme = new_active.data('om-optin-theme'),
                old_theme = old_active.data('om-optin-theme'),
                title     = $('.optin-monster-toolbar-title h3').html();
            new_theme     = new_theme.replace('-', ' ').replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
            old_theme     = old_theme.replace('-', ' ').replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
            title         = title.replace(old_theme, new_theme);
            $('.optin-monster-toolbar-title h3').html(title);

            // Force a save to regenerate the theme.
            $('.om-toolbar-button[data-om-action="save"]').trigger('click', true);
        });
    }

    // Initializes the media modal in the parent to add images.
    function omMediaModal(){
        $(document).on('OptinMonsterMediaModal', function(e, info){
            var tgm_media_frame = wp.media.frames.tgm_media_frame = wp.media({
                className: 'media-frame tgm-media-frame',
                frame: 'select',
                multiple: false,
                title: optin_monster_edit.title,
                library: {
                    type: 'image'
                },
                button: {
                    text:  optin_monster_edit.insert
                }
            });

            tgm_media_frame.on('select', function(){
                // Grab our attachment selection and construct a JSON representation of the model.
                var attachment = tgm_media_frame.state().get('selection').first().toJSON();

                // Store the theme in the attachment data.
                attachment.om_theme = decodeURIComponent(info.theme);
                attachment.om_type  = decodeURIComponent(info.type);

                // Send the attachment id to the preview frame to have it processed.
                $('.fa-spinner').fadeTo(0, 1);
                var data = {
                    event:      'omMediaTrigger',
                    attachment: encodeURIComponent(JSON.stringify(attachment))
                };
                omSendMessage(data);
            });

            // Now that everything has been set, let's open up the frame.
            tgm_media_frame.open();
        });

        $(document).on('OptinMonsterMediaModalSuccess', function(){
            $('.fa-spinner').fadeTo(300, 0);
        });
    }

    // Conditionally display the name field based on option selection.
    function omNameDisplay(){
        var name_field = $('#optin-monster-field-name_show');
        if ( ! $(name_field).is(':checked') ) {
            $(name_field).parent().parent().nextUntil('.optin-monster-field-header').hide();
        }

        var data = {};
        $(name_field).on('change', function(){
            var $this   = $(this),
                target  = $this.data('target'),
                input   = $this.data('input'),
                name    = $this.data('name'),
                checked = false;

            if ( $(this).is(':checked') ) {
                checked = true;
                $(this).parent().parent().nextUntil('.optin-monster-field-header').fadeIn(300);
                omInitChosen();
            } else {
                $(this).parent().parent().nextUntil('.optin-monster-field-header').fadeOut(300);
            }

            var request = {
                event:   'omNameTrigger',
                target:  encodeURIComponent(target),
                input:   encodeURIComponent(input),
                name:    encodeURIComponent(name),
                checked: checked
            };
            omSendMessage(request);
        });
    }

    // Conditionally display the "powered by" link based on option selection.
    function omLinkDisplay(){
        var link_field = $('#optin-monster-field-powered_by'),
            data       = {};
        $(link_field).on('change', function(){
            var $this   = $(this),
                target  = $this.data('target'),
                html    = $this.data('html'),
                checked = $(this).is(':checked') ? true : false;

            var request = {
                event:   'omLinkTrigger',
                target:  encodeURIComponent(target),
                html:    encodeURIComponent(html),
                checked: checked
            };
            omSendMessage(request);
        });
    }

    // Initialize the chosen library.
    function omInitChosen(){
        // When Chosen is ready, add styles to the active elements.
        $('.om-font-field').on('chosen:ready', function(e, params){
            $.each(params.chosen.results_data, function(i, obj){
                obj.style = 'font-family:"' + obj.value + '";';
            });
            $(this).trigger('chosen:updated');
        });

        // Initialize Chosen.
        $('.om-font-field, .om-chosen-field').chosen({
            width: '100%'
        });
    }

    // Utility function to apply throttling to keyup/keydown checking for input fields.
    var omDelay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    // Utility function to check for IE.
    function omIsIE() {
        return ((navigator.appName == 'Microsoft Internet Explorer') || ((navigator.appName == 'Netscape') && (new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})").exec(navigator.userAgent) != null)));
    }

    // Ensure that window.location.origin is normalized across browsers.
    if ( ! window.location.origin ) {
        window.location.origin = window.location.protocol + '//' + window.location.hostname + ( window.location.port ? ':' + window.location.port : '' );
    }

    // Utility function to send a message using postMessage.
    function omSendMessage(data, domain, frame){
        var post_data   = data || false,
            post_domain = domain || om_current_url,
            post_frame  = frame || om_preview_frame.get(0).contentWindow;
        $.postMessage(post_data, post_domain, post_frame);
    }

    // Receive messages from the preview frame.
    $.receiveMessage(function(e){
        var data = $.getQueryParameters(decodeURIComponent(e.data));
        switch ( data.event ) {
            case 'omSaveTrigger' :
                $(document).trigger('OptinMonsterSave', data);
                break;
            case 'omFieldTrigger' :
                omFieldTrigger(data.target);
                break;
            case 'omMediaTrigger' :
                $(document).trigger('OptinMonsterMediaModal', data);
                break;
            case 'omMediaSuccessTrigger' :
                $(document).trigger('OptinMonsterMediaModalSuccess');
                break;
            case 'omOauthTrigger' :
                if ( data.origin !== om_request_url ) {
                    return;
                }

                omfillOauthFields(data, om_request_provider);
                break;
        }
    }, window.location.origin );

    // Function to trigger selecting fields based on clicking in the preview frame.
    function omFieldTrigger(target){
        // If the target is not visible, force it to become visible before adding focus.
        var delay = 0;
        if ( ! $(decodeURIComponent(target)).is(':visible') ) {
            delay = 300;
            $(decodeURIComponent(target)).closest('.optin-monster-board').prev().children('a').click();
        }

        setTimeout(function(){
            $(decodeURIComponent(target)).focus();
        }, delay);
    }
});