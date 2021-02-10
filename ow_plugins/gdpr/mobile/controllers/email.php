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

class GDPR_MCTRL_Email extends OW_ActionController
{
    public function sendEmail()
    {
        $language = OW::getLanguage();

        $form = new GDPR_CLASS_EmailForm();
        $this->addForm($form);

        if ( OW::getRequest()->isPost() && !empty($_POST['gdpr_message']) )
        {
            $message = trim(strip_tags($_POST['gdpr_message']));
            $config = OW::getConfig()->getValues('base');

            if ( isset($config['site_email']) )
            {
                $adminEmail = $config['site_email'];

                try
                {
                    $mail = OW::getMailer()->createMail();
                    $mail->addRecipientEmail($adminEmail);
                    $mail->setSubject($language->text('gdpr', 'gdpr_email_subject'));
                    $mail->setTextContent($message);
                    $mail->setHtmlContent($message);
                    OW::getMailer()->send($mail);

                    OW::getFeedback()->info($language->text('gdpr', 'gdpr_email_feedback_success'));
                }
                catch ( Exception $e )
                {
                    OW::getFeedback()->error($language->text('gdpr', 'gdpr_email_feedback_error'));
                    OW::getLogger()->addEntry(json_encode($e));
                }

                $this->redirect($_SERVER['HTTP_REFERER']);
            }
        }

        $this->setPageHeading($language->text('gdpr', 'gdpr_send_message_label'));
    }

    public function editProfile()
    {
        $this->redirect('profile/edit');

        exit;
    }
}