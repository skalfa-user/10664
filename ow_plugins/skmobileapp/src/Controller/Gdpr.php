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
namespace Skadate\Mobile\Controller;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use BOL_UserService;
use OW;

class Gdpr extends Base
{
    /**
     * Is plugin active
     *
     * @var bool
     */
    protected $isPluginActive = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isPluginActive = OW::getPluginManager()->isPluginActive('gdpr');
    }

    /**
     * Connect methods
     *
     * @param SilexApplication $app
     * @return mixed
     */
    public function connect(SilexApplication $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // request download user data
        $controllers->post('/downloads/', function (SilexApplication $app) {
            if ($this->isPluginActive) {
                $user = BOL_UserService::getInstance()->findUserById($app['users']->getLoggedUserId());

                $subject = OW::getLanguage()->text('gdpr', 'request_download_email_subject', ['username' => $user->username]);
                $content = OW::getLanguage()->text('gdpr', 'request_download_email_content', ['userName' => $user->username]);

                $this->sendRequest($subject, $content);

                return $app->json([], 204);
            }

            throw new BadRequestHttpException('Gdpr plugin is not activated');
        });

        // request deletion user data
        $controllers->post('/deletions/', function (SilexApplication $app) {
            if ($this->isPluginActive) {
                $user = BOL_UserService::getInstance()->findUserById($app['users']->getLoggedUserId());

                $subject = OW::getLanguage()->text('gdpr', 'request_deletion_email_subject', ['username' => $user->username]);
                $content = OW::getLanguage()->text('gdpr', 'request_deletion_email_content', ['userName' => $user->username]);

                $this->sendRequest($subject, $content);

                return $app->json([], 204);
            }

            throw new BadRequestHttpException('Gdpr plugin is not activated');
        });

        // send message to admin
        $controllers->post('/messages/', function (Request $request, SilexApplication $app) {
            if ($this->isPluginActive) {
                $vars = json_decode($request->getContent(), true);

                if (!empty($vars['message'])) {
                    $message = strip_tags($vars['message']);
                    $subject = OW::getLanguage()->text('gdpr', 'send_message_subject');

                    $this->sendRequest($subject, $message);
                }

                return $app->json([], 204);
            }

            throw new BadRequestHttpException('Gdpr plugin is not activated');
        });

        return $controllers;
    }

    protected function sendRequest($subject, $content)
    {
        $config = OW::getConfig()->getValues('base');

        if (isset($config['site_email'])) {
            $adminEmail = $config['site_email'];

            try {
                $mail = OW::getMailer()->createMail();
                $mail->addRecipientEmail($adminEmail);
                $mail->setSubject($subject);
                $mail->setTextContent($content);
                $mail->setHtmlContent($content);

                OW::getMailer()->send($mail);
            }
            catch (Exception $e)
            {
                OW::getLogger()->addEntry(json_encode($e));
            }
        }
    }
}
