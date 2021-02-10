<?php

class CVIDEOUPLOAD_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @var CVIDEOUPLOAD_BOL_Service
     */
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();

        OW::getNavigation()->activateMenuItem(OW_Navigation::ADMIN_PLUGINS, 'admin', 'sidebar_menu_plugins_installed');
    }

    public function index()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading($this->service->getLanguageText('admin_settings'));
        }

        $form = OW::getClassInstance('CVIDEOUPLOAD_CLASS_SettingsForm', 'settings');
        /* @var CVIDEOUPLOAD_CLASS_SettingsForm $form */

        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid(array_merge($_FILES, $_POST)) )
        {
            $form->process();

            OW::getFeedback()->info($this->service->getLanguageText('settings_saved'));

            $this->redirect();
        }

        list($isServerReady, $configurationError) = $this->service->isServerReadyForUploading();

        if ( !$isServerReady )
        {
            $this->assign('configurationError', $configurationError);
        }

        $this->assign('maxUploadFileSize', $this->service->getMaxUploadFileSizeInMegabytes());
    }
}