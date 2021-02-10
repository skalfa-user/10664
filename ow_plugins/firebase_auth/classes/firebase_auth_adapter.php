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

class FIREBASEAUTH_CLASS_FirebaseAuthAdapter extends OW_RemoteAuthAdapter
{
    /**
     * Provider prefix
     */
    const PROVIDER_PREFIX = 'f';

    /**
     * Construct
     */
    public function __construct( $providerType, $remoteId )
    {
        parent::__construct($remoteId, self::PROVIDER_PREFIX . '.' . $providerType);
    }
}