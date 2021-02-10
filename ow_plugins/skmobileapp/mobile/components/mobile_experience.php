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
class SKMOBILEAPP_MCMP_MobileExperience extends SKMOBILEAPP_CMP_MobileExperience
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct($paramObj);    
        $this->setTemplate(OW::getPluginManager()->getPlugin('skmobileapp')->getMobileCmpViewDir() . 'mobile_experience.html');
    }
}
