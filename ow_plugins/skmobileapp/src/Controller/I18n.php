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
use SKMOBILEAPP_CLASS_LanguageEventCollector;
use BOL_LanguageService;
use OW;

class I18n extends Base
{
    /**
     * Rtl mode
     */
    const RTL_MODE = 'rtl';

    /**
     * Ltr mode
     */
    const LTR_MODE = 'ltr';

    /**
     * Lang service
     *
     * @param BOL_LanguageService
     */
    protected $langService;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->langService = BOL_LanguageService::getInstance();
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

        // all translations
        $controllers->get('/{id}/', function (SilexApplication $app, $id) {
            // try to find given language
            $defaultLanguage  = $this->langService->findDefault();
            $languages = $this->langService->getLanguages();
            $isLanguageRtl = $defaultLanguage->getRtl();

            $id = $this->service->conversionLang($id);

            // process all languages
            foreach ($languages as $lang) {
                if (mb_substr($lang->tag, 0, 2) == $id && $lang->status == 'active') {
                    $defaultLanguage = $lang;
                    $isLanguageRtl = $lang->getRtl();

                    break;
                }
            }

            $translations = [];
            $this->langService->setCurrentLanguage($defaultLanguage);
            $keys = $this->langService->findAllPrefixKeys($this->langService->findPrefixId(self::LANG_PREFIX));

            // get all translations
            foreach ($keys as $item) {
                $translations[$item->key] = OW::getLanguage()->text(self::LANG_PREFIX, $item->key);
            }

            $event = new SKMOBILEAPP_CLASS_LanguageEventCollector('skmobileapp.get_translations', [
                'defaultLanguage' => $defaultLanguage
            ]);

            OW::getEventManager()->trigger($event);
            $data = $event->getData();

            if ( !empty($data) )
            {
                $translations = array_merge( $translations, $data );
            }

            return $app->json([
                'dir' => $isLanguageRtl ? self::RTL_MODE : self::LTR_MODE,
                'translations' => $translations
            ]);
        });

        return $controllers;
    }
}
