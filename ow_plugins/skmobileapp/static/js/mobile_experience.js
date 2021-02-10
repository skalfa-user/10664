/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
(function( $, logic )
{
    this.SKADATE_ME_SETTINGS = function( params )
    {
        logic.call(this, $, params);
    }.bind(this);
}.call(window, jQuery, function( $, params, ud )
{
    var root = this, form = root.document.querySelector('form.settings_form'),

        banners = $('[name="banners\[\]"]', form),
        iosBanner = banners.filter('[value="skadateios"]'),
        androidBanner = banners.filter('[value="skandroid"]'),
        pwaBanner = banners.filter('[value="pwa"]'),

        sf = $('[name="show_first\[\]"]', form),
        iosSF = sf.filter('[value="skadateios"]'),
        androidSF = sf.filter('[value="skandroid"]');
        pwaSF = sf.filter('[value="pwa"]');

    if ( !params.iosActive || !params.androidActive || !params.pwaActive )
    {
        if (!params.iosActive) {
            hideInput(iosBanner);
            hideInput(iosSF);
        }
        else if (!params.androidActive) {
            hideInput(androidBanner);
            hideInput(androidSF);
        }
        else if (!params.pwaActive) {
            hideInput(pwaBanner);
            hideInput(pwaSF);
        }

        if (!params.iosActive && !params.androidActive) {
            $(pwaSF).attr({checked: 'checked', disabled: 'disabled'});
        }
        else {
            $(!params.iosActive ? androidSF: iosSF).attr({checked: 'checked', disabled: 'disabled'});
        }
    }
    else
    {
        if ( !iosBanner.attr('checked') )
        {
            hideInput(iosSF);
        }

        if ( !androidBanner.attr('checked') )
        {
            hideInput(androidSF);
        }

        if ( !pwaBanner.attr('checked') )
        {
            hideInput(pwaSF);
        }

        banners.on('click', function()
        {
            var input;

            switch ( this.value )
            {
                case 'skadateios':
                    input = iosSF;
                    break;
                case 'skandroid':
                    input = androidSF;
                    break;
                case 'pwa':
                    input = pwaSF;
                    break;
            }

            if ( this.checked )
            {
                showInput(input);
            }
            else
            {
                hideInput(input);
            }

            sf.filter(':visible:first').attr('checked', 'checked');
        });
    }

    function hideInput( input )
    {
        input.closest('li').hide();
    }

    function showInput( input )
    {
        input.closest('li').show();
    }
}));
