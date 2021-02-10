<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow_plugins.protected_photos.classes
 * @since 1.7.6
 */
class PROTECTEDPHOTOS_CLASS_EventHandler
{
    private static $instance;

    public static function getInstance()
    {
        if ( null === self::$instance )
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private $service;

    private function __construct()
    {
        $this->service = PROTECTEDPHOTOS_BOL_Service::getInstance();
    }

    public function init()
    {
        $em = OW::getEventManager();

        $em->bind('photo.form_complete', array($this, 'onPhotoFormComplete'));
        $em->bind('photo.onReadyResponse', array($this, 'onReadyResponse'));
        $em->bind('class.get_instance.PHOTO_CMP_AlbumInfo', array($this, 'getInstanceAlbumInfo'));
        $em->bind('class.get_instance.PHOTO_CMP_AlbumNameList', array($this, 'getInstanceAlbumNames'));
        $em->bind('photo.view_album', array($this, 'onViewAlbum'));
        $em->bind('photo.upload_data', array($this, 'onGetPhotoUploadData'));
        $em->bind('photo.collect_photo_context_actions', array($this, 'onCollectPhotoContext'));
        $em->bind('protectedphotos.init_enter_password', array($this, 'initEnterPassword'));
        $em->bind('protectedphotos.init_headers', array($this, 'initHeaders'));
        $em->bind('photo.before_album_delete', array($this, 'onBeforeDeleteAlbum'));
        $em->bind('photo.onDownloadPhoto', array($this, 'onDownloadPhoto'));
        $em->bind('photo.onRate', array($this, 'onRatePhoto'));
        $em->bind('photo.onIndexWidgetListReady', array($this, 'onIndexWidgetReady'));
        $em->bind('feed.on_item_render', array($this, 'feedOnItemRender'), PHP_INT_MAX);
        $em->bind('photo.albumsWidgetReady', array($this, 'onAlbumsWidgetReady'));

        $em->bind('photo.onRenderAjaxUpload', array($this, 'onRenderUploadAlbumForm'));
        $em->bind('photo.onRenderEditAlbum', array($this, 'onRenderEditAlbumForm'));
        $em->bind('photo.onRenderCreateFakeAlbum', array($this, 'onRenderCreateFakeAlbumForm'));
        $em->bind('photo.user_album_view', array($this, 'onUserAlbumView'));
        $em->bind('photo.after_add_feed', array($this, 'photoAfterAddFeed'));
        $em->bind('photo.onAfterPhotoMove', array($this, 'afterPhotoMode'));

        $em->bind(OW_EventManager::ON_FINALIZE, function()
        {
            $handler = OW::getRequestHandler()->getHandlerAttributes();

            if ( array_key_exists('ADMIN_CTRL_Abstract', class_parents($handler[OW_RequestHandler::ATTRS_KEY_CTRL])) )
            {
                return;
            }

            $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
            OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'style.css');
            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'script.js');
            $cmp = new PROTECTEDPHOTOS_CMP_PasswordWrapper();
            OW::getDocument()->appendBody($cmp->render());
            OW::getLanguage()->addKeyForJs('protectedphotos', 'enter_password');
        });

        PROTECTEDPHOTOS_CLASS_QueryHandler::getInstance()->init();

        $this->genericInit();
    }

    public function genericInit()
    {
        $em = OW::getEventManager();

        $em->bind('friends.request-accepted', array($this, 'onFriendRequestAccept'));
        $em->bind('friends.cancelled', array($this, 'onFriendShipCancel'));
        $em->bind('photo.getPhotoUrl', array($this, 'onPhotoGetUrl'));
    }

    public function onRenderUploadAlbumForm( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $this->addComplex($event, 'ajax-upload');
    }

    public function onRenderEditAlbumForm( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) || empty($params['albumId']) )
        {
            return;
        }

        $this->addComplex($event, 'albumEditForm');
    }

    public function onRenderCreateFakeAlbumForm( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $this->addComplex($event, 'create_fake_album');
    }

    private function addComplex( BASE_CLASS_EventCollector $event, $formName )
    {
        $params = $event->getParams();
        $userId = $params['userId'];
        $albumId = !empty($params['albumId']) ? $params['albumId'] : null;
        $complex = new PROTECTEDPHOTOS_CMP_PrivacyComplex($formName, $userId, $albumId);

        $privacy = array(
            PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC => array(
                'default' => true,
                'name' => PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC,
                'label' => OW::getLanguage()->text('protectedphotos', 'privacy_public_label')
            )
        );

        if ( OW::getPluginManager()->isPluginActive('friends') )
        {
            $privacy[PROTECTEDPHOTOS_BOL_Service::PRIVACY_FRIENDS_ONLY] = array(
                'name' => PROTECTEDPHOTOS_BOL_Service::PRIVACY_FRIENDS_ONLY,
                'label' => OW::getLanguage()->text('protectedphotos', 'privacy_all_friends_label')
            );
            $privacy[PROTECTEDPHOTOS_BOL_Service::PRIVACY_INDIVIDUAL_FRIENDS] = array(
                'name' => PROTECTEDPHOTOS_BOL_Service::PRIVACY_INDIVIDUAL_FRIENDS,
                'label' => OW::getLanguage()->text('protectedphotos', 'privacy_individual_friends_label')
            );
        }

        $privacy[PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD] = array(
            'name' => PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD,
            'label' => OW::getLanguage()->text('protectedphotos', 'privacy_password_label')
        );

        $complex->setPrivacyOptions($privacy);
        $complex->setSearchPlaceHolder(OW::getLanguage()->text('protectedphotos', 'search_user_placeholder'));
        $complex->setSearchApiUrl(OW::getRouter()->urlForRoute('protectedphotos.rsp.friend_list'));
        $event->add($complex->render());
    }

    public function onFriendRequestAccept( OW_Event $event )
    {
        $params = $event->getParams();
        $senderId = $params['senderId'];
        $recipientId = $params['recipientId'];

        $this->grantAccessForNewFriend($senderId, $recipientId);
        $this->grantAccessForNewFriend($recipientId, $senderId);
    }

    private function grantAccessForNewFriend( $userId, $friendId )
    {
        $passwords = $this->service->findUserPasswordByPrivacy($userId, array(
            PROTECTEDPHOTOS_BOL_Service::PRIVACY_FRIENDS_ONLY
        ));

        foreach ( $passwords as $password )
        {
            $this->service->grantAccess($password->albumId, $friendId);
        }
    }

    public function onFriendShipCancel( OW_Event $event )
    {
        $params = $event->getParams();
        $senderId = $params['senderId'];
        $recipientId = $params['recipientId'];

        $this->deleteAccessFromFriend($senderId, $recipientId);
        $this->deleteAccessFromFriend($recipientId, $senderId);
    }

    private function deleteAccessFromFriend( $userId, $friendId )
    {
        $passwords = $this->service->findUserPasswordByPrivacy($userId, array(
            PROTECTEDPHOTOS_BOL_Service::PRIVACY_FRIENDS_ONLY,
            PROTECTEDPHOTOS_BOL_Service::PRIVACY_INDIVIDUAL_FRIENDS
        ));

        foreach ( $passwords as $password )
        {
            $this->service->deleteAlbumAccessFromUser($password->albumId, $friendId);
        }
    }

    public function onUserAlbumView( OW_Event $event )
    {
        $params = $event->getParams();
        $album = $params['album'];
        $password = $this->service->findPasswordForAlbumByAlbumId($album->id);

        if ( $password === null || $password->privacy === PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD ) return;

        $useId = OW::getUser()->getId();
        $access = $this->service->getAccessForUser($useId, array($album->id));

        if ( count($access) === 0 && !OW::getUser()->isAdmin() )
        {
            throw new AuthorizationException();
        }
    }

    public function onPhotoFormComplete( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['form']) || !$params['form'] instanceof Form )
        {
            return;
        }

        $form = $params['form'];
        $data = $event->getData();

        switch ( $form->getName() )
        {
            case 'ajax-upload':
            case 'albumEditForm':
                $this->onAlbumEditFormProcessor($form, $data['album'], $_POST);
                break;
        }
    }

    /**
     * @param $form Form
     * @param $album PHOTO_BOL_PhotoAlbum
     * @param $data[]
     */
    private function onAlbumEditFormProcessor( Form $form, $album, $data )
    {
        $complex = new PROTECTEDPHOTOS_CMP_PrivacyComplex($form->getName(), $album->userId, $album->id);
        $complex->setValue($data);
        $values = $complex->getValue();

        $this->service->clearFeedPrivacyForAlbum($album->id, $values[PROTECTEDPHOTOS_CMP_PrivacyComplex::ELEMENT_PRIVACY]);

        if ( $values[PROTECTEDPHOTOS_CMP_PrivacyComplex::ELEMENT_PRIVACY] === PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC )
        {
            $this->service->deleteAlbumPassword($album->id);

            return;
        }

        switch ( $values[PROTECTEDPHOTOS_CMP_PrivacyComplex::ELEMENT_PRIVACY] )
        {
            case PROTECTEDPHOTOS_BOL_Service::PRIVACY_FRIENDS_ONLY:
                $this->service->protectAlbumButExcludeAllFriends($album->id);
                $this->service->grantAccessForAllFriends($album->userId, $album->id);
                break;
            case PROTECTEDPHOTOS_BOL_Service::PRIVACY_INDIVIDUAL_FRIENDS:
                $this->service->protectAlbumButExcludeIndividualFriends($album->id, $values[PROTECTEDPHOTOS_CMP_PrivacyComplex::ELEMENT_SELECTED_LIST]);
                $this->service->grantAccessForIndividualFriends($album->id, $values[PROTECTEDPHOTOS_CMP_PrivacyComplex::ELEMENT_SELECTED_LIST]);
                $this->service->changeAlbumPrivacyForIndividualFriends($album->id, $values[PROTECTEDPHOTOS_CMP_PrivacyComplex::ELEMENT_SELECTED_LIST]);
                break;
            case PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD:
                $this->service->protectAlbumByPassword($album->id, $values[PROTECTEDPHOTOS_CMP_PrivacyComplex::ELEMENT_PASSWORD]);
                break;
        }

        $this->service->grantAccess($album->id, $album->userId);
    }

    public function photoAfterAddFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['album']) ) return;

        $this->service->changeFeedPrivacyByAlbum($params['album']->id);
    }

    public function afterPhotoMode( OW_Event $event )
    {
        $params = $event->getParams();

        $this->service->changeFeedPrivacyByAlbum($params['fromAlbum']);
        $this->service->changeFeedPrivacyByAlbum($params['toAlbum']);
    }

    public function onReadyResponse( OW_Event $event )
    {
        $params = $event->getParams();

        switch ( $params['ajaxFunc'] )
        {
            case 'getPhotoList':
            case 'getPhotoInfo':
                $this->processPhotoList($event);
                break;
            case 'getFloatbox':
                $this->processFloatBox($event);
                break;
            case 'getAlbumList':
                $this->processAlbumList($event);
                break;
            case 'reloadAlbumCover':
                $this->processReloadAlbumCover($event);
                break;
        }
    }

    private function processPhotoList( OW_Event $event )
    {
        $data = $event->getData();
        $userId = OW::getUser()->getId();
        $albumIds = array_keys($data['data']['albumNameList']);

        $accessAlbumIds = $this->service->getAccessForUser($userId, $albumIds);
        $isAdmin = OW::getUser()->isAuthorized('photo');

        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $photoUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';

        $photoList = $data['data']['photoList'];
        $passwordPhotos = array_map(function( $password)
        {
            return $password->albumId;
        }, $this->service->findPasswordForAlbums($albumIds));
        $accessPhotoIds = array();

        foreach (  $photoList as $index => $photo )
        {
            $photoList[$index]['isProtected'] = in_array($photo['albumId'], $passwordPhotos);

            if ( $isAdmin || in_array((int) $photo['albumId'], $accessAlbumIds, true) )
            {
                $accessPhotoIds[] = $photo['id'];
                $accessAlbumIds[] = $photo['albumId'];

                continue;
            }

            $photoList[$index]['hash'] = uniqid();
            $photoList[$index]['url'] = $photoUrl;
        }


        $data['data']['photoList'] = $photoList;

        OW::getEventManager()->trigger(
            new OW_Event('protectedphotos.init_headers', array(
                'albums' => $accessAlbumIds,
                'photos' => $accessPhotoIds
            ))
        );

        $event->setData($data);
    }

    private function processFloatBox( OW_Event $event )
    {
        $data = $event->getData();
        $userId = OW::getUser()->getId();

        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $photoUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';

        $photos = $data['photos'];
        $albumIds = array_map(function( $photo )
        {
            return $photo['album']->id;
        }, $photos);
        $accessAlbumIds = $this->service->getAccessForUser($userId, $albumIds);
        $protectedAlbumIds = $passwords = array();

        foreach ( $this->service->findPasswordForAlbums($albumIds) as $password )
        {
            $protectedAlbumIds[] = $password->albumId;
            $passwords[$password->albumId] = $password;
        }

        $isAdmin = OW::getUser()->isAuthorized('photo');

        $accessPhotos = array();
        $comment = new PROTECTEDPHOTOS_CMP_PhotoComment();

        foreach (  $photos as $photoId => $photo )
        {
            $photos[$photoId]['photo']->isProtected = in_array($photo['album']->id, $protectedAlbumIds);

            if ( $isAdmin || in_array($photo['album']->id, $accessAlbumIds) )
            {
                $accessPhotos[] = $photoId;

                continue;
            }

            $photos[$photoId]['photo']->hash = uniqid();
            $photos[$photoId]['photo']->url = $photoUrl;
            $photos[$photoId]['share'] = '';

            if ( isset($passwords[$photo['album']->id]) &&
                $passwords[$photo['album']->id]->privacy === PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD )
            {
                $comment->setPhotoId($photoId)->setAlbumId($photo['album']->id);
                $photos[$photoId]['comment'] = $comment->render();
            }
            else
            {
                unset($photos[$photoId]['comment']);
            }
        }

        $data['photos']= $photos;

        OW::getEventManager()->trigger(
            new OW_Event('protectedphotos.init_headers', array(
                'photos' => $accessPhotos,
                'albums' => $accessAlbumIds
            ))
        );

        $event->setData($data);
    }

    public function processAlbumList( OW_Event $event )
    {
        $data = $event->getData();
        $userId = OW::getUser()->getId();

        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $photoUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';

        $albums = $data['data']['photoList'];
        $albumIds = array_map(function( $album )
        {
            return $album['id'];
        }, $albums);
        $accessAlbumIds = $this->service->getAccessForUser($userId, $albumIds);
        $passwordAlbums = array_map(function( $password)
        {
            return $password->albumId;
        }, $this->service->findPasswordForAlbums($albumIds));
        $isAdmin = OW::getUser()->isAuthorized('photo');
        $accessAlbums = array();

        foreach (  $albums as $index => $album )
        {
            $albums[$index]['isProtected'] = in_array($album['id'], $passwordAlbums);

            if ( $isAdmin || in_array($album['id'], $accessAlbumIds) )
            {
                $accessAlbums[] = $album['id'];

                continue;
            }

            $albums[$index]['url'] = $photoUrl;
        }

        $data['data']['photoList'] = $albums;

        OW::getEventManager()->trigger(
            new OW_Event('protectedphotos.init_headers', array(
                'albums' => $accessAlbums
            ))
        );

        $event->setData($data);
    }

    public function onGetPhotoUploadData( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['albumId']) )
        {
            return;
        }

        $albumId = $params['albumId'];
        $password = $this->service->findPasswordForAlbumByAlbumId($albumId);

        if ( $password === null )
        {
            return;
        }

        $event->setData(array(
            'enable_password' => true,
            'password' => $password->password
        ));
    }

    public function getInstanceAlbumInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $arguments = $params['arguments'];
        $album = array_shift($arguments);
        $album = $album['album'];
        $access = $this->service->getAccessForUser(OW::getUser()->getId(), array($album->id));

        if ( OW::getUser()->isAuthorized('photo') || in_array($album->id, $access) ) return;

        $event->setData(new PROTECTEDPHOTOS_CMP_AlbumInfo($album));

        return $event->getData();
    }

    public function getInstanceAlbumNames( OW_Event $event )
    {
        $rClass = new ReflectionClass('PROTECTEDPHOTOS_CMP_AlbumNameList');
        $params = $event->getParams();

        $event->setData($rClass->newInstanceArgs($params['arguments']));

        return $event->getData();
    }

    public function onViewAlbum( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $album = $params['album'];

        if ( $this->service->isAlbumProtected($album->id) )
        {
            $event->add('<div class="ow_photo_album_description">
                <span class="ow_pass_protected_icon"></span> ' .
                OW::getLanguage()->text('protectedphotos', 'password_protected') .
            '</div>');
        }
    }

    public function onCollectPhotoContext( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $photo = $params['photoDto'];

        if ( !$this->service->isAlbumProtected($photo->albumId) ||
            OW::getUser()->isAuthorized('photo') ||
            count($this->service->getAccessForUser(OW::getUser()->getId(), array($photo->albumId))) !== 0 )
        {
            return;
        }

        $id = uniqid('pphotos');

        $event->add(array(
            'key' => 'enter_password',
            'label' => OW::getLanguage()->text('protectedphotos', 'enter_password'),
            'id' => $id,
            'order' => PHP_INT_MAX
        ));

        OW::getEventManager()->trigger(
            new OW_Event('protectedphotos.init_enter_password', array(
                'id' => '#' . $id,
                'albumId' => $photo->albumId,
                'context' => implode('|', array(
                    'photo_view', OW::getRouter()->urlForRoute('view_photo', array(
                        'id' => $photo->id
                    ))
                ))
            ))
        );
    }

    public function initEnterPassword( OW_Event $event )
    {
        $params = $event->getParams();

        OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString(';
            $(document).on("click", {$id}, function()
            {
                window.passPhotoFB = OW.ajaxFloatBox("PROTECTEDPHOTOS_CMP_EnterPassword", [{$albumId}, {$context}], {
                    title: {$title}
                });
            });', array(
                'id' => $params['id'],
                'albumId' => $params['albumId'],
                'context' => $params['context'],
                'title' => OW::getLanguage()->text('protectedphotos', 'password_protection')
            ))
        );
    }

    public function initHeaders( OW_Event $event )
    {
        $params = $event->getParams();
        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');

        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'style.css');
        OW::getDocument()->addScriptDeclarationBeforeIncludes(UTIL_JsGenerator::composeJsString(';
            if ( !window.hasOwnProperty("PASS_PROTECT") )
            {
                window.PASS_PROTECT = {albums: [], photos: []};
            }

            var arrayUnique = function( array )
            {
                return array.filter(function( item, i, arr )
                {
                    return arr.indexOf(item) === i;
                });
            };

            window.PASS_PROTECT.albums = arrayUnique(window.PASS_PROTECT.albums.concat({$albums}));
            window.PASS_PROTECT.photos = arrayUnique(window.PASS_PROTECT.photos.concat({$photos}));',
            array(
                'albums' => !empty($params['albums']) ? array_map('intval', $params['albums']) : array(),
                'photos' => !empty($params['photos']) ? array_map('intval', $params['photos']) : array()
            )
        ));
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'script.js');
        OW::getLanguage()->addKeyForJs('protectedphotos', 'enter_password');
        OW::getLanguage()->addKeyForJs('protectedphotos', 'rate_error_msg');
    }

    public function onBeforeDeleteAlbum( OW_Event $event )
    {
        $params = $event->getParams();
        $this->service->deleteAlbumPassword($params['id']);
    }

    public function onDownloadPhoto( OW_Event $event )
    {
        if ( OW::getUser()->isAuthorized('photo') )
        {
            return;
        }

        $params = $event->getParams();

        $photoEvent = OW::getEventManager()->trigger(
            new OW_Event('photo.find', array('photoId' => $params['id']))
        );
        $data = $photoEvent->getData();

        if ( !isset($data['photo']) )
        {
            return;
        }

        $userId = OW::getUser()->getId();
        $photo = $data['photo'];
        $access = $this->service->getAccessForUser($userId, array($photo['albumId']));

        if ( !in_array($photo['albumId'], $access) )
        {
            $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
            $event->setData($plugin->getStaticUrl() . 'images/ic_pass_protected.svg');
        }
    }

    public function onRatePhoto( OW_Event $event )
    {
        if ( OW::getUser()->isAuthorized('photo') )
        {
            return;
        }

        $params = $event->getParams();

        $photoEvent = OW::getEventManager()->trigger(
            new OW_Event('photo.find', array('photoId' => $params['photoId']))
        );
        $data = $photoEvent->getData();

        if ( !isset($data['photo']) )
        {
            return;
        }

        $userId = $params['userId'];
        $photo = $data['photo'];
        $access = $this->service->getAccessForUser($userId, array($photo['albumId']));

        if ( !in_array($photo['albumId'], $access) )
        {
            $event->setData(array(
                'result' => false,
                'error' => OW::getLanguage()->text('protectedphotos', 'rate_error_msg')
            ));
        }
    }

    public function onIndexWidgetReady( OW_Event $event )
    {
        $params = $event->getParams();
        $albumIds = $accessPhotoIds = array();

        foreach ( $params as $key => $list )
        {
            foreach ( $list as $photo )
            {
                $albumIds[] = $photo['albumId'];
            }
        }

        $userId = OW::getUser()->getId();
        $accessAlbumIds = $this->service->getAccessForUser($userId, $albumIds);
        $isAdmin = OW::getUser()->isAuthorized('photo');

        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $photoUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';

        $passwordPhotos = array_map(function( $password)
        {
            return $password->albumId;
        }, $this->service->findPasswordForAlbums($albumIds));

        foreach ( $params as $key => $photoList )
        {
            foreach (  $photoList as $index => $photo )
            {
                $photoList[$index]['isProtected'] = in_array($photo['albumId'], $passwordPhotos, true);

                if ( $isAdmin || in_array($photo['albumId'], $accessAlbumIds) )
                {
                    $accessPhotoIds[] = $photo['id'];

                    continue;
                }

                $photoList[$index]['hash'] = uniqid();
                $photoList[$index]['url'] = $photoUrl;
                $photoList[$index]['class'] = 'ow_protected_photo_widget_icon';
            }

            $params[$key] = $photoList;
        }

        OW::getEventManager()->trigger(
            new OW_Event('protectedphotos.init_headers', array(
                'albums' => $accessAlbumIds,
                'photos' => $accessPhotoIds
            ))
        );

        $event->setData($params);
    }

    public function feedOnItemRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !in_array($params['action']['entityType'], array('photo_comments', 'multiple_photo_upload'), true) )
        {
            return;
        }

        $autoId = $params['autoId'];
        $data = $event->getData();

        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $photoUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';
        $content = $data['content'];

        switch ( $params['action']['entityType'] )
        {
            case 'photo_comments':
                $photoId = !empty($data['photoIdList']) ? $data['photoIdList'][0] : $params['action']['entityId'];
                $photoEvent = OW::getEventManager()->trigger(
                    new OW_Event('photo.find', array('photoId' => $photoId))
                );
                $photoEventData = $photoEvent->getData();

                if ( !isset($photoEventData['photo']) )
                {
                    return;
                }

                $photo = $photoEventData['photo'];
                $access = $this->service->getAccessForUser(OW::getUser()->getId(), array($photo['albumId']));
                $isAdmin = OW::getUser()->isAuthorized('photo');

                if ( !$isAdmin && !in_array($photo['albumId'], $access) )
                {
                    $content['templateFile'] = $plugin->getCmpViewDir() . 'image_format.html';
                    $content['vars']['image'] = $photoUrl;
                    $content['vars']['class'] = 'ow_protected_photo_widget_icon';

                    OW::getDocument()->addOnloadScript(
                        UTIL_JsGenerator::composeJsString('$(".ow_newsfeed_photo_grid_item a", "#" + {$autoId}).off().on("click", function( event )
                        {
                            event.preventDefault();
                            var dimension = {$dimension}, _data = {};

                            if ( dimension.main )
                            {
                                _data.main = dimension.main;
                            }
                            else
                            {
                                _data.main = [400, 400];
                            }

                            _data.mainUrl = {$url};
                            window.photoView.setId({$photoId}, "latest", null, _data, {$photo} );
                        });',
                            array(
                                'autoId' => $autoId,
                                'dimension' => !empty($photo['dimension']) ? $photo['dimension'] : array(),
                                'photoId' => $photoId,
                                'url' => $photoUrl,
                                'photo' => array(
                                    'id' => $photoId,
                                    'albumId' => $photo['albumId']
                                )
                            )
                        )
                    );
                }
                break;
            case 'multiple_photo_upload':
                $photoEvent = OW::getEventManager()->trigger(
                    new OW_Event('photo.finds', array(
                            'idList' => $data['photoIdList'])
                    )
                );
                $photoEventData = $photoEvent->getData();

                if ( !isset($photoEventData['photos']) || !is_array($photoEventData['photos']) )
                {
                    return;
                }

                $photos = $photoEventData['photos'];
                $albumIds = array_map(function( $photo )
                {
                    return $photo['albumId'];
                }, $photos);

                $access = $this->service->getAccessForUser(OW::getUser()->getId(), $albumIds);
                $isAdmin = OW::getUser()->isAuthorized('photo');

                foreach ( $content['vars']['list'] as $index => $info )
                {
                    $photoId = $info['url']['vars']['id'];

                    if ( !$isAdmin && !in_array($photos[$photoId]['albumId'], $access) )
                    {
                        $content['vars']['list'][$index]['image'] = $photoUrl;
                        $content['vars']['list'][$index]['class'] = 'ow_protected_photo_widget_icon';
                    }
                }
                break;
        }

        $data['content'] = $content;

        $event->setData($data);
    }

    public function onAlbumsWidgetReady( OW_Event $event )
    {
        $albums = $event->getData();

        if ( count($albums) === 0 )
        {
            return;
        }

        $albumIds = array_map(function( $album )
        {
            return $album['dto']->id;
        }, $albums);
        $userId = OW::getUser()->getId();
        $accessAlbumIds = $this->service->getAccessForUser($userId, $albumIds);
        $isAdmin = OW::getUser()->isAuthorized('photo');

        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $coverUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';

        foreach ( $albums as $index => $album )
        {
            if ( $isAdmin || in_array($album['dto']->id, $accessAlbumIds) )
            {
                $accessAlbumIds[] = $album['dto']->id;

                continue;
            }

            $albums[$index]['cover'] = $coverUrl;
            $albums[$index]['class'] = 'ow_protected_photo_widget_icon';
        }
        OW::getEventManager()->trigger(
            new OW_Event('protectedphotos.init_headers', array(
                'albums' => $accessAlbumIds
            ))
        );

        $event->setData($albums);
    }

    public function processReloadAlbumCover( OW_Event $event )
    {
        if ( OW::getUser()->isAuthorized('photo') )
        {
            return;
        }

        $params = $event->getParams();
        $userId = OW::getUser()->getId();
        $accessAlbumIds = $this->service->getAccessForUser($userId, array($params['albumId']));

        if ( !in_array($params['albumId'], $accessAlbumIds) )
        {
            $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
            $coverUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';

            $event->setData(array(
                'coverUrl' => $coverUrl,
                'coverUrlOrig' => $coverUrl
            ));
        }
    }

    public function onPhotoGetUrl( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['id']) || empty($params['photoInfo']['albumId']) )
        {
            return $event->getData();
        }

        $albumId = $params['photoInfo']['albumId'];

        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $photoUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';

        if ( $this->service->isAlbumProtected($albumId) )
        {
            if ( OW::getUser()->isAuthenticated() ) {

                //check administrator or isAuthorized photo
                if ( OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('photo') )
                {
                    return $event->getData();
                }
                else
                {
                    $result = $this->service->getAccessForUser(OW::getUser()->getId(), array($albumId));
                    if ( !in_array($albumId, $result) ) {
                        $event->setData($photoUrl);
                    }
                }
            }
            else {
                $event->setData($photoUrl);
            }
        }

        return $event->getData();
    }
}
