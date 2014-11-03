/* ==========================================================
 * preview.js
 * http://optinmonster.com/
 * ==========================================================
 * Copyright 2014 Thomas Griffin.
 *
 * Use of this file is bound by the Terms of Service agreed
 * upon when purchasing OptinMonster.
 * http://optinmonster.com/terms/
 * ========================================================== */
jQuery(document).ready(function($){
    // Set global variable for easy access to the parent frame.
    var om_preview_parent = window.parent.document,
        om_parent_url     = decodeURIComponent( document.location.href.replace( /^%23/, '' ).replace( 'om-' + optin_monster_preview.optin + '#', '' ) ).split('#'),
        om_parent_url	  = om_parent_url[om_parent_url.length - 1],
        om_editors        = {},
        om_jq_object;

    // Set the proper jQuery object for targeting OptinMonster events.
    if ( typeof window[optin_monster_preview.optinjs] != 'undefined' ) {
        if ( window[optin_monster_preview.optinjs].hasOwnProperty('public') ) {
            om_jq_object = window[optin_monster_preview.optinjs].public.ejQuery;
        } else {
            om_jq_object = window.jQuery;
        }
    } else {
        om_jq_object = window.jQuery;
    }

    // If the jQuery object is still undefined, set it to the window.jQuery object.
    if ( typeof om_jq_object == 'undefined' ) {
        om_jq_object = window.jQuery;
    }

    // Load the query string parser.
    omInitQuery();

    // Load all the available webfonts for previewing.
    omLoadFonts();

    // Set some default CKEDITOR flags.
    omSetEditorFlags();

    // Hook into the optin show event to initialize the live preview editing experience.
    om_jq_object(document).on('OptinMonsterOnShow OptinMonsterBeforeClose OptinMonsterBeforeOpen', omInitLivePreview);

    // Initialize the media modal window.
    omMediaModal();

    // Load the anchor jump helper.
    omAnchorJump();

    // If IE, load the placeholder shim.
    if ( typeof om_ie_browser != 'undefined' ) {
        omLoadPlaceholder();
    }

    // Registers the jQuery plugin to parse query strings.
    function omInitQuery(){
        $.extend({
            getQueryParameters : function(str) {
                return (str || document.location.search).replace(/(^\?)/,'').split("&").map(function(n){return n = n.split("="),this[n[0]] = n[1],this}.bind({}))[0];
            }
        });
    }

    // Loads Google fonts for previewing.
    function omLoadFonts(){
        WebFont.load({
            google: {
                families: [optin_monster_preview.fonts]
            }
        });
    }

    // Sets editor config flags.
    function omSetEditorFlags(){
        CKEDITOR.config.customConfig = optin_monster_preview.config;
    }

    // Function for initializing the live preview editor.
    function omInitLivePreview(event, optin){
        var selectable = $('#om-' + optin.hash).find('[data-om-action="selectable"]'),
            editable   = $('#om-' + optin.hash).find('[data-om-action="editable"]');

        // Loop through selectable and give them selectable capabilities.
        $.each(selectable, function(i, el){
            $(this).on(omInitSelectable(el));
        });

        // Loop through selectable and give them selectable capabilities.
        $.each(editable, function(i, el){
            var id 	    	= $(this).attr('id'),
            	$this       = $(this);
            $this.addClass('om-editable').attr('contenteditable', true);
            if ( CKEDITOR.instances[id] ) {
                CKEDITOR.instances[id].destroy(true);
            }

            CKEDITOR.inline(id);
            om_editors[i] = id;
        });

        // Disable any link navigation.
        $(document).on('click', 'a', function(e){
            e.preventDefault();
        });

        // Fire an event so that the get_js method for themes can attach after CKEDITOR instances have been initialized.
        $(document).trigger('OptinMonsterPreviewInit', om_editors);
    }

    // Initialize selectable fields with their respective event delegations.
    function omInitSelectable(self){
        return {
            mouseenter: function(){
                $(self).addClass('om-selectable');
            },
            mouseleave: function(){
                $(self).removeClass('om-selectable');
            },
            click: function(e){
                e.preventDefault();

                if ( e.target !== this ) {
                    return;
                }

                var $this  = $(this),
                    target = $(this).data('om-target'),
                    select = getSelection().toString(),
                    data   = {
                        target: encodeURIComponent(target),
                        event:  'omFieldTrigger'
                    };

                if ( ! target || select ) {
                    return;
                }

                // Send a message to the parent to trigger an action.
                omSendMessage(data);
            },
            focus: function(e){
                e.preventDefault();
            }
        }
    }

    // Saves inline data from the editor instances.
    function omSaveEditor(request){
        var data  = {},
            fonts = {};
        $.each(om_editors, function(i, editor){
            var editor_id = '#' + CKEDITOR.instances[editor].name,
                field     = $(editor_id).data('om-field');

            // Grab the HTML and parse it into loopable elements to find font-families.
            $($.parseHTML(CKEDITOR.instances[editor].getData())).find('*').addBack().each(function(i){
            	// If this is just a text element, pass over it.
            	if ( 3 === $(this).get(0).nodeType ) {
	            	return;
            	}

                // Grab the font family. If it does not exist on the element, cotinue to the next element.
                var font = $(this).css('font-family');
                    font = font.replace(/["']/g, '');
                if ( 0 === font.length ) {
                    return;
                }
                fonts[font] = font;
            });

            // Store the data.
            data[field] = encodeURIComponent(CKEDITOR.instances[editor].getData());
        });

        // Prepare the ajax request.
        var ajax = {
            action: 'optin_monster_save_optin_content',
            id:     optin_monster_preview.id,
            data:   data,
            fonts:  fonts
        };

        $.post(optin_monster_preview.ajax, ajax, function(res){
            omSendMessage(request);
        }, 'json');
    }

    // Initializes the media modal in the parent to add images.
    function omMediaModal(){
        $(document).on('click', '.om-media-modal, .om-button-modify', function(e){
            e.preventDefault();

            // Trigger the event to force the modal to open.
            var data = {
                theme: encodeURIComponent($(this).data('om-theme')),
                type:  encodeURIComponent($(this).data('om-type')),
                event: 'omMediaTrigger'
            };
            omSendMessage(data);
        });

        $(document).on('click', '.om-button-remove', function(e){
            e.preventDefault();
            if ( ! confirm( optin_monster_preview.remove ) ) {
                return;
            }

            var data = {
                action: 'optin_monster_remove_image',
                theme:  $(this).data('om-theme'),
                type:   $(this).data('om-type'),
                id:     optin_monster_preview.id
            };

            $('.om-image-container').append('<div class="om-image-loading"></div>');
            $.post(optin_monster_preview.ajax, data, function(res){
                $('.om-image-container').html(res);
                omSendMessage({ event: 'omMediaSuccessTrigger' });
                $('.om-media-modal').on({
                    mouseenter: function(){
                        $(this).addClass('om-selectable');
                    },
                    mouseleave: function(){
                        $(this).removeClass('om-selectable');
                    }
                });
            }, 'json');
        });

        $(document).on('OptinMonsterMediaModal', function(e, attachment){
            var info = $.parseJSON(decodeURIComponent(attachment)),
                data = {
                    action: 'optin_monster_save_image',
                    attach: info.id,
                    theme:  info.om_theme,
                    type:   info.om_type,
                    id:     optin_monster_preview.id
            };

            $('.om-image-container').append('<div class="om-image-loading"></div>');
            $.post(optin_monster_preview.ajax, data, function(res){
                $('.om-image-container').html(res);
                omSendMessage({ event: 'omMediaSuccessTrigger' });
            }, 'json');
        });
    }

    // If an anchor exists in the document URL, jump to it.
    function omAnchorJump(){
        var anchor = decodeURIComponent( document.location.hash.replace( /^#/, '' ) );
        if ( anchor.indexOf(optin_monster_preview.optin) > -1 ) {
	        $(document).scrollTop( $('#om-' + optin_monster_preview.optin).offset().top );
        }
    }

    // Adjust the name field for showing/hiding.
    $(document).on('OptinMonsterNameChange', function(e, data){
        if ( 'true' == data.checked ) {
            if ( typeof om_ie_browser != 'undefined' ) {
                $(decodeURIComponent(data.input)).prependTo($(decodeURIComponent(data.target))).placeholder().on(omInitSelectable(decodeURIComponent(data.name)));
            } else {
                $(decodeURIComponent(data.input)).prependTo($(decodeURIComponent(data.target))).on(omInitSelectable(decodeURIComponent(data.name)));
            }

            $(decodeURIComponent(data.target)).removeClass('om-has-email').addClass('om-has-name-email');
        } else {
            $(decodeURIComponent(data.name)).remove();
            $(decodeURIComponent(data.target)).removeClass('om-has-name-email').addClass('om-has-email');
        }
    });

    // Loads the placeholder shim for IE browsers.
    function omLoadPlaceholder(){
        !function(e,a,t){function l(e){var a={},l=/^jQuery\d+$/;return t.each(e.attributes,function(e,t){t.specified&&!l.test(t.name)&&(a[t.name]=t.value)}),a}function r(e,a){var l=this,r=t(l);if(l.value==r.attr("placeholder")&&r.hasClass("placeholder"))if(r.data("placeholder-password")){if(r=r.hide().next().show().attr("id",r.removeAttr("id").data("placeholder-id")),e===!0)return r[0].value=a;r.focus()}else l.value="",r.removeClass("placeholder"),l==d()&&l.select()}function o(){var e,a=this,o=t(a),d=this.id;if(""==a.value){if("password"==a.type){if(!o.data("placeholder-textinput")){try{e=o.clone().attr({type:"text"})}catch(c){e=t("<input>").attr(t.extend(l(this),{type:"text"}))}e.removeAttr("name").data({"placeholder-password":o,"placeholder-id":d}).bind("focus.placeholder",r),o.data({"placeholder-textinput":e,"placeholder-id":d}).before(e)}o=o.removeAttr("id").hide().prev().attr("id",d).show()}o.addClass("placeholder"),o[0].value=o.attr("placeholder")}else o.removeClass("placeholder")}function d(){try{return a.activeElement}catch(e){}}var c,n,i="[object OperaMini]"==Object.prototype.toString.call(e.operamini),p="placeholder"in a.createElement("input")&&!i,u="placeholder"in a.createElement("textarea")&&!i,h=t.fn,s=t.valHooks,v=t.propHooks;p&&u?(n=h.placeholder=function(){return this},n.input=n.textarea=!0):(n=h.placeholder=function(){var e=this;return e.filter((p?"textarea":":input")+"[placeholder]").not(".placeholder").bind({"focus.placeholder":r,"blur.placeholder":o}).data("placeholder-enabled",!0).trigger("blur.placeholder"),e},n.input=p,n.textarea=u,c={get:function(e){var a=t(e),l=a.data("placeholder-password");return l?l[0].value:a.data("placeholder-enabled")&&a.hasClass("placeholder")?"":e.value},set:function(e,a){var l=t(e),c=l.data("placeholder-password");return c?c[0].value=a:l.data("placeholder-enabled")?(""==a?(e.value=a,e!=d()&&o.call(e)):l.hasClass("placeholder")?r.call(e,!0,a)||(e.value=a):e.value=a,l):e.value=a}},p||(s.input=c,v.value=c),u||(s.textarea=c,v.value=c),t(function(){t(a).delegate("form","submit.placeholder",function(){var e=t(".placeholder",this).each(r);setTimeout(function(){e.each(o)},10)})}),t(e).bind("beforeunload.placeholder",function(){t(".placeholder").each(function(){this.value=""})}))}(this,document,jQuery);
    }

    // Utility function to send a message using postMessage.
    function omSendMessage(data){
        $.postMessage(data, om_parent_url, parent);
    }

    // Receive messages from the parent.
    $.receiveMessage(function(e){
        var data = $.getQueryParameters(decodeURIComponent(e.data));
        switch ( data.event ) {
            case 'omSaveTrigger' :
                omSaveEditor(data);
                break;
            case 'omApplyColor' :
                omApplyColor(data);
                break;
            case 'omFieldTrigger' :
                omFieldTrigger(data);
                break;
            case 'omApplyStyles' :
                omApplyStyles(data);
                break;
            case 'omApplyHtml' :
                omApplyHtml(data);
                break;
            case 'omMediaTrigger' :
                $(document).trigger('OptinMonsterMediaModal', data.attachment);
                break;
            case 'omNameTrigger' :
                $(document).trigger('OptinMonsterNameChange', data);
                break;
            case 'omLinkTrigger' :
                omApplyLink(data);
                break;
        }
    });

    // Function to trigger applying color selections.
    function omApplyColor(data){
        $(decodeURIComponent(data.target)).css(decodeURIComponent(data.prop), decodeURIComponent(data.color));
    }

    // Function to trigger applying color selections.
    function omFieldTrigger(data){
        if ( 'attr' == decodeURIComponent(data.method) || 'css' == decodeURIComponent(data.method) ) {
            $(decodeURIComponent(data.target))[decodeURIComponent(data.method)](decodeURIComponent(data.attr), decodeURIComponent(data.val));
        } else {
            $(decodeURIComponent(data.target))[decodeURIComponent(data.method)](decodeURIComponent(data.val));
        }
    }

    // Function to trigger applying styles.
    function omApplyStyles(data){
        if ( $(decodeURIComponent(data.target)).find('.om-custom-styles').length > 0 ) {
            $('.om-custom-styles').html(decodeURIComponent(data.val));
        } else {
            var styles = $('<style />').addClass('om-custom-styles').attr('type', 'text/css').html(decodeURIComponent(data.val));
            $(decodeURIComponent(data.target)).find('.om-theme-' + decodeURIComponent(data.theme) + '-styles').after(styles);
        }
    }

    // Apply HTML for the Canvas addon
    function omApplyHtml(data){
        $('.optin_custom_html_applied').html(decodeURIComponent(data.val));
    }

    // Apply the powered by link.
    function omApplyLink(data){
        if ( 'true' == decodeURIComponent(data.checked) ) {
            $(decodeURIComponent(data.target)).append(decodeURIComponent(data.html));
        } else {
            $('.optin-monster-powered-by').remove();
        }
    }
});