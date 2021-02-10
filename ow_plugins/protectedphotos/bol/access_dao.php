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
class PROTECTEDPHOTOS_BOL_AccessDao extends OW_BaseDao
{
    const PASSWORD_ID = 'passwordId';
    const USER_ID = 'userId';

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
        return OW_DB_PREFIX . 'protectedphotos_accesses';
    }

    public function getDtoClassName()
    {
        return 'PROTECTEDPHOTOS_BOL_Access';
    }

    public function findByPasswordIdsAndUserId( array $passwordIds, $userId )
    {
        if ( count($passwordIds) === 0 || empty($userId) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray(self::PASSWORD_ID, $passwordIds);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findListByExample($example);
    }

    /**
     * @param int $passwordId
     * @param int $userId
     * @return PROTECTEDPHOTOS_BOL_Access
     */
    public function findByPasswordIdAndUserId( $passwordId, $userId )
    {
        if ( empty($passwordId) || empty($userId) )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::PASSWORD_ID, $passwordId);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findObjectByExample($example);
    }

    public function deleteAccessByPasswordId( $passwordId )
    {
        if ( empty($passwordId) )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::PASSWORD_ID, $passwordId);

        return $this->deleteByExample($example);
    }

    public function deleteAccessForUser( $passwordId, $userId )
    {
        if ( empty($passwordId) || empty($userId) )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::PASSWORD_ID, $passwordId);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->deleteByExample($example);
    }
}
