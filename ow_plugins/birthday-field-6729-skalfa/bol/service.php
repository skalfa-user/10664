<?php

/**
 * Class BIRTHDAYF_BOL_Service
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 * @package ow_plugins.pluginkey
 * @since 1.8.4
 */
class BIRTHDAYF_BOL_Service
{
    const PLUGIN_KEY = 'birthdayf';

    const QUESTION_NAME = 'birthdate';
    const MATCH_QUESTION_NAME = 'match_age';


    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {

    }

    public function getHiddenQuestion()
    {
        return [
            self::MATCH_QUESTION_NAME,
            self::QUESTION_NAME
        ];
    }
}