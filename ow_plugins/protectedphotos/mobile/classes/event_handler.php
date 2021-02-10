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
 * @package ow_plugins.protected_photos.mobile.classes
 * @since 1.7.6
 */
class PROTECTEDPHOTOS_MCLASS_EventHandler
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
        $eventManager = OW::getEventManager();

        $eventManager->bind('photo.getPhotoList', array($this, 'handlerQueryGetPhotoList'));
        $eventManager->bind('feed.on_item_render', array($this, 'onFeedItemRender'));

        PROTECTEDPHOTOS_CLASS_EventHandler::getInstance()->genericInit();
    }

    public function handlerQueryGetPhotoList( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        if ( OW::getUser()->isAuthorized('photo') ) return;

        $params = $builderEvent->getParams();

        switch ( true )
        {
            case in_array($params['listType'], array(
                'countAlbums',
                'getUserAlbumList',
                'latest',
                'toprated'
            ), true):
                $this->handlerQueryForAlbum($builderEvent);
                break;
            case in_array($params['listType'], array(
                'getAlbumPhotos',
                'countAlbumPhotos',
                'countPhotos',
                'findPhotoListByUserId',
                'getAlbumAllPhotos',
                'findEntityPhotoList'
            ), true):
                $this->handlerQueryForPhoto($builderEvent);
                break;
        }
    }

    private function handlerQueryForAlbum( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        $params = $builderEvent->getParams();
        $aliases = $params['aliases'];

        $sql = 'LEFT JOIN `%s` AS `pwd` ON(`%s`.`id` = `pwd`.`albumId`)';
        $builderEvent->addJoin(sprintf($sql, PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->getTableName(), $aliases['album']));
        $builderEvent->addWhere('`pwd`.`albumId` IS NULL');
    }

    private function handlerQueryForPhoto( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        $params = $builderEvent->getParams();
        $aliases = $params['aliases'];

        $sql = 'LEFT JOIN `%s` AS `pwd` ON(`%s`.`albumId` = `pwd`.`albumId`)';
        $builderEvent->addJoin(sprintf($sql, PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->getTableName(), $aliases['photo']));
        $builderEvent->addWhere('`pwd`.`albumId` IS NULL');
    }

    public function onFeedItemRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !in_array($params['action']['entityType'], array('photo_comments', 'multiple_photo_upload'), true) )
        {
            return;
        }

        $data = $event->getData();
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
        $plugin = OW::getPluginManager()->getPlugin('protectedphotos');
        $photoUrl = $plugin->getStaticUrl() . 'images/ic_pass_protected.svg';
        $content = $data['content'];

        switch ( $params['action']['entityType'] )
        {
            case 'photo_comments':
                if ( $this->service->isAlbumProtected($photo['albumId']) )
                {
                    $content['vars']['image'] = $photoUrl;
                    $content['vars']['class'] = 'owm_protected_photo_widget_icon';
                    OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'mobile.css');
                    $data['disabled'] = true;
                }
                break;
            case 'multiple_photo_upload':
                if ( $this->service->isAlbumProtected($photo['albumId']) )
                {
                    foreach ( $content['vars']['list'] as $index => $info )
                    {
                        $content['vars']['list'][$index]['image'] = $photoUrl;
                        $content['vars']['list'][$index]['class'] = 'owm_protected_photo_widget_icon';
                    }

                    OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'mobile.css');
                    $data['disabled'] = true;
                }
                break;
        }

        $data['content'] = $content;

        $event->setData($data);

    }
}
