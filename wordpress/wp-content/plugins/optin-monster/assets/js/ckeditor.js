/* ==========================================================
 * ckeditor.js
 * http://optinmonster.com/
 * ==========================================================
 * Copyright 2014 Thomas Griffin.
 *
 * Use of this file is bound by the Terms of Service agreed
 * upon when purchasing OptinMonster.
 * http://optinmonster.com/terms/
 * ========================================================== */
CKEDITOR.editorConfig = function( config ) {
    // Modify the toolbar groups.
    config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'list' ] },
        { name: 'paragraph',   groups: [ 'list', 'align' ] },
        { name: 'colors' },
        { name: 'styles' },
        { name: 'links' }
    ];

    // Override core styles for bold, italiv and underlined to allow styling defaults.
    config.coreStyles_bold      = { element : 'span', attributes : { 'style' : 'font-weight:bold' } };
    config.coreStyles_italic    = { element : 'span', attributes : { 'style' : 'font-style:italic' } };
    config.coreStyles_underline = { element : 'span', attributes : { 'style' : 'text-decoration:underline' } };

    // Handle other config properties.
    config.removeButtons         = 'Strike,Subscript,Superscript,Styles';
    config.format_tags           = 'p;h1;h2;h3;pre';
    config.removeDialogTabs      = 'image:advanced;link:advanced';
    config.baseFloatZIndex       = 6351541435;
    config.enterMode             = CKEDITOR.ENTER_BR;
    config.shiftEnterMode        = CKEDITOR.ENTER_BR;
    config.allowedContent        = true;
    config.extraAllowedContent   = 'div(*)';
    config.extraPlugins          = 'pastetext';
    config.forcePasteAsPlainText = true;
    config.basePath              = optin_monster_preview.ckpath;
    config.language				 = 'en';

    // Add custom fonts.
    var fonts          = optin_monster_preview.ckfonts;
    config.font_names  = fonts.split(';').sort().join(';');
    config.contentsCss = optin_monster_preview.google + optin_monster_preview.fonts;
};