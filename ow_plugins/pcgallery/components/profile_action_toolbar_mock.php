<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class PCGALLERY_CMP_ProfileActionToolbarMock extends BASE_CMP_ProfileActionToolbar
{
    public function __construct( $userId )
    {
        $this->setVisible(false);
    }

    public function onBeforeRender() {}
}
