<?php

class CVIDEOUPLOAD_CLASS_ContentProvider
{
    const ENTITY_TYPE = CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY;

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @var CVIDEOUPLOAD_BOL_Service
     */
    private $service;
    
    private function __construct()
    {
        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
    }

    public function init()
    {
        OW::getEventManager()->bind(BOL_ContentService::EVENT_COLLECT_TYPES, [$this, 'onCollectTypes']);
        OW::getEventManager()->bind(BOL_ContentService::EVENT_GET_INFO, [$this, 'onGetInfo']);
        OW::getEventManager()->bind(BOL_ContentService::EVENT_UPDATE_INFO, [$this, 'onUpdateInfo']);
        OW::getEventManager()->bind(BOL_ContentService::EVENT_DELETE, [$this, 'onDelete']);

        OW::getEventManager()->bind(CVIDEOUPLOAD_BOL_Service::EVENT_VIDEO_CONVERTING_EDIT, [$this, 'onAfterVideoEdit']);
        OW::getEventManager()->bind(CVIDEOUPLOAD_BOL_Service::EVENT_VIDEO_DELETE, [$this, 'onVideoDelete']);
        OW::getEventManager()->bind(CVIDEOUPLOAD_BOL_Service::EVENT_VIDEO_EDIT, [$this, 'onAfterVideoEdit']);
    }

    public function onCollectTypes( BASE_CLASS_EventCollector $event )
    {
        $event->add([
            'pluginKey' => CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY,
            'group' => CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY,
            'groupLabel' => $this->service->getLanguageText('content_video_group_label'),
            'entityType' => self::ENTITY_TYPE,
            'entityLabel' => $this->service->getLanguageText('content_video_label'),
            'displayFormat' => 'video_player'
        ]);
    }

    public function onGetInfo( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] == self::ENTITY_TYPE )
        {
            $entityList = $this->service->findByIds( $params['entityIds'] );

            $out = [];

            foreach ( $entityList as $entity )
            {
                /* @var $entity CVIDEOUPLOAD_BOL_Video */

                $videoForPreview = $this->service->getVideoForPreview($entity);
                $info = get_object_vars($videoForPreview);
                $info['authorization'] = $entity->getAuthorization() == CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVED
                    ? BOL_ContentService::STATUS_ACTIVE
                    : BOL_ContentService::STATUS_APPROVAL;
                $out[$entity->id] = $info;
            }

            $event->setData($out);

            return $out;
        }
    }

    public function onUpdateInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != self::ENTITY_TYPE )
        {
            return;
        }

        foreach ( $data as $entityId => $info )
        {
            $statusActive = $info['status'] == BOL_ContentService::STATUS_ACTIVE;
            $status = $statusActive ? CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVED : CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVAL;

            $entityDto = $this->service->findById($entityId);

            if ( !empty($entityDto) )
            {
                /* @var CVIDEOUPLOAD_BOL_Video $entityDto */

//                if ( $entityDto->getAuthorization() != VIDEOI_BOL_VideoDao::STATUS_PENDING )
//                {
//                }

                $entityDto->setAuthorization($status);

                $this->service->saveVideo($entityDto);
            }
        }
    }

    public function onDelete( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != self::ENTITY_TYPE )
        {
            return;
        }

        foreach ( $params['entityIds'] as $entityId )
        {
            $video = $this->service->findById($entityId);

            if ( !empty($video) )
            {
                $this->service->deleteVideo($video);
            }
        }
    }

    public function onAfterVideoEdit( OW_Event $event )
    {
        $params = $event->getParams();
        
        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_CHANGE, [
            'entityType' => self::ENTITY_TYPE,
            'entityId' => $params['id']
        ], [
            'string' => ['key' => CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY . '+video_edited_string']
        ]));
    }

    public function onVideoDelete( OW_Event $event )
    {
        $params = $event->getParams();

        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_BEFORE_DELETE, [
            'entityType' => self::ENTITY_TYPE,
            'entityId' => $params['id']
        ]));
    }
}