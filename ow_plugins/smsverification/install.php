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

 $dbPrefix = OW_DB_PREFIX;
 OW::getPluginManager()->addPluginSettingsRouteName('smsverification', 'smsverification_admin');

 OW::getConfig()->addConfig('smsverification', 'testAccountSID', '');
 OW::getConfig()->addConfig('smsverification', 'testAuthToken', '');
 OW::getConfig()->addConfig('smsverification', 'accountSID', '');
 OW::getConfig()->addConfig('smsverification', 'authToken', '');
 OW::getConfig()->addConfig('smsverification', 'sandboxMode', '');
 OW::getConfig()->addConfig('smsverification', 'twilioTelNumber', '');
 OW::getConfig()->addConfig('smsverification', 'isTurnedOn', 1);
 OW::getConfig()->addConfig('smsverification', 'mandatorySmsVerification', 0);

 $path = OW::getPluginManager()->getPlugin('smsverification')->getRootDir() . 'langs.zip';
 OW::getLanguage()->importPluginLangs($path, 'smsverification');
 
 $sql = array();
 
 $sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}smsverification_users` (
  `Id` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `isVeryfied` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `country` varchar(50) DEFAULT NULL,
  `countryCode` varchar(10) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
 
 $sql[] = "ALTER TABLE `{$dbPrefix}smsverification_users`
  ADD PRIMARY KEY (`Id`);";


$sql[] = "ALTER TABLE `{$dbPrefix}smsverification_users`
  MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;";
 

 

 
$sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}smsverification_users` (
  `Id` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `isVeryfied` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `country` varchar(50) DEFAULT NULL,
  `countryCode` varchar(10) DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
 
 $sql[] = "ALTER TABLE `{$dbPrefix}smsverification_users`
  ADD PRIMARY KEY (`Id`);";


$sql[] = "ALTER TABLE `{$dbPrefix}smsverification_users`
  MODIFY `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;";




$sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}smsverification_country_phone_code` (
  `id` int(11) NOT NULL,
  `title` varchar(60) CHARACTER SET utf8 DEFAULT NULL,
  `phoneCode` varchar(10) NOT NULL DEFAULT ''
) ENGINE=MyISAM AUTO_INCREMENT=239 DEFAULT CHARSET=utf8;";



$sql[] = "INSERT INTO `{$dbPrefix}smsverification_country_phone_code` (`id`, `title`, `phoneCode`) VALUES
(1, 'Afghanistan', '93'),
(2, 'Albania', '355'),
(3, 'Algeria', '21'),
(4, 'American Samoa', '684'),
(5, 'Andorra', '376'),
(6, 'Angola', '244'),
(7, 'Anguilla', '1-264'),
(8, 'Antigua and Barbuda', '1-268'),
(9, 'Argentina', '54'),
(10, 'Armenia', '374'),
(11, 'Aruba', '297'),
(12, 'Ascension', '247'),
(13, 'Australia', '61'),
(14, 'Australian External Territories', '672'),
(15, 'Austria', '43'),
(16, 'Azerbaijan', '994'),
(17, 'Azores', '351'),
(18, 'Bahamas', '1-242'),
(19, 'Bahrain', '973'),
(20, 'Bangladesh', '880'),
(21, 'Barbados', '1-246'),
(22, 'Belarus', '375'),
(23, 'Belgium', '32'),
(24, 'Belize', '501'),
(25, 'Benin', '229'),
(26, 'Bermuda', '1-441'),
(27, 'Bhutan', '975'),
(28, 'Bolivia', '591'),
(29, 'Bosnia and Herzegovina', '387'),
(30, 'Botswana', '267'),
(31, 'Brazil', '55'),
(32, 'British Virgin Islands', '1-284'),
(33, 'Brunei', '673'),
(34, 'Bulgaria', '359'),
(35, 'Burkina Faso', '226'),
(36, 'Burundi', '257'),
(38, 'Cambodia', '855'),
(39, 'Cameroon', '237'),
(40, 'Cape Verde', '238'),
(41, 'Cayman Islands', '1-345'),
(42, 'Central African Republic', '236'),
(43, 'Chad', '235'),
(44, 'Chile', '56'),
(45, 'China', '86'),
(46, 'Christmas Island', '672'),
(47, 'Cocos Islands', '672'),
(48, 'Colombia', '57'),
(49, 'Commonwealth of the Northern M', '1-670'),
(50, 'Comoros and Mayotte Island', '269'),
(51, 'Congo', '242'),
(52, 'Democratic Republic (ex. Zaire)', '243'),
(53, 'Cook Islands', '682'),
(54, 'Costa Rica', '506'),
(55, 'Croatia', '385'),
(56, 'Cuba', '53'),
(57, 'Cyprus', '357'),
(58, 'Czech Republic', '420'),
(59, 'Denmark', '45'),
(60, 'Diego Garcia', '246'),
(61, 'Djibouti', '253'),
(62, 'Dominica', '1-767'),
(63, 'Dominican Republic', '1-809'),
(64, 'East Timor', '62'),
(65, 'Ecuador', '593'),
(66, 'Egypt', '20'),
(67, 'El Salvador', '503'),
(68, 'Equatorial Guinea', '240'),
(69, 'Eritrea', '291'),
(70, 'Estonia', '372'),
(71, 'Ethiopia', '251'),
(72, 'Faeroe Islands', '298'),
(73, 'Falkland Islands', '500'),
(74, 'Fiji', '679'),
(75, 'Finland', '358'),
(76, 'France', '33'),
(77, 'French Antilles', '590'),
(78, 'French Guiana', '594'),
(79, 'French Polynesia', '689'),
(80, 'Gabonese Republic', '241'),
(81, 'Gambia', '220'),
(82, 'Georgia', '995'),
(83, 'Germany', '49'),
(84, 'Ghana', '233'),
(85, 'Gibraltar', '350'),
(86, 'Greece', '30'),
(87, 'Greenland', '299'),
(88, 'Grenada', '1-473'),
(89, 'Guam', '671'),
(90, 'Guatemala', '502'),
(91, 'Guinea', '224'),
(92, 'Guinea-Bissau', '245'),
(93, 'Guyana', '592'),
(94, 'Haiti', '509'),
(95, 'Honduras', '504'),
(96, 'Hong Kong', '852'),
(97, 'Hungary', '36'),
(98, 'Iceland', '354'),
(99, 'India', '91'),
(100, 'Indonesia', '62'),
(101, 'Iran', '98'),
(102, 'Iraq', '964'),
(103, 'Irish Republic', '353'),
(104, 'Israel', '972'),
(105, 'Italy', '39'),
(106, 'Ivory Coast', '225'),
(107, 'Jamaica', '1-876'),
(108, 'Japan', '81'),
(109, 'Jordan', '962'),
(110, 'Kazakhstan', '7'),
(111, 'Kenya', '254'),
(112, 'Kiribati Republic', '686'),
(113, 'Korea, Dem. Peoples Republic', '850'),
(114, 'Korea Republic', '82'),
(115, 'Kuwait', '965'),
(116, 'Kyrgyzstan', '996'),
(117, 'Laos', '856'),
(118, 'Latvia', '371'),
(119, 'Lebanon', '961'),
(120, 'Lesotho', '266'),
(121, 'Liberia', '231'),
(122, 'Libya', '21'),
(123, 'Liechtenstein', '41'),
(124, 'Lithuania', '370'),
(125, 'Luxembourg', '352'),
(126, 'Macau', '853'),
(127, 'Macedonia', '389'),
(128, 'Madagascar', '261'),
(129, 'Malawi', '265'),
(130, 'Malaysia', '60'),
(131, 'Maldives', '960'),
(132, 'Mali', '223'),
(133, 'Malta', '356'),
(134, 'Marshall Islands', '692'),
(135, 'Martinique', '596'),
(136, 'Mauritania', '222'),
(137, 'Mauritius', '230'),
(138, 'Mexico', '52'),
(139, 'Micronesia', '691'),
(140, 'Monaco', '377'),
(141, 'Mongolia', '976'),
(142, 'Montserrat', '1-664'),
(143, 'Moldova', '373'),
(144, 'Morocco', '212'),
(145, 'Mozambique', '258'),
(146, 'Myanmar', '95'),
(147, 'Namibia', '264'),
(148, 'Nauru', '674'),
(149, 'Nepal', '977'),
(150, 'Netherlands', '31'),
(151, 'Netherlands Antilles', '599'),
(152, 'New Caledonia', '687'),
(153, 'New Zealand', '64'),
(154, 'Nicaragua', '505'),
(155, 'Niger', '227'),
(156, 'Nigeria', '234'),
(157, 'Niue Islands', '683'),
(158, 'Norfolk Island', '672'),
(159, 'Northern Mariana Islands', '670'),
(160, 'Norway', '47'),
(161, 'Oman', '968'),
(162, 'Pakistan', '92'),
(163, 'Palau', '680'),
(164, 'Panama', '507'),
(165, 'Papua New Guinea', '675'),
(166, 'Paraguay', '595'),
(167, 'Peru', '51'),
(168, 'Philippines', '63'),
(169, 'Poland', '48'),
(170, 'Portugal', '351'),
(171, 'Puerto Rico', '1-787'),
(172, 'Qatar', '974'),
(173, 'Republic of San Marino', '378'),
(174, 'Reunion Islands', '262'),
(175, 'Romania', '40'),
(176, 'Russia', '7'),
(177, 'Rwandese Republic', '250'),
(178, 'Saint Helena and Ascension Isl', '247'),
(179, 'Saint Pierre et Miquelon', '508'),
(180, 'San Marino', '39'),
(181, 'Sao Tome e Principe', '239'),
(182, 'Saudi Arabia', '966'),
(183, 'Senegal', '221'),
(184, 'Seychelles', '248'),
(185, 'Sierra Leone', '232'),
(186, 'Singapore', '65'),
(187, 'Slovak Republic', '421'),
(188, 'Slovenia', '386'),
(189, 'Solomon Islands', '677'),
(190, 'Somalia', '252'),
(191, 'South Africa', '27'),
(192, 'Spain', '34'),
(193, 'Sri Lanka', '94'),
(194, 'St. Kitts and Nevis', '1-869'),
(195, 'St. Lucia', '1-758'),
(196, 'St. Vincent and the Grenadines', '1-784'),
(197, 'Sudan', '249'),
(198, 'Suriname', '597'),
(199, 'Svalbard and Jan Mayen Islands', '47'),
(200, 'Swaziland', '268'),
(201, 'Sweden', '46'),
(202, 'Switzerland', '41'),
(203, 'Syria', '963'),
(204, 'Taiwan', '886'),
(205, 'Tajikistan', '992'),
(206, 'Tanzania', '255'),
(207, 'Thailand', '66'),
(208, 'Togolese Republic', '228'),
(209, 'Tokelau', '690'),
(210, 'Tonga', '676'),
(211, 'Trinidad and Tobago', '1-868'),
(212, 'Tunisia', '21'),
(213, 'Turkey', '90'),
(214, 'Turkmenistan', '993'),
(215, 'Turks & Caicos Islands', '1-649'),
(216, 'Tuvalu', '688'),
(217, 'Uganda', '256'),
(218, 'Ukraine', '380'),
(219, 'United Arab Emirates', '971'),
(220, 'United Kingdom', '44'),
(221, 'Uruguay', '598'),
(222, 'US Virgin Islands', '1-340'),
(223, 'USA', '1'),
(224, 'Uzbekistan', '998'),
(225, 'Vanuatu', '678'),
(226, 'Vatican City State', '39'),
(227, 'Venezuela', '58'),
(228, 'Vietnam', '84'),
(229, 'Wallis and Futuna', '681'),
(230, 'Western Sahara', '21'),
(231, 'Western Samoa', '685'),
(232, 'Yemen, North', '967'),
(233, 'Yemen, South', '969'),
(234, 'Yugoslavia', '381'),
(235, 'Zaire', '243'),
(236, 'Zambia', '260'),
(237, 'Zanzibar', '259'),
(238, 'Zimbabwe', '263');";


$sql[] = "ALTER TABLE `{$dbPrefix}smsverification_country_phone_code`
  ADD PRIMARY KEY (`id`)";


$sql[] = "ALTER TABLE `{$dbPrefix}smsverification_country_phone_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=239";

 foreach ( $sql as $q )
{
    try 
    {
        OW::getDbo()->query( $q );
    } 
    catch (Exception $ex) 
    {
        // Log
    }
}