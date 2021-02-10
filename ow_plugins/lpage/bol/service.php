<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Sergey Pryadkdkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.lpage.bol
 * @since 1.8.6
 */
class LPAGE_BOL_Service {

    protected $replaceableLangKeys = [
        'base+reset_password_mail_template_content_txt',
        'base+reset_password_mail_template_content_html',
        'base+email_verify_template_html',
        'base+email_verify_template_text',
        'matchmaking+email_html_description'
    ];


    const PLUGIN_KEY = 'lpage';

    /**
     * Class instance
     *
     * @var LPAGE_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return LPAGE_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null)
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @param $prefix
     * @param $key
     * @return false|int|string
     */
    public function isKeyReplaceable( $prefix, $key )
    {
        return array_search($prefix .'+'. $key, $this->replaceableLangKeys);
    }

    /**
     * @param $key
     * @param $vars
     * @return null|string
     */
    public function getKeyAnalog( $key, $vars )
    {
        return OW::getLanguage()->text(self::PLUGIN_KEY, $key, $vars);
    }
}
