<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * Class SKTEXTCR_CTRL_Admin
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKTEXTCR_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @var SKTEXTCR_BOL_Service
     */
    private $service;

    /**
     * SKTEXTCR_CTRL_Admin constructor.
     *
     * @throws AuthenticateException
     * @throws InterceptException
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = SKTEXTCR_BOL_Service::getInstance();

        OW::getNavigation()->activateMenuItem(OW_Navigation::ADMIN_PLUGINS, 'admin', 'sidebar_menu_plugins_installed');

        if ( !OW::getPluginManager()->isPluginActive('mailbox') )
        {
            throw new Redirect403Exception('Mailbox plugin is not active');
        }
    }

    public function init()
    {
        parent::init();

        $handler = OW::getRequestHandler()->getHandlerAttributes();
        $menus = [];

        $all = new BASE_MenuItem();
        $all->setLabel($this->service->getLanguageText('admin_menu_all_messages'));
        $all->setUrl(OW::getRouter()->urlForRoute('sktextcr.admin-all-message'));
        $all->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'index');
        $all->setKey('index');
        $all->setIconClass('ow_ic_clock');
        $all->setOrder(0);
        $menus[] = $all;

        $search = new BASE_MenuItem();
        $search->setLabel($this->service->getLanguageText('admin_menu_search_messages'));
        $search->setUrl(OW::getRouter()->urlForRoute('sktextcr.admin-search-message'));
        $search->setActive($handler[OW_RequestHandler::ATTRS_KEY_ACTION] === 'search');
        $search->setKey('search');
        $search->setIconClass('ow_ic_lens');
        $search->setOrder(1);
        $menus[] = $search;

        $this->addComponent('menu', new BASE_CMP_ContentMenu($menus));
    }


    public function index()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading($this->service->getLanguageText('admin_all_message_heading'));
        }

        $params = null;

        $formSearchByUserName = OW::getClassInstance('SKTEXTCR_CLASS_SearchByUserNameForm', $params);
        /* @var SKTEXTCR_CLASS_SearchByUserNameForm $formSearchByUserName */

        $this->addForm($formSearchByUserName);



        $formSearchFromToForm = OW::getClassInstance('SKTEXTCR_CLASS_SearchFromToForm', $params);
        /* @var SKTEXTCR_CLASS_SearchFromToForm $formSearchFromToForm */

        $this->addForm($formSearchFromToForm);



        $formSearchBadWordForm = OW::getClassInstance('SKTEXTCR_CLASS_SearchBadWordForm', $params);
        /* @var SKTEXTCR_CLASS_SearchBadWordForm $formSearchBadWordForm */

        $this->addForm($formSearchBadWordForm);


        if ( !OW::getRequest()->isPost() )
        {
            $this->service->clearData();
        }

        if ( OW::getRequest()->isPost() && isset($_POST['form_name']) && in_array($_POST['form_name'], [
            SKTEXTCR_BOL_Service::FORM_NAME_BAD_WORD, SKTEXTCR_BOL_Service::FORM_NAME_FROM_TO, SKTEXTCR_BOL_Service::FORM_NAME_BY_NAME]) )
        {
            $dataSearch = null;

            switch ($_POST['form_name'])
            {
                case SKTEXTCR_BOL_Service::FORM_NAME_BY_NAME:

                    if ( $formSearchByUserName->isValid($_POST) )
                    {
                        $data = $formSearchByUserName->getValues();

                        $dataSearch = new SKTEXTCR_BOL_DataSearch();
                        $dataSearch->type = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_BY_USER_NAME;
                        $dataSearch->userName = trim($data['userName']);
                    }

                    break;

                case SKTEXTCR_BOL_Service::FORM_NAME_FROM_TO:

                    if ( $formSearchFromToForm->isValid($_POST) )
                    {
                        $data = $formSearchFromToForm->getValues();

                        $dataSearch = new SKTEXTCR_BOL_DataSearch();
                        $dataSearch->type = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_FROM_TO;
                        $dataSearch->userNameFrom = trim($data['userNameFrom']);
                        $dataSearch->userNameTo = trim($data['userNameTo']);
                    }


                    break;

                case SKTEXTCR_BOL_Service::FORM_NAME_BAD_WORD:

                    if ( $formSearchBadWordForm->isValid($_POST) )
                    {
                        $data = $formSearchBadWordForm->getValues();

                        $dataSearch = new SKTEXTCR_BOL_DataSearch();
                        $dataSearch->type = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_BAD_WORD;
                        $dataSearch->badWord = trim($data['badWord']);
                    }


                    break;
            }

            if ( !empty($dataSearch) )
            {
                $this->service->initDataSearch( $dataSearch );
                $this->redirect(OW::getRouter()->urlForRoute('sktextcr.admin-search-message'));
            }
        }

        $usersCmp = OW::getClassInstance("SKTEXTCR_CMP_MessagesList", null);
        $this->addComponent('messageList', $usersCmp);
    }

    public function search()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading($this->service->getLanguageText('admin_search_message_heading'));
        }

        $params = $this->service->getDataSearch();

        $formSearchByUserName = OW::getClassInstance('SKTEXTCR_CLASS_SearchByUserNameForm', $params);
        /* @var SKTEXTCR_CLASS_SearchByUserNameForm $formSearchByUserName */

        $this->addForm($formSearchByUserName);



        $formSearchFromToForm = OW::getClassInstance('SKTEXTCR_CLASS_SearchFromToForm', $params);
        /* @var SKTEXTCR_CLASS_SearchFromToForm $formSearchFromToForm */

        $this->addForm($formSearchFromToForm);



        $formSearchBadWordForm = OW::getClassInstance('SKTEXTCR_CLASS_SearchBadWordForm', $params);
        /* @var SKTEXTCR_CLASS_SearchBadWordForm $formSearchBadWordForm */

        $this->addForm($formSearchBadWordForm);

        if ( OW::getRequest()->isPost() && isset($_POST['form_name']) && in_array($_POST['form_name'], [
                SKTEXTCR_BOL_Service::FORM_NAME_BAD_WORD, SKTEXTCR_BOL_Service::FORM_NAME_FROM_TO, SKTEXTCR_BOL_Service::FORM_NAME_BY_NAME]) )
        {
            $dataSearch = null;

            switch ($_POST['form_name'])
            {
                case SKTEXTCR_BOL_Service::FORM_NAME_BY_NAME:

                    if ( $formSearchByUserName->isValid($_POST) )
                    {
                        $data = $formSearchByUserName->getValues();

                        $dataSearch = new SKTEXTCR_BOL_DataSearch();
                        $dataSearch->type = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_BY_USER_NAME;
                        $dataSearch->userName = trim($data['userName']);
                    }

                    break;

                case SKTEXTCR_BOL_Service::FORM_NAME_FROM_TO:

                    if ( $formSearchFromToForm->isValid($_POST) )
                    {
                        $data = $formSearchFromToForm->getValues();

                        $dataSearch = new SKTEXTCR_BOL_DataSearch();
                        $dataSearch->type = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_FROM_TO;
                        $dataSearch->userNameFrom = trim($data['userNameFrom']);
                        $dataSearch->userNameTo = trim($data['userNameTo']);
                    }


                    break;

                case SKTEXTCR_BOL_Service::FORM_NAME_BAD_WORD:

                    if ( $formSearchBadWordForm->isValid($_POST) )
                    {
                        $data = $formSearchBadWordForm->getValues();

                        $dataSearch = new SKTEXTCR_BOL_DataSearch();
                        $dataSearch->type = SKTEXTCR_BOL_Service::CONFIG_TYPE_SEARCH_BAD_WORD;
                        $dataSearch->badWord = trim($data['badWord']);
                    }


                    break;
            }

            if ( !empty($dataSearch) )
            {
                $this->service->initDataSearch( $dataSearch );
                $this->redirect(OW::getRouter()->urlForRoute('sktextcr.admin-search-message'));
            }
        }

        $usersCmp = OW::getClassInstance("SKTEXTCR_CMP_MessagesList", $params);
        $this->addComponent('messageList', $usersCmp);
    }
}