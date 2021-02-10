<?php

class CVIDEOUPLOAD_CLASS_EventHandler
{
    /**
     * @var CVIDEOUPLOAD_BOL_Service
     */
    private $service;

    /**
     * @var OW_EventManager
     */
    private $eventManager;


    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance instanceof self )
        {
            return self::$classInstance;
        }

        return self::$classInstance = new self();
    }

    public function __construct()
    {
        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
    }

    public function init()
    {
        $this->eventManager = OW::getEventManager();

        $this->eventManager->bind(OW_EventManager::ON_PLUGINS_INIT, [$this, 'afterInit']);
    }

    public function afterInit()
    {
        if ( !OW_DEBUG_MODE )
        {
            $this->eventManager->bind('admin.plugins_list_view', [$this, 'pluginsListView']);
        }

        $this->eventManager->bind('admin.add_auth_labels', [$this, 'addLabels']);
        $this->eventManager->bind('usercredits.on_action_collect', [$this, 'bindCreditActionsCollect']);
        $this->eventManager->bind('admin.add_admin_notification', [$this, 'onCollectAdminNotification']);
        $this->eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, [$this, 'onUserUnRegister']);
        $this->eventManager->bind('content.collect_presenters', [$this, 'collectPresenters']);

        $this->eventManager->bind('class.get_instance.MEMBERSHIP_CTRL_Subscribe',  [$this, 'onPageSubscribe']);
        $this->eventManager->bind('class.get_instance.USERCREDITS_CTRL_BuyCredits',  [$this, 'onPageBuyCredits']);

        $this->eventManager->bind('class.get_instance.BASE_CTRL_Billing',  [$this, 'onPageBilling']);

        $this->eventManager->bind('skmobileapp.get_application_permissions', [$this, 'bindGetApplicationPermissions']);
    }

    public function pluginsListView( OW_Event $event )
    {
        $data = $event->getData();

        if ( !empty($data['active']) || !empty($data['inactive']) )
        {
            if ( isset($data['active'][CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY]['un_url']) )
            {
                $data['active'][CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY]['un_url'] = null;
            }
        }

        $event->setData($data);
    }

    public function collectPresenters( BASE_CLASS_EventCollector $event )
    {
        $presenter = [
            'name'  => 'video_player',
            'class' => 'CVIDEOUPLOAD_CMP_VideoContentPresenter'
        ];

        $event->add($presenter);
    }

    public function onPageSubscribe( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('CVIDEOUPLOAD_CTRL_Subscribe', $params['arguments']) );
    }

    public function onPageBuyCredits( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('CVIDEOUPLOAD_CTRL_BuyCredits', $params['arguments']) );
    }

    public function onPageBilling( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('CVIDEOUPLOAD_CTRL_Billing', $params['arguments']) );
    }

    public function onUserUnRegister( OW_Event $event )
    {
        $params = $event->getParams();

        // TODO мочим юзера из privacy и видео
        if ( !empty($params['userId']) )
        {
            $userId = intval($params['userId']);

            $this->service->deleteUserAndFriends($userId);
            $this->service->deleteVideosByUserId($userId);
        }
    }

    public function bindCreditActionsCollect( BASE_CLASS_EventCollector $eventCollector )
    {
        $credits = new CVIDEOUPLOAD_CLASS_Credits();
        $credits->bindCreditActionsCollect($eventCollector);
    }

    public function addLabels(BASE_CLASS_EventCollector $event)
    {
        $event->add([
            CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY => [
                'label' => OW::getLanguage()->text(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY, 'auth_group_label'),
                'actions' => [
                    CVIDEOUPLOAD_BOL_Service::ACTION_UPLOAD_VIDEO => OW::getLanguage()->text(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY, 'auth_action_label_upload_video'),
                ]
            ]
        ]);
    }

    public function onCollectAdminNotification( ADMIN_CLASS_NotificationCollector $event )
    {
        list($isServerReady) = $this->service->isServerReadyForUploading();

        if ( !$isServerReady )
        {
            $event->add($this->service->getLanguageText('admin_configuration_required_notification', [
                'href' => OW::getRouter()->urlForRoute('cvideoupload.admin-settings')
            ]), ADMIN_CLASS_NotificationCollector::NOTIFICATION_WARNING);
        }
    }

    public function bindGetApplicationPermissions(OW_Event $event)
    {
        $data = $event->getData();

        $data[] = [
            'group' => CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY,
            'plugin' => CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY,
            'actions' => [
                CVIDEOUPLOAD_BOL_Service::ACTION_UPLOAD_VIDEO
            ],
            'tracking_actions' => [
                CVIDEOUPLOAD_BOL_Service::ACTION_UPLOAD_VIDEO => function ($userId, &$isAllowed) {

                    return $this->service->isAddVideoAfterTracking($userId);
                }
            ]
        ];

        $event->setData($data);
    }
}