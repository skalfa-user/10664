<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class GDPR_CTRL_Email extends OW_ActionController
{
    public function sendEmail()
    {
        if ( OW::getRequest()->isPost() && !empty($_POST['gdpr_message']) )
        {
            $message = trim(strip_tags($_POST['gdpr_message']));
            $subject = OW::getLanguage()->text('gdpr', 'gdpr_email_subject');

            $this->sendRequest($subject, $message);

            OW::getFeedback()->info(OW::getLanguage()->text('gdpr', 'gdpr_email_feedback_success'));
            $this->redirect($_SERVER['HTTP_REFERER']);
        }

        exit;
    }

    public function requestDownload()
    {
        $userId = OW::getUser()->getId();

        $userService = BOL_UserService::getInstance();
        $user = $userService->findUserById($userId);

        $subject = OW::getLanguage()->text('gdpr', 'gdpr_request_download_email_text', ['username' => $user->username]);
        $content = OW::getLanguage()->text('gdpr', 'gdpr_request_download_email_text', ['userName' => $user->username]);

        $this->sendRequest($subject, $content);

        OW::getFeedback()->info(OW::getLanguage()->text('gdpr', 'gdpr_request_successfull'));
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function requestDeletion()
    {
        $userId = OW::getUser()->getId();

        $userService = BOL_UserService::getInstance();
        $user = $userService->findUserById($userId);

        $subject = OW::getLanguage()->text('gdpr', 'gdpr_request_deletion_email_text', ['username' => $user->username]);
        $content = OW::getLanguage()->text('gdpr', 'gdpr_request_deletion_email_text', ['userName' => $user->username]);

        $this->sendRequest($subject, $content);

        OW::getFeedback()->info(OW::getLanguage()->text('gdpr', 'gdpr_request_successfull'));
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Send message to email
     */
    protected function sendRequest($subject, $content)
    {
        $config = OW::getConfig()->getValues('base');

        if ( isset($config['site_email']) )
        {
            $adminEmail = $config['site_email'];

            try {
                $mail = OW::getMailer()->createMail();
                $mail->addRecipientEmail($adminEmail);
                $mail->setSubject($subject);
                $mail->setTextContent($content);
                $mail->setHtmlContent($content);

                OW::getMailer()->send($mail);
            }
            catch ( Exception $e )
            {
                OW::getLogger()->addEntry(json_encode($e));
            }
        }
    }
}