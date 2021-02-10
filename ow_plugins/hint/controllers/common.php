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
 * @package hint.controllers
 */
class HINT_CTRL_Common extends OW_ActionController
{
    /**
     *
     * @var HINT_CLASS_GroupsBridge
     */
    private $bridge;
    
    public function __construct() 
    {
        parent::__construct();
        
        $this->bridge = HINT_CLASS_GroupsBridge::getInstance();
    }
    
    public function groupFollow()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $_POST['groupId'];

        $groupInfo = $this->bridge->getGroupById($groupId);
        
        if ( empty($groupInfo) )
        {
            exit;
        }

        $eventParams = array(
            'userId' => OW::getUser()->getId(),
            'feedType' => "groups",
            'feedId' => $groupId
        );

        $title = UTIL_String::truncate(strip_tags($groupInfo["title"]), 100, '...');

        $message = null;
        
        if ( $_POST['add'] )
        {
            OW::getEventManager()->call('feed.add_follow', $eventParams);
            $message = OW::getLanguage()->text('groups', 'feed_follow_complete_msg', array('groupTitle' => $title));
        } 
        else
        {
            OW::getEventManager()->call('feed.remove_follow', $eventParams);
            $message = OW::getLanguage()->text('groups', 'feed_unfollow_complete_msg', array('groupTitle' => $title));
        }
        
        $this->out(array(
            "message" => $message
        ));
    }
    
    public function groupJoin()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $groupId = (int) $_POST['groupId'];
        $add = $_POST['add'];
        $userId = OW::getUser()->getId();
        
        $groupInfo = $this->bridge->getGroupById($groupId);
        
        if ( empty($groupInfo) )
        {
            exit;
        }
        
        if ( !$add )
        {
            $this->bridge->deleteUser($groupId, $userId);
            
            $this->out(array(
                "message" => OW::getLanguage()->text('groups', 'leave_complete_message'),
                "invite" => $this->bridge->isCurrentUserCanInvite($groupId)
            ));
        }
        
        $invited = $this->bridge->isInvitedUser($userId, $groupId);

        if ( $groupInfo["whoCanView"] == "invite" && !$invited )
        {
            $this->out(array(
                "error" => OW::getLanguage()->text('hint', 'group_private_join_error'),
                "invite" => $this->bridge->isCurrentUserCanInvite($groupId)
            ));
        }
        
        $this->bridge->markInviteViewed($groupId, $userId);             
        $this->bridge->addUser($groupId, $userId);

        $following = OW::getEventManager()->call('feed.is_follow', array(
            'userId' => OW::getUser()->getId(),
            'feedType' => "groups",
            'feedId' => $groupId
        ));
        
        $this->out(array(
            "message" => OW::getLanguage()->text('groups', 'join_complete_message'),
            "invite" => $this->bridge->isCurrentUserCanInvite($groupId),
            "followed" => $following
        ));
    }
    
    private function out( $out )
    {
        echo json_encode($out);
        
        exit;
    }
}