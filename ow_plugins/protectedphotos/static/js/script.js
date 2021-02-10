/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
(function( $, ow, params, undf )
{
    var root = this, photoDimCache = {};

    function getAccess( type )
    {
        type = type || 'albums';

        if ( root.PASS_PROTECT === undf )
        {
            root.PASS_PROTECT = {
                albums: [],
                photos: []
            };
        }

        return root.PASS_PROTECT[type];
    }

    function isHaveAlbumAccess( albumId )
    {
        return getAccess().indexOf(+albumId) !== -1;
    }

    function isHavePhotoAccess( photoId )
    {
        return getAccess('photos').indexOf(+photoId) !== -1;
    }

    function addAccessByPhoto( photoId )
    {
        getAccess('photos').push(+photoId);
    }

    ow.bind('photo.onRenderPhotoItem', function( slot )
    {
        if ( slot.isProtected )
        {
            this.find('.ow_photo_icon').addClass('ow_pass_protected_icon');
        }

        var id = slot.albumId || slot.id;

        if ( isHaveAlbumAccess(id) )
        {
            this.find('.pass_photo_preview').remove();

            if ( params.classicMode )
            {
                this.find('img:not(.ow_hidden)').show();
            }
        }
        else
        {
            if ( params.classicMode )
            {
                this.find('img').hide();
                this.find('.ow_photo_item').prepend($('#pass_photo_preview_classic').clone().removeAttr('id'));
            }
            else
            {
                this.find('img').hide().after($('#pass_photo_preview').clone().removeAttr('id'));
            }
        }
    });

    ow.bind('photo.canRate', function( event )
    {
        event.canRate = isHaveAlbumAccess(event.photo.albumId);
    });

    ow.bind('photo.canRateMessage', function( event )
    {
        event.msg = ow.getLanguageText('protectedphotos', 'rate_error_msg');
    });

    ow.bind('base.onFormReady.pphotos-enter-password-form', function()
    {
        this.bind('success', function( data )
        {
            if ( !data )
            {
                OW.error('Server error');

                return;
            }

            if ( data.success )
            {
                if ( data.msg )
                {
                    OW.info(data.msg);
                }

                window.passPhotoFB.close();

                var context = data.data['pphotos-context'];
                var pairs = context.split('|');

                switch ( pairs[0] )
                {
                    case 'album_view':
                    case 'photo_view':
                        root.location = pairs[1];
                        break;
                    case 'photo_list':
                        var photoId = pairs[1];
                        addAccessByPhoto(photoId);
                        ow.trigger('photo.deleteCache', [[photoId]]);
                        root.photoView.setId(photoId);
                        ow.trigger('photo.updateAlbumPhotos', [photoId]);
                        ow.trigger('photo.reloadAlbumCover', [data.data['pphotos-album-id']]);
                        break;
                }
            }
            else if ( data.msg )
            {
                OW.error(data.msg);
            }
        });
    });

    ow.bind('photo.collectMenuItems', function( event, listType )
    {
        event.buttons.push(photoContextAction.createElement('enterPassword', OW.getLanguageText('protectedphotos', 'enter_password')));
        event.actions.enterPassword = function( slot )
        {
            var id, context;

            if ( listType === 'albums' )
            {
                id = slot.id;
                context = 'album_view';
            }
            else
            {
                id = slot.albumId;
                context = 'photo_list';
            }

            window.passPhotoFB = OW.ajaxFloatBox('PROTECTEDPHOTOS_CMP_EnterPassword', [id, context + '|' + slot.id], {
                title: OW.getLanguageText('protectedphotos', 'enter_password')
            });
        };
    });

    ow.bind('photo.contextActionReady', function( contextAction, slot )
    {
        var id = slot.albumId || slot.id;

        if ( isHaveAlbumAccess(id) )
        {
            $('.enterPassword', contextAction).remove();
            $('.ow_context_action_list', contextAction).prepend($('.downloadPhoto', contextAction));
        }
        else
        {
            $('.downloadPhoto', contextAction).remove();
        }
    });

    ow.bind('photo.setStage', function( photo, data, stageCss, imgCss )
    {
        if ( isHaveAlbumAccess(photo.albumId) )
        {
            this.find('.pass_photo_view').remove();
            this.find('img.ow_photo_view').show();
            this.find('.ow_photoview_fullscreen').show();
        }
        else
        {
            this.find('.ow_photoview_stage_wrap').prepend(
                $('#pass_photo_view').clone().removeAttr('id').css('height', '100%')
            );
            this.find('img.ow_photo_view').hide();
            this.find('.ow_photoview_fullscreen').hide();
        }

        photoDimCache[photo.id] = stageCss;
    });

    ow.bind('photo.fitSize', function( photoCmp, options )
    {
        if ( isHaveAlbumAccess(photoCmp.album.id) ) return;

        if ( photoCmp.photo.isProtected && photoDimCache.hasOwnProperty(photoCmp.photo.id) )
        {
            $.extend(options, photoDimCache[photoCmp.photo.id]);
        }
    });

    ow.bind('photo.markupReady', function( photoCmp )
    {
        if ( photoCmp.photo.isProtected )
        {
            this.find('.ow_user_list_data .ow_photo_album_icon').addClass('ow_pass_protected_icon');
            this.find('.ow_photoview_bottom_menu .ow_photo_album_icon').addClass('ow_pass_protected_icon');
        }
        else
        {
            this.find('.ow_user_list_data .ow_photo_album_icon').removeClass('ow_pass_protected_icon');
            this.find('.ow_photoview_bottom_menu .ow_photo_album_icon').removeClass('ow_pass_protected_icon');
        }

        if ( isHaveAlbumAccess(photoCmp.photo.albumId) || isHavePhotoAccess(photoCmp.photo.id) )
        {
            this.find('.pass_photo_view').remove();
            this.find('img.ow_photo_view').show();
            this.find('.ow_photoview_fullscreen').show();
        }
        else
        {
            var wrap = this.find('.ow_photoview_stage_wrap');

            if ( wrap.has('.pass_photo_view').length === 0 )
            {
                wrap.prepend(
                    $('#pass_photo_view').clone().removeAttr('id').css('height', '100%')
                );
            }

            this.find('img.ow_photo_view').hide();
            this.find('.ow_photoview_fullscreen').hide();
        }
    });

    ow.bind('photo.onBeforeLoadFromCache', function( photoId )
    {
        var cmp = this.getPhotoCmp(photoId);

        if ( cmp && this.fullscreen.isFullscreen() && !isHaveAlbumAccess(cmp.album.id) )
        {
            this.fullscreen.exit();
        }
    });

    ow.bind('photo.collectChangePhotoSelectors', function( event )
    {
        event.selectors.push('.ow_pass_protected_wrap');
        event.selectors.push('.ow_pass_protected_cont');
        event.selectors.push('.ow_pass_protected_lang');
    });

    ow.bind('photo.collectShowPhoto', function( event )
    {
        event.selectors.push('.ow_pass_protected_wrap');
    });

    ow.bind('photo.reloadAlbumCover', function( albumId )
    {
        if ( !albumId ) return;

        $.ajax({
            url: browsePhotoParams.getPhotoURL,
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                "ajaxFunc": 'reloadAlbumCover',
                "albumId": albumId
            },
            success: function( data )
            {
                if ( !data || !data.coverUrl || !data.coverUrlOrig ) return;

                var container = $('#photo-album-info');

                $('.ow_pass_protected_wrap', container).remove();
                $('.ow_photo_album_cover', container).css('background-image', 'url(' + data.coverUrl + ')');
                $('img.cover_orig', container).attr('src', data.coverUrlOrig);
            }
        });
    });

}.call(window, jQuery, OW, window.browsePhotoParams));
