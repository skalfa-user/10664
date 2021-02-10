<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Data Access Object for `base_banner` table.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.advertisement.bol
 * @since 1.0
 */
class ADS_BOL_BannerDao extends OW_BaseDao
{
    const LABEL = 'label';
    const CODE = 'code';
    const CACHE_TAG_ADS_BANNERS = 'ads.position_banner_cache';

    /**
     * Singleton instance.
     *
     * @var ADS_BOL_BannerDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ADS_BOL_BannerDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'ADS_BOL_Banner';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'ads_banner';
    }

    public function findAllBannersInfo()
    {
        $query = "SELECT `b`.*, `l`." . ADS_BOL_BannerLocationDao::LOCATION . " FROM `" . $this->getTableName() . "` AS `b`
            LEFT JOIN `" . ADS_BOL_BannerLocationDao::getInstance()->getTableName() . "` AS `l` ON ( `b`.`id` = `l`.`" . ADS_BOL_BannerLocationDao::BANNER_ID . "` )";

        return $this->dbo->queryForList($query);
    }

    public function findPlaceBannerList( $pluginKey, $position, $location = null )
    {
        $query = "SELECT `b`.* FROM `" . $this->getTableName() . "` AS `b`
            LEFT JOIN `" . ADS_BOL_BannerPositionDao::getInstance()->getTableName() . "` AS `bp` ON (`b`.`id` = `bp`.`" . ADS_BOL_BannerPositionDao::BANNER_ID . "`)
            LEFT JOIN `" . ADS_BOL_BannerLocationDao::getInstance()->getTableName() . "` AS `bl` ON (`b`.`id` = `bl`.`" . ADS_BOL_BannerLocationDao::BANNER_ID . "`)
            WHERE `bp`.`" . ADS_BOL_BannerPositionDao::POSITION . "` = :position AND `bp`.`" . ADS_BOL_BannerPositionDao::PLUGIN_KEY . "` IN ( :pluginKey, 'base' )
                AND ( `bl`.`" . ADS_BOL_BannerLocationDao::LOCATION . "` IS NULL" . ( $location === null ? ')' : " OR `bl`.`" . ADS_BOL_BannerLocationDao::LOCATION . "` = :location)" );

        $params = array('pluginKey' => $pluginKey, 'position' => $position);

        if ( $location != null )
        {
            $params['location'] = $location;
        }

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params, 3600 * 24, array(self::CACHE_TAG_ADS_BANNERS, OW_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(self::CACHE_TAG_ADS_BANNERS));
    }
}