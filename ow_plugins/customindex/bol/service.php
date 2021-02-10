<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CUSTOMINDEX_BOL_Service
{
    use OW_Singleton;

    /**
     * Theme name
     */
    const THEME_NAME = 'platinum_pro';

    /**
     * Plugin key
     */
    const PLUGIN_KEY = 'customindex';

    protected $bannerDao;

    private function __construct()
    {
        $this->bannerDao = CUSTOMINDEX_BOL_BannerDao::getInstance();
    }

    /**
     * Is plugin ready for usage
     *
     * @return array
     *      boolean is ready
     *      string  error message
     */
    public function isPluginReadyForUsage()
    {
        // check a current theme
        if ( $this->getActiveTheme() !== self::THEME_NAME ) 
        {
            return array(
                false,
                OW::getLanguage()->text(self::PLUGIN_KEY, 'missing_theme_error', [
                    'theme' => self::THEME_NAME
                ])
            );
        }

        $requiredPlugins = [
            'slpremiumtheme'
        ];

        $missingPlugin = [];

        foreach ( $requiredPlugins as $requiredPlugin )
        {
            if ( !OW::getPluginManager()->isPluginActive($requiredPlugin)  )
            {
                $missingPlugin[] = $requiredPlugin;
            }
        }

        if ( $missingPlugin )
        {
            return array(
                false,
                OW::getLanguage()->text(self::PLUGIN_KEY, 'missing_plugins_error', [
                    'plugins' => implode(', ', $missingPlugin)
                ])
            );
        }

        return array(
            true,
            null
        );
    }

    /**
     * Get users count
     *
     * @return integer
     */
    public function getUsersCount()
    {
        return number_format(BOL_UserService::getInstance()->count(true), 0, ' ', ' ');
    }

    public function findBanner($id)
    {
        if (empty($id)) {
            return null;
        }

        return $this->bannerDao->findById($id);
    }

    public function findAllBanners()
    {
        return $this->bannerDao->findAll();
    }

    public function createBannerEntity($name, $html = null)
    {
        $entity = new CUSTOMINDEX_BOL_Banner();
        $entity->name = sprintf('%s.%s', uniqid(), $this->normalizeName($name));
        $entity->html = $html;

        $this->bannerDao->save($entity);

        return $entity;
    }

    public function normalizeName($name)
    {
        return pathinfo($name, PATHINFO_EXTENSION);
    }

    public function deleteBannerEntity(CUSTOMINDEX_BOL_Banner $banner)
    {
        $this->deleteBannerFile($banner);
        $this->bannerDao->delete($banner);
    }

    public function deleteBannerFile(CUSTOMINDEX_BOL_Banner $banner) {
        $path = OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getUserFilesDir();

        @unlink(sprintf('%s%s', $path, $banner->name));
    }

    public function updateBanner(CUSTOMINDEX_BOL_Banner $banner)
    {
        $this->bannerDao->save($banner);
    }

    /**
     * Get active theme
     * 
     * @return string
     */
    protected function getActiveTheme()
    {
        if ( isset($_SESSION['demoreset']['layout']) )
        {
            return $_SESSION['demoreset']['layout'];
        }

        return OW::getConfig()->getValue('base', 'selectedTheme');
    }
}
