<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
final class SKADATE_BOL_LicenceService
{
    /**
     * Singleton instance.
     *
     * @var SKADATE_BOL_LicenceService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SKADATE_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        
    }

    /**
     * @return boolean
     */
    public function validateKey()
    {
        $licenseKey = OW::getConfig()->getValue("skadate", "license_key");

        if ( empty($licenseKey) )
        {
            return false;
        }

        $result = array();

        try
        {
            $result = $this->checkLicenseKey($licenseKey);
        }
        catch ( LogicException $e )
        {
            // if server returned invalid responce, add result to log and exit
            OW::getLogger()->addEntry($e->getMessage());
            return false;
        }

        if ( isset($result["licenseValid"]) )
        {
            OW::getConfig()->saveConfig("skadate", "license_key_valid", (bool) $result["licenseValid"]);
        }

        if ( isset($result["brandingRemoval"]) )
        {
            OW::getConfig()->saveConfig("skadate", "brand_removal", (bool) $result["brandingRemoval"]);
        }

        OW::getConfig()->saveConfig("skadate", "license_info", json_encode($result));
        
        // update license status for all dependent items
        BOL_StorageService::getInstance()->checkUpdates();
        
        return true;
    }

    /**
     * @param string $licenseKey
     * @throws LogicException
     * @return mixed
     */
    public function checkLicenseKey( $licenseKey )
    {
        if ( empty($licenseKey) )
        {
            $licenseKey = "empty_license_key";
        }

        $url = parse_url(OW_URL_HOME);
        $paramsString = json_encode(array("bundleName" => "skadate", "licenseKey" => $licenseKey, "domain" => $url["host"], "dir" => OW_DIR_ROOT));
        $url = BOL_PluginService::UPDATE_SERVER . "validate-bundle-license-key?params=" . urlencode($paramsString);

        $data = file_get_contents($url);
        $result = $data ? json_decode($data, true) : array();

        if ( !isset($result["result"]) || !$result["result"] )
        {
            $message = !empty($result["error"]) ? $result["error"] : "Invalid server response";
            throw new LogicException($message);
        }

        return $result;
    }
}
