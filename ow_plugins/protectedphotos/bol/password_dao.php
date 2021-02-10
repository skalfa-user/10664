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
 * @package ow_plugins.protected_photos.bol
 * @since 1.7.6
 */
class PROTECTEDPHOTOS_BOL_PasswordDao extends OW_BaseDao
{
    const ALBUM_ID = 'albumId';

    private static $instance;

    public static function getInstance()
    {
        if ( null === self::$instance )
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'protectedphotos_passwords';
    }

    public function getDtoClassName()
    {
        return 'PROTECTEDPHOTOS_BOL_Password';
    }

    public function findByAlbumId( $albumId )
    {
        if ( empty($albumId) )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::ALBUM_ID, $albumId);

        return $this->findObjectByExample($example);
    }

    public function isAlbumProtected( $albumId )
    {
        if ( empty($albumId) )
        {
            return false;
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::ALBUM_ID, $albumId);

        return (int) $this->countByExample($example) > 0;
    }

    /**
     * @param array $ids
     * @return PROTECTEDPHOTOS_BOL_Password[]
     */
    public function findByAlbumIds( array $ids )
    {
        if ( count($ids) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray(self::ALBUM_ID, $ids);

        return $this->findListByExample($example);
    }

    public function deleteByAlbumId( $albumId )
    {
        if ( empty($albumId) )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::ALBUM_ID, $albumId);

        return $this->deleteByExample($example);
    }

    /**
     * @param $userId
     * @param $privacies
     * @return PROTECTEDPHOTOS_BOL_Password[]
     */
    public function findUserPasswordByPrivacy( $userId, array $privacies )
    {
        if ( empty($userId) || count($privacies) === 0 )
        {
            return array();
        }

        $sql = 'SELECT `p`.*
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . OW_DB_PREFIX . 'photo_album` AS `a` ON(`p`.`albumId` = `a`.`id`)
            WHERE `p`.`privacy` IN (' . $this->dbo->mergeInClause($privacies) . ') AND `a`.`userId` = :userId';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(
            'userId' => $userId
        ));
    }
}