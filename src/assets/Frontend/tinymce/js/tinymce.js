/**
 * This is only setup & fixes (no initialize) for TinyMCE.
 * TinyMCE is initialized in WK_Nette_Ajax.js - because TinyMCE must be re-initialize after components redrawing.
 * Author: Jiří Zapletal
 * Company: Wakers (http://www.wakers.cz)
 * Contact: zapletal@wakers.cz
 * Copyright 2017
 */

$(function ()
{
    // Prevent Bootstrap dialog from blocking focusin
    $(document).on('focusin', function(e)
    {
        if ($(e.target).closest('.mce-window').length !== 0)
        {
            e.stopImmediatePropagation();
        }
    });

    $(document).on('focusin', '.tiny_mce', function ()
    {
        var id = $(this).attr('id');
        tinymce.get(id).focus()
    });


    // funkce pro inicializaci tinymce editoru
    $.tinymce_init = function (selector, configuration)
    {
        var config = $.extend(
        {
            selector: selector,

            setup: function (editor)
            {
                editor.on('keyup change blur', function ()
                {
                    editor.save();

                    //var $form = $(editor.getElement()).parents('form');
                    var textarea = document.getElementById(editor.id);

                    Nette.validateControl(textarea);
                });
            }

        }, configuration);

        $.nette.ext('tinymce_' + selector,
        {
            load: function ()
            {
                tinymce.remove();
                tinymce.init(config);
            }
        });
    };


    $.tinymce_init('.tiny_mce',
    {
        skin: false,
        language: false,
        mode: 'textareas',
        paste_as_text: true,
        entity_encoding: 'named',
        height: 300,
        relative_urls : false,
        remove_script_host : true,
        menubar: false,
        nonbreaking_force_tab: true,
        allow_unsafe_link_target: true,

        block_formats: 'Heading 2=h2;Heading 3=h3;Paragraph=p;',
        content_style: '*{font-family:Ubuntu,sans-serif} table{width:100%} p,td,th,li{color:#666} table,td{border: 1px solid #ccc}}',

        plugins:
        [
            ['nonbreaking textcolor link lists code table paste']
        ],

        toolbar:
        [
            'formatselect | nonbreaking bold italic underline subscript superscript forecolor | link table | bullist numlist | alignjustify aligncenter alignleft alignright | code'
        ],

        //valid_elements: 'table,thead,tbody,tr,td,th,strong,em,sub,sup,br,p[style=text-align],span[style=color],ul,ol,li,a[href|target|title]',

        invalid_styles:
        {
            'table': 'width height border-collapse',
            'tr': 'width height',
            'th': 'width height',
            'td': 'width height'
        }

        //valid_elements: 'table,thead,tbody,tr,td,th,strong,p[style=text-align],h1,h2,h3,br,span[style=color|text-decoration],em,sub,sup,a[href|target|title],ul,li,ol',
    });
});