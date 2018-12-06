/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

$(function ()
{
    $(document).on('click', '[data-wakers-toggle]', function ()
    {
        var id = '#' + $(this).data('wakers-toggle');

        $(id).slideToggle('fast', 'swing');
    });
});