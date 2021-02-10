<?php

class CVIDEOUPLOAD_CTRL_Video extends OW_ActionController
{
    /**
     * @var CVIDEOUPLOAD_BOL_Service
     */
    private $service;

    /**
     * CVIDEOUPLOAD_CTRL_Video constructor.
     *
     * @throws AuthenticateException
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
    }

    public function upload()
    {
        $userId = OW::getUser()->getId();

        list($result, $message) = $this->service->isActionAllowed(CVIDEOUPLOAD_BOL_Service::ACTION_UPLOAD_VIDEO);

        if ( $result === false )
        {
            $this->assign('authMessage', $message);

            return;
        }

        $form = OW::getClassInstance('CVIDEOUPLOAD_CLASS_UploadVideoForm', 'upload-video-form', $userId );
        /* @var CVIDEOUPLOAD_CLASS_UploadVideoForm $form */

        $this->addForm($form);

        $userSearchEnabled = false;
        if ( !empty($form->getElement('privacy')) &&
            !empty($form->getElement('privacy')->getValue()) &&
            $form->getElement('privacy')->getValue() == CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_CERTAIN_USERS )
        {
            $userSearchEnabled = true;
        }

        $script = "$('select[name=privacy]').on('change', function() {
            if (this.value == 'certain_users') {
                $('.certain_users_enabled_settings').removeClass('ow_hidden');
            } else {
                $('.certain_users_enabled_settings').addClass('ow_hidden');
            }
        });";

        OW::getDocument()->addOnloadScript($script);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $video = $form->process();
            $userUrl = OW::getRouter()->urlForRoute('base_user_profile', [
                'username' => BOL_UserService::getInstance()->getUserName($userId)
            ]);

            if ( !is_null($video) )
            {
                OW::getFeedback()->info($this->service->getLanguageText('video_updated'));

                $this->redirect($userUrl);
            }
            else
            {
                OW::getFeedback()->error($this->service->getLanguageText('video_updated_error'));
                $this->redirect();
            }
        }

        $plugin = OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY);

        $this->assign('formName', $form->getName());
        $this->assign('userSearchEnabled', $userSearchEnabled);

        $document = OW::getDocument();
        $document->setHeading($this->service->getLanguageText('tb_upload_video'));
        $document->setHeadingIconClass('ow_ic_video');
        $document->setTitle($this->service->getLanguageText('tb_upload_video'));

        // init css
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'file_upload.css');
    }
}