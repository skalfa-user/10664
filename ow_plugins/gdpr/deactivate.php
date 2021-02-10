<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

$component = BOL_ComponentAdminService::getInstance();

// desktop widget
$component->deleteWidget('GDPR_CMP_UserDataWidget');
$component->deleteWidget('GDPR_CMP_ThirdPartyWidget');

// mobile widget
$component->deleteWidget('GDPR_MCMP_UserDataWidget');
$component->deleteWidget('GDPR_MCMP_ThirdPartyWidget');
