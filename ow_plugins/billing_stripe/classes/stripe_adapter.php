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

/**
 * Stripe billing gateway adapter class.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.billing_stripe.classes
 * @since 1.0
 */
class BILLINGSTRIPE_CLASS_StripeAdapter implements OW_BillingAdapter
{
    const GATEWAY_KEY = 'billingstripe';

    /**
     * @var BOL_BillingService
     */
    private $billingService;

    protected $logger;

    public function __construct()
    {
        $this->billingService = BOL_BillingService::getInstance();
        $this->logger = OW::getLogger('billing_stripe');
    }

    public function prepareSale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    public function verifySale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getFields($params)
     */
    public function getFields( $params = null )
    {
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getOrderFormUrl()
     */
    public function getOrderFormUrl()
    {
        return OW::getRouter()->urlForRoute('billingstripe.order_form');
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getLogoUrl()
     */
    public function getLogoUrl()
    {
        $plugin = OW::getPluginManager()->getPlugin('billingstripe');

        return $plugin->getStaticUrl() . 'img/stripe-logo.svg';
    }

    /**
     * @return string
     */
    public static function getSecretKey()
    {
        $billingService = BOL_BillingService::getInstance();

        $sandboxMode = $billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'sandboxMode');

        if ( $sandboxMode )
        {
            return $billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'testSK');
        }
        else
        {
            return $billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'liveSK');
        }
    }

    /**
     * @return string
     */
    public static function getPublicKey()
    {
        $billingService = BOL_BillingService::getInstance();

        $sandboxMode = $billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'sandboxMode');

        if ( $sandboxMode )
        {
            return $billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'testPK');
        }
        else
        {
            return $billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'livePK');
        }
    }

    public function createToken( $cardDetails = array() )
    {
        if( empty($cardDetails) )
        {
            $this->logger->addEntry('missed_card_details_data', 'create_token_operation_adapter');
            $this->logger->writeLog();

            return null;
        }

        $apiKey = self::getSecretKey();

        if( empty( $apiKey ) )
        {
            $this->logger->addEntry('missed_api_key', 'create_token_operation_adapter');
            $this->logger->writeLog();
        }

        $token = BILLINGSTRIPE_BOL_Service::getInstance()->createToken($cardDetails, $apiKey);

        return $token;
    }

    /**
     * Process application payment
     *
     * @param $sale
     * @param  array $purchaseOptions
     * @return array
     */
    public function processApplicationPayment( $sale, $purchaseOptions)
    {
        if ( empty($sale) )
        {
            return ['status' => 'error', 'message' => 'Sale not found'];
        }

        $result = BILLINGSTRIPE_BOL_Service::getInstance()->processApplicationPayment($sale, $purchaseOptions);

        return $result;
    }

    /**
     * @return array
     */
    public static function getCountryList()
    {
        return array(
            "US" => "United States",
            "GB" => "United Kingdom",
            "CA" => "Canada",
            "DE" => "Germany",
            "NL" => "Netherlands",
            "FR" => "France",
            "AF" => "Afghanistan",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan Republic",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BR" => "Brazil",
            "BN" => "Brunei",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "C2" => "China",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CD" => "Democratic Republic of the Congo",
            "CG" => "Republic of the Congo",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "CI" => "Côte d'Ivoire",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "TP" => "East Timor",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "GA" => "Gabon Republic",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GU" => "Guam",
            "GP" => "Guadeloupe",
            "GT" => "Guatemala",
            "GN" => "Guinea",
            "GW" => "Guinea Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HN" => "Honduras",
            "HK" => "Hong Kong",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KP" => "North Korea",
            "KR" => "South Korea",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Laos",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MK" => "Macedonia",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "MX" => "Mexico",
            "FM" => "Micronesia",
            "MD" => "Moldova",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "CS" => "Montenegro",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "AN" => "Netherlands Antilles",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "NO" => "Norway",
            "MP" => "Northern Mariana Islands",
            "OM" => "Oman",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestine",
            "PA" => "Panama",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn Islands",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RE" => "Reunion",
            "RO" => "Romania",
            "RU" => "Russia",
            "RW" => "Rwanda",
            "KN" => "Saint Kitts and Nevis Anguilla",
            "LC" => "Saint Lucia",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "ST" => "Sao Tome and Principe",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "SR" => "Serbia and Montenegro",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SH" => "St. Helena",
            "SD" => "Sudan",
            "SS" => "Sudan, South",
            "SJ" => "Svalbard and Jan Mayen Islands",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syria",
            "TW" => "Taiwan",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania",
            "TH" => "Thailand",
            "TG" => "Togo",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "AE" => "United Arab Emirates",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VA" => "Vatican City State",
            "VE" => "Venezuela",
            "VN" => "Vietnam",
            "VG" => "Virgin Islands. British",
            "VI" => "Virgin Islands, U.S.",
            "WF" => "Wallis and Futuna Islands",
            "YE" => "Yemen",
            "ZM" => "Zambia"
        );
    }
}
