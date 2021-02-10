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
class PROTECTEDPHOTOS_CLASS_QueryHandler
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

        $em->bind('photo.getPhotoList', array($this, 'handlerQueryGetPhotoList'));
        $em->bind('base.query.content_filter', array($this, 'handlerBaseContentFilter'));
        $em->bind('photo.getPhotoList', array($this, 'handlerQueryUserPhoto'));
    }

    public function unbind()
    {
        OW::getEventManager()->unbind('photo.getPhotoList', array($this, 'handlerQueryGetPhotoList'));
        OW::getEventManager()->unbind('base.query.content_filter', array($this, 'handlerBaseContentFilter'));
        OW::getEventManager()->unbind('photo.getPhotoList', array($this, 'handlerQueryUserPhoto'));
    }

    public function handlerQueryGetPhotoList( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        if ( OW::getUser()->isAuthorized('photo') ) return;

        $params = $builderEvent->getParams();

        switch ( true )
        {
            case in_array($params['listType'], array(
                'findAlbumPhotoList.latest',
                'findEntityPhotoList'
            ), true):
                $this->handlerQueryForPhoto($builderEvent);
                break;
            case in_array($params['listType'], array(
                'latest',
                'featured',
                'toprated',

                'findPhotoListByUserId',
                'searchByDesc',
                'searchByUser',
                'searchByHashtag',
                'searchByUsername'
            ), true):
                $this->handlerQueryForPublicList($builderEvent);
                break;
        }
    }

    private function handlerQueryForPhoto( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        $params = $builderEvent->getParams();
        $aliases = $params['aliases'];

        $sql = 'LEFT JOIN `%s` AS `pwd` ON(`%s`.`albumId` = `pwd`.`albumId`)';
        $builderEvent->addJoin(sprintf($sql, PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->getTableName(), $aliases['photo']));
        $builderEvent->addWhere('`pwd`.`albumId` IS NULL');
    }

    private function handlerQueryForPublicList( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        $params = $builderEvent->getParams();
        $aliases = $params['aliases'];

        $sql = 'LEFT JOIN `%s` AS `pwd` ON(`%s`.`albumId` = `pwd`.`albumId`)';
        $builderEvent->addJoin(sprintf($sql, PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->getTableName(), $aliases['photo']));
        $builderEvent->addWhere(sprintf('`pwd`.`albumId` IS NULL OR `pwd`.`privacy` IN(%s)',
            OW::getDbo()->mergeInClause(array(
                PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC,
                PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD
            ))
        ));
    }

    public function handlerBaseContentFilter( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        $params = $builderEvent->getParams();

        if ( isset($params['type']) )
        {
            switch ($params['type'])
            {
                case 'photo_rates':
                case 'photo_comments';

                    switch ( $params['method'] )
                    {
                        case 'BOL_RateDao::findMostRatedEntityList':
                            $this->handlerRateFilter($builderEvent);
                            break;
                        case 'BOL_CommentDao::findMostCommentedEntityList':
                            $this->handlerCommentFilter($builderEvent);
                            break;
                    }

                    break;
            }
        }
    }

    private function handlerRateFilter( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        $params = $builderEvent->getParams();

        $builderEvent->addJoin(sprintf(
            'INNER JOIN `%s` as `p` ON(`p`.`id` = `%s`.`entityId`)',
            OW_DB_PREFIX . 'photo',
            $params['tables']['content']
        ));
        $builderEvent->addJoin(sprintf(
            'LEFT JOIN `%s` AS `pwd` ON(`p`.`albumId` = `pwd`.`albumId`)',
            PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->getTableName()
        ));
        $builderEvent->addWhere(sprintf('`pwd`.`albumId` IS NULL OR `pwd`.`privacy` IN(%s)',
            OW::getDbo()->mergeInClause(array(
                PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC,
                PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD
            ))
        ));
    }

    private function handlerCommentFilter( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        $params = $builderEvent->getParams();

        $builderEvent->addJoin(sprintf(
            'INNER JOIN `%s` as `p` ON(`p`.`id` = `%s`.`entityId`)',
            OW_DB_PREFIX . 'photo',
            $params['tables']['comment_entity']
        ));
        $builderEvent->addJoin(sprintf(
            'LEFT JOIN `%s` AS `pwd` ON(`p`.`albumId` = `pwd`.`albumId`)',
            PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->getTableName()
        ));
        $builderEvent->addWhere(sprintf('`pwd`.`albumId` IS NULL OR `pwd`.`privacy` IN(%s)',
            OW::getDbo()->mergeInClause(array(
                PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC,
                PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD
            ))
        ));
    }

    public function handlerQueryUserPhoto( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        $params = $builderEvent->getParams();

        switch ( true )
        {
            case in_array($params['listType'], array(
                'getUserAlbumList',
                'findUserAlbumList'
            ), true):
                $this->handlerUserAlbumList($builderEvent);
                break;
        }
    }

    private function handlerUserAlbumList( BASE_CLASS_QueryBuilderEvent $builderEvent )
    {
        if ( OW::getUser()->isAuthorized('photo') ) return;

        $params = $builderEvent->getParams();
        $aliases = $params['aliases'];

        if ( OW::getUser()->isAuthenticated() )
        {
            $builderEvent->addJoin(sprintf(
                'LEFT JOIN `%s` AS `pwd` ON(`%s`.`id` = `pwd`.`albumId`)',
                PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->getTableName(),
                $aliases['album']
            ));
            $builderEvent->addJoin(sprintf(
                'LEFT JOIN `%s` AS `access` ON(`access`.`passwordId` = `pwd`.`id`)',
                PROTECTEDPHOTOS_BOL_AccessDao::getInstance()->getTableName()
            ));
            $builderEvent->addWhere(sprintf('(`pwd`.`albumId` IS NULL OR `access`.`passwordId` IS NULL OR
                (`pwd`.`privacy` IN(%s) AND `access`.`userId` = :pppUserId)) OR (`pwd`.`privacy` IN (%s))',
                OW::getDbo()->mergeInClause(array(
                    PROTECTEDPHOTOS_BOL_Service::PRIVACY_FRIENDS_ONLY,
                    PROTECTEDPHOTOS_BOL_Service::PRIVACY_INDIVIDUAL_FRIENDS,
                    PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD
                )),
                OW::getDbo()->mergeInClause(array(
                    PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD,
                    PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC
                ))
            ));
            $builderEvent->addQueryParam('pppUserId', OW::getUser()->getId());
        }
        else
        {
            $builderEvent->addJoin(sprintf(
                'LEFT JOIN `%s` AS `pwd` ON(`%s`.`id` = `pwd`.`albumId`)',
                PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->getTableName(),
                $aliases['album']
            ));
            $builderEvent->addWhere(sprintf('`pwd`.`albumId` IS NULL OR `pwd`.`privacy` IN(%s)',
                OW::getDbo()->mergeInClause(array(
                    PROTECTEDPHOTOS_BOL_Service::PRIVACY_PUBLIC,
                    PROTECTEDPHOTOS_BOL_Service::PRIVACY_PASSWORD
                ))
            ));
        }
    }
}
