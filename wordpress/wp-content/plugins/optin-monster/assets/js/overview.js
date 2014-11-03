/* ==========================================================
 * overview.js
 * http://optinmonster.com/
 * ==========================================================
 * Copyright 2014 Thomas Griffin.
 *
 * Use of this file is bound by the Terms of Service agreed
 * upon when purchasing OptinMonster.
 * http://optinmonster.com/terms/
 * ========================================================== */
;(function ($) {
    $(function () {
        // Initialize the tabs.
        var optin_monster_tabs = $('#optin-monster-tabs'),
            optin_monster_tabs_nav = $('#optin-monster-tabs-nav'),
            optin_monster_tabs_hash = window.location.hash,
            optin_monster_tabs_hash_sani = window.location.hash.replace('!', '');

        // If we have a hash and it begins with "optin-monster-tab", set the proper tab to be opened.
        if (optin_monster_tabs_hash && optin_monster_tabs_hash.indexOf('optin-monster-tab-') >= 0) {
            $('.optin-monster-active').removeClass('optin-monster-active nav-tab-active');
            optin_monster_tabs_nav.find('a[href="' + optin_monster_tabs_hash_sani + '"]').addClass('optin-monster-active nav-tab-active');
            optin_monster_tabs.find(optin_monster_tabs_hash_sani).addClass('optin-monster-active').show();
        }

        // Change tabs on click.
        $('#optin-monster-tabs-nav a').on('click', function (e) {
            e.preventDefault();
            var $this = $(this);
            if ($this.hasClass('optin-monster-active')) {
                return;
            } else {
                window.location.hash = optin_monster_tabs_hash = this.hash.split('#').join('#!');
                var current = optin_monster_tabs_nav.find('.optin-monster-active').removeClass('optin-monster-active nav-tab-active').attr('href');
                $this.addClass('optin-monster-active nav-tab-active');
                optin_monster_tabs.find(current).removeClass('optin-monster-active').hide();
                optin_monster_tabs.find($this.attr('href')).addClass('optin-monster-active').show();

                // Remove any notices from the screen when changing tabs.
                $(document).find('div.updated').remove();
            }
        });

        // Initialize sortable optins.
        var optins = $('#optin-monster-optins-table');
        optins.sortable({
            containment         : 'table.widefat',
            items               : 'tbody > tr',
            cursor              : 'move',
            axis                : 'y',
            forcePlaceholderSize: true,
            placeholder         : 'dropzone',
            distance            : 2,
            opacity             : .8,
            tolerance           : 'pointer',
            cancel              : '.om-optin-slug, .om-unique-slug',
            start               : function (e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            update              : function (e, ui) {
                // Show the spinner.
                $('.fa-spinner').fadeTo(0, 1);

                // Prepare the data.
                var data = {
                    action: 'optin_monster_sort_optins',
                    order : optins.sortable('toArray').toString()
                };

                // Make the ajax request.
                $.post(optin_monster_overview.ajax, data, function (res) {
                    $('.fa-spinner').fadeTo(300, 0);
                }, 'json');

                // Ensure row classes are set properly.
                var table_rows = document.querySelectorAll('#the-list > tr'),
                    table_row_count = table_rows.length;
                while (table_row_count--) {
                    if (table_row_count % 2 == 0) {
                        $(table_rows[table_row_count]).addClass('alternate');
                    } else {
                        $(table_rows[table_row_count]).removeClass('alternate');
                    }
                }
            },
            helper              : function (e, ui) {
                // Ensure table cells don't collapse.
                ui.children().each(function () {
                    $(this).width($(this).width());
                });
                return ui;
            }
        });

        // Make sure users want to perform the bulk action before proceeding.
        $('#optin-monster-optins-table').submit( function () {
            return confirm(optin_monster_overview.confirm);
        });

        // Make sure users want to delete optins before proceeding.
        $(document).on('click', '.submitdelete', function () {
            return confirm(optin_monster_overview.confirm);
        });

        // Open the optin settings icon on click.
        $(document).on('click', '.om-settings-button', function (e) {
            e.preventDefault();
            // Hide any popovers active.
            $('.om-settings-popover').hide();

            var $this = $(this),
                popup = $this.next(),
                width = popup.width();

            if ($this.hasClass('om-active')) {
                $this.removeClass('om-active');
                popup.hide().css('left', '0');
            } else {
                $('.om-settings-button').removeClass('om-active');
                $this.addClass('om-active');
                popup.show().css('left', -(width + 22));
            }
        });

        // Hide the settings popover when the user clicks anywhere on the page.
        $(document).on('click', function(e){
            var target = e.target,
                parent = $(e.target).closest('.om-settings-popover');

            if ( $(e.target).hasClass('om-settings-button') || $(e.target).hasClass('fa-cog') ) {
                return;
            }

            if ( 0 === parent.length ) {
                $('.om-settings-popover').hide();
                $('.om-settings-button').removeClass('om-active');
            }
        });

        // Re-enable install button if user clicks on it, needs creds but tries to install another addon instead.
        $('#optin-monster-addons-area').on('click.refreshInstallAddon', '.optin-monster-addon-action-button', function (e) {
            var el = $(this);
            var buttons = $('#optin-monster-addons-area').find('.optin-monster-addon-action-button');
            $.each(buttons, function (i, element) {
                if (el == element)
                    return true;

                omAddonRefresh(element);
            });
        });

        // Process Addon activations for those currently installed but not yet active.
        $('#optin-monster-addons-area').on('click.activateAddon', '.optin-monster-activate-addon', function (e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.optin-monster-addon-error').remove();
            $(this).text(optin_monster_overview.activating);
            $(this).next().css({'display': 'inline-block', 'margin-top': '0px'});
            var button = $(this);
            var plugin = $(this).attr('rel');
            var el = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url     : ajaxurl,
                type    : 'post',
                async   : true,
                cache   : false,
                dataType: 'json',
                data    : {
                    action: 'optin_monster_activate_addon',
                    nonce : optin_monster_overview.activate_nonce,
                    plugin: plugin
                },
                success : function (response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if (response && true !== response) {
                        $(el).slideDown('normal', function () {
                            $(this).after('<div class="optin-monster-addon-error"><strong>' + response.error + '</strong></div>');
                            $this.next().hide();
                            $('.optin-monster-addon-error').delay(3000).slideUp();
                        });
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).text(optin_monster_overview.deactivate).removeClass('optin-monster-activate-addon').addClass('optin-monster-deactivate-addon');
                    $(message).text(optin_monster_overview.active);
                    $(el).removeClass('optin-monster-addon-inactive').addClass('optin-monster-addon-active');
                    $this.next().hide();
                },
                error   : function (xhr, textStatus, e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        // Process Addon deactivations for those currently active.
        $('#optin-monster-addons-area').on('click.deactivateAddon', '.optin-monster-deactivate-addon', function (e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.optin-monster-addon-error').remove();
            $(this).text(optin_monster_overview.deactivating);
            $(this).next().css({'display': 'inline-block', 'margin-top': '0px'});
            var button = $(this);
            var plugin = $(this).attr('rel');
            var el = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url     : ajaxurl,
                type    : 'post',
                async   : true,
                cache   : false,
                dataType: 'json',
                data    : {
                    action: 'optin_monster_deactivate_addon',
                    nonce : optin_monster_overview.deactivate_nonce,
                    plugin: plugin
                },
                success : function (response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if (response && true !== response) {
                        $(el).slideDown('normal', function () {
                            $(this).after('<div class="optin-monster-addon-error"><strong>' + response.error + '</strong></div>');
                            $this.next().hide();
                            $('.optin-monster-addon-error').delay(3000).slideUp();
                        });
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).text(optin_monster_overview.activate).removeClass('optin-monster-deactivate-addon').addClass('optin-monster-activate-addon');
                    $(message).text(optin_monster_overview.inactive);
                    $(el).removeClass('optin-monster-addon-active').addClass('optin-monster-addon-inactive');
                    $this.next().hide();
                },
                error   : function (xhr, textStatus, e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        // Process Addon installations.
        $('#optin-monster-addons-area').on('click.installAddon', '.optin-monster-install-addon', function (e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.optin-monster-addon-error').remove();
            $(this).text(optin_monster_overview.installing);
            $(this).next().css({'display': 'inline-block', 'margin-top': '0px'});
            var button = $(this);
            var plugin = $(this).attr('rel');
            var el = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url     : ajaxurl,
                type    : 'post',
                async   : true,
                cache   : false,
                dataType: 'json',
                data    : {
                    action: 'optin_monster_install_addon',
                    nonce : optin_monster_overview.install_nonce,
                    plugin: plugin
                },
                success : function (response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if (response.error) {
                        $(el).slideDown('normal', function () {
                            $(button).parent().parent().after('<div class="optin-monster-addon-error"><strong>' + response.error + '</strong></div>');
                            $(button).text(optin_monster_overview.install);
                            $this.next().hide();
                            $('.optin-monster-addon-error').delay(4000).slideUp();
                        });
                        return;
                    }

                    // If we need more credentials, output the form sent back to us.
                    if (response.form) {
                        // Display the form to gather the users credentials.
                        $(el).slideDown('normal', function () {
                            $(this).after('<div class="optin-monster-addon-error">' + response.form + '</div>');
                            $this.next().hide();
                        });

                        // Add a disabled attribute the install button if the creds are needed.
                        $(button).attr('disabled', true);

                        $('#optin-monster-addons-area').on('click.installCredsAddon', '#upgrade', function (e) {
                            // Prevent the default action, let the user know we are attempting to install again and go with it.
                            e.preventDefault();
                            $this.next().hide();
                            $(this).val(optin_monster_overview.installing);
                            $(this).next().css({'display': 'inline-block', 'margin-top': '0px'});

                            // Now let's make another Ajax request once the user has submitted their credentials.
                            var hostname = $(this).parent().parent().find('#hostname').val();
                            var username = $(this).parent().parent().find('#username').val();
                            var password = $(this).parent().parent().find('#password').val();
                            var proceed = $(this);
                            var connect = $(this).parent().parent().parent().parent();
                            var cred_opts = {
                                url     : ajaxurl,
                                type    : 'post',
                                async   : true,
                                cache   : false,
                                dataType: 'json',
                                data    : {
                                    action  : 'optin_monster_install_addon',
                                    nonce   : optin_monster_overview.install_nonce,
                                    plugin  : plugin,
                                    hostname: hostname,
                                    username: username,
                                    password: password
                                },
                                success : function (response) {
                                    // If there is a WP Error instance, output it here and quit the script.
                                    if (response.error) {
                                        $(el).slideDown('normal', function () {
                                            $(button).parent().parent().after('<div class="optin-monster-addon-error"><strong>' + response.error + '</strong></div>');
                                            $(button).text(optin_monster_overview.install);
                                            $this.next().hide();
                                            $('.optin-monster-addon-error').delay(4000).slideUp();
                                        });
                                        return;
                                    }

                                    if (response.form) {
                                        $this.next().hide();
                                        $('.optin-monster-inline-error').remove();
                                        $(proceed).val(optin_monster_overview.proceed);
                                        $(proceed).after('<span class="optin-monster-inline-error">' + optin_monster_overview.connect_error + '</span>');
                                        return;
                                    }

                                    // The Ajax request was successful, so let's update the output.
                                    $(connect).remove();
                                    $(button).show();
                                    $(button).text(optin_monster_overview.activate).removeClass('optin-monster-install-addon').addClass('optin-monster-activate-addon');
                                    $(button).attr('rel', response.plugin);
                                    $(button).removeAttr('disabled');
                                    $(message).text(optin_monster_overview.inactive);
                                    $(el).removeClass('optin-monster-addon-not-installed').addClass('optin-monster-addon-inactive');
                                    $this.next().hide();
                                },
                                error   : function (xhr, textStatus, e) {
                                    $this.next().hide();
                                    return;
                                }
                            }
                            $.ajax(cred_opts);
                        });

                        // No need to move further if we need to enter our creds.
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).text(optin_monster_overview.activate).removeClass('optin-monster-install-addon').addClass('optin-monster-activate-addon');
                    $(button).attr('rel', response.plugin);
                    $(message).text(optin_monster_overview.inactive);
                    $(el).removeClass('optin-monster-addon-not-installed').addClass('optin-monster-addon-inactive');
                    $this.next().hide();
                },
                error   : function (xhr, textStatus, e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        $('.delete-integration').on('click', function (e) {
            var $this = $(this),
                delete_integration = confirm('Are you sure you want to delete this integration?');

            if (!delete_integration) return false;

            e.preventDefault();
            var data = {
                action  : 'optin_monster_delete_integration',
                provider: $this.data('provider'),
                hash    : $this.data('hash')
            };

            $.post(ajaxurl, data, function (resp) {
                if (resp.provider_empty) {
                    $('.optin-integration.' + data.provider).fadeOut(300);
                }
                if (resp.updated) {
                    $this.parent().fadeOut(300);
                } else {
                    $this.parent().append('<p class="error">There was an error deleting the integration</p>');
                }
            }, 'json');
        });

        // Saves the Settings options.
        $(document).on('click', '.om-save-settings', function(e){
            e.preventDefault();
            var $this = $(this),
                text  = $this.text(),
                data  = {
                    action: 'optin_monster_save_settings',
                    fields: $('#optin-monster-settings').find(':input').serialize()
            };
            $this.text(optin_monster_overview.saving);
            $.post(optin_monster_overview.ajax, data, function(res){
                $this.text(text);
                window.location.href = res;
            }, 'json');
        });

        // Function to clear any disabled buttons and extra text if the user needs to add creds but instead tries to install a different addon.
        function omAddonRefresh(element) {
            if ($(element).attr('disabled'))
                $(element).removeAttr('disabled');

            if ($(element).parent().parent().hasClass('optin-monster-addon-not-installed'))
                $(element).text(optin_monster_overview.install);
        }
    });
}(jQuery));