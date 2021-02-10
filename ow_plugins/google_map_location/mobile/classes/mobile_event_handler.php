<?php

class GOOGLELOCATION_MCLASS_MobileEventHandler
{

    public function __construct()
    {
        
    }

    public function getMapItemCmp( OW_Event $event )
    {
        $params = $event->getParams();
        if ( !empty($params['className']) && $params['className'] == 'GOOGLELOCATION_CMP_MapItem' )
        {
            $event->setData(new GOOGLELOCATION_MCMP_MapItem());
        }
    }

    public function getMapItemListCmp( OW_Event $event )
    {
        $params = $event->getParams();
        if ( !empty($params['className']) && $params['className'] == 'GOOGLELOCATION_CMP_MapUserList' )
        {
            $event->setData(new GOOGLELOCATION_MCMP_MapUserList($params['arguments'][0], $params['arguments'][1], $params['arguments'][2], $params['arguments'][3]));
        }
    }

    public function getUserListFields( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();
        $list = !empty($params['list']) ? $params['list'] : null;
        $userIdList = !empty($params['userIdList']) ? $params['userIdList'] : null;
        
        if ( !in_array($list, array( 'hotlist', 'bookmarks-latest', 'bookmarks-online', 'latest', 'online', 'featured')) )
        {
            return;
        }
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        
        if ( empty($userIdList) )
        {
            return;
        }
        
        $locationList = GOOGLELOCATION_BOL_LocationService::getInstance()->findByUserIdList($userIdList);
        $location = array();

        foreach ( $locationList as $item )
        {
            $location[$item->entityId] = !empty($item) && !empty($item->address) ? $item->address : '';
        }
        
        foreach ( $userIdList as $userId )
        {
            $data[$userId][] = !empty($location[$userId]) ? $location[$userId] : null;
        }
        
        $e->setData($data);
    }

    public function init()
    {
        if ( !GOOGLELOCATION_BOL_LocationService::getInstance()->isApiKeyExists() )
        {
            return;
        }

        OW::getEventManager()->bind('class.get_instance', array($this, 'getMapItemListCmp'));
        OW::getEventManager()->bind('class.get_instance', array($this, 'getMapItemCmp'));
        OW::getEventManager()->bind("base.user_list.get_fields", array($this, 'getUserListFields'));
    }
}