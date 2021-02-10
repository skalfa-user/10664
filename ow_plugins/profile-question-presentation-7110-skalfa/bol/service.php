<?php

/**
 * Class SKPROFILEQP_BOL_Service
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKPROFILEQP_BOL_Service
{
    const PLUGIN_KEY = 'skprofileqp';

    /**
     * @var BOL_LanguageService
     */
    private $languageService;

    private static $classInstance;

    private function __construct()
    {
        $this->languageService = BOL_LanguageService::getInstance();
    }

    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
}