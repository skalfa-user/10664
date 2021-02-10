<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
class GOOGLELOCATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    /**
     * Default action
     */
    public function index()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('googlelocation', 'admin_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_comment');
        
        $configSaveForm = new GoogleLocationConfigForm();
        $this->addForm($configSaveForm);
        
        $fieldList = array();
        
        foreach($configSaveForm->getElements() as $key => $value)
        {
            if( !($value instanceof HiddenField ) )
            {
                $fieldList[$value->getName()] = $value->getName();
            }
        }
        
        $this->assign('elements', $fieldList);

        if ( OW::getRequest()->isPost()  )
        {
            if (isset( $_POST['save'] ) && $configSaveForm->isValid($_POST) )
            {
                $configSaveForm->process();
                OW::getFeedback()->info($language->text('googlelocation', 'settings_updated'));
            }
            else
            {
                $configSaveForm->updateValues();
            }
            $this->redirect();
        }
        
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('googlelocation')->getStaticCssUrl() . 'flags.css');
        
        OW::getEventManager()->trigger(new OW_Event('googlelocation.add_js_lib'));
    }
}

/**
 * Save Configurations form class
 */
class GoogleLocationConfigForm extends Form
{
    const SESSION_DATA = 'location_settings_tmp_data';
    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct('configSaveForm');

        $configs = OW::getConfig()->getValues('googlelocation');
        
        $autofill = OW::getConfig()->getValue('googlelocation', 'auto_fill_location_on_search');
        $displayMap = OW::getConfig()->getValue('googlelocation', 'display_map_on_profile_pages');

        
        $data = $this->getData();
        
        $mapProvider = !empty($data['map_provider']) ? $data['map_provider']: GOOGLELOCATION_BOL_LocationService::getInstance()->getMapProvider();
        $apiKey = empty($data['api_key']) ? $configs['api_key'] : $data['api_key'];
        $bingApiKey = empty($data['bing_api_key']) ? $configs['bing_api_key'] : $data['bing_api_key'];
        $distanseUnits = empty($data['distanse_units']) ? GOOGLELOCATION_BOL_LocationService::getInstance()->getDistanseUnits() : $data['distanse_units'];
        $countryRestriction = empty($data['country_restriction']) ? $configs['country_restriction'] : $data['distanse_units'];
        $autoFillOnSearch = empty($data['auto_fill_location_on_search']) ? ((empty($autofill) || $autofill == '0') ? false : $autofill) : $data['auto_fill_location_on_search'];
        $displayMapOnProdilePages = empty($data['display_map_on_profile_pages']) ? ((empty($displayMap) || $displayMap == '0') ? false : true) : $data['display_map_on_profile_pages'];

        $language = OW::getLanguage();

        

        $options = array(
            GOOGLELOCATION_BOL_LocationService::PROVIDER_GOOGLE => $language->text('googlelocation', 'google'),
            GOOGLELOCATION_BOL_LocationService::PROVIDER_BING => $language->text('googlelocation', 'bing')
        );                
        
        $distanseUnits = new Selectbox('map_provider');
        $distanseUnits->setOptions($options);
        $distanseUnits->setValue($mapProvider);
        $distanseUnits->setHasInvitation(false);
        $distanseUnits->setLabel($language->text('googlelocation', 'map_provider_label'));
        $this->addElement($distanseUnits);
        
        switch( $mapProvider )
        {
            case GOOGLELOCATION_BOL_LocationService::PROVIDER_GOOGLE :
                
                $element = new TextField('api_key');
                $element->setLabel($language->text('googlelocation', 'api_key'));
                $element->setDescription($language->text('googlelocation', 'api_key_description'));
                $element->setValue($apiKey);
                $element->setRequired();

                $validator = new StringValidator(0, 40);
                $validator->setErrorMessage($language->text('googlelocation', 'api_key_too_long'));
                $element->addValidator($validator);

                $validator = new RegExpValidator('/^[^\s]+$/');
                $validator->setErrorMessage($language->text('googlelocation', 'invalid_api_key'));
                $element->addValidator($validator);

                $this->addElement($element);
                
                $restrictions = new Selectbox('country_restriction');
                $restrictions->setLabel($language->text('googlelocation', 'country_restriction_label'));
                $restrictions->setDescription($language->text('googlelocation', 'country_restriction_description'));
                $restrictions->setValue($countryRestriction);
                $restrictions->setOptions($this->countryList);
                $restrictions->setInvitation(OW::getLanguage()->text('googlelocation', 'no_country_restriction'));
                $this->addElement($restrictions);
                
                break;
            case GOOGLELOCATION_BOL_LocationService::PROVIDER_BING :
                
                $element = new TextField('bing_api_key');
                $element->setLabel($language->text('googlelocation', 'bing_api_key_label'));
                $element->setDescription($language->text('googlelocation', 'bing_api_key_description'));
                $element->setValue($bingApiKey);

                $validator = new StringValidator(0, 100);
                $validator->setErrorMessage($language->text('googlelocation', 'api_key_too_long'));
                $element->addValidator($validator);

                $validator = new RegExpValidator('/^[^\s]+$/');
                $validator->setErrorMessage($language->text('googlelocation', 'invalid_api_key'));
                $element->addValidator($validator);

                $element->setRequired();
                $this->addElement($element);
                
                break;
        }
        
        $options = array(
            GOOGLELOCATION_BOL_LocationService::DISTANCE_UNITS_MILES => $language->text('googlelocation', 'miles'),
            GOOGLELOCATION_BOL_LocationService::DISTANCE_UNITS_KM => $language->text('googlelocation', 'kms')
        );                
        
        $distanseUnits = new Selectbox('distanse_units');
        $distanseUnits->setLabel($language->text('googlelocation', 'distanse_units'));
        $distanseUnits->setDescription($language->text('googlelocation', 'distanse_units_description'));
        $distanseUnits->setOptions($options);
        $distanseUnits->setValue(GOOGLELOCATION_BOL_LocationService::getInstance()->getDistanseUnits());
        $distanseUnits->setHasInvitation(false);
        $this->addElement($distanseUnits);
        
        $autoFillLocationOnSearch = new CheckboxField('auto_fill_location_on_search');
        $autoFillLocationOnSearch->setLabel($language->text('googlelocation', 'auto_fill_location_on_search'));
        $autoFillLocationOnSearch->setDescription($language->text('googlelocation', 'auto_fill_location_on_search_description'));
        $autoFillLocationOnSearch->setValue( $autoFillOnSearch );
        $this->addElement($autoFillLocationOnSearch);


        $displayMapOnProdilePagesField = new CheckboxField('display_map_on_profile_pages');
        $displayMapOnProdilePagesField->setLabel($language->text('googlelocation', 'display_map_on_profile_pages_pages'));
        $displayMapOnProdilePagesField->setDescription($language->text('googlelocation', 'display_map_on_profile_pages_description'));
        $displayMapOnProdilePagesField->setValue( $displayMapOnProdilePages );
        $this->addElement($displayMapOnProdilePagesField);


        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('base', 'edit_button'));
        $this->addElement($submit);
    }
    
    public function updateValues()
    {
        $configs = OW::getConfig()->getValues('googlelocation');
        
        $autofill = OW::getConfig()->getValue('googlelocation', 'auto_fill_location_on_search');
        $displayMap = OW::getConfig()->getValue('googlelocation', 'display_map_on_profile_pages');

        $data = array();
        $data['map_provider'] = !empty($_POST['map_provider']) ? $_POST['map_provider']: GOOGLELOCATION_BOL_LocationService::getInstance()->getMapProvider();
        $data['api_key'] = empty($_POST['api_key']) ? $configs['api_key'] : $_POST['api_key'];
        $data['bing_api_key'] = empty($_POST['bing_api_key']) ? $configs['bing_api_key'] : $_POST['bing_api_key'];
        $data['distanse_units'] = empty($_POST['distanse_units']) ? GOOGLELOCATION_BOL_LocationService::getInstance()->getDistanseUnits() : $_POST['distanse_units'];
        $data['country_restriction'] = empty($data['country_restriction']) ? null : $_POST['distanse_units'];
        $data['auto_fill_location_on_search'] = empty($_POST['auto_fill_location_on_search']) ? ((empty($autofill) || $autofill == '0') ? false : $autofill) : $_POST['auto_fill_location_on_search'];
        $data['display_map_on_profile_pages'] = empty($_POST['display_map_on_profile_pages']) ? ((empty($displayMap) || $displayMap == '0') ? false : true) : $_POST['display_map_on_profile_pages'];

        OW::getSession()->start();
        OW::getSession()->set(self::SESSION_DATA, $data);
    }
    
    public function getData()
    {
        $data = OW::getSession()->get(self::SESSION_DATA);
        
        return $data ? $data : array();
    }
    
    /**
     * Updates forum plugin configuration
     *
     * @return boolean
     */
    public function process()
    {
        if ( !OW::getConfig()->configExists('googlelocation', 'country_restriction') )
        {
            OW::getConfig()->addConfig('googlelocation', 'country_restriction', '');
        }

        $values = $this->getValues();
        
        $apiKey = empty($_POST['api_key']) ? '' : $_POST['api_key'];
        $bingApiKey = empty($_POST['bing_api_key']) ? '' : $_POST['bing_api_key'];
        $distanseUnits = empty($_POST['distanse_units']) ? '' : $_POST['distanse_units'];
        $autoFillOnSearch = empty($_POST['auto_fill_location_on_search']) ? false : $_POST['auto_fill_location_on_search'];
        $displayMap = empty($_POST['display_map_on_profile_pages']) ? false : true;
        $mapProvider = empty($_POST['map_provider']) ? GOOGLELOCATION_BOL_LocationService::PROVIDER_GOOGLE : $_POST['map_provider'];
        
        $config = OW::getConfig();
        $config->saveConfig('googlelocation', 'map_provider', $mapProvider);
        
        switch( $mapProvider )
        {
            case GOOGLELOCATION_BOL_LocationService::PROVIDER_GOOGLE :
                $config->saveConfig('googlelocation', 'api_key', $apiKey);
                $config->saveConfig('googlelocation', 'country_restriction', $_POST['country_restriction']);
                break;
            
            case GOOGLELOCATION_BOL_LocationService::PROVIDER_BING :
                $config->saveConfig('googlelocation', 'bing_api_key', $bingApiKey);
                break;
        }

        $config->saveConfig('googlelocation', 'auto_fill_location_on_search', $autoFillOnSearch);
        $config->saveConfig('googlelocation', 'display_map_on_profile_pages', $displayMap);


        GOOGLELOCATION_BOL_LocationService::getInstance()->setDistanseUnits($distanseUnits);

        OW::getSession()->set(self::SESSION_DATA, array() );

        return array('result' => true);
    }
    
    protected $countryList = array(
        'AF' => 'Afghanistan <span class="googlemap_location_flag flag-af"></span>',
        'AX' => 'Åland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia (Plurinational State of)',
        'BQ' => 'Bonaire, Sint Eustatius and Saba',
        'BA' => 'Bosnia and Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cabo Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo',
        'CD' => 'Congo (Democratic Republic of the)',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Côte d\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CW' => 'Curaçao',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands (Malvinas)',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island and McDonald Islands',
        'VA' => 'Holy See',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran (Islamic Republic of)',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea (Democratic People\'s Republic of)',
        'KR' => 'Korea (Republic of)',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Lao People\'s Democratic Republic',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia (the former Yugoslav Republic of)',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia (Federated States of)',
        'MD' => 'Moldova (Republic of)',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestine, State of',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthélemy',
        'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin (French part)',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SX' => 'Sint Maarten (Dutch part)',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'SS' => 'South Sudan',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard and Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan, Province of China',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania, United Republic of',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom of Great Britain and Northern Ireland',
        'US' => 'United States of America',
        'UM' => 'United States Minor Outlying Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela (Bolivarian Republic of)',
        'VN' => 'Viet Nam',
        'VG' => 'Virgin Islands (British)',
        'VI' => 'Virgin Islands (U.S.)',
        'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
        );
}