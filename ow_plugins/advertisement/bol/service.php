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
 * Ads Service.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.advertisement.bol
 * @since 1.0
 */
final class ADS_BOL_Service
{
    const BANNER_POSITION_TOP = ADS_BOL_BannerPositionDao::POSITION_VALUE_TOP;
    const BANNER_POSITION_SIDEBAR = ADS_BOL_BannerPositionDao::POSITION_VALUE_SIDEBAR;
    const BANNER_POSITION_BOTTOM = ADS_BOL_BannerPositionDao::POSITION_VALUE_BOTTOM;

    /**
     * @var ADS_BOL_BannerDao
     */
    private $bannerDao;
    /**
     * @var ADS_BOL_BannerLocationDao
     */
    private $bannerLocationDao;
    /**
     * @var ADS_BOL_BannerPositionDao
     */
    private $bannerPositionDao;
    /**
     * @var boolean
     */
    private $locationEnabled;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->bannerDao = ADS_BOL_BannerDao::getInstance();
        $this->bannerLocationDao = ADS_BOL_BannerLocationDao::getInstance();
        $this->bannerPositionDao = ADS_BOL_BannerPositionDao::getInstance();

        $this->locationEnabled = BOL_GeolocationService::getInstance()->isServiceAvailable();
    }
    /**
     * Singleton instance.
     *
     * @var ADS_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ADS_BOL_Service
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
     * @return bool
     */
    public function getLocationEnabled()
    {
        return (bool) $this->locationEnabled;
    }

    /**
     * @param ADS_BOL_Banner $dto
     */
    public function saveBanner( ADS_BOL_Banner $dto )
    {
        $this->bannerDao->save($dto);
    }

    /**
     * @param ADS_BOL_BannerPosition $dto
     */
    public function saveBannerPosition( ADS_BOL_BannerPosition $dto )
    {
        $this->bannerPositionDao->save($dto);
    }

    /**
     * @param ADS_BOL_BannerLocation $dto
     */
    public function saveBannerLocation( ADS_BOL_BannerLocation $dto )
    {
        $this->bannerLocationDao->save($dto);
    }

    /**
     * @return array<ADS_BOL_Banner>
     */
    public function findAllBanners()
    {
        return $this->bannerDao->findAll();
    }

    public function resetBannersForPlugin( $position, $pluginKey )
    {
        $this->bannerPositionDao->deleteByPositionAndPluginKey($position, $pluginKey);
    }

    public function findBannersCount( $position, $pluginKey )
    {
        return $this->bannerPositionDao->findBannersCount($position, $pluginKey);
    }

    public function findBannerIdList( $position, $pluginKey )
    {
        $banners = $this->bannerPositionDao->findBannerList($position, $pluginKey);
        $idList = array();

        /* @var $banner ADS_BOL_BannerPosition */
        foreach ( $banners as $banner )
        {
            $idList[] = $banner->getBannerId();
        }

        return $idList;
    }

    public function findAllBannersInfo()
    {
        $info = $this->bannerDao->findAllBannersInfo();
        $resultArray = array();

        foreach ( $info as $infoItem )
        {
            if ( !isset($resultArray[$infoItem['id']]) )
            {
                $resultArray[$infoItem['id']] = array();
                $resultArray[$infoItem['id']] = array('label' => $infoItem['label'], 'code' => $infoItem['code']);
            }

            if ( $infoItem['location'] !== null )
            {
                if ( !isset($resultArray[$infoItem['id']]['location']) )
                {
                    $resultArray[$infoItem['id']]['location'] = array();
                }

                $resultArray[$infoItem['id']]['location'][$infoItem['location']] = BOL_GeolocationService::getInstance()->getCountryNameForCC3($infoItem['location']);
            }
        }

        return $resultArray;
    }

    /**
     *
     * @param integer $id
     * @return ADS_BOL_Banner
     */
    public function findBannerById( $id )
    {
        return $this->bannerDao->findById($id);
    }

    public function findBannerLocations( $bannerId )
    {
        return $this->bannerLocationDao->findListByBannerId($bannerId);
    }

    public function resetBannerLocations( $bannerId )
    {
        $this->bannerLocationDao->deleteByBannerId($bannerId);
    }

    public function deleteBanner( $bannerId )
    {
        $this->bannerDao->deleteById($bannerId);
        $this->bannerLocationDao->deleteByBannerId($bannerId);
        $this->bannerPositionDao->deleteByBannerId($bannerId);
    }

    public function findPlaceBannerList( $pluginKey, $position, $location = null )
    {
        $event = new BASE_CLASS_EventCollector('ads.enabled_plugins');
        OW::getEventManager()->trigger($event);

        $pluginList = $event->getData();

        $banners = $this->bannerDao->findPlaceBannerList($pluginKey, $position, $location);

        $plugin = BOL_PluginService::getInstance()->findPluginByKey($pluginKey);

        if ( empty($banners) && $pluginKey !== 'base' && $plugin !== null && in_array($plugin->getKey(), $pluginList) )
        {
            $banners = $this->bannerDao->findPlaceBannerList('base', $position, $location);
        }

        return $banners;
    }
}