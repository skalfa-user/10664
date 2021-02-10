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
namespace Skadate\Mobile\Middleware;

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Request;
use BOL_LanguageService;
use SKMOBILEAPP_BOL_Service;

class ApiLanguage extends Base
{
    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return SilexApplication::EARLY_EVENT;
    }

    /**
     * Get middleware
     *
     * @return mixed
     */
    public function getMiddleware()
    {
        return function (Request $request) {
            // try to extract the api lang both from headers and get params
            $apiLanguageParam = $request->headers->get('api-language', $request->query->get('api-language'));

            // convert the api lang param
            $apiLanguage = SKMOBILEAPP_BOL_Service::getInstance()->conversionLang($apiLanguageParam);

            if ($apiLanguage) {
                $languages = BOL_LanguageService::getInstance()->getLanguages();

                // try to define api language
                foreach($languages as $lang) {
                    if (mb_substr($lang->tag, 0, 2) == $apiLanguage && $lang->status == 'active') {
                        BOL_LanguageService::getInstance()->setCurrentLanguage($lang);

                        break;
                    }
                }
            }
        };
    }
}
