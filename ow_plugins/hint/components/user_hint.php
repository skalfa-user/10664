<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.components
 */
class HINT_CMP_UserHint extends HINT_CMP_HintBase
{
    public function __construct($userId)
    {
        parent::__construct(HINT_BOL_Service::ENTITY_TYPE_USER, $userId);
    }

    public function getCover()
    {
        $bridge = HINT_CLASS_UheaderBridge::getInstance();
        
        if ( !$bridge->isActive() || !$bridge->isEnabled() )
        {
            return null;
        }
        
        $cover = $bridge->getCoverForUser($this->entityId);

        if ( $cover === null )
        {
            return null;
        }
        
        return $this->prepareCover($cover["src"], $cover["data"]);
    }
    
    protected function getUserInfo()
    {
        $user = array();

        $user['id'] = $this->entityId;

        $onlineUser = BOL_UserService::getInstance()->findOnlineUserById($this->entityId);
        $user['isOnline'] = $onlineUser !== null;

        $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($this->entityId, 2);
        $user['avatar'] =  $avatar ? $avatar : BOL_AvatarService::getInstance()->getDefaultAvatarUrl(2);

        $roles = BOL_AuthorizationService::getInstance()->getRoleListOfUsers(array($this->entityId));

        $user['role'] = !empty($roles[$this->entityId]) ? $roles[$this->entityId] : null;

        $user['displayName'] = BOL_UserService::getInstance()->getDisplayName($this->entityId);
        $user['url'] = BOL_UserService::getInstance()->getUserUrl($this->entityId);
        
        $user["fgift"] = OW::getEventManager()->call("fgift.get_user_gift", array(
            "userId" => $this->entityId
        ));

        return $user;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        OW::getEventManager()->call("uavatars.init_for_node", array(
            "userId" => $this->entityId,
            "node" => "#" . $this->uniqId . " .uhint-avatar-image"
        ));

        $this->assign('user', $this->getUserInfo());
    }
}
