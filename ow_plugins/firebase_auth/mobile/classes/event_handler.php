<?php

/**
 * Copyright (c) 2019, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class FIREBASEAUTH_MCLASS_EventHandler extends FIREBASEAUTH_CLASS_AbstractEventHandler
{
    use OW_Singleton;

    /**
     * Init
     */
    public function init()
    {
        parent::genericInit();
    }
}
