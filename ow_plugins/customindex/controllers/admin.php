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

class CUSTOMINDEX_CTRL_Admin extends ADMIN_CTRL_Abstract {

    protected $service;

    public function __construct() {
        parent::__construct();

        $this->service = CUSTOMINDEX_BOL_Service::getInstance();
    }

    public function init()
    {
        parent::init();

        OW::getDocument()->setHeading(OW::getLanguage()->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'admin_settings'));

        $handler = OW::getRequestHandler()->getHandlerAttributes();
        $menus = array();

        $banners = new BASE_MenuItem();
        $banners->setLabel(OW::getLanguage()->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'menu_banners'));
        $banners->setUrl(OW::getRouter()->urlForRoute('customindex.admin'));
        $banners->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'index');
        $banners->setKey('banner');
        $banners->setOrder(0);
        $menus[] = $banners;

        if ($handler[OW_RequestHandler::ATTRS_KEY_ACTION] == 'bannerManager') 
        {
            if (isset($handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['id']))
            {
                $banners = new BASE_MenuItem();
                $banners->setLabel(OW::getLanguage()->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'menu_banner_edit'));
                $banners->setUrl(OW::getRouter()->urlForRoute('customindex.admin-banner-id', [
                    'id' => $handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['id']
                ]));
                $banners->setActive(true);
                $banners->setKey('banner-edit');
                $banners->setOrder(1);
                $menus[] = $banners;
            }
            else
            {
                $banners = new BASE_MenuItem();
                $banners->setLabel(OW::getLanguage()->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'menu_banner_create'));
                $banners->setUrl(OW::getRouter()->urlForRoute('customindex.admin-banner'));
                $banners->setActive(true);
                $banners->setKey('banner-create');
                $banners->setOrder(1);
                $menus[] = $banners;
            }
        }

        $this->addComponent('menu', new BASE_CMP_ContentMenu($menus));
    }

    public function index() {
        $banners = $this->service->findAllBanners();

        $this->assign('banners', $banners);
        $this->assign('url', OW::getPluginManager()->getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY)->getUserFilesUrl());
    }

    public function bannerManager(array $params) {
        $banner = $this->service->findBanner(@$params['id']);
        $form = new CUSTOMINDEX_CLASS_BannerForm($banner);

        if ($form->process()) {
            OW::getFeedback()->info(OW::getLanguage()->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'saved_success_message'));

            $this->redirect(OW::getRouter()->urlForRoute(CUSTOMINDEX_BOL_Service::PLUGIN_KEY . '.admin'));
        }

        $this->assign('banner', $banner);
        $this->addForm($form);
        $this->assign('url', OW::getPluginManager()->getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY)->getUserFilesUrl());
    }

    public function updateBanner(array $params)
    {
        if (!OW::getRequest()->isPost() || empty($params['id']) || ($banner = $this->service->findBanner($params['id'])) === null)
        {
            $this->redirect(OW::getRouter()->urlForRoute(CUSTOMINDEX_BOL_Service::PLUGIN_KEY . '.admin'));
        }

        if (
            isset($_FILES[CUSTOMINDEX_CLASS_BannerForm::ELEMENT_BANNER_FILE]) &&
            is_uploaded_file($_FILES[CUSTOMINDEX_CLASS_BannerForm::ELEMENT_BANNER_FILE]['tmp_name'])
        ) {
            $this->service->deleteBannerFile($banner);
            $banner->name = sprintf('%s.%s', uniqid(), $this->service->normalizeName($_FILES[CUSTOMINDEX_CLASS_BannerForm::ELEMENT_BANNER_FILE]['name']));
            move_uploaded_file(
                $_FILES[CUSTOMINDEX_CLASS_BannerForm::ELEMENT_BANNER_FILE]['tmp_name'],
                OW::getPluginManager()->getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY)->getUserFilesDir() . $banner->name
            );
        }

        $banner->html = $_POST[CUSTOMINDEX_CLASS_BannerForm::ELEMENT_BANNER_CONTENT];
        $this->service->updateBanner($banner);

        OW::getFeedback()->info(OW::getLanguage()->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'saved_success_message'));

        $this->redirect(OW::getRouter()->urlForRoute(CUSTOMINDEX_BOL_Service::PLUGIN_KEY . '.admin'));
    }

    public function deleteBanner()
    {
        if ( !OW::getRequest()->isPost() || !isset($_GET['bannerId']) || ($banner = $this->service->findBanner($_GET['bannerId'])) === null ) 
        {
            exit(json_encode(false));
        }

        $this->service->deleteBannerEntity($banner);
        exit(json_encode(true));
    }
}
