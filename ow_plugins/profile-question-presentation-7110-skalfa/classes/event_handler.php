<?php

/**
 * Class SKPROFILEQP_CLASS_EventHandler
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKPROFILEQP_CLASS_EventHandler
{
    private static $classInstance;

    /**
     * @var SKPROFILEQP_BOL_Service
     */
    private $service;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->service = SKPROFILEQP_BOL_Service::getInstance();
    }

    public function init()
    {
        $eventManager = OW::getEventManager();

        if ( !OW_DEBUG_MODE )
        {
            $eventManager->bind('admin.plugins_list_view', [$this, 'pluginsListView']);
        }

        $eventManager->bind('class.get_instance.BASE_CMP_UserViewWidget', [$this, 'onGetComponentUserViewWidget'], 1000);
        $eventManager->bind('class.get_instance.BASE_CMP_UserViewSection', [$this, 'onGetComponentUserViewSection'], 1000);
    }

    public function pluginsListView( OW_Event $event )
    {
        $data = $event->getData();

        if ( !empty($data['active']) || !empty($data['inactive']) )
        {
            if ( isset($data['active'][SKPROFILEQP_BOL_Service::PLUGIN_KEY]['un_url']) )
            {
                $data['active'][SKPROFILEQP_BOL_Service::PLUGIN_KEY]['un_url'] = null;
            }
        }

        $event->setData($data);
    }

    public function onGetComponentUserViewWidget( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('SKPROFILEQP_CMP_UserViewWidget', $params['arguments']) );
    }

    public function onGetComponentUserViewSection( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('SKPROFILEQP_CMP_UserViewSection', $params['arguments']) );
    }
}